<?php   if(!defined('_PATH')) die('Error');

Class M_BitAppPayment
{
    public $db;
    public $rules = array('nal'       => 'Наличные платежи',
                          'bnal'      => 'Безналичные платежи <small>(Для РО)</small>',
                          'bnal_sum'  => 'Показывать остаток на банковских счетах',
                          'vb'        => 'Загрузка выписки',
                          'rules'     => 'Правила авторазбиения <small>(Для РО)</small>',
                          'allrules'  => 'Просмотр всех правил',
                          'dellrules' => 'Удаление правил',
                          'part'      => 'Разбиение платежей <small>(Для РО)</small>',
                          'zp'        => 'Разбиение на зарплату <small>(Все начисления с указанием остатка)</small>',
                          'zp1'       => 'Разбиение на зарплату <small>(Все начисления без указания остатка)</small>',
                          'zp2'       => 'Разбиение на зарплату <small>(Cвои начисления)</small>',
                          'zp3'       => 'Разбиение на зарплату <small>(Свои подчинённые)</small>',
                          'partdel'   => 'Удаление платежа');
    public $safe     = array();
    public $safeHis  = '';
    public $dateUpload = '2023-12-09';

    public function __construct()
    {
        require_once _PATH .'/crest/crest.php';
        $file = (object)  json_decode(file_get_contents(_PATH .'/crest/settings.json'), true);

      // file_put_contents('log.txt', print_r($file, true));

        if($file->expires > 0 && $file->expires <= time() || $file->error_information !== "")
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, 'https://oauth.bitrix.info/oauth/token/?grant_type=refresh_token&client_id='.C_REST_CLIENT_ID.'&client_secret='.C_REST_CLIENT_SECRET.'&refresh_token='.$file->refresh_token);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $result = json_decode(curl_exec($ch));

          //  file_put_contents('log.txt', print_r($result, true));
            
            if(!empty($result->access_token))
            {
                $file->access_token  = $result->access_token;
                $file->expires       = $result->expires;
                $file->refresh_token = $result->refresh_token;
                
                file_put_contents(_PATH .'/crest/settings.json', json_encode($file));
            }
            curl_close($ch);
        }

        
        $this->db = new mysqli('localhost', 'bitrix0', 'ILcJtZ?M6W@uOVgj7zlX', 'sitemanager');

        if($this->db->connect_errno > 0) die('DB Error');

        $this->db->set_charset('utf8');

        $this->db->query("SET sql_mode=''");
        
        if(isset($_REQUEST['AUTH_ID']))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $_REQUEST['DOMAIN'] .'/rest/profile?auth='.$_REQUEST['AUTH_ID']);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $result = json_decode(curl_exec($ch));
            curl_close($ch);

            if(isset($result->result->ID))
            {
                $_SESSION['bitAppPayment']['ID']        = (int)$result->result->ID;
                $_SESSION['bitAppPayment']['UID']       = (int)$result->result->ID;
                $_SESSION['bitAppPayment']['ADMIN']     = ((int)$result->result->ID == 121 || (int)$result->result->ID == 128) ? 1 : (int)$result->result->ADMIN;
                $_SESSION['bitAppPayment']['NAME']      = mb_substr($result->result->NAME,0,1,"UTF-8");
                $_SESSION['bitAppPayment']['LAST_NAME'] = $result->result->LAST_NAME;
                $_SESSION['bitAppPayment']['allPay']    = 0;
                $_SESSION['bitAppPayment']['arTask']    = array();
                $_SESSION['bitAppPayment']['arDeal']    = array();
                $_SESSION['bitAppPayment']['arLead']    = array();
                $_SESSION['bitAppPayment']['arZPlist']  = array();
                $_SESSION['bitAppPayment']['arSalary']  = '<option></option>';

                if($_SESSION['bitAppPayment']['ID'] == 100)
                    $_SESSION['bitAppPayment']['ADMIN'] = 0;
            }
            else
            {
                echo 'У вас нет доступа к приложению(ERR 0)<br><a href="?reload">Перезагрузить</a>';
                exit;
            }
        }
if($_SESSION['bitAppPayment']['ID'] == 100)
    $_SESSION['bitAppPayment']['ADMIN'] = 0;

        if(file_exists(_PATH .'/access/sumAcc'.$_SESSION['bitAppPayment']['ID']))
            $_SESSION['bitAppPayment']['sumAcc'] = (int)file_get_contents(_PATH .'/access/sumAcc'.$_SESSION['bitAppPayment']['ID']);
        elseif(isset($_POST['saveSumAcc']))
            $_SESSION['bitAppPayment']['sumAcc'] = (int)$_POST['saveSumAcc'];
        else
            $_SESSION['bitAppPayment']['sumAcc'] = 1;

            if($_SESSION['bitAppPayment']['ADMIN'] != 1 && file_exists(_PATH .'/access/'.$_SESSION['bitAppPayment']['ID']))
            {
                $a = file_get_contents(_PATH .'/access/'.$_SESSION['bitAppPayment']['ID']);
                $a = unserialize($a);
                $code = $a['code'];
                unset($a['code']);
                
                $sql = $this->db->query("SELECT * FROM `b_user_group` WHERE `GROUP_ID` = 31 AND `USER_ID` = ".(int)$_SESSION['bitAppPayment']['ID']);
                if($sql->num_rows > 0)
                    $_SESSION['bitAppPayment']['allPay'] = 1;

                $hash = md5($_SESSION['bitAppPayment']['ID'].serialize($a));
                #if($code == $hash)
                #{
                    $_SESSION['bitAppPayment']['ACCESS'] = $a;
                    #if($_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1)
                    #{
                        $sql = $this->db->query("SELECT DISTINCT(`IBLOCK_ELEMENT_ID`) FROM `b_iblock_element_property` WHERE `IBLOCK_PROPERTY_ID` = 139 AND `VALUE` = ".(int)$_SESSION['bitAppPayment']['ID']);
                        if($sql->num_rows > 0)
                        {
                            while ($result = $sql->fetch_assoc())
                            {
                                $_SESSION['bitAppPayment']['arZPlist'][] = $result['IBLOCK_ELEMENT_ID'];
                            }
                        }

                        #   Подчинённые

                    #}
                    /*
                    if($_SESSION['bitAppPayment']['ACCESS']['zp'] == 1)
                    {
                        $sql = $this->db->query("SELECT `e`.`ID`, `e2`.`VALUE` AS `date`, `e0`.`VALUE_NUM` AS `ZP`, `e3`.`VALUE_NUM` AS `ZP2`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`
                                                         FROM `b_iblock_element` AS `e`
                                                         LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 138
                                                         LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 258
                                                         LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 142
                                                         LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                                         LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                                         WHERE `e`.`IBLOCK_ID` = 27 AND `e0`.`VALUE_NUM` > 0
                                                         GROUP BY `e`.`ID`
                                                         ORDER BY `u`.`LAST_NAME` ASC, ' ', `u`.`NAME` ASC, `e2`.`VALUE` ASC");
                        if($sql->num_rows > 0)
                        {
                            while($result = $sql->fetch_assoc())
                            {
                                $result['ZP'] = $result['ZP'] + $result['ZP2'];
                                $_SESSION['bitAppPayment']['arSalary'] .= '<option value="'.(int)$result['ID'].'">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'] ?? '').' ('.number_format($result['ZP'], 2, '.', ' ').')</option>';

                            }
                        }
                    }
                    */

                    if($_SESSION['bitAppPayment']['allPay'] != 1)
                    {
                        $sql = $this->db->query("SELECT `ID`
                                                FROM `b_tasks` 
                                                WHERE (`CREATED_BY` = ".(int)$_SESSION['bitAppPayment']['ID']." OR `RESPONSIBLE_ID` = ".(int)$_SESSION['bitAppPayment']['ID']." OR `ID` IN(SELECT DISTINCT(`TASK_ID`) FROM `b_tasks_member` WHERE `USER_ID` = ".(int)$_SESSION['bitAppPayment']['ID'].")) AND `ZOMBIE` = 'N' 
                                                ORDER BY `TITLE` ASC");
                        if($sql->num_rows > 0)
                        {
                            while($result = $sql->fetch_assoc())
                            {
                                $_SESSION['bitAppPayment']['arTask'][$result['ID']] = $result['ID'];
                            }
                        }
                        
                        $sql = $this->db->query("SELECT `ID`
                                                FROM `b_crm_deal` 
                                                WHERE (`ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppPayment']['ID']." OR 
                                                        `ID` IN(SELECT DISTINCT(`ENTITY_ID`) FROM `b_crm_observer` WHERE `USER_ID` = ".(int)$_SESSION['bitAppPayment']['ID']."))");
                        if($sql->num_rows > 0)
                        {
                            while($result = $sql->fetch_assoc())
                            {
                                $_SESSION['bitAppPayment']['arDeal'][$result['ID']] = $result['ID'];
                            }
                        }
                    }
                    
/*
                    $sql = $this->db->query("SELECT `ID`
                                             FROM `b_crm_lead` 
                                             WHERE `OPENED` = 'Y'
                                                AND (`ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppPayment']['ID']." OR 
                                                     `ID` IN(SELECT DISTINCT(`ENTITY_ID`) FROM `b_crm_observer` WHERE `USER_ID` = ".(int)$_SESSION['bitAppPayment']['ID']."))");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $_SESSION['bitAppPayment']['arLead'][$result['ID']] = $result['ID'];
                        }
                    }
*/
                #}
                #else
                #{
                #    echo 'У вас нет доступа к приложению(ERR 1)';
                #    exit;
                #}
            }
            elseif($_SESSION['bitAppPayment']['ADMIN'] != 1)
            {
                echo 'У вас нет доступа к приложению(ERR 2)<br><a href="?reload">Перезагрузить</a>';
                exit;
            }
        
        if(isset($_POST['getQuenue']))
        {
            $this->quenue();
            $this->getQuenue();
            exit;
        }
        
        if($_SESSION['bitAppPayment']['ADMIN'] == 1)
        {
            #   Сотрудники

            $this->userAccess    = array();
            $this->GetUserAccess = array();

            $sql = $this->db->query("SELECT `u`.`ID`, `u`.`LAST_NAME`, `u`.`NAME`, `d`.`VALUE_INT` AS `dept`
                                     FROM  `b_user` AS `u`
                                     LEFT JOIN `b_utm_user` AS `d` ON `d`.`VALUE_ID` = `u`.`ID`
                                     WHERE `u`.`ACTIVE` = 'Y' AND (`u`.`NAME` IS NOT NULL OR `u`.`NAME` != '') AND (`u`.`LAST_NAME` IS NOT NULL OR `u`.`LAST_NAME` != '')
                                     ORDER BY `u`.`LAST_NAME` ASC, `u`.`NAME` ASC");

            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $this->userAccess[$result['ID']] = $result['LAST_NAME'] .' '. $result['NAME'];
                }
            }
/*
            $sql = $this->db->query("SELECT `e`.`ID`, `e2`.`VALUE` AS `date`, `e0`.`VALUE_NUM` AS `ZP`, `e3`.`VALUE_NUM` AS `ZP2`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`
                                                         FROM `b_iblock_element` AS `e`
                                                         LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 138
                                                         LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 258
                                                         LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 142
                                                         LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                                         LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                                         WHERE `e`.`IBLOCK_ID` = 27 AND `e0`.`VALUE_NUM` > 0
                                                         GROUP BY `e`.`ID`
                                                         ORDER BY `u`.`LAST_NAME` ASC, ' ', `u`.`NAME` ASC, `e2`.`VALUE` ASC");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $result['ZP'] = $result['ZP'] + $result['ZP2'];
                    $_SESSION['bitAppPayment']['arSalary'] .= '<option value="'.(int)$result['ID'].'">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'] ?? '').' ('.number_format($result['ZP'], 2, '.', ' ').')</option>';

                }
            }
*/
            $this->GetUserAccess = $this->getAccUpd();
        }

        
        if($_SESSION['bitAppPayment']['ID'] == 26 || $_SESSION['bitAppPayment']['ID'] == 3 || $_SESSION['bitAppPayment']['ID'] == 7 || $_SESSION['bitAppPayment']['ID'] == 121 || $_SESSION['bitAppPayment']['ID'] == 128)
            $this->getSafe();

        $this->PostRequest();

        require_once _PATH .'/tpl/base.php';
    }
    
    public function getAccUpd()
    {
        $ret = array();
            $files = scandir(_PATH .'/access/');
            if(!empty($files))
            {
                $user = array();
                $sql = $this->db->query("SELECT `ID`, CONCAT(`LAST_NAME`, ' ', `NAME`) AS `USER` FROM `b_user`");
                while($result = $sql->fetch_assoc())
                {
                    $user[$result['ID']] = $result['USER'];
                }
                
                foreach($files as $f)
                {
                    if((int)$f > 0)
                    {
                        $a = file_get_contents(_PATH .'/access/'.$f);
                        if(!empty($a))
                        {
                            $a = unserialize($a);
                        
                            if(!empty($a))
                            {
                                foreach($a as $rule => $val)
                                {
                                    if($val == 1)
                                        $ret[$user[$f]][$rule] = $this->rules[$rule];
                                }
                            }
                        }
                    }
                }
            }
        return $ret;
    }

    public function getAccSum($account)
    {
        $return = array('acc' => '',
                        'account' => '',
                        'asum' => 0,
                        'totalsum' => 0);
        
                        $arCloseAcc = array();
                        $arCloseAcc[] = '40702810610000422359';
                        $arCloseAcc[] = '40702810424000014478';
                        $arCloseAcc[] = '40702810848000008123';
                        $arCloseAcc[] = '40802810848000010985';
                        $arCloseAcc[] = '40802810329170003257';
                        $arCloseAcc[] = '40802810129170001488';
                        $arCloseAcc[] = '40702810129170002325';
                        $arCloseAcc[] = '40802910100280003521';
                        $arCloseAcc[] = '40802810600280003487';
                        
                        foreach($account as $id_c => $comp)
                        {
                            $aSum = 0;
                            $return['acc'] .= '<optgroup label="'.htmlspecialchars($comp['COMPANY']).'"></optgroup>';
                            
                            $row = count($comp['ACCOUNT'])+1; 
                            if($row > 1)
                            {
                                $key      = array_keys($comp['ACCOUNT']);
                                $firstAcc = $comp['ACCOUNT'][$key[0]];
    
                                $err = ($firstAcc['BALANCE'] > 0) ? '' : 'class="text-danger"';
                                
                                $accNum = (!in_array($firstAcc['ACCOUNT'], $arCloseAcc)) ? htmlspecialchars($firstAcc['ACCOUNT']) : '<strike class="text-danger">'.htmlspecialchars($firstAcc['ACCOUNT']).'</strike>"';
                                
                                $return['account'] .= '<tr id="'.(int)$key[0].'str" onclick="setAcc('.(int)$key[0].')">
                                                        <td rowspan="'.$row.'">'.htmlspecialchars($comp['COMPANY']).'</td>
                                                        <td>'.$accNum.'</td>
                                                        <td style="font-size: 12px;">'.htmlspecialchars($firstAcc['BANK']).'</td>
                                                        <td style="font-size: 12px;" '.$err.'>'.number_format($firstAcc['BALANCE'], 2, '.', ' ').'</td>
                                                      </tr>';
                                
                                $return['totalsum'] += $firstAcc['BALANCE'];
                                $aSum += $firstAcc['BALANCE'];
                                
                                $return['acc'] .= '<option value="'.(int)$key[0].'">'.htmlspecialchars($firstAcc['ACC']).'</option>';
                                unset($comp['ACCOUNT'][$key[0]]);
                                
                                foreach($comp['ACCOUNT'] as $id_acc => $acc)
                                {
                                    $err = ($acc['BALANCE'] > 0) ? '' : 'class="text-danger"';   
                                    $accNum = (!in_array($acc['ACCOUNT'], $arCloseAcc)) ? htmlspecialchars($acc['ACCOUNT']) : '<strike class="text-danger">'.htmlspecialchars($acc['ACCOUNT']).'</strike>"';
                                    
                                    $return['account'] .= '<tr id="'.(int)$id_acc.'str" onclick="setAcc('.(int)$id_acc.')">
                                                            <td>'.$accNum.'</td>
                                                            <td style="font-size: 12px;">'.htmlspecialchars($acc['BANK']).'</td>
                                                            <td style="font-size: 12px;" '.$err.'>'.number_format($acc['BALANCE'], 2, '.', ' ').'</td>
                                                          </tr>';
                                    
                                    $aSum += $acc['BALANCE'];
                                    $return['totalsum'] += $acc['BALANCE'];
    
                                    $return['acc'] .= '<option value="'.(int)$id_acc.'">'.htmlspecialchars($acc['ACC']).'</option>';
                                }
                            }
                            else
                            {
                                $info = current($comp['ACCOUNT']);
                                $key  = array_keys($comp['ACCOUNT']);
                                $err = ($info['BALANCE'] > 0) ? '' : 'class="text-danger"';
                                $accNum = (!in_array($info['ACCOUNT'], $arCloseAcc)) ? htmlspecialchars($info['ACCOUNT']) : '<strike class="text-danger">'.htmlspecialchars($info['ACCOUNT']).'</strike>"';
                                
                                $return['account'] .= '<tr id="'.(int)$key[0].'str" onclick="setAcc('.(int)$key[0].')">
                                                        <td>'.htmlspecialchars($comp['COMPANY']).'</td>
                                                        <td>'.$accNum.'</td>
                                                        <td style="font-size: 12px;">'.htmlspecialchars($info['BANK']).'</td>
                                                        <td style="font-size: 12px;" '.$err.'>'.number_format($info['BALANCE'], 2, '.', ' ').'</td>
                                                      </tr>';
                                $return['totalsum'] += $info['BALANCE'];    
                                $aSum += $info['BALANCE'];
    
                                $return['acc'] .= '<option value="'.(int)$key[0].'">'.htmlspecialchars($info['ACC']).'</option>';
                            }
                            
                            $return['account'] .= '<tr>
                                                    <td colspan="2" style="text-align:right">Остаток по счёту:</td>
                                                    <td style="font-size: 12px;"><strong>'.number_format($aSum, 2, '.', ' ').'</strong></td>
                                                  </tr>';
                        }
                        
                        $return['account'] .=  '<tr>
                                                <td colspan="3" class="text-right"><strong>Итого:</strong></td>
                                                <td class="text-right"><strong>'.number_format($return['totalsum'], 2, '.', ' ').'</strong></td>
                                              </tr>';
        if(isset($_POST['saveSumAcc']))
        {
            echo $return['account'];
            exit;
        }
        else
            return $return;
    }
    
    public function PostRequest()
    {                    
        if(isset($_POST['saveNalPay']))
        {
          //  file_put_contents("log.txt", "999");      
            $query = array();
            $out = array('status' => 0,
                         'zp1' => '',
                         'zp2' => '',
                         'msg' => '');
            if(($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['nal'] == 1) && ($_POST['type'] == 'task' || $_POST['type'] == 'zp' || $_POST['type'] == 'user' || $_POST['type'] == 'invoice' || $_POST['type'] == 'comand') && $_POST['sum'] != 0 && $_POST['task'] > 0)
            {
                $_POST['sum'] = str_replace(',', '.', $_POST['sum']);
                $_POST['sum'] = ($_POST['typeSum'] == 'r') ? abs($_POST['sum']) * -1 : abs($_POST['sum']);

                if($_POST['sum'] < 0)
                {
                    $sql = $this->db->query("SELECT SUM(`p1`.`VALUE_NUM`) AS `sum`
                                             FROM `b_iblock_element` AS `e`
                                             LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 147
                                             LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 163
                                             LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 160
                                             WHERE `e`.`IBLOCK_ID` = 28 AND `p2`.`VALUE` = ".(int)$_SESSION['bitAppPayment']['ID']." AND `p3`.`VALUE` = '1'");
                    $result = $sql->fetch_assoc();
                    if(abs($_POST['sum']) > $result['sum'])
                    {
                        $out['msg'] = 'Баланс сейфа меньше суммы платежа';
                        echo json_encode($out);
                        exit;
                    }
                }

                if($_POST['type'] == 'task')
                {                                                          
                    $deal = '';

                    $sql = $this->db->query("SELECT `CREATED_BY`, `TITLE` FROM `b_tasks` WHERE `ID` = ".(int)$_POST['task']);
                    if($sql->num_rows > 0)
                    {
                        $sql1 = $this->db->query("SELECT `UF_CRM_TASK` AS `deal` FROM `b_uts_tasks_task` WHERE `VALUE_ID` = ".(int)$_POST['task']." LIMIT 1");
                        if($sql1->num_rows > 0)
                        {
                            $result1 = $sql1->fetch_assoc();
                            $tmpDeal = unserialize($result1['deal']);
                            if(!empty($tmpDeal[0]))
                            {
                                $tmp = explode('_', $tmpDeal[0]);
                                if(!empty($tmp[1]))
                                {
                                    if($tmp[0] == 'D')
                                    {
                                        $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_deal` WHERE `ID` = ".(int)$tmp[1]." AND `STAGE_ID` NOT LIKE '%WON' AND `STAGE_ID` NOT LIKE '%LOSE'");
                                        if($sql2->num_rows > 0)
                                        {
                                            $deal = $tmpDeal[0];
                                        }
                                        else
                                        {
                                            $out['msg'] = 'Не найдена сделка';
                                            echo json_encode($out);
                                            exit;
                                        }
                                    }
                                    else
                                    {
                                        $deal = $tmpDeal[0];
                                    }
                                }
                            }
                        }

                        $result = $sql->fetch_assoc();
                        $query = array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => time(),
                                       'FIELDS' => array(
                                                         'NAME'         => 'Платёж на задачу',
                                                         'PROPERTY_147' => $_POST['sum'],
                                                         'PROPERTY_156' => 0,
                                                         'PROPERTY_164' => $result['CREATED_BY'],
                                                         'PROPERTY_146' => date('d.m.Y'),
                                                         'PROPERTY_162' => date('d.m.Y'),
                                                         'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],
                                                         'PROPERTY_157' => (int)$_POST['task'],
                                                         'PROPERTY_155' => $deal,
                                                         'PROPERTY_159' => '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$_POST['task'].'/" target="_blank">'.$result['TITLE'].'</a>',
                                                         #'PROPERTY_158' => '',
                                                         'PROPERTY_165' => trim($_POST['comment']),
                                                         #'PROPERTY_161' => 0, # ZP
                                                         'PROPERTY_160' => 1));
                    }
                    else
                    {
                        $out['msg'] = 'Задача не найдена';
                    }
                }
                elseif($_POST['type'] == 'user')
                {
                    $query = array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => time(),
                                           'FIELDS' => array(
                                                'NAME'         => 'Выдача наличных сотруднику',
                                                'PROPERTY_147' => $_POST['sum'],
                                                'PROPERTY_156' => 0,
                                                'PROPERTY_164' => (int)$_POST['task'],
                                                'PROPERTY_146' => date('d.m.Y'),
                                                'PROPERTY_162' => date('d.m.Y'),
                                                'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],
                                                'PROPERTY_156' => 0,
                                                'PROPERTY_157' => 0,
                                                #'PROPERTY_155' => 0,
                                                #'PROPERTY_159' => '',
                                                #'PROPERTY_158' => '<a href="/services/lists/28/element/0/'.(int)$_POST['task'].'/" target="_blank">'.htmlspecialchars($result['TITLE']).'</a>',
                                                'PROPERTY_165' => trim($_POST['comment']),
                                                #'PROPERTY_161' => 0, # ZP
                                                'PROPERTY_160' => 1));
                }
                elseif($_POST['type'] == 'zp')
                {
                    $sql = $this->db->query("SELECT `VALUE` FROM `b_iblock_element_property` WHERE `IBLOCK_PROPERTY_ID` = 142 AND `IBLOCK_ELEMENT_ID` = ".(int)$_POST['task']);
                    if($sql->num_rows > 0)
                    {
                        $result = $sql->fetch_assoc();

                        $partSum = 0;
                        $sql = $this->db->query("SELECT `VALUE_NUM` AS `sum` 
                                                 FROM `b_iblock_element_property` 
                                                 WHERE `IBLOCK_PROPERTY_ID` = 138 AND `IBLOCK_ELEMENT_ID` = ".(int)$_POST['task']);
                        if($sql->num_rows > 0)
                        {
                            $result = $sql->fetch_assoc();
                            $partSum += $result['sum'];
                        }
                        
                        $sql = $this->db->query("SELECT `VALUE_NUM` AS `sum` 
                                                 FROM `b_iblock_element_property` 
                                                 WHERE `IBLOCK_PROPERTY_ID` = 258 AND `IBLOCK_ELEMENT_ID` = ".(int)$_POST['task']);
                        if($sql->num_rows > 0)
                        {
                            $result = $sql->fetch_assoc();
                            $partSum += $result['sum'];
                        }
                        
                        $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum`
                                                 FROM `b_iblock_element_property`
                                                 WHERE `IBLOCK_PROPERTY_ID` = 147 AND `IBLOCK_ELEMENT_ID` IN(SELECT `IBLOCK_ELEMENT_ID`
                                                                                                             FROM `b_iblock_element_property`
                                                                                                             WHERE `IBLOCK_PROPERTY_ID` = 161 AND `VALUE` = ".(int)$_POST['task'].")");
                        if($sql->num_rows > 0)
                        {
                            $resSum = $sql->fetch_assoc();
                            $partSum += $resSum['sum'];
                        }

                        if(((abs($_POST['sum']) * -1) + $partSum) >= 0)
                        {
                            $query = array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => time(),
                                            'FIELDS' => array(
                                                'NAME'         => 'Платёж на зарплату',
                                                'PROPERTY_147' => (abs($_POST['sum']) * -1),
                                                'PROPERTY_156' => 0,
                                                'PROPERTY_164' => $result['VALUE'],
                                                'PROPERTY_146' => date('d.m.Y'),
                                                'PROPERTY_162' => date('d.m.Y'),
                                                'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],
                                                'PROPERTY_157' => 0,
                                                #'PROPERTY_155' => 0,
                                                #'PROPERTY_159' => 0,
                                                #'PROPERTY_158' => '',
                                                'PROPERTY_165' => trim($_POST['comment']),
                                                'PROPERTY_161' => (int)$_POST['task'], # ZP
                                                'PROPERTY_160' => 1));
                        }
                        else
                            $out['msg'] = 'Сумма платежа превышает остаток по ЗП';
                    }
                }
                elseif($_POST['type'] == 'comand')
                {
                    $sql = $this->db->query("SELECT `d`.`ID`, `d`.`UF_TASK_ID`, `t`.`TITLE`, `ut`.`UF_CRM_TASK`
                                             FROM `b_crm_dynamic_items_167` AS `d`
                                             LEFT JOIN `b_tasks` AS `t` ON `t`.`ID` = `d`.`UF_TASK_ID`
                                             LEFT JOIN `b_uts_tasks_task` AS `ut` ON `ut`.`VALUE_ID` = `d`.`UF_TASK_ID`
                                             WHERE `d`.`ID` = ".(int)$_POST['task']);
                    if($sql->num_rows > 0)
                    {
                        $result = $sql->fetch_assoc();

                        if(!empty($result['UF_CRM_TASK']))
                        {
                            $tmp  = unserialize($result['UF_CRM_TASK']);
                            $deal = $tmp[0];
                        }
                        else
                            $deal = '';
                        
                        $query = array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => time(),
                                       'FIELDS' => array(
                                                         'NAME'         => 'Платёж на командировку',
                                                         'PROPERTY_147' => $_POST['sum'],
                                                         'PROPERTY_156' => 0,
                                                         #'PROPERTY_164' => $result['CREATED_BY'],
                                                         'PROPERTY_146' => date('d.m.Y'),
                                                         'PROPERTY_162' => date('d.m.Y'),
                                                         'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],
                                                         'PROPERTY_157' => (int)$result['UF_TASK_ID'],
                                                         'PROPERTY_254' => (int)$_POST['task'],
                                                         'PROPERTY_155' => $deal,
                                                         'PROPERTY_159' => '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$result['UF_TASK_ID'].'/" target="_blank">'.$result['TITLE'].'</a>',
                                                         #'PROPERTY_158' => '',
                                                         'PROPERTY_165' => trim($_POST['comment']),
                                                         #'PROPERTY_161' => 0, # ZP
                                                         'PROPERTY_160' => 1));
                    }
                    else
                    {
                        $out['msg'] = 'Командировка не найдена';
                    }
                }
                else
                {
                    $out['msg'] = 'Не найден тип платежа';
                }
                
                if(!empty($query)) 
                {
                    $file = 'log.txt';
                   // $current = "888";
                    file_put_contents($file, print_r($query, true)); 

                    $result = CRest::call('lists.element.add', $query);

                    file_put_contents($file, $result); 

                    $out['status'] = 1;
                    $out['zp1'] = $this->zpList(1);#nalPayZP
                    $out['zp2'] = $this->zpList();#PFpartListZP
                }
                
            }
            echo json_encode($out);
            exit;
        }
        
        if(isset($_POST['getAccUpd']))
        {
            if($_SESSION['bitAppPayment']['ADMIN'] == 1)
            {
                $gau = $this->getAccUpd();
                if(!empty($gau))
                {
                    foreach($gau as $user => $rules)
                    {
                        if(!empty($rules))
                        {
                            echo '<div><strong>'.htmlspecialchars($user).'</strong><br>';
                            foreach($rules as $r => $r_name)
                            {
                                echo '&nbsp;&nbsp;&nbsp;<small>'.($r_name).'</small><br>';
                            }
                            echo '<hr></div>';
                        }
                    }
                }
            }
            
            exit;
        }
        
        #   Загрузка выписки
        if(!empty($_FILES['txt']))
        {
            $this->uploadVB();
            exit;
        }
        
        if(!empty($_POST))
        {
            #   Список ответственныйх за платёж
            if(isset($_POST['getRespPay']) && !empty($_POST['getRespPay']))
            {
                $out = '<option></option>';
                $arOut = array();
                $arAcc = array();
                $payments = array();
                $_POST['getRespPay'] = explode(',', $_POST['getRespPay']);
                foreach($_POST['getRespPay'] as $pay)
                {
                    if($pay > 0)
                        $payments[] = $pay;
                }
                $sql = $this->db->query("SELECT `p`.`VALUE`, `b`.`ID`
                                         FROM `b_iblock_element_property` AS `p`
                                         LEFT JOIN `b_crm_bank_detail` AS `b` ON `b`.`RQ_ACC_NUM` = `p`.`VALUE`
                                         WHERE `p`.`IBLOCK_PROPERTY_ID` = 153 AND `p`.`IBLOCK_ELEMENT_ID` IN(".implode(',', $payments).")");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $arAcc[$result['ID']] = $result['VALUE'];
                    }
                    
                    $tmpAcc = scandir(_PATH .'/access/');
                    if(!empty($tmpAcc))
                    {
                        foreach($tmpAcc as $acc)
                        {
                            if($acc > 0)
                            {
                                $tmpU = file_get_contents(_PATH.'/access/'.$acc);
                                $tmpU = unserialize($tmpU);
                                if(!empty($tmpU['acc']))
                                {
                                    foreach($arAcc as $k => $v)
                                    {
                                        if(in_array($k, $tmpU['acc']))
                                            $arOut[$acc] = $acc;
                                    }
                                }
                            }
                        }

                        if(!empty($arOut))
                        {
                            $sql = $this->db->query("SELECT `ID`, CONCAT(`LAST_NAME`, ' ', `NAME`) AS `user` FROM `b_user` WHERE `ID` IN(".implode(',', $arOut).") ORDER BY `LAST_NAME` ASC, `NAME` ASC");
                            if($sql->num_rows > 0)
                            {
                                while($result = $sql->fetch_assoc())
                                {
                                    $out .= '<option value="'.(int)$result['ID'].'">'.htmlspecialchars($result['user']).'</option>';
                                }
                            }
                        }
                    }
                }
                echo $out;
                exit;
            }

            #   Смена ответственного за платёж
            if(isset($_POST['saveRespPay'], $_POST['resp']))
            {
                $out['status'] = 0;
                $out['payments'] = '';

                if($_POST['resp'] > 0 && !empty($_POST['saveRespPay']))
                {
                    $req = array();
                    $arReq = array();
                    $payments = array();
                    $_POST['saveRespPay'] = explode(',', $_POST['saveRespPay']);
                    foreach($_POST['saveRespPay'] as $pay)
                    {
                        if($pay > 0)
                            $payments[$pay] = $pay;
                    }
                    $sql = $this->db->query("SELECT `e`.`ID`, `e`.`NAME`, `p`.`IBLOCK_PROPERTY_ID`, `p`.`VALUE` 
                                             FROM `b_iblock_element` AS `e`
                                             LEFT JOIN `b_iblock_element_property` AS `p` ON `p`.`IBLOCK_ELEMENT_ID` = `e`.`ID`
                                             WHERE `e`.`IBLOCK_ID` = 28 AND `e`.`ID` IN(".implode(',', $payments).")");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            if($result['IBLOCK_PROPERTY_ID'] == 146 || $result['IBLOCK_PROPERTY_ID'] == 162)
                                $result['VALUE'] = date('d.m.Y', strtotime($result['VALUE']));
                            
                                if($result['IBLOCK_PROPERTY_ID'] == 159)
                                {
                                    $tmp = unserialize($result['VALUE']);
                                    if(isset($tmp['TEXT']))
                                        $result['VALUE'] = $tmp['TEXT'];
                                }

                            $arReq[$result['ID']]['FIELDS']['NAME'] = $result['NAME'];
                            $arReq[$result['ID']]['FIELDS']['PROPERTY_'.$result['IBLOCK_PROPERTY_ID']] = $result['VALUE'];
                            $arReq[$result['ID']]['FIELDS']['PROPERTY_166'] = $_POST['resp'];
                        }

                        if(!empty($arReq))
                        {
                            $i = 0;
                            foreach($arReq as $id => $pay)
                            {
                                $i ++;

                                if($i < 50)
                                {
                                    $req[$id] = array('method' => 'lists.element.update',
                                                      'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id,
                                                      'FIELDS' => $pay['FIELDS']));
                                }
                                else
                                {
                                    CRest::callBatch($req);
                                    $req = array();
                                    $i = 0;
                                }
                            }

                            if(!empty($req))
                            {
                                CRest::callBatch($req);
                                $req = array();
                                $i = 0;
                            }
                        }

                        $out['status'] = 1;
                    }
                    
/*
                    if(!empty($strUpd))
                    {
                        $sql = $this->db->query("SELECT CONCAT(`LAST_NAME`, ' ', `NAME`) AS `user` FROM `b_user` WHERE `ID` = ".(int)$_POST['resp']);
                        if($sql->num_rows > 0)
                        {
                            $result = $sql->fetch_assoc();
                        }
                    }
*/
                }

                echo json_encode($out);
                exit;
            }



            if(isset($_POST['getSafeHistory']))
            {
                if($_SESSION['bitAppPayment']['ID'] == 26 || $_SESSION['bitAppPayment']['ID'] == 3 || $_SESSION['bitAppPayment']['ID'] == 7 || $_SESSION['bitAppPayment']['ID'] == 121 || $_SESSION['bitAppPayment']['ID'] == 128)
                {
                    $this->getSafeHis();
                }
                
                exit;
            }
            
            if(isset($_POST['getListMoreTask']))
            {
                $return = '';
                
                if($_POST['getListMoreTask'] == 2 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1  || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp3'] == 1 ))
                {
                    $return = $this->zpList(2);
                }
                elseif($_POST['getListMoreTask'] == 3)
                {
                    #   Список командировок
                    /*
                    $sql = $this->db->query("SELECT `d`.`ID`, CONCAT(`d`.`TITLE`, '. Ответственный: ', CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`)) AS `TITLE`, `d`.`UF_CRM_6_BEGINDATE`, `d`.`UF_CRM_6_CLOSEDATE`
                                         FROM `b_crm_dynamic_items_167` AS `d`
                                         LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `d`.`ASSIGNED_BY_ID`
                                         WHERE `d`.`STAGE_ID` != 'DT167_7:FAIL' AND `d`.`STAGE_ID` != 'DT167_7:SUCCESS'
                                         ORDER BY `d`.`TITLE` ASC");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $return .= '<div class="objList" onclick="$(\'#PFpartTask2\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($result['TITLE']).' ('.date('d.m.Y', strtotime($result['UF_CRM_6_BEGINDATE'])).'-'.date('d.m.Y', strtotime($result['UF_CRM_6_CLOSEDATE'])).')</div>';
                        }
                    }
                    */
                }
                else
                {
                    $taskList = $this->taskList();
                    if(!empty($taskList))
                    {
                        foreach($taskList as $id_task => $task)
                        {
                            if($task['status'] != 5)
                            {
                                $return .= '<div class="objList" onclick="$(\'#PFpartTask2\').val('.(int)$id_task.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($task['title']).'</div>';
                            }
                            else
                            {
                                $return .= '<div class="objList alert-danger" onclick="$(\'#PFpartTask2\').val('.(int)$id_task.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($task['title']).'</div>';
                            }
                        }
                    }
                }
                echo $return;
                exit;
            }
            
            #   Правила авторазбиения
            if(isset($_POST['addRule']))
            {
                if($_POST['BRPart'] == 1 && $_POST['BRtask'] > 0)
                {
                    $part = $_POST['BRtask'];
                }
                elseif($_POST['BRPart'] == 2 && $_POST['BRzp'] > 0)
                {
                    $part = $_POST['BRzp'];
                }
                else
                {
                    $part = 0;
                }
                
                if($_POST['BRcompany'] > 0 && $part > 0 && (!empty($_POST['BRcontr']) || !empty($_POST['BRnazn'])))
                {
                    $_POST['count'] = 0;
                    
                    if(file_exists(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db'))
                    {
                        $data = file_get_contents(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db');
                        $data = unserialize($data);
                        unset($_POST['addRule']);
                        $data[] = $_POST;
                        file_put_contents(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db', serialize($data));
                        end($data);
                        echo $_SESSION['bitAppPayment']['ID'].key($data);
                    }
                    else
                    {
                        unset($_POST['addRule']);
                        $data = array();
                        $data[] = $_POST;
                        file_put_contents(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db', serialize($data));
                        end($data);
                        echo $_SESSION['bitAppPayment']['ID'].key($data);
                    }
                }
                
                exit;
            }
            
            if(isset($_POST['showRule']))
            {
                $out['status'] = 0;
                $out['user']   = '';
                $out['task']   = '<strong class="text-danger">Не указана задача для разбиения</strong>';
                $out['btn']    = '';
                $out['table']  = '';
                
                $sql = $this->db->query("SELECT `NAME`, `LAST_NAME` FROM `b_user` WHERE `ID` = ".(int)$_POST['u']);
                if($sql->num_rows > 0)
                {
                    $result = $sql->fetch_assoc();
                    $out['user'] = '('.htmlspecialchars($result['LAST_NAME'].' '. $result['NAME']).')';
                }
                
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['rules'] == 1 && $_POST['u'] == $_SESSION['bitAppPayment']['ID'] || $_SESSION['bitAppPayment']['ACCESS']['allrules'] == 1)
                {
                    if(file_exists(_PATH .'/rules/'.(int)$_POST['u'].'.db'))
                    {
                        $file = file_get_contents(_PATH .'/rules/'.(int)$_POST['u'].'.db');
                        $rule = unserialize($file);
                        
                        if(!empty($rule[$_POST['showRule']]['IDs']))
                        {
                            $qArray = $this->getQuenue(2);
                            $out['btn']  = '<button type="button" class="btn btn-sm btn-success" onclick="runAutoPart('.$_POST['showRule'].', '.$_POST['u'].')" data-dismiss="modal">Применить правило</button>';
                            
                            if($rule[$_POST['showRule']]['BRPart'] == 1)
                            {
                                #   Задача
                                $sql = $this->db->query("SELECT `TITLE` FROM `b_tasks` WHERE `ID` = ".(int)$rule[$_POST['showRule']]['BRtask']);
                                if($sql->num_rows > 0)
                                {
                                    $result = $sql->fetch_assoc();
                                    $out['task'] = '<small>Платежи будут разбиты на задачу <strong>'.htmlspecialchars($result['TITLE']).'</strong></small>';
                                }
                            }
                            else
                            {
                                #   Зарплата
                                $sql = $this->db->query("SELECT `e1`.`IBLOCK_ELEMENT_ID`, `u`.`LAST_NAME`, `u`.`NAME`, `e2`.`VALUE` AS `date`
                                                             FROM `b_iblock_element_property` AS `e1`
                                                             LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e1`.`IBLOCK_ELEMENT_ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                                             LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                                             WHERE `e1`.`IBLOCK_ELEMENT_ID` = ".(int)$rule[$_POST['showRule']]['BRzp']." AND `e1`.`IBLOCK_PROPERTY_ID` = 142");
                                if($sql->num_rows > 0)
                                {
                                    $result = $sql->fetch_assoc();
                                    $out['task'] = '<small>Платежи будут разбиты на зарплату <strong>'.htmlspecialchars(date('m/Y', strtotime($result['date'])) .' '.$result['LAST_NAME'].' '.$result['NAME']).'</strong></small>';
                                }
                            }
                            
                            $pay = $this->getPay($rule[$_POST['showRule']]['IDs'], 283);
                            foreach($pay as $res)
                            {
                                $spinner = (!empty($qArray) && in_array($res['ID'], $qArray)) ? '<span class="fa fa-spin fa-spinner"></span>' : '';
                                $sum = ($res['sum'] > 0) ? '<span class="text-success">'.(float)$res['sum'].'</span>' : '<span class="text-danger">'.(float)$res['sum'].'</span>';
                                $out['table'] .= '<tr>
                                                    <td>'.$spinner.'</td>
                                                    <td>'.date('d.m.Y', strtotime($res['date'])).'</td>
                                                    <td>'.htmlspecialchars($res['comp_name']).'</td>
                                                    <td>'.htmlspecialchars($res['contr_name']).'</td>
                                                    <td>'.$sum.'</td>
                                                    <td>'.htmlspecialchars($res['naznach']).'</td>
                                                  </tr>';
                            }
                        }
                    }
                }
                
                echo json_encode($out);
                exit;
            }
            
            if(isset($_POST['getRules']))
            {
                $return['status']  = 0;
                $return['content'] = '';
                
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['allrules'] == 1)
                {
                    $tmp = scandir(_PATH .'/rules');
                    foreach($tmp as $file)
                    {
                        if($file != '.' && $file != '..')
                        {
                            $data = file_get_contents(_PATH .'/rules/'.$file);
                            $data = unserialize($data);
                            $u = str_replace('.db', '', $file);
                            if(!empty($data))
                            {
                                krsort($data);
                                if(is_array($data) && !empty($data))
                                {
                                    $return['status'] = 1;
                                    
                                    $idTask = array();
                                    $idZP = array();
                                    foreach($data as $id => $rule)
                                    {
                                        if($rule['BRPart'] == 1)
                                        {
                                            $idTask[] = (int)$rule['BRtask'];
                                        }
                                        
                                        if($rule['BRPart'] == 2)
                                        {
                                            $idZP[] = (int)$rule['BRzp'];
                                        }
                                    }
                                    
                                    if(!empty($idTask))
                                    {
                                        $sql = $this->db->query("SELECT `ID`, `TITLE` FROM `b_tasks` WHERE `ID` IN(".implode(',', $idTask).")");
                                        if($sql->num_rows > 0)
                                        {
                                            while($result = $sql->fetch_assoc())
                                            {
                                                $tList[$result['ID']] = $result['TITLE'];
                                            }
                                        }
                                    }
                                    if(!empty($idZP))
                                    {
                                        $sql = $this->db->query("SELECT `e1`.`IBLOCK_ELEMENT_ID`, `u`.`LAST_NAME`, `u`.`NAME`, `e2`.`VALUE` AS `date`
                                                                FROM `b_iblock_element_property` AS `e1`
                                                                LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e1`.`IBLOCK_ELEMENT_ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                                                LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                                                WHERE `e1`.`IBLOCK_ELEMENT_ID` IN(".implode(',', $idZP).") AND `e1`.`IBLOCK_PROPERTY_ID` = 142");
                                        if($sql->num_rows > 0)
                                        {
                                            while($result = $sql->fetch_assoc())
                                            {
                                                $zList[$result['IBLOCK_ELEMENT_ID']] = $result;
                                            }
                                        }
                                    }
                                    
                                    foreach($data as $id => $rule)
                                    {
                                        $d1   = (!empty($rule['BRdateStart'])) ? 'от '. date('d.m.Y', strtotime($rule['BRdateStart'])) : '';
                                        $d2   = (!empty($rule['BRdateStop']))  ? 'до '. date('d.m.Y', strtotime($rule['BRdateStop']))  : '';
                                        #$sum  = (!empty($rule['BRsum'])) ? number_format($rule['BRsum'], 2, '.', ' ') : '';
                                        $sum  = (!empty($rule['BRsum'])) ? (float)$rule['BRsum'] : '';
                                        $task = (isset($rule['BRtask']) && isset($tList[$rule['BRtask']])) ? 'Задача: '.htmlspecialchars($tList[$rule['BRtask']]) : '';
                                        
                                        $zp = (isset($rule['BRzp']) && isset($zList[$rule['BRzp']])) ? 'Зарплата: '.date('d.m.Y', strtotime($zList[$rule['BRzp']]['date'])).' / '.htmlspecialchars($zList[$rule['BRzp']]['LAST_NAME'].' '.$zList[$rule['BRzp']]['NAME']) : $zp = '';
                                        
                                        $rBtn = ($rule['count'] > 0) ? '<button id="btnR'.(int)$u.(int)$id.'" class="btn btn-sm btn-success" onclick="showRule('.$id.', '.$u.')">'.$rule['count'].'</button>' : $rBtn = $rule['count'];
                                        
                                        $return['content'] .= '<tr id="BRrule'.$u.$id.'"><td>'.$d1.'<br>'.$d2.'</td>
                                                                <td>'.htmlspecialchars($rule['BRcontr']).'</td>
                                                                <td>'.$sum.'</td>
                                                                <td>'.htmlspecialchars($rule['BRnazn']).'</td>
                                                                <td>'.$task.$zp.'</td>
                                                                <td>'.$rBtn.'</td>';
                                        if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['dellrules'] == 1)
                                            $return['content'] .= '<td><button class="btn btn-sm btn-danger" id="BRbtnDelRule'.$u.$id.'" onclick="delAutoPart('.(int)$id.', '.(int)$u.')"><i class="fa fa-trash"></i></button></td>';
                                        else
                                            $return['content'] .= '<td></td>';
                                        
                                        $return['content'] .= '</tr>';
                                    }
                                }
                            }
                        }
                    }
                }
                elseif($_SESSION['bitAppPayment']['ACCESS']['rules'] == 1)
                {
                    if(file_exists(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db'))
                    {
                        $data = file_get_contents(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db');
                        $data = unserialize($data);
                        krsort($data);
                        if(is_array($data) && !empty($data))
                        {
                            $return['status'] = 1;
                            
                            $idTask = array();
                            $idZP = array();
                            foreach($data as $id => $rule)
                            {
                                if($rule['BRPart'] == 1)
                                {
                                    $idTask[] = (int)$rule['BRtask'];
                                }
                                
                                if($rule['BRPart'] == 2)
                                {
                                    $idZP[] = (int)$rule['BRzp'];
                                }
                            }
                            
                            if(!empty($idTask))
                            {
                                $sql = $this->db->query("SELECT `ID`, `TITLE` FROM `b_tasks` WHERE `ID` IN(".implode(',', $idTask).")");
                                if($sql->num_rows > 0)
                                {
                                    while($result = $sql->fetch_assoc())
                                    {
                                        $tList[$result['ID']] = $result['TITLE'];
                                    }
                                }
                            }
                            if(!empty($idZP))
                            {
                                $sql = $this->db->query("SELECT `e1`.`IBLOCK_ELEMENT_ID`, `u`.`LAST_NAME`, `u`.`NAME`, `e2`.`VALUE` AS `date`
                                                         FROM `b_iblock_element_property` AS `e1`
                                                         LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e1`.`IBLOCK_ELEMENT_ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                                         LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                                         WHERE `e1`.`IBLOCK_ELEMENT_ID` IN(".implode(',', $idZP).") AND `e1`.`IBLOCK_PROPERTY_ID` = 142");
                                if($sql->num_rows > 0)
                                {
                                    while($result = $sql->fetch_assoc())
                                    {
                                        $zList[$result['IBLOCK_ELEMENT_ID']] = $result;
                                    }
                                }
                            }
                            
                            $i = 0;
                            foreach($data as $id => $rule)
                            {
                                $d1   = (!empty($rule['BRdateStart'])) ? 'от '. date('d.m.Y', strtotime($rule['BRdateStart'])) : '';
                                $d2   = (!empty($rule['BRdateStop']))  ? 'до '. date('d.m.Y', strtotime($rule['BRdateStop']))  : '';
                                #$sum  = (!empty($rule['BRsum'])) ? number_format($rule['BRsum'], 2, '.', ' ') : '';
                                $sum  = (!empty($rule['BRsum'])) ? (float)$rule['BRsum'] : '';
                                $task = (isset($rule['BRtask']) && isset($tList[$rule['BRtask']])) ? 'Задача: '.htmlspecialchars($tList[$rule['BRtask']]) : '';
                                
                                $zp = (isset($rule['BRzp']) && isset($zList[$rule['BRzp']])) ? 'Зарплата: '.date('d.m.Y', strtotime($zList[$rule['BRzp']]['date'])).' / '.htmlspecialchars($zList[$rule['BRzp']]['LAST_NAME'].' '.$zList[$rule['BRzp']]['NAME']) : $zp = '';
                                
                                $rBtn = ($rule['count'] > 0) ? '<button id="btnR'.(int)$_SESSION['bitAppPayment']['ID'].(int)$id.'" class="btn btn-sm btn-success" onclick="showRule('.$id.', '.$_SESSION['bitAppPayment']['ID'].')">'.$rule['count'].'</button>' : $rBtn = $rule['count'];
                                
                                $return['content'] .= '<tr id="BRrule'.$_SESSION['bitAppPayment']['ID'].$id.'"><td>'.$d1.'<br>'.$d2.'</td>
                                                           <td>'.htmlspecialchars($rule['BRcontr']).'</td>
                                                           <td>'.$sum.'</td>
                                                           <td>'.htmlspecialchars($rule['BRnazn']).'</td>
                                                           <td>'.$task.$zp.'</td>
                                                           <td>'.$rBtn.'</td>';
                                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['dellrules'] == 1)
                                    $return['content'] .= '<td><button class="btn btn-sm btn-danger" id="BRbtnDelRule'.$_SESSION['bitAppPayment']['ID'].$id.'" onclick="delAutoPart('.(int)$id.', '.(int)$_SESSION['bitAppPayment']['ID'].')"><i class="fa fa-trash"></i></button></td>';
                                else
                                    $return['content'] .= '<td></td>';
                                
                                $return['content'] .= '</tr>';
                            }
                        }
                    }
                }
                
                echo json_encode($return);
                exit;
            }
            
            if(isset($_POST['delRule']))
            {
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['dellrules'] == 1)
                {
                    if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['allrules'] == 1)
                    {
                        if(file_exists(_PATH .'/rules/'.(int)$_POST['u'].'.db'))
                        {
                            $data = file_get_contents(_PATH .'/rules/'.(int)$_POST['u'].'.db');
                            $data = unserialize($data);
                            unset($data[$_POST['delRule']]);
                            file_put_contents(_PATH .'/rules/'.(int)$_POST['u'].'.db', serialize($data));
                            echo 1;
                        }
                    }
                    else
                    {
                        if(file_exists(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db'))
                        {
                            $data = file_get_contents(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db');
                            $data = unserialize($data);
                            unset($data[$_POST['delRule']]);
                            file_put_contents(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db', serialize($data));
                            echo 1;
                        }
                    }
                }
                
                exit;
            }
            
            #   Запуск правила
            if(isset($_POST['runRule'], $_POST['u']))
            {
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['rules'] == 1 && $_POST['u'] == $_SESSION['bitAppPayment']['ID'] || $_SESSION['bitAppPayment']['ACCESS']['allrules'] == 1)
                {
                    if(file_exists(_PATH .'/rules/'.(int)$_POST['u'].'.db'))
                    {
                        $file = file_get_contents(_PATH .'/rules/'.(int)$_POST['u'].'.db');
                        $rule = unserialize($file);
                        
                        if(!empty($rule[$_POST['runRule']]['IDs']))
                        {
                            echo 1;
                            
                            $zp         = 0;
                            $deal       = 0;
                            $task       = 0;
                            $task_link  = '';
                            
                            if($rule[$_POST['runRule']]['BRPart'] == 1)
                            {
                                #   Задача
                                $sql = $this->db->query("SELECT `b`.`ID`, `b`.`TITLE`, `ut`.`UF_CRM_TASK`
                                                         FROM `b_tasks` AS `b`
                                                         LEFT JOIN `b_uts_tasks_task` AS `ut` ON `ut`.`VALUE_ID` = `b`.`ID`
                                                         WHERE `b`.`ID` = ".(int)$rule[$_POST['runRule']]['BRtask']);
                                if($sql->num_rows > 0)
                                {
                                    $result = $sql->fetch_assoc();
                                    $task = $result['ID'];
                                    $task_link = '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$result['ID'].'/">'.htmlspecialchars($result['TITLE']).'</a>';
                                    
                                    if(!empty($result['UF_CRM_TASK']))
                                    {
                                        $tmp = unserialize($result['UF_CRM_TASK']);
                                        if(!empty($tmp[0]))
                                        {
                                            $deal = $tmp[0];
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $zp = (int)$rule[$_POST['runRule']]['BRzp'];
                            }

                            $pay = $this->getPay($rule[$_POST['runRule']]['IDs'], 283);

                            #if($task > 0)
                            #{
                                $query = array();
                                foreach($pay as $payment)
                                {
                                    if(count($query) < 50)
                                    {
                                        $query[] = array('method' => 'lists.element.update',
                                             'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $payment['ID'],
                                                               'FIELDS' => array(
                                                                    'NAME'         => $payment['NAME'],
                                                                    'PROPERTY_149' => $payment['pp'],                       #   ПП
                                                                    'PROPERTY_146' => $payment['date'],                     #   Дата
                                                                    'PROPERTY_147' => $payment['sum'],                      #   Сумма
                                                                    'PROPERTY_152' => $payment['company'],                  #   Компания
                                                                    'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                                                    'PROPERTY_151' => trim($payment['contr_name']),         #   Контрагент
                                                                    'PROPERTY_154' => trim($payment['naznach']),            #   Назначение платежа
                                                                    'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                                                    'PROPERTY_155' => (($zp > 0) ? '' : $deal),                               #   Сделка
                                                                    'PROPERTY_156' => 0,                                   #   ID счёта
                                                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                    'PROPERTY_157' => (($zp > 0) ? 0 : $task),            #   Задача
                                                                    'PROPERTY_159' => (($zp > 0) ? '' : $task_link),                               #   Ссылка на задачу
                                                                    'PROPERTY_158' => '',                                  #   Ссылка на счёт
                                                                    'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                                                    'PROPERTY_161' => (($zp > 0) ? $zp : 0),                                   #   Зарплата
                                                                    'PROPERTY_165' => 'Правило № '.$_POST['runRule'].'-'.$_POST['u'],                 #   комментарий
                                                                    'PROPERTY_166' => $payment['card'],                 #   ответственный за карту
                                                                    'PROPERTY_260' => $payment['contr2'],
                                                                    'PROPERTY_420' => $payment['nds'],
                                                               )));
                                    }
                                    else
                                    {
                                        CRest::callBatch($query);
                                        $query = array();
                                    }
                                }
                            #}
                            
                            if(!empty($query))
                            {
                                CRest::callBatch($query);
                                $query = array();
                            }
                        }
                    }
                }
                
                exit;
            }
            
            #   Сохранение доступов пользователя
            if(isset($_POST['setAccessUsr']))
            {
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 && !empty($_POST['user']))
                {
                    $users = explode(',', $_POST['user']);
                    
                    $access['nal']       = (isset($_POST['nal']))       ? 1 : 0;
                    $access['bnal']      = (isset($_POST['bnal']))      ? 1 : 0;
                    $access['bnal_sum']  = (isset($_POST['bnal_sum']))  ? 1 : 0;
                    $access['vb']        = (isset($_POST['vb']))        ? 1 : 0;
                    $access['rules']     = (isset($_POST['rules']))     ? 1 : 0;
                    $access['allrules']  = (isset($_POST['allrules']))  ? 1 : 0;
                    $access['dellrules'] = (isset($_POST['dellrules'])) ? 1 : 0;
                    $access['part']      = (isset($_POST['part']))      ? 1 : 0;
                    $access['zp']        = (isset($_POST['zp']))        ? 1 : 0;
                    $access['zp1']       = (isset($_POST['zp1']))       ? 1 : 0;
                    $access['zp2']       = (isset($_POST['zp2']))       ? 1 : 0;
                    $access['zp3']       = (isset($_POST['zp3']))       ? 1 : 0;
                    $access['partdel']   = (isset($_POST['partdel']))   ? 1 : 0;
                    $access['quenue']    = (isset($_POST['quenue']))    ? 1 : 0;
                    
                    #   Счета
                    $acc = array();
                    if(isset($_POST['bnal']))
                    {
                        $acc = explode(',', $_POST['account']);
                    }
                    
                    $access['acc'] = $acc;
                    if(!empty($users))
                    {
                        foreach($users as $usr)
                        {
                            unset($access['code']);
                            $access['code'] = md5($usr.serialize($access));
                            file_put_contents(_PATH .'/access/'.$usr, serialize($access));
                        }
                        echo 1;
                    }
                }
                
                exit;
            }
            
            #   Счета в таблицу доступа
            if(isset($_POST['getAccessAcc']))
            {
                $acc = $this->getAcc();
                
                if(!empty($acc))
                {
                    if(isset($_POST['usr']) && $_POST['usr'] > 0)
                    {
                        $out = array();
                        if(file_exists(__DIR__.'/access/'.(int)$_POST['usr']))
                        {
                            $accFile = file_get_contents(__DIR__.'/access/'.(int)$_POST['usr']);
                            $accFile = unserialize($accFile);
                            
                            $out['acc'] = '<label><input type="checkbox" onclick="setAllAcc(this)"><small> Отметить все</small></label><br><table style="font-size:12px;">';
                            foreach($acc as $comp)
                            {
                                $out['acc'] .= '<tr><td style="background-color:#eee">'.htmlspecialchars($comp['COMPANY']).'</td></tr>';
                                if(!empty($comp['ACCOUNT']))
                                {
                                    foreach($comp['ACCOUNT'] as $id_acc => $acc)
                                    {
                                        $checked = (in_array($id_acc, $accFile['acc'])) ? 'checked="checked"' : '';
                                        $out['acc'] .= '<tr><td><label><input type="checkbox" '.$checked.' class="accList" name="AccAcc'.$id_acc.'" value="'.$id_acc.'">&nbsp;&nbsp;&nbsp;'.$acc['ACCOUNT'].'&nbsp;&nbsp;&nbsp;'.htmlspecialchars($acc['BANK']).'</label></td></tr>';
                                    }
                                }
                            }
                            $out['acc'] .= '</table>';
                            
                            $out['nal']       = ($accFile['nal'] == 1) ? 1 : 0;
                            $out['bnal']      = ($accFile['bnal'] == 1) ? 1 : 0;
                            $out['bnal_sum']  = ($accFile['bnal_sum'] == 1) ? 1 : 0;
                            $out['vb']        = ($accFile['vb'] == 1) ? 1 : 0;
                            $out['rules']     = ($accFile['rules'] == 1) ? 1 : 0;
                            $out['allrules']  = ($accFile['allrules'] == 1) ? 1 : 0;
                            $out['dellrules'] = ($accFile['dellrules'] == 1) ? 1 : 0;
                            $out['part']      = ($accFile['part'] == 1) ? 1 : 0;
                            $out['zp']        = ($accFile['zp'] == 1) ? 1 : 0;
                            $out['zp1']       = (isset($accFile['zp1']) && $accFile['zp1'] == 1) ? 1 : 0;
                            $out['zp2']       = ($accFile['zp2'] == 1) ? 1 : 0;
                            $out['zp3']       = (isset($accFile['zp3']) && $accFile['zp3'] == 1) ? 1 : 0;
                            $out['partdel']   = ($accFile['partdel'] == 1) ? 1 : 0;
                            $out['quenue']    = ($accFile['quenue'] == 1) ? 1 : 0;
                        }
                        else
                        {
                            $out['acc'] = '<label><input type="checkbox" onclick="setAllAcc(this)"><small> Отметить все</small></label><br><table style="font-size:12px;">';
                            foreach($acc as $comp)
                            {
                                $out['acc'] .= '<tr><td style="background-color:#eee">'.htmlspecialchars($comp['COMPANY']).'</td></tr>';
                                if(!empty($comp['ACCOUNT']))
                                {
                                    foreach($comp['ACCOUNT'] as $id_acc => $acc)
                                    {
                                        $out['acc'] .= '<tr><td><label><input type="checkbox" class="accList" name="AccAcc'.$id_acc.'" value="'.$id_acc.'">&nbsp;&nbsp;&nbsp;'.$acc['ACCOUNT'].'&nbsp;&nbsp;&nbsp;'.htmlspecialchars($acc['BANK']).'</label></td></tr>';
                                    }
                                }
                            }
                            $out['acc'] .= '</table>';
                            
                            $out['nal']       = 0;
                            $out['bnal']      = 0;
                            $out['bnal_sum']  = 0;
                            $out['vb']        = 0;
                            $out['rules']     = 0;
                            $out['allrules']  = 0;
                            $out['dellrules'] = 0;
                            $out['part']      = 0;
                            $out['zp']        = 0;
                            $out['zp1']       = 0;
                            $out['zp2']       = 0;
                            $out['zp3']       = 0;
                            $out['partdel']   = 0;
                            $out['quenue']    = 0;
                        }
                        
                        echo json_encode($out);
                        exit;
                        
                    }
                    else
                    {
                        $out = '<label><input type="checkbox" onclick="setAllAcc(this)"><small> Отметить все</small></label><br><table style="font-size:12px;">';
                        foreach($acc as $comp)
                        {
                            $out .= '<tr><td style="background-color:#eee">'.htmlspecialchars($comp['COMPANY']).'</td></tr>';
                            if(!empty($comp['ACCOUNT']))
                            {
                                foreach($comp['ACCOUNT'] as $id_acc => $acc)
                                {
                                    $out .= '<tr><td><label><input type="checkbox" class="accList" name="AccAcc'.$id_acc.'" value="'.$id_acc.'">&nbsp;&nbsp;&nbsp;'.$acc['ACCOUNT'].'&nbsp;&nbsp;&nbsp;'.htmlspecialchars($acc['BANK']).'</label></td></tr>';
                                }
                            }
                        }
                        $out .= '</table>';
                    }
                }
                else
                    $out = '<div class="alert alert-danger">Счета не найдены</div>';
                
                echo $out;
                exit;
            }
            
            #   Удалить разбиение(модальное окно подтверждения)
            if(isset($_POST['removePay']))
            {
                $tmp = $this->getPay($_POST['removePay'], $_POST['type']);
                
                if($_POST['type'] == 283)
                {
                    $date  = (strtotime($tmp['date']) > 0) ? 'Платёж от '.date('d.m.Y', strtotime($tmp['date'])) : '';
                    $pp    = (!empty($tmp['pp'])) ? ' №'.htmlspecialchars($tmp['pp']) : '№ <small style="font-style:italic;color:#777777">без номера</small>';
                    $contr = (!empty($tmp['contr_name'])) ? 'От <strong>'.htmlspecialchars($tmp['contr_name']).'</strong>' : '';
                    $nazn  = (!empty($tmp['naznach']))    ? htmlspecialchars($tmp['naznach']) : '';
                    
                    echo $date .' '. $pp .' ('.number_format((float)$tmp['sum'], 2, '.', ' ').' руб.)<br>'.$contr.'<br>'.$nazn;
                }
                else
                {
                    $date  = (strtotime($tmp['date']) > 0) ? 'Платёж от '.date('d.m.Y', strtotime($tmp['date'])) : '';
                    
                    echo 'Наличный платёж от '.date('d.m.Y', strtotime($tmp['date'])).' на сумму '.number_format((float)$tmp['sum'], 2, '.', ' ').' руб.';
                }
                
                exit;
            }
            
            #   Удалить разбиение
            if(isset($_POST['removePart']))
            {
                $this->delPart($_POST['removePart'], $_POST['PFpayTypeList']);
                echo 1;
                exit;
            }
            
            #   Множественное удаление
            if(isset($_POST['delMorePart'], $_POST['ids']))
            {
                $tmp = explode(',', $_POST['ids']);
                if(!empty($tmp))
                {
                    if($_SESSION['bitAppPayment']['ID'] == 26)
                    {
                        $q = array();
                        foreach($tmp as $id)
                        {
                            $q[] = array('method' => 'lists.element.delete',
                                         'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id));
                        }
                        
                        if(!empty($q))
                            CRest::callBatch($q);
                    }
                }
                
                exit;
            }
            
            #   Сохраняем разбиение
            if(isset($_POST['savePart']))
            {
                $this->savePart($_POST);
                exit;
            }
            
            #   Сохраняем множественное разбиение
            if(isset($_POST['saveMorePart']))
            {
                #elseif(isset($_POST['zp']) && $_POST['zp'] == 3)
                #    $this->saveMoreComand($_POST);
                if(isset($_POST['zp']) && $_POST['zp'] == 2)
                    $this->saveMoreZP($_POST);
                else
                    $this->saveMorePart($_POST);

                exit;
            }
            
            #   Сумма платежа
            if(isset($_POST['getSum']))
            {
                echo $this->getSum($_POST['getSum']);
                exit;
            }
            
            #   Информация о платеже
            if(isset($_POST['getPay']))
            {
                if($_POST['id'] > 0)
                {
                    $out = array('title' => '',
                                 'part'  => '',
                                 'sum'   => '',
                                 'zp'    => '',
                                 'rent'  => 'Информация об аренде не найдена',
                                 'com'   => '',
                                 'cd'    => '',
                                 'inv'   => 'Счета не найдены');
                    
                    if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1)
                    {
                        $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum` FROM `b_iblock_element_property` WHERE `IBLOCK_PROPERTY_ID` = 138");
                        $result = $sql->fetch_assoc();

                        $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum` FROM `b_iblock_element_property` WHERE `IBLOCK_PROPERTY_ID` = 258");
                        $resultP = $sql->fetch_assoc();

                        $sql = $this->db->query("SELECT SUM(`p2`.`VALUE_NUM`) AS `sum` 
                                                FROM `b_iblock_element_property` AS `p1`
                                                LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `p1`.`IBLOCK_ELEMENT_ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 147
                                                WHERE `p1`.`IBLOCK_PROPERTY_ID` = 161 AND `p1`.`VALUE` > 0");
                        $resultPay = $sql->fetch_assoc();
                        $out['cd'] = '<span class="text-danger">Текущая задолженность: '.number_format($result['sum'] + $resultPay['sum'] + $resultP['sum'], 2, '.', ' ').'</span><br>';
                    }

                    #   Безнал
                    if($_POST['type'] == 283 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['bnal'] == 1))
                    {
                        $sql = $this->db->query("SELECT `e`.`ID`, `e0`.`VALUE` AS `pp`, `e1`.`VALUE` AS `date`, `e2`.`VALUE_NUM` AS `sum`, 
                                                        `e3`.`VALUE` AS `contragent`, `e5`.`VALUE` AS `INN`, `e6`.`VALUE` AS `invoice`, `e7`.`VALUE` AS `task_id`, 
                                                        `e8`.`VALUE` AS `ls`, `e9`.`VALUE` AS `comment`, `i`.`ORDER_TOPIC`,`t`.`TITLE` AS `task`,
                                                        CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`, `e10`.`VALUE` AS `id_zp`, `r`.`ID` AS `rent`, `r`.`TITLE` AS `rent_name`,
                                                        CONCAT(`u2`.`LAST_NAME`, ' ', `u2`.`NAME`) AS `rent_user`
                                                 FROM `b_iblock_element` AS `e`
                                                 LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 149
                                                 LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 146
                                                 LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 147
                                                 LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 151
                                                 LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 150
                                                 LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 156
                                                 LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 157
                                                 LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 153
                                                 LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 165
                                                 LEFT JOIN `b_iblock_element_property` AS `e10` ON `e10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e10`.`IBLOCK_PROPERTY_ID` = 161
                                                 LEFT JOIN `b_iblock_element_property` AS `e11` ON `e11`.`IBLOCK_ELEMENT_ID` = `e10`.`VALUE` AND `e11`.`IBLOCK_PROPERTY_ID` = 142
                                                 LEFT JOIN `b_iblock_element_property` AS `e12` ON `e12`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e12`.`IBLOCK_PROPERTY_ID` = 436
                                                 LEFT JOIN `b_tasks`       AS `t` ON `t`.`ID` = `e7`.`VALUE`
                                                 LEFT JOIN `b_user`        AS `u` ON `u`.`ID` = `e11`.`VALUE`
                                                 LEFT JOIN `b_crm_invoice` AS `i` ON `i`.`ID` = `e6`.`VALUE`
                                                 LEFT JOIN `b_crm_dynamic_items_176` AS `r` ON `r`.`ID` = `e12`.`VALUE`
                                                 LEFT JOIN `b_user`        AS `u2` ON `u2`.`ID` = `r`.`ASSIGNED_BY_ID`
                                                 WHERE `e`.`ID` = ".(int)$_POST['id']);
                        if($sql->num_rows > 0)
                        {
                            $result = $sql->fetch_assoc();
                            
                            $wh1[0] = (!empty($result['date'])) ? "`e1`.`VALUE` = '".$result['date']."'" : "(`e1`.`VALUE` = '' OR `e1`.`VALUE` IS NULL)";
                            $wh1[1] = (!empty($result['pp'])) ? "`e0`.`VALUE` = '".$result['pp']."'" : "(`e0`.`VALUE` = '' OR `e0`.`VALUE` IS NULL)";
                            $wh1[2] = (!empty($result['contragent'])) ? "`e3`.`VALUE` = '".trim($this->db->real_escape_string($result['contragent']))."'" : "(`e3`.`VALUE` = '' OR `e3`.`VALUE` IS NULL)";
                            $wh1[3] = (!empty($result['ls'])) ? "`e8`.`VALUE` = '".$result['ls']."'" : "(`e8`.`VALUE` = '' OR `e8`.`VALUE` IS NULL)";
                            
                            $sql2 = $this->db->query("SELECT `e`.`ID`, `e0`.`VALUE` AS `pp`, `e1`.`VALUE` AS `date`, `e2`.`VALUE_NUM` AS `sum`, 
                                                             `e3`.`VALUE` AS `contragent`,`e6`.`VALUE` AS `invoice`, `e7`.`VALUE` AS `task_id`, 
                                                             `e8`.`VALUE` AS `ls`, `i`.`ORDER_TOPIC`, LEFT(`t`.`TITLE`, 60) AS `task`,
                                                             CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`, `e10`.`VALUE` AS `id_zp`,`r`.`ID` AS `rent`, `r`.`TITLE` AS `rent_name`,
                                                             CONCAT(`u2`.`LAST_NAME`, ' ', `u2`.`NAME`) AS `rent_user`
                                                      FROM `b_iblock_element` AS `e`
                                                      LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 149
                                                      LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 146
                                                      LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 147
                                                      LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 151
                                                      LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 156
                                                      LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 157
                                                      LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 153
                                                      LEFT JOIN `b_iblock_element_property` AS `e10` ON `e10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e10`.`IBLOCK_PROPERTY_ID` = 161
                                                      LEFT JOIN `b_iblock_element_property` AS `e11` ON `e11`.`IBLOCK_ELEMENT_ID` = `e10`.`VALUE` AND `e11`.`IBLOCK_PROPERTY_ID` = 142
                                                      LEFT JOIN `b_iblock_element_property` AS `e12` ON `e12`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e12`.`IBLOCK_PROPERTY_ID` = 436
                                                      LEFT JOIN `b_tasks`       AS `t` ON `t`.`ID` = `e7`.`VALUE`
                                                      LEFT JOIN `b_crm_invoice` AS `i` ON `i`.`ID` = `e6`.`VALUE`
                                                      LEFT JOIN `b_user`        AS `u` ON `u`.`ID` = `e11`.`VALUE`
                                                      LEFT JOIN `b_crm_dynamic_items_176` AS `r` ON `r`.`ID` = `e12`.`VALUE`
                                                      LEFT JOIN `b_user`        AS `u2` ON `u2`.`ID` = `r`.`ASSIGNED_BY_ID`
                                                      WHERE ".implode(' AND ', $wh1)."
                                                      ORDER BY `e7`.`VALUE` DESC, `e6`.`VALUE` DESC,`e10`.`VALUE` DESC");
                            if($sql2->num_rows > 0)
                            {
                                while($result2 = $sql2->fetch_assoc())
                                {
                                    if($_SESSION['bitAppPayment']['ADMIN'] == 1)
                                    {
                                        $task = (!empty($result2['task'])) ? ' / Задача: '.$result2['task'] : '';
                                        $inv  = (!empty($result2['invoice'])) ? ' / Счёт: '.$result2['invoice'] : '';
                                        $zp   = (!empty($result2['id_zp'])) ? ' / Зарплата: '.htmlspecialchars($result2['user']) : '';
                                        $rent = (!empty($result2['rent'])) ? ' / Аренда ТС: '.$result2['rent_name'] : '';
                                        #$active = ($result2['ID'] == $_POST['id']) ? 'selected="selected"' : '';
                                        $active = (empty($task) && empty($inv) && empty($zp) && empty($rent)) ? 'selected="selected"' : '';
                                        $color  = (empty($result2['ID']) && empty($result2['ID']) && empty($result2['ID'])) ? 'style="color:red"' : '';

                                        $out['part'] .= '<option '.$color.' value="'.$result2['ID'].'" '.$active.'>'.number_format($result2['sum'], 2, '.', ' ').' руб. '.$task.' '.$rent.' '.$inv.' '.$zp.'</option>';
                                    }
                                    elseif(($_SESSION['bitAppPayment']['allPay'] == 1 || $_SESSION['bitAppPayment']['ID'] == 128 || $_SESSION['bitAppPayment']['ID'] == 96 || $_SESSION['bitAppPayment']['ID'] == 60 || $_SESSION['bitAppPayment']['ID'] == 590) ||
                                            (int)$result2['id_zp'] == 0 && $result2['task_id'] > 0 && (in_array($result2['task_id'], $_SESSION['bitAppPayment']['arTask']) || $_SESSION['bitAppPayment']['ID'] == 109) ||
                                            $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 && $result2['id_zp'] > 0 && in_array($result2['id_zp'], $_SESSION['bitAppPayment']['arZPlist']) ||
                                            $result2['task_id'] == 0 && $result2['id_zp'] == 0 && $result2['invoice'] == 0)
                                    {
                                        $task = (!empty($result2['task'])) ? ' / Задача: '.$result2['task'] : '';
                                        $inv  = (!empty($result2['invoice'])) ? ' / Счёт: '.$result2['invoice'] : '';
                                        $zp   = (!empty($result2['id_zp'])) ? ' / Зарплата: '.htmlspecialchars($result2['user']) : '';
                                        $rent = (!empty($result2['rent'])) ? ' / Аренда ТС: '.$result2['rent_name'] : '';
                                        #$active = ($result2['ID'] == $_POST['id']) ? 'selected="selected"' : '';
                                        $active = (empty($task) && empty($inv) && empty($zp) && empty($rent)) ? 'selected="selected"' : '';
                                        $color  = (empty($result2['ID']) && empty($result2['ID']) && empty($result2['ID'])) ? 'style="color:red"' : '';

                                        $out['part'] .= '<option '.$color.' value="'.$result2['ID'].'" '.$active.'>'.number_format($result2['sum'], 2, '.', ' ').' руб. '.$task.' '.$rent.' '.$inv.' '.$zp.'</option>';
                                    }
                                    elseif($result2['id_zp'] > 0 && in_array($result2['id_zp'], $_SESSION['bitAppPayment']['arZPlist']))
                                    {
                                        $task = (!empty($result2['task'])) ? ' / Задача: '.$result2['task'] : '';
                                        $inv  = (!empty($result2['invoice'])) ? ' / Счёт: '.$result2['invoice'] : '';
                                        $zp   = (!empty($result2['id_zp'])) ? ' / Зарплата: '.htmlspecialchars($result2['user']) : '';
                                        $rent = (!empty($result2['rent'])) ? ' / Аренда ТС: '.$result2['rent_name'] : '';
                                        #$active = ($result2['ID'] == $_POST['id']) ? 'selected="selected"' : '';
                                        $active = (empty($task) && empty($inv) && empty($zp) && empty($rent)) ? 'selected="selected"' : '';
                                        $color  = (empty($result2['ID']) && empty($result2['ID']) && empty($result2['ID'])) ? 'style="color:red"' : '';

                                        $out['part'] .= '<option '.$color.' value="'.$result2['ID'].'" '.$active.'>'.number_format($result2['sum'], 2, '.', ' ').' руб. '.$task.' '.$rent.' '.$inv.' '.$zp.'</option>';
                                    }

                                }
                            }
                            else
                            {
                                if($_SESSION['bitAppPayment']['ADMIN'] == 1)
                                {
                                    $task = (!empty($result['task'])) ? ' / Задача: '.$result['task'] : '';
                                    $inv  = (!empty($result['invoice'])) ? ' / Счёт: '.$result['invoice'] : '';
                                    $zp   = (!empty($result['id_zp'])) ? ' / Зарплата: '.htmlspecialchars($result['user']) : '';
                                    $rent = (!empty($result['rent'])) ? ' / Аренда ТС: '.$result['rent_name'] : '';
                                    #$active = ($result['ID'] == $_POST['id']) ? 'selected="selected"' : '';
                                    $active = (empty($task) && empty($inv) && empty($zp) && empty($rent)) ? 'selected="selected"' : '';
                                    $color = (empty($result['ID']) && empty($result['ID']) && empty($result['ID'])) ? 'style="color:red"' : '';
                                    $out['part'] .= '<option '.$color.' value="'.$result['ID'].'" '.$active.'>'.number_format($result['sum'], 2, '.', ' ').' руб. '.$task.' '.$rent.' '.$inv.' '.$zp.'</option>';
                                }
                                elseif(($_SESSION['bitAppPayment']['ID'] == 128 || $_SESSION['bitAppPayment']['ID'] == 96 || $_SESSION['bitAppPayment']['ID'] == 60 || $_SESSION['bitAppPayment']['ID'] == 590) ||
                                        (int)$result['id_zp'] == 0 && $result['task_id'] > 0 && (in_array($result['task_id'], $_SESSION['bitAppPayment']['arTask']) || $_SESSION['bitAppPayment']['ID'] == 109) ||
                                        $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 && $result['id_zp'] > 0 && in_array($result['id_zp'], $_SESSION['bitAppPayment']['arZPlist']) ||
                                        $result['task_id'] == 0 && $result['id_zp'] == 0 && $result['invoice'] == 0)
                                {
                                    $task = (!empty($result['task'])) ? ' / Задача: '.$result['task'] : '';
                                    $inv  = (!empty($result['invoice'])) ? ' / Счёт: '.$result['invoice'] : '';
                                    $zp   = (!empty($result['id_zp'])) ? ' / Зарплата: '.htmlspecialchars($result['user']) : '';
                                    $rent = (!empty($result['rent'])) ? ' / Аренда ТС: '.$result['rent_name'] : '';
                                    #$active = ($result['ID'] == $_POST['id']) ? 'selected="selected"' : '';
                                    $active = (empty($task) && empty($inv) && empty($zp) && empty($rent)) ? 'selected="selected"' : '';
                                    $color = (empty($result['ID']) && empty($result['ID']) && empty($result['ID'])) ? 'style="color:red"' : '';
                                    $out['part'] .= '<option '.$color.' value="'.$result['ID'].'" '.$active.'>'.number_format($result['sum'], 2, '.', ' ').' руб. '.$task.' '.$rent.' '.$inv.' '.$zp.'</option>';
                                }
                            }
                            
                            $out['title'] = (!empty($result['contragent'])) ? htmlspecialchars($result['contragent']) : '';
                            $out['com']   = (!empty($result['comment'])) ? htmlspecialchars($result['comment']) : '';
                            $out['sum']   = (float)$result['sum'];
                            $out['rent']  = $this->getRentList();
                            $zp = $this->zpList(0, 1);
                            $out['zp']    = $this->zpList();
                            $out['cd'] .= '<span class="text-danger">'.$zp[1].'</span>';

                            if($result['sum'] > 0)
                                $out['inv']   = $this->invList(trim($result['INN']));
                                                 
                            echo json_encode($out);
                            exit;
                        }
                    }
                    
                    #  Нал
                    if($_POST['type'] == 274)
                    {
                        if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['nal'] == 1)
                        {
                            $sql = $this->db->query("SELECT `e`.`ID`, `e`.`NAME`, `e1`.`VALUE` AS `date`, `e3`.`VALUE` AS `deal_id`, `d`.`TITLE` AS `deal`, 
                                                            `e4`.`VALUE` AS `invoice`, `i`.`ORDER_TOPIC`, `e5`.`VALUE` AS `task_id`, `t`.`TITLE` AS `task`,
                                                            `e6`.`VALUE_NUM` AS `sum`, `e8`.`VALUE` AS `comment`, `e`.`TIMESTAMP_X` AS `date_edit`,  
                                                            CONCAT(`u1`.`LAST_NAME`, ' ', LEFT(`u1`.`NAME`, 1),'.', LEFT(`u1`.`SECOND_NAME`, 1),'.') AS `kassir`,
                                                            CONCAT(`u2`.`LAST_NAME`, ' ', LEFT(`u2`.`NAME`, 1),'.', LEFT(`u2`.`SECOND_NAME`, 1),'.') AS `employee`,
                                                            CONCAT(`u3`.`LAST_NAME`, ' ', LEFT(`u3`.`NAME`, 1),'.', LEFT(`u3`.`SECOND_NAME`, 1),'.') AS `salary`, `r`.`ID` AS `rent`, `r`.`TITLE` AS `rent_name`,
                                                            CONCAT(`u4`.`LAST_NAME`, ' ', `u4`.`NAME`) AS `rent_user`
                                                     FROM `b_iblock_element` AS `e`
                                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 146
                                                     LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 164
                                                     LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 155
                                                     LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 156
                                                     LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 157
                                                     LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 147
                                                     LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 163
                                                     LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 165
                                                     LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 161
                                                     LEFT JOIN `b_iblock_element_property` AS `e10` ON `e10`.`IBLOCK_ELEMENT_ID` = `e9`.`VALUE` AND `e10`.`IBLOCK_PROPERTY_ID` = 142
                                                     LEFT JOIN `b_iblock_element_property` AS `e12` ON `e12`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e12`.`IBLOCK_PROPERTY_ID` = 436
                                                     LEFT JOIN `b_tasks`       AS `t` ON `t`.`ID` = `e5`.`VALUE`
                                                     LEFT JOIN `b_crm_deal`    AS `d` ON `d`.`ID` = `e3`.`VALUE`
                                                     LEFT JOIN `b_crm_invoice` AS `i` ON `i`.`ID` = `e4`.`VALUE`
                                                     LEFT JOIN `b_user`        AS `u1` ON `u1`.`ID` = `e7`.`VALUE`
                                                     LEFT JOIN `b_user`        AS `u2` ON `u2`.`ID` = `e2`.`VALUE`
                                                     LEFT JOIN `b_user`        AS `u3` ON `u3`.`ID` = `e10`.`VALUE`
                                                     LEFT JOIN `b_crm_dynamic_items_176` AS `r` ON `r`.`ID` = `e12`.`VALUE`
                                                     LEFT JOIN `b_user`        AS `u4` ON `u4`.`ID` = `r`.`ASSIGNED_BY_ID`
                                                     WHERE `e`.`ID` = ".(int)$_POST['id']);
                            if($sql->num_rows > 0)
                            {
                                $result = $sql->fetch_assoc();
                                
                                $task = (!empty($result['task'])) ? ' / Задача: '.$result['task'] : '';
                                $inv  = (!empty($result['invoice'])) ? ' / Счёт: '.$result['invoice'] : '';
                                $zp   = (!empty($result['id_zp'])) ? ' / Зарплата: '.htmlspecialchars($result['salary']) : '';
                                $rent = (!empty($result['rent'])) ? ' / Аренда ТС: '.$result['rent_name'] : '';
                                $active = ($result['ID'] == $_POST['id']) ? 'selected="selected"' : '';
                                $color = (empty($result['ID']) && empty($result['ID']) && empty($result['ID'])) ? 'style="color:red"' : '';
                                
                                $out['title'] = htmlspecialchars($result['NAME']);
                                $out['part']  = '<option '.$color.' value="'.$result['ID'].'" '.$active.'>'.number_format($result['sum'], 2, '.', ' ').' руб. '.$task.' '.$rent.' '.$inv.' '.$zp.'</option>';
                                $out['sum']   = (float)$result['sum'];
                                $out['zp']    = $this->zpList();
                                $out['rent']  = $this->getRentList();
                                $out['com']   = $result['comment'];
                                $out['inv']   = 'Список пуст';
                                    
                                echo json_encode($out);
                                exit;
                            }
                        }
                    }
                }
                
                exit;
            }
            
            $return = array('account'   => '', 
                            'acc'       => '<option></option>', 
                            'taskList'  => '', 
                            'taskList2' => '', 
                            'taskList3' => '<option></option>', 
                            'taskList4' => '', 
                            'comandList4' => '', 
                            'task'      => '<option></option>', 
                            'zp'        => '<option></option>', 
                            'deal'      => '<option></option>', 
                            'employee'  => '<option></option>', 
                            'invoice'   => '<option></option>', 
                            'company'   => '<option></option>');
            
            if(isset($_POST['saveSumAcc']))
            {
                file_put_contents(_PATH .'/access/sumAcc'.$_SESSION['bitAppPayment']['ID'], (int)$_POST['saveSumAcc']);
                $_SESSION['bitAppPayment']['sumAcc'] = (int)$_POST['saveSumAcc'];
                $account = $this->getAcc();
                $this->getAccSum($account);
                exit;
            }

            #   Данные после загрузки страницы
            if(isset($_POST['loadInfo']))
            {
                #   Период
                $return['year']  = '<option></option>';
                $return['month'] = '<option></option>';
                $return['oper']  = '<option></option>';
                $return['comandList'] = '';
                $return['comandList4'] = '';
                $return['rentList'] = $this->getRentList();
                
                $m['1'] = 'Январь';
                $m['2'] = 'Февраль';
                $m['3'] = 'Март';
                $m['4'] = 'Апрель';
                $m['5'] = 'Май';
                $m['6'] = 'Июнь';
                $m['7'] = 'Июль';
                $m['8'] = 'Август';
                $m['9'] = 'Сентябрь';
                $m['10'] = 'Октябрь';
                $m['11'] = 'Ноябрь';
                $m['12'] = 'Декабрь';
                
                for($i = 1; $i <= 12; $i++)
                {
                    if($i == date('n'))
                        $return['month'] .= '<option value="'.$i.'" selected="selected">'.$m[$i].'</option>';
                    else
                        $return['month'] .= '<option value="'.$i.'">'.$m[$i].'</option>';
                }
                
                $y = range(date('Y'), 2016);
                foreach($y as $year)
                {
                    if(date('Y') == $year)
                        $return['year'] .= '<option selected="selected" value="'.$year.'">'.$year.'</option>';
                    else
                        $return['year'] .= '<option value="'.$year.'">'.$year.'</option>';
                }
                
                #  Список задач
                $taskList = $this->taskList();
                if(!empty($taskList))
                {
                    foreach($taskList as $id_task => $task)
                    {
                        if($task['status'] != 5)
                        {
                            $return['task'] .= '<option value="'.(int)$id_task.'">'. htmlspecialchars($task['title']) .'</option>';
                            $return['taskList'] .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.(int)$id_task.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($task['title']).'</div>';
                            $return['taskList2'] .= '<div class="objList" onclick="$(\'#PFpartTask2\').val('.(int)$id_task.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($task['title']).'</div>';
                            $return['taskList4'] .= '<div class="objList" onclick="$(\'#nalPayTask\').val('.(int)$id_task.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($task['title']).'</div>';
                            $return['taskList3'] .= '<option value="'.(int)$id_task.'">'.htmlspecialchars($task['title']).'</option>';
                        }
                        else
                        {
                            #$return['task'] .= '<option value="'.(int)$id_task.'" style="color: red;display:none;">'. htmlspecialchars($task['title']) .'</option>';
                            $return['taskList'] .= '<div class="objList BitClose" style="display:none" onclick="$(\'#PFpartTask\').val('.(int)$id_task.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')"><i class="text-danger">'.htmlspecialchars($task['title']).'</i></div>';
                            $return['taskList3'] .= '<option value="'.(int)$id_task.'" style="color:red">'.htmlspecialchars($task['title']).'</option>';
                            $return['taskList2'] .= '<div class="objList BitClose" style="display:none" onclick="$(\'#PFpartTask2\').val('.(int)$id_task.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')"><small class="bage alert-danger">закрыта </small> '.htmlspecialchars($task['title']).'</div>';
                            $return['taskList4'] .= '<div class="objList BitClose" onclick="$(\'#nalPayTask\').val('.(int)$id_task.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')"><small class="bage alert-danger">закрыта </small>  '.htmlspecialchars($task['title']).'</div>';
                        }
                    }
                }
                
                #  Список зарплаты
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1)
                {
                    $return['zp'] = $this->zpList(1);
                }
                
                #   Список счетов
                $sql = $this->db->query("SELECT `ID`, `TITLE`, `OPPORTUNITY` 
                                         FROM `b_crm_dynamic_items_159` 
                                         WHERE `STAGE_ID` != 'DT159_1:SUCCESS' AND `STAGE_ID` != 'DT159_1:FAIL' AND `OPPORTUNITY` != 0
                                         ORDER BY `TITLE` ASC");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $return['invoice'] .= '<option value="'.$result['ID'].'">'.$result['OPPORTUNITY'].'руб. '.htmlspecialchars($result['TITLE']).' </option>';
                    }
                }

                #   Список командировок
                $sql = $this->db->query("SELECT `d`.`ID`,  CONCAT(`d`.`TITLE`, ' (', CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`), ')') AS `TITLE`, `d`.`UF_CRM_6_BEGINDATE`, `d`.`UF_CRM_6_CLOSEDATE`
                                         FROM `b_crm_dynamic_items_167` AS `d`
                                         LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `d`.`ASSIGNED_BY_ID`
                                         WHERE `d`.`STAGE_ID` != 'DT167_7:FAIL' AND `d`.`STAGE_ID` != 'DT167_7:SUCCESS'
                                         ORDER BY `d`.`TITLE` ASC");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $dStart = (!empty($result['UF_CRM_6_BEGINDATE'])) ? date('d.m.Y', strtotime($result['UF_CRM_6_BEGINDATE'])) : ' ' ;
                        $dStop  = (!empty($result['UF_CRM_6_CLOSEDATE'])) ? date('d.m.Y', strtotime($result['UF_CRM_6_CLOSEDATE'])) : ' ' ;
                        $return['comandList']  .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($result['TITLE']).' ('.$dStart.'-'.$dStop.')</div>';
                        $return['comandList4'] .= '<div class="objList" onclick="$(\'#nalPayComand\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">  '.htmlspecialchars($result['TITLE']).'</div>';
                    }
                }
                
                #  Список компаний
                $tmp = $this->companyList();
                $return['company']  = $tmp[0];
                $return['company2'] = $tmp[1];
                
                #  Список сделок
                $return['deal'] = $this->dealList();
                
                #   Счета
                $totalSum = 0;
                $account = $this->getAcc();
                if(!empty($account))
                {
                    $accInfo = $this->getAccSum($account);
                    $return['acc'] = $accInfo['acc'];
                    $return['account'] = $accInfo['account'];
                    $totalSum = $accInfo['totalsum'];
                }
                
                #  Список сотрудников
                $return['employee'] = $this->empList();

                #   Список держателей карт
                $return['card'] = '<option></option>';
                $sql = $this->db->query("SELECT `u`.`ID`, `u`.`LAST_NAME`, `u`.`NAME`
                                         FROM `b_iblock_element_property` AS `e`
                                         LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e`.`VALUE`
                                         WHERE `e`.`IBLOCK_PROPERTY_ID` = 166 AND `e`.`VALUE` > 0
                                         GROUP BY `e`.`VALUE`
                                         ORDER BY `u`.`LAST_NAME` ASC, `u`.`NAME` ASC");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $return['card'] .= '<option value="'.$result['ID'].'">'. htmlspecialchars($result['LAST_NAME'] .' '. $result['NAME']) .'</option>';
                    }
                }

                echo json_encode($return);
                exit;
            }
            
            if(isset($_POST['getInfo']))
            {
                $where = $this->buildWhere($_POST);
                $order = (isset($_POST['group']) && $_POST['group'] == 1) ? "`e`.`TIMESTAMP_X` DESC" : "`e1`.`VALUE` DESC";
                $group = (isset($_POST['group']) && $_POST['group'] == 1) ? 1 : 0;
                
                $users = array();
                $sql = $this->db->query("SELECT `ID`, `LAST_NAME`, LEFT(`NAME`, 1) AS `NAME`, LEFT(`SECOND_NAME`, 1) AS `SECOND_NAME` FROM `b_user`");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $users[$result['ID']] = $result['LAST_NAME'] .' '.$result['NAME'].'.'.$result['SECOND_NAME'].'.';
                    }
                }
                
                $taskIDs = $this->getQuenue(2);
                $out  = array();
                $logID = array();
                $arNal = array();
                #   Наличные платежи
                if($_POST['f_part_r'] == 274 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['nal'] == 1))
                {
                    $sql = $this->db->query("SELECT `e`.`ID`, `e`.`NAME`, `e1`.`VALUE` AS `date`, 
                                                    `d`.`ID` AS `deal_id`, `d`.`TITLE` AS `deal`,
                                                    `mtr`.`ID` AS `mtr_id`, `mtr`.`TITLE` AS `mtr`, 
                                                    `cmp`.`ID` AS `cmp_id`, `cmp`.`TITLE` AS `cmpTitle`, 
                                                    `e4`.`VALUE` AS `invoice`, `i`.`TITLE` AS `ORDER_TOPIC`, `e5`.`VALUE` AS `task_id`, `t`.`TITLE` AS `task`,
                                                    `e6`.`VALUE_NUM` AS `sum`, `e8`.`VALUE` AS `comment`, `e`.`TIMESTAMP_X` AS `date_edit`, `l`.`TITLE` AS `lead`, `l`.`ID` AS `lead_id`,  
                                                    `e7`.`VALUE` AS `kassir`, `e2`.`VALUE` AS `employee`, `e9`.`VALUE` AS `zp_id`, `e10`.`VALUE` AS `ZP`, `e12`.`VALUE` AS `comand`,
                                                    `com`.`TITLE` AS `comandTitle`, `com`.`UF_CRM_6_BEGINDATE` AS `comStart`, `com`.`UF_CRM_6_CLOSEDATE` AS `comStop`,
                                                    `e13`.`VALUE` AS `nazn`, `r`.`ID` AS `rent`, `r`.`TITLE` AS `rent_name`,
                                                     CONCAT(`u2`.`LAST_NAME`, ' ', `u2`.`NAME`) AS `rent_user`
                                             FROM `b_iblock_element` AS `e`
                                             LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 146
                                             LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 164
                                             LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 155
                                             LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 156
                                             LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 157
                                             LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 147
                                             LEFT JOIN `b_iblock_element_property` AS `e66` ON `e66`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e66`.`IBLOCK_PROPERTY_ID` = 148
                                             LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 163
                                             LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 165
                                             LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 161
                                             LEFT JOIN `b_iblock_element_property` AS `e10` ON `e10`.`IBLOCK_ELEMENT_ID` = `e9`.`VALUE` AND `e10`.`IBLOCK_PROPERTY_ID` = 142
                                             LEFT JOIN `b_iblock_element_property` AS `e11` ON `e11`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e11`.`IBLOCK_PROPERTY_ID` = 160
                                             LEFT JOIN `b_iblock_element_property` AS `e12` ON `e12`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e12`.`IBLOCK_PROPERTY_ID` = 254
                                             LEFT JOIN `b_iblock_element_property` AS `e13` ON `e13`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e13`.`IBLOCK_PROPERTY_ID` = 154
                                             LEFT JOIN `b_iblock_element_property` AS `e14` ON `e14`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e14`.`IBLOCK_PROPERTY_ID` = 436
                                             LEFT JOIN `b_tasks`       AS `t` ON `t`.`ID` = `e5`.`VALUE`
                                             LEFT JOIN `b_utm_tasks_task` AS `ut` ON `ut`.`VALUE_ID` = `t`.`ID`
                                             LEFT JOIN `b_crm_lead`    AS `l` ON `l`.`ID` = REPLACE(`e3`.`VALUE`, 'L_', '')
                                             LEFT JOIN `b_crm_deal`    AS `d` ON `d`.`ID` = REPLACE(`e3`.`VALUE`, 'D_', '')
                                             LEFT JOIN `b_crm_dynamic_items_133` AS `mtr` ON `mtr`.`ID` = REPLACE(`e3`.`VALUE`, 'T85_', '')
                                             LEFT JOIN `b_crm_company` AS `cmp` ON `cmp`.`ID` = REPLACE(`e3`.`VALUE`, 'CO_', '')
                                             LEFT JOIN `b_crm_dynamic_items_159` AS `i` ON `i`.`ID` = `e4`.`VALUE`
                                             LEFT JOIN `b_crm_dynamic_items_167` AS `com` ON `com`.`ID` = `e12`.`VALUE`
                                             LEFT JOIN `b_crm_dynamic_items_176` AS `r` ON `r`.`ID` = `e14`.`VALUE`
                                             LEFT JOIN `b_user`        AS `u2` ON `u2`.`ID` = `r`.`ASSIGNED_BY_ID`
                                             WHERE ".$where."
                                             GROUP BY `e`.`ID`
                                             ORDER BY ".$order);
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            if($result['kassir'] == $_SESSION['bitAppPayment']['ID'] || $_SESSION['bitAppPayment']['ID'] == 128)
                            {
                                $logID[$result['ID']] = $result['ID'];
                                $arNal[] = $result;
                            }
                            elseif($_SESSION['bitAppPayment']['ADMIN'] != 1 && ($result['task_id'] > 0 || $result['deal_id'] > 0 && (in_array($result['deal_id'], $_SESSION['bitAppPayment']['arDeal']) || $result['zp_id'] > 0)))
                            {
                                if(($_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1) && $result['zp_id'] > 0)
                                {
                                    if($result['kassir'] == $_SESSION['bitAppPayment']['ID'])
                                    {
                                        $logID[$result['ID']] = $result['ID'];
                                        $arNal[] = $result;
                                    }
                                }
                                elseif($_SESSION['bitAppPayment']['allPay'] == 1 || $_SESSION['bitAppPayment']['ID'] == 128 || (int)$result['zp_id'] == 0 && $result['task_id'] > 0 && in_array($result['task_id'], $_SESSION['bitAppPayment']['arTask']) || (int)$result['zp_id'] == 0 && $result['deal_id'] > 0 && in_array($result['deal_id'], $_SESSION['bitAppPayment']['arDeal']))
                                {
                                    if($result['zp_id'] > 0 && $result['kassir'] == $_SESSION['bitAppPayment']['ID'])
                                    {
                                        $logID[$result['ID']] = $result['ID'];
                                        $arNal[] = $result;
                                    }
                                    elseif($result['zp_id'] == 0)
                                    {
                                        $logID[$result['ID']] = $result['ID'];
                                        $arNal[] = $result;
                                    }
                                }
                                elseif(!empty($_SESSION['bitAppPayment']['arZPlist']) && in_array($result['zp_id'], $_SESSION['bitAppPayment']['arZPlist']))
                                {
                                    $logID[$result['ID']] = $result['ID'];
                                    $arNal[] = $result;
                                }
                            }
                            elseif($_SESSION['bitAppPayment']['ADMIN'] == 1 && ($_SESSION['bitAppPayment']['ID'] == 26 || $_SESSION['bitAppPayment']['ID'] == 3 || $_SESSION['bitAppPayment']['ID'] == 7))
                            {
                                $logID[$result['ID']] = $result['ID'];
                                $arNal[] = $result;
                            }
                        }
                        unset($result);
                        
                        #   Лог
                        $logHis = array();
                        if(!empty($logID) && $_SESSION['bitAppPayment']['ADMIN'] == 1)
                        {
                            $sqlHis = $this->db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`, `p2`.`VALUE` AS `id_pay`, `p3`.`VALUE` AS `CRM`, 
                                                               `p4`.`VALUE` AS `id_task`, `p5`.`VALUE` AS `p_inv`, `p6`.`VALUE` AS `r_inv`, `p7`.`VALUE` AS `id_zp`,
                                                               `p8`.`VALUE` AS `id_comand`, `p9`.`VALUE` AS `id_oper`, `p10`.`VALUE` AS `sum`,
                                                               `l`.`TITLE` AS `lead`, `d`.`TITLE` AS `deal`, `m`.`TITLE` AS `mtr`, `t`.`TITLE` AS `task`, `c`.`TITLE` AS `comand`,
                                                               `p`.`TITLE` AS `p_invoice`, `r1`.`TITLE` AS `r_invoice`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `oper`,
                                                               `r`.`ID` AS `rent`, `r`.`TITLE` AS `rent_name`, CONCAT(`u2`.`LAST_NAME`, ' ', `u2`.`NAME`) AS `rent_user`
                                                        FROM `b_iblock_element` AS `e`
                                                        LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 393
                                                        LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 394
                                                        LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 395
                                                        LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = 396
                                                        LEFT JOIN `b_iblock_element_property` AS `p5` ON `p5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p5`.`IBLOCK_PROPERTY_ID` = 397
                                                        LEFT JOIN `b_iblock_element_property` AS `p6` ON `p6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p6`.`IBLOCK_PROPERTY_ID` = 398
                                                        LEFT JOIN `b_iblock_element_property` AS `p7` ON `p7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p7`.`IBLOCK_PROPERTY_ID` = 399
                                                        LEFT JOIN `b_iblock_element_property` AS `p8` ON `p8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p8`.`IBLOCK_PROPERTY_ID` = 400
                                                        LEFT JOIN `b_iblock_element_property` AS `p9` ON `p9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p9`.`IBLOCK_PROPERTY_ID` = 401
                                                        LEFT JOIN `b_iblock_element_property` AS `p10` ON `p10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p10`.`IBLOCK_PROPERTY_ID` = 409
                                                        LEFT JOIN `b_iblock_element_property` AS `p11` ON `p11`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p11`.`IBLOCK_PROPERTY_ID` = 436
                                                        LEFT JOIN `b_crm_lead` AS `l` ON `l`.`ID` = REPLACE(`p3`.`VALUE`, 'L_', '')
                                                        LEFT JOIN `b_crm_deal` AS `d` ON `d`.`ID` = REPLACE(`p3`.`VALUE`, 'D_', '')
                                                        LEFT JOIN `b_crm_dynamic_items_133` AS `m` ON `m`.`ID` = REPLACE(`p3`.`VALUE`, 'T85_', '')
                                                        LEFT JOIN `b_tasks` AS `t` ON `t`.`ID` = `p4`.`VALUE`
                                                        LEFT JOIN `b_crm_dynamic_items_167` AS `c` ON `c`.`ID` = `p8`.`VALUE`
                                                        LEFT JOIN `b_crm_dynamic_items_159` AS `p` ON `p`.`ID` = `p5`.`VALUE`
                                                        LEFT JOIN `b_crm_dynamic_items_128` AS `r1` ON `r1`.`ID` = `p6`.`VALUE`
                                                        LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `p9`.`VALUE`
                                                        LEFT JOIN `b_crm_dynamic_items_176` AS `r` ON `r`.`ID` = `p11`.`VALUE`
                                                        LEFT JOIN `b_user`        AS `u2` ON `u2`.`ID` = `r`.`ASSIGNED_BY_ID`
                                                        WHERE `e`.`IBLOCK_ID` = 94 AND `p2`.`VALUE` IN(".implode(',', $logID).")
                                                        ORDER BY `e`.`ID` DESC");
                            if($sqlHis->num_rows > 0)
                            {
                                while($resultHis = $sqlHis->fetch_assoc())
                                {
                                    $logHis[$resultHis['id_pay']][$resultHis['ID']] = $resultHis;
                                }
                            }
                        }
                        
                        foreach($arNal as $result)
                        {
                            $kassir   = $users[$result['kassir']];
                            $employee = (!empty($users[$result['employee']])) ? $users[$result['employee']] : '';
                            $salary_n = (!empty($result['ZP'])) ? '<br><small class="text-success">ЗП: '.htmlspecialchars($users[$result['ZP']]).'</small>' : '';
                            
                            if($result['deal_id'] > 0)
                                $crm = '<a target="_blank" href="/crm/deal/details/'.(int)$result['deal_id'].'/">Сделка:'.htmlspecialchars($result['deal']).'</a>';
                            elseif($result['lead_id'] > 0)
                                $crm = '<a target="_blank" href="/crm/lead/details/'.(int)$result['lead_id'].'/">ЛИД: '.htmlspecialchars($result['lead']).'</a>';
                            elseif($result['mtr_id'] > 0)
                                $crm = '<a target="_blank" href="/crm/type/133/details/'.(int)$result['mtr_id'].'/">МТР: '.htmlspecialchars($result['mtr']).'</a>';
                            elseif($result['cmp_id'] > 0)
                                $crm = '<a target="_blank" href="/crm/company/details/'.(int)$result['cmp_id'].'/">Компания: '.htmlspecialchars($result['cmpTitle']).'</a>';
                            else
                                $crm = '';
                            
                            $inv_comm = (!empty($result['ORDER_TOPIC'])) ? htmlspecialchars($result['ORDER_TOPIC']) : '';
                            
                            if($result['invoice'] > 0)
                                $inv = ($result['sum'] > 0) ? '<a target="_blank" href="/crm/type/159/details/'.(int)$result['invoice'].'/" title="'.$inv_comm.'">'.(int)$result['invoice'].'</a>' : '<a target="_blank" href="/crm/type/128/details/'.(int)$result['invoice'].'/" title="'.$inv_comm.'">'.(int)$result['invoice'].'</a>';
                            else
                                $inv = '';
                            
                            $task     = (!empty($result['task'])) ? '<a target="_blank" href="/company/personal/user/0/tasks/task/view/'.(int)$result['task_id'].'/">'.htmlspecialchars($result['task']).'</a>' : '';
                            $errStr   = ((int)$result['task_id'] == 0 && (int)$result['invoice'] == 0 && (int)$result['zp_id'] == 0 && (int)$result['rent'] == 0) ? 'class="alert-danger"' : '';
                            $comment  = (!empty($result['comment']) && $result['comment'] != "''") ? trim($result['comment'], "'") : '';

                            if($result['comand'] > 0)
                                $task .= '<br><small>(Командировка <a target="_blank" href="/crm/type/167/details/'.(int)$result['comand'].'/">'.$result['comandTitle'].'</a> с '.date('d.m.Y', strtotime($result['comStart'])).' по '.date('d.m.Y', strtotime($result['comStop'])).')</small>';
                            
                            if($result['rent'] > 0)
                                $crm = 'Аренда ТС: <a target="_blank" href="/crm/type/176/details/'.(int)$result['rent'].'/">'.htmlspecialchars($result['rent_name']).'</a>';

                            if($group == 1)
                            {
                                if(!isset($dEdit) || $dEdit != date('d.m.Y', strtotime($result['date_edit'])))
                                    echo '<tr class="alert-warning"><td colspan="10"></td><td>'.date('d.m.Y', strtotime($result['date_edit'])).'</td><td></td></tr>';
                            }
                            else
                            {
                                if(!isset($dEdit) || $dEdit != date('d.m.Y', strtotime($result['date'])))
                                    echo '<tr class="alert-warning"><td></td><td colspan="11">'.date('d.m.Y', strtotime($result['date'])).'</td></tr>';
                            }
                            
                            if(!empty($taskIDs) && in_array($result['ID'], $taskIDs))
                            {
                                $checkbox = '<i class="fa fa-spinner fa-spin"></i>';
                                $sumProc  = '<strong class="text-primary">'.number_format($result['sum'], 2, '.', ' ').'</strong>';
                            }
                            else
                            {
                                $checkbox = '<input id="check'.$result['ID'].'" type="checkbox" name="MorePart" onclick="showMorePart()" value="'.$result['ID'].'">';
                                $sumProc  = '<strong class="text-primary" id="sm'.$result['ID'].'" style="cursor:pointer" onclick="showPartForm('.$result['ID'].')">'.(float)$result['sum'].'</strong>';
                            }
                                
                            $iconHis = '';
                            $pHis = '';
                            if(isset($logHis[$result['ID']]))
                            {
                                $iconHis = '<span class="text-success fa fa-history" style="cursor:pointer" onclick="$(\'.cl'.$result['ID'].'\').toggle(200)"></span>';
                                foreach($logHis[$result['ID']] as $id_ppp => $his)
                                {
                                    $hCRM    = '';
                                    if(preg_match('#\D\_([0-9]+)#', $his['CRM'], $d))
                                        $hCRM = '<a href="/crm/deal/details/'.(int)$d[1].'/" target="_blank">'.$his['deal'].'</a>';
                                    if(preg_match('#\L\_([0-9]+)#', $his['CRM'], $l))
                                        $hCRM = '<a href="/crm/lead/details/'.(int)$l[1].'/" target="_blank">'.$his['lead'].'</a>';
                                    if(preg_match('#\T85\_([0-9]+)#', $his['CRM'], $m))
                                        $hCRM = '<a href="/crm/type/133//details/'.(int)$m[1].'/" target="_blank">'.$his['mtr'].'</a>';
                                    
                                    $hInv = '';
                                    if(!empty($his['r_inv'])) $hInv = '<a href="/crm/type/128/details/'.(int)$his['r_inv'].'/" target="_blank">'.htmlspecialchars($his['r_invoice']).'</a>';
                                    if(!empty($his['p_inv'])) $hInv = '<a href="/crm/type/159/details/'.(int)$his['p_inv'].'/" target="_blank">'.htmlspecialchars($his['p_invoice']).'</a>';

                                    $hTask   = (!empty($his['id_task']))   ? '<a href="/company/personal/user/0/tasks/task/view/'.(int)$his['id_task'].'/" target="_blank">'.htmlspecialchars($his['task_title']).'</a>' : '';
                                    $hComand = (!empty($his['id_comand'])) ? '<a href="/crm/type/167/details/'.(int)$his['id_comand'].'/" target="_blank">'.htmlspecialchars($his['comand']).'</a>' : '';
                                    $hZP     = (!empty($his['id_zp']))     ? 'Разбито на зарплату' : '';

                                    $hOper   = (!empty($his['id_oper']))   ? htmlspecialchars($his['oper']) : '';
                                    $pHis .= '<tr class="cl'.$result['ID'].'" style="display:none; background:#eee">
                                                <td><span class="text-success fa fa-arrow-up"></span></td>
                                                <td colspan="4"></td>
                                                <td>'.$hCRM.'</td>
                                                <td>'.$hInv.'</td>
                                                <td>'.$hTask.$hComand.$hZP.'</td>
                                                <td>'.number_format($his['sum'], 2, '.', ' ').'</td>
                                                <td></td>
                                                <td>'.date('d.m.Y H:i:s', strtotime($his['date'])).'</td>
                                                <td>'.$hOper.'</td>
                                            </tr>';
                                }
                            }
                            
                            $nazn = (!empty($result['nazn'])) ? '<hr>'.htmlspecialchars($result['nazn']) : '';
                            
                            echo    '<tr '.$errStr.' id="str'.$result['ID'].'">
                                        <td id="chk'.$result['ID'].'">'.$checkbox.$iconHis.'<input type="hidden" id="type'.$result['ID'].'" value="274"></td>
                                        <td>'.date('d.m.Y', strtotime($result['date'])).'</td>
                                        <td>'.htmlspecialchars($result['NAME']).$nazn.'</td>
                                        <td><strong>'.htmlspecialchars($employee).'</strong><br><p><small>'.htmlspecialchars($comment).'</small></p></td>
                                        <td>'.$sumProc.'</td>
                                        <td>'.$crm.'</td>
                                        <td id="strI'.$result['ID'].'">'.$inv.'</td>
                                        <td id="strT'.$result['ID'].'">'.$task.'</td>
                                        <td>'.number_format($result['sum'], 2, '.', ' ').$salary_n.' <input type="hidden" id="smtr'.$result['ID'].'" value="'.(float)$result['sum'].'"></td>
                                        <td></td>
                                        <td>'.date('d.m.Y', strtotime($result['date_edit'])).'</td>
                                        <td>'.htmlspecialchars($kassir).'</td>
                                     </tr>'.$pHis;
                            
                            $dEdit = ($group == 1) ? date('d.m.Y', strtotime($result['date_edit'])) : date('d.m.Y', strtotime($result['date']));
                        }
                    }
                    else
                        echo '<tr><td colspan="12" class="alert-danger text-center">По выбранному фильтру платежей не найдено</td></tr>';
                }
                else
                {
                    $tmpArDeal = array();
                    $tmpArLead = array();
                    $tmpArMTR  = array();
                    $tmpArTask = array();
                    $pArDeal = array();
                    $pArLead = array();
                    $pArMTR  = array();
                    $pArTask = array();
                    $sql = $this->db->query("SELECT `e`.`ID`, `e1`.`VALUE` AS `date`, `e0`.`VALUE` AS `pp`, `e2`.`VALUE_NUM` AS `sum`, `e13`.`VALUE_NUM` AS `sum_osn`, `e3`.`VALUE` AS `contragent`, `c`.`TITLE` AS `company`,
                                                    `e6`.`VALUE` AS `invoice`, `e8`.`VALUE` AS `ls`, REPLACE(`e5`.`VALUE`, 'D_', '') AS `deal_id`,
                                                    `e7`.`VALUE` AS `task_id`, `e9`.`VALUE` AS `comment`, `i`.`TITLE` AS `ORDER_TOPIC`, `e`.`TIMESTAMP_X` AS `date_edit`, REPLACE(`e5`.`VALUE`, 'L_', '') AS `lead_id`, 
                                                    `e`.`CREATED_BY` AS `username`, `e10`.`VALUE` AS `id_zp`, `e11`.`VALUE` AS `salary_user`, `e12`.`VALUE` AS `oper`, `e15`.`VALUE` AS `comment2`, `e16`.`VALUE` AS `INN_contr`,
                                                    CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `card`,
                                                    REPLACE(`e5`.`VALUE`, 'T85_', '') AS `mtr_id`, `r`.`ID` AS `rent`, `r`.`TITLE` AS `rent_name`,
                                                     CONCAT(`u2`.`LAST_NAME`, ' ', `u2`.`NAME`) AS `rent_user`, `cmp`.`ID` AS `cmp_id`, `cmp`.`TITLE` AS `cmpTitle`,
                                                    `e19`.`VALUE` AS `comand`, `com`.`TITLE` AS `comandTitle`, `com`.`UF_CRM_6_BEGINDATE` AS `comStart`, `com`.`UF_CRM_6_CLOSEDATE` AS `comStop`
                                             FROM `b_iblock_element` AS `e`
                                             LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 149
                                             LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 146
                                             LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 147
                                             LEFT JOIN `b_iblock_element_property` AS `e22` ON `e22`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e22`.`IBLOCK_PROPERTY_ID` = 148
                                             LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 151
                                             LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 152
                                             LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 155
                                             LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 156
                                             LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 157
                                             LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 153
                                             LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 154
                                             LEFT JOIN `b_iblock_element_property` AS `e10` ON `e10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e10`.`IBLOCK_PROPERTY_ID` = 161
                                             LEFT JOIN `b_iblock_element_property` AS `e11` ON `e11`.`IBLOCK_ELEMENT_ID` = `e10`.`VALUE` AND `e11`.`IBLOCK_PROPERTY_ID` = 142
                                             LEFT JOIN `b_iblock_element_property` AS `e12` ON `e12`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e12`.`IBLOCK_PROPERTY_ID` = 163
                                             LEFT JOIN `b_iblock_element_property` AS `e13` ON `e13`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e13`.`IBLOCK_PROPERTY_ID` = 148
                                             LEFT JOIN `b_iblock_element_property` AS `e14` ON `e14`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e14`.`IBLOCK_PROPERTY_ID` = 1934
                                             LEFT JOIN `b_iblock_element_property` AS `e15` ON `e15`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e15`.`IBLOCK_PROPERTY_ID` = 165
                                             LEFT JOIN `b_iblock_element_property` AS `e16` ON `e16`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e16`.`IBLOCK_PROPERTY_ID` = 150
                                             LEFT JOIN `b_iblock_element_property` AS `e17` ON `e17`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e17`.`IBLOCK_PROPERTY_ID` = 166
                                             LEFT JOIN `b_iblock_element_property` AS `e18` ON `e18`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e18`.`IBLOCK_PROPERTY_ID` = 160
                                             LEFT JOIN `b_iblock_element_property` AS `e19` ON `e19`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e19`.`IBLOCK_PROPERTY_ID` = 254
                                             LEFT JOIN `b_iblock_element_property` AS `e20` ON `e20`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e20`.`IBLOCK_PROPERTY_ID` = 436
                                             LEFT JOIN `b_utm_tasks_task` AS `ut` ON `ut`.`VALUE_ID` = `e7`.`VALUE`
                                             LEFT JOIN `b_crm_company` AS `c` ON `c`.`ID` = `e4`.`VALUE`
                                             LEFT JOIN `b_crm_dynamic_items_159` AS `i` ON `i`.`ID` = `e6`.`VALUE`
                                             LEFT JOIN `b_crm_dynamic_items_167` AS `com` ON `com`.`ID` = `e19`.`VALUE`
                                             LEFT JOIN `b_crm_company` AS `cmp` ON `cmp`.`ID` = REPLACE(`e3`.`VALUE`, 'CO_', '')
                                             LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e17`.`VALUE`
                                             LEFT JOIN `b_crm_dynamic_items_176` AS `r` ON `r`.`ID` = `e20`.`VALUE`
                                             LEFT JOIN `b_user`        AS `u2` ON `u2`.`ID` = `r`.`ASSIGNED_BY_ID`
                                             WHERE ".$where."
                                             GROUP BY `e`.`ID`
                                             ORDER BY  ".$order.", `e8`.`VALUE` ASC, `e0`.`VALUE` ASC");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            if(!empty($result['ls']) && trim($result['ls']) == '40802810729170004115' && $_SESSION['bitAppPayment']['ADMIN'] != 1)
                                continue;

                            #   Разбитые платежи
                            if($_SESSION['bitAppPayment']['ADMIN'] != 1)
                            {
                                #$result['deal_id'] = (!empty($result['deal_id'])) ? trim($result['deal_id']) : '';
                                #   Зарплата
                                if($result['id_zp'] > 0)
                                {
                                    if(($_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1) && $_SESSION['bitAppPayment']['ID'] == $result['oper'] || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 && in_array($result['id_zp'], $_SESSION['bitAppPayment']['arZPlist']))
                                    {
                                        $result['contragent'] = trim($result['contragent']);

                                        if($group == 1)
                                        {
                                            if(!isset($out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM']))
                                                $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] = 0;

                                            if(!isset($out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']]))
                                                $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] += $result['sum'];

                                            $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']] = $result;
                                        }
                                        else
                                        {
                                            if(!isset($out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM']))
                                                $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] = 0;

                                            if(!isset($out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']]))
                                                $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] += $result['sum'];

                                            $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']] = $result;
                                        }

                                        $logID[$result['ID']] = $result['ID'];
                                    }
                                    elseif(in_array($result['id_zp'], $_SESSION['bitAppPayment']['arZPlist']))
                                    {
                                        $result['contragent'] = trim($result['contragent']);

                                        if($group == 1)
                                        {
                                            if(!isset($out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM']))
                                                $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] = 0;

                                            if(!isset($out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']]))
                                                $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] += $result['sum'];

                                            $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']] = $result;
                                        }
                                        else
                                        {
                                            if(!isset($out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM']))
                                                $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] = 0;

                                            if(!isset($out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']]))
                                                $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] += $result['sum'];

                                            $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']] = $result;
                                        }

                                        $logID[$result['ID']] = $result['ID'];
                                    }
                                }
                                elseif($_SESSION['bitAppPayment']['allPay'] == 1 || (int)$result['task_id'] == 0 && (int)$result['deal_id'] == 0 && $result['zp_id'] == 0 || $result['task_id'] > 0 && (in_array($result['task_id'], $_SESSION['bitAppPayment']['arTask'])) || $result['deal_id'] > 0 && (in_array($result['deal_id'], $_SESSION['bitAppPayment']['arDeal'])))
                                {
                                    $result['contragent'] = trim($result['contragent']);

                                    if((int)$result['deal_id'] > 0)
                                        $tmpArDeal[$result['deal_id']] = $result['deal_id'];
                                    
                                    if((int)$result['lead_id'] > 0)
                                        $tmpArLead[$result['lead_id']] = $result['lead_id'];

                                    if((int)$result['mtr_id'] > 0)
                                        $tmpArMTR[$result['mtr_id']]  = $result['mtr_id'];
                                    
                                    if((int)$result['task_id'] > 0)
                                        $tmpArTask[$result['task_id']] = $result['task_id'];
                            
                                    if($group == 1)
                                    {
                                        if(!isset($out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM']))
                                            $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] = 0;
                                        
                                        if(!isset($out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']]))
                                            $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] += $result['sum'];
                                                                                                                                                                            
                                        $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']] = $result;
                                    }
                                    else
                                    {
                                        if(!isset($out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM']))
                                            $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] = 0;
                                            
                                        if(!isset($out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']]))
                                            $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] += $result['sum'];
                                        
                                        $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']] = $result;
                                    }
                                    
                                    $logID[$result['ID']] = $result['ID'];
                                }
                            }
                            elseif($_SESSION['bitAppPayment']['ADMIN'] == 1)
                            {
                                $result['contragent'] = (!empty($result['contragent'])) ? trim($result['contragent']) : '';
                                
                                if((int)$result['deal_id'] > 0)
                                        $tmpArDeal[$result['deal_id']] = $result['deal_id'];
                                    
                                    if((int)$result['lead_id'] > 0)
                                        $tmpArLead[$result['lead_id']] = $result['lead_id'];

                                    if((int)$result['mtr_id'] > 0)
                                        $tmpArMTR[$result['mtr_id']]  = $result['mtr_id'];
                                    
                                    if((int)$result['task_id'] > 0)
                                        $tmpArTask[$result['task_id']] = $result['task_id'];

                                if($group == 1)
                                {
                                    if(!isset($out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM']))
                                        $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] = 0;
                                    
                                    if(!isset($out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']]))
                                        $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] += $result['sum'];
                                                                                                                                                                        
                                    $out[$result['date_edit']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']] = $result;
                                }
                                else
                                {
                                    if(!isset($out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM']))
                                        $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] = 0;
                                        
                                    if(!isset($out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']]))
                                        $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['SUM'] += $result['sum'];
                                    
                                    $out[$result['date']][$result['date']][$result['pp']][$result['ls']][$result['contragent']]['IDS'][$result['ID']] = $result;
                                }
                                
                                $logID[$result['ID']] = $result['ID'];
                            }
                        }
                        
                        if(!empty($tmpArDeal))
                        {
                            $sql = $this->db->query("SELECT `ID`, `TITLE` FROM `b_crm_deal` WHERE `ID` IN(".implode(',', $tmpArDeal).")");
                            if($sql->num_rows > 0)
                            {
                                while($result = $sql->fetch_assoc())
                                {
                                    $pArDeal[$result['ID']] = $result['TITLE'];
                                }
                            }
                        }

                        if(!empty($tmpArLead))
                        {
                            $sql = $this->db->query("SELECT `ID`, `TITLE` FROM `b_crm_lead` WHERE `ID` IN(".implode(',', $tmpArLead).")");
                            if($sql->num_rows > 0)
                            {
                                while($result = $sql->fetch_assoc())
                                {
                                    $pArLead[$result['ID']] = $result['TITLE'];
                                }
                            }
                        }

                        if(!empty($tmpArMTR))
                        {
                            $sql = $this->db->query("SELECT `ID`, `TITLE` FROM `b_crm_dynamic_items_133` WHERE `ID` IN(".implode(',', $tmpArMTR).")");
                            if($sql->num_rows > 0)
                            {
                                while($result = $sql->fetch_assoc())
                                {
                                    $pArMTR[$result['ID']] = $result['TITLE'];
                                }
                            }
                        }

                        if(!empty($tmpArTask))
                        {
                            $sql = $this->db->query("SELECT `ID`, `TITLE` FROM `b_tasks` WHERE `ID` IN(".implode(',', $tmpArTask).")");
                            if($sql->num_rows > 0)
                            {
                                while($result = $sql->fetch_assoc())
                                {
                                    $pArTask[$result['ID']] = $result['TITLE'];
                                }
                            }
                        }

                        #   Лог
                        $logHis = array();
                        if(!empty($logID) && $_SESSION['bitAppPayment']['ADMIN'] == 1)
                        {
                            $sqlHis = $this->db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`, `p2`.`VALUE` AS `id_pay`, `p3`.`VALUE` AS `CRM`, 
                                                               `p4`.`VALUE` AS `id_task`, `p5`.`VALUE` AS `p_inv`, `p6`.`VALUE` AS `r_inv`, `p7`.`VALUE` AS `id_zp`,
                                                               `p8`.`VALUE` AS `id_comand`, `p9`.`VALUE` AS `id_oper`, `p10`.`VALUE` AS `sum`,
                                                               `l`.`TITLE` AS `lead`, `d`.`TITLE` AS `deal`, `m`.`TITLE` AS `mtr`, `t`.`TITLE` AS `task`, `c`.`TITLE` AS `comand`,
                                                               `p`.`TITLE` AS `p_invoice`, `r`.`TITLE` AS `r_invoice`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `oper`
                                                        FROM `b_iblock_element` AS `e`
                                                        LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 393
                                                        LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 394
                                                        LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 395
                                                        LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = 396
                                                        LEFT JOIN `b_iblock_element_property` AS `p5` ON `p5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p5`.`IBLOCK_PROPERTY_ID` = 397
                                                        LEFT JOIN `b_iblock_element_property` AS `p6` ON `p6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p6`.`IBLOCK_PROPERTY_ID` = 398
                                                        LEFT JOIN `b_iblock_element_property` AS `p7` ON `p7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p7`.`IBLOCK_PROPERTY_ID` = 399
                                                        LEFT JOIN `b_iblock_element_property` AS `p8` ON `p8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p8`.`IBLOCK_PROPERTY_ID` = 400
                                                        LEFT JOIN `b_iblock_element_property` AS `p9` ON `p9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p9`.`IBLOCK_PROPERTY_ID` = 401
                                                        LEFT JOIN `b_iblock_element_property` AS `p10` ON `p10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p10`.`IBLOCK_PROPERTY_ID` = 409
                                                        LEFT JOIN `b_crm_lead` AS `l` ON `l`.`ID` = REPLACE(`p3`.`VALUE`, 'L_', '')
                                                        LEFT JOIN `b_crm_deal` AS `d` ON `d`.`ID` = REPLACE(`p3`.`VALUE`, 'D_', '')
                                                        LEFT JOIN `b_crm_dynamic_items_133` AS `m` ON `m`.`ID` = REPLACE(`p3`.`VALUE`, 'T85_', '')
                                                        LEFT JOIN `b_tasks` AS `t` ON `t`.`ID` = `p4`.`VALUE`
                                                        LEFT JOIN `b_crm_dynamic_items_167` AS `c` ON `c`.`ID` = `p8`.`VALUE`
                                                        LEFT JOIN `b_crm_dynamic_items_159` AS `p` ON `p`.`ID` = `p5`.`VALUE`
                                                        LEFT JOIN `b_crm_dynamic_items_128` AS `r` ON `r`.`ID` = `p6`.`VALUE`
                                                        LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `p9`.`VALUE`
                                                        WHERE `e`.`IBLOCK_ID` = 94 AND `p2`.`VALUE` IN(".implode(',', $logID).")
                                                        ORDER BY `e`.`ID` DESC");
                            if($sqlHis->num_rows > 0)
                            {
                                while($resultHis = $sqlHis->fetch_assoc())
                                {
                                    $logHis[$resultHis['id_pay']][$resultHis['ID']] = $resultHis;
                                }
                            }
                        }
                        
                        unset($result, $result2);
                        if(!empty($out))
                        {
                            foreach($out as $date_edit => $val0)
                            {
                                if($group == 1 && (!isset($dEdit1) || $dEdit1 != date('d.m.Y', strtotime($date_edit))))
                                {
                                    echo '<tr class="alert-warning"><td></td><td colspan="11">'.date('d.m.Y', strtotime($date_edit)).'</td></tr>';
                                }
                                
                                foreach($val0 as $date => $val1)
                                {
                                    if($group == 0 && (!isset($dEdit) || $dEdit != date('d.m.Y', strtotime($date))))
                                    {
                                        echo '<tr class="alert-warning"><td></td><td colspan="10">'.date('d.m.Y', strtotime($date)).'</td><td></td></tr>';
                                    }
                                    
                                    foreach($val1 as $pp => $val2)
                                    {
                                        foreach($val2 as $ls => $val3)
                                        {
                                            foreach($val3 as $contr => $val4)
                                            {
                                                $sum = $val4['SUM'];
                                                $cnt = count($val4['IDS']);
                                                
                                                if($cnt > 1)
                                                {
                                                    $row = $cnt;
                                                    $sum = $val4['SUM'];
                                                    
                                                    $first = current($val4['IDS']);
                                                    
                                                    if($first['deal_id'] > 0 && isset($pArDeal))
                                                        $crm = '<a target="_blank" href="/crm/deal/details/'.(int)$first['deal_id'].'/">Сделка:'.htmlspecialchars($pArDeal[$first['deal_id']]).'</a>';
                                                    elseif($first['lead_id'] > 0)
                                                        $crm = '<a target="_blank" href="/crm/lead/details/'.(int)$first['lead_id'].'/">ЛИД: '.htmlspecialchars($pArLead[$first['lead_id']]).'</a>';
                                                    elseif($first['mtr_id'] > 0)
                                                        $crm = '<a target="_blank" href="/crm/type/133/details/'.(int)$first['mtr_id'].'/">МТР: '.htmlspecialchars($pArMTR[$first['mtr_id']]).'</a>';
                                                    elseif($first['cmp_id'] > 0)
                                                        $crm = '<a target="_blank" href="/crm/company/details/'.(int)$first['cmp_id'].'/">Компания: '.htmlspecialchars($first['cmpTitle']).'</a>';
                                                    else
                                                        $crm = '';
                                                    $ls       = ($first['ls'] > 0) ? '<br><small>'.htmlspecialchars($first['ls']).'</small>' : '';
                                                    $inv_comm = (!empty($first['ORDER_TOPIC'])) ? htmlspecialchars($first['ORDER_TOPIC']) : '';

                                                    if($first['invoice'] > 0)
                                                    {
                                                        if($first['sum'] > 0)
                                                            $inv ='<a target="_blank" href="/crm/type/159/details/'.(int)$first['invoice'].'/" title="'.$inv_comm.'">'.(int)$first['invoice'].'</a>';
                                                        else
                                                            $inv ='<a target="_blank" href="/crm/type/128/details/'.(int)$first['invoice'].'/" title="'.$inv_comm.'">'.(int)$first['invoice'].'</a>';
                                                    }
                                                    else
                                                        $inv = '';
                                                    
                                                    $task     = ($first['task_id'] > 0) ? '<a target="_blank" href="/company/personal/user/0/tasks/task/view/'.(int)$first['task_id'].'/">'.htmlspecialchars($pArTask[$first['task_id']]).'</a>' : '';
                                                    $errStr   = ((int)$first['task_id'] == 0 && (int)$first['invoice'] == 0 && (int)$first['id_zp'] == 0 && (int)$first['rent'] == 0) ? 'class="alert-danger"' : '';
                                                    $comment  = (!empty($first['comment']) && $first['comment'] != "''") ? trim($first['comment'], "'") : '';
                                                    $comment2 = trim($first['comment2']);
                                                    $username = $users[$first['oper']];
                                                    $salary_n = (!empty($first['salary_user'])) ? '<span class="text-success">ЗП: '.htmlspecialchars($users[$first['salary_user']]).'</span>' : '';

                                                    if($first['comand'] > 0)
                                                        $task .= '<br><small>(Командировка <a target="_blank" href="/crm/type/167/details/'.(int)$first['comand'].'/">'.$first['comandTitle'].'</a> с '.date('d.m.Y', strtotime($first['comStart'])).' по '.date('d.m.Y', strtotime($first['comStop'])).')</small>';
                                                    
                                                    if($first['rent'] > 0)
                                                        $crm = 'Аренда ТС: <a target="_blank" href="/crm/type/176/details/'.(int)$first['rent'].'/">'.htmlspecialchars($first['rent_name']).'</a>';
                                                        #$task = 'Аренда ТС: '.htmlspecialchars($first['rent_name']);

                                                    if(!empty($taskIDs) && in_array($first['ID'], $taskIDs))
                                                    {
                                                        $checkbox = '<i class="fa fa-spinner fa-spin"></i>';
                                                        $sumProc  = '<strong class="text-primary">'.number_format($sum, 2, '.', ' ').'</strong>';
                                                    }
                                                    else
                                                    {
                                                        $checkbox = '<input id="check'.$first['ID'].'" type="checkbox" name="MorePart" onclick="showMorePart()" value="'.$first['ID'].'">';
                                                        $sumProc  = '<strong class="text-primary" id="sm'.$first['ID'].'" style="cursor:pointer" onclick="showPartForm('.$first['ID'].')">'.(float)$sum.'</strong>';
                                                    }
                                                    
                                                    $iconHis = '';
                                                    $iconEdit = '';
                                                    $pHis = '';
                                                    if(isset($logHis[$first['ID']]))
                                                    {
                                                        $iconHis = '<br><span class="text-success fa fa-history" style="cursor:pointer" onclick="$(\'.cl'.$first['ID'].'\').toggle(200)"></span>';
                                                        foreach($logHis[$first['ID']] as $id_ppp => $his)
                                                        {
                                                            $hCRM    = '';
                                                            if(preg_match('#\D\_([0-9]+)#', $his['CRM'], $d))
                                                                $hCRM = '<a href="/crm/deal/details/'.(int)$d[1].'/" target="_blank">'.$his['deal'].'</a>';
                                                            if(preg_match('#\L\_([0-9]+)#', $his['CRM'], $l))
                                                                $hCRM = '<a href="/crm/lead/details/'.(int)$l[1].'/" target="_blank">'.$his['lead'].'</a>';
                                                            if(preg_match('#\T85\_([0-9]+)#', $his['CRM'], $m))
                                                                $hCRM = '<a href="/crm/type/133//details/'.(int)$m[1].'/" target="_blank">'.$his['mtr'].'</a>';
                                                            
                                                            $hInv = '';
                                                            if(!empty($his['r_inv'])) $hInv = '<a href="/crm/type/128/details/'.(int)$his['r_inv'].'/" target="_blank">'.htmlspecialchars($his['r_invoice']).'</a>';
                                                            if(!empty($his['p_inv'])) $hInv = '<a href="/crm/type/159/details/'.(int)$his['p_inv'].'/" target="_blank">'.htmlspecialchars($his['p_invoice']).'</a>';
                        
                                                            $hTask   = (!empty($his['id_task']))   ? '<a href="/company/personal/user/0/tasks/task/view/'.(int)$his['id_task'].'/" target="_blank">'.htmlspecialchars($his['task_title']).'</a>' : '';
                                                            $hComand = (!empty($his['id_comand'])) ? '<a href="/crm/type/167/details/'.(int)$his['id_comand'].'/" target="_blank">'.htmlspecialchars($his['comand']).'</a>' : '';
                                                            $hZP     = (!empty($his['id_zp']))     ? 'Разбито на зарплату' : '';
                        
                                                            $hOper   = (!empty($his['id_oper']))   ? htmlspecialchars($his['oper']) : '';
                                                            $pHis .= '<tr class="cl'.$first['ID'].'" style="display:none; background:#eee">
                                                                        <td><span class="text-success fa fa-arrow-up"></span></td>
                                                                        <td colspan="4"></td>
                                                                        <td>'.$hCRM.'</td>
                                                                        <td>'.$hInv.'</td>
                                                                        <td>'.$hTask.$hComand.$hZP.'</td>
                                                                        <td>'.number_format($his['sum'], 2, '.', ' ').'</td>
                                                                        <td></td>
                                                                        <td>'.date('d.m.Y H:i:s', strtotime($his['date'])).'</td>
                                                                        <td>'.$hOper.'</td>
                                                                    </tr>';
                                                        }
                                                    }
                                                    
                                                    if($_SESSION['bitAppPayment']['ADMIN'] == 1)
                                                        $iconEdit = '<br><a href="/services/lists/28/element/0/'.$first['ID'].'/" target="_blank"><span class="text-success fa fa-pencil"></span></a>';
                                                        
                                                    echo '<tr '.$errStr.' id="str'.$first['ID'].'">
                                                            <td id="chk'.$first['ID'].'">'.$checkbox.$iconHis.$iconEdit.'<input type="hidden" id="type'.$first['ID'].'" value="283"></td>
                                                            <td rowspan="'.$row.'"><strong>'.date('d.m.Y', strtotime($first['date'])).'</strong><br><small>пп: '.htmlspecialchars($pp).'</small><br><small>'.(float)$first['sum_osn'].'</small></td>
                                                            <td rowspan="'.$row.'"><strong>'.htmlspecialchars($first['company']).'</strong>'.$ls.'</td>
                                                            <td rowspan="'.$row.'"><strong title="ИНН '.htmlspecialchars($first['INN_contr']).'">'.htmlspecialchars($first['contragent']).'</strong> <br>ИНН: '.htmlspecialchars($first['INN_contr']).'<br><p><small>'.htmlspecialchars($comment).'</small></p></td>
                                                            <td rowspan="'.$row.'">'.$sumProc.'</td>
                                                            <td>'.$crm.$salary_n.'</td>
                                                            <td id="strI'.$first['ID'].'">'.$inv.'</td>
                                                            <td id="strT'.$first['ID'].'">'.$task.'</td>
                                                            <td>'.number_format($first['sum'], 2, '.', ' ').'<br><small class="text-danger">'.htmlspecialchars($comment2).'</small> <input type="hidden" id="smtr'.$first['ID'].'" value="'.(float)$first['sum'].'"></td>
                                                            <td>'.htmlspecialchars($first['card']).'</td>
                                                            <td>'.date('d.m.Y', strtotime($first['date_edit'])).'</td>
                                                            <td>'.htmlspecialchars($first['card']).'</td>
                                                         </tr>'.$pHis;
                                                    
                                                    unset($val4['IDS'][$first['ID']]);
                                                    
                                                    foreach($val4['IDS'] as $id_s => $value_s)
                                                    {
                                                        $iconHis = '';
                                                        $iconEdit = '';
                                                        $pHis = '';
                                                        if(isset($logHis[$id_s]))
                                                        {
                                                            $iconHis = '<br><span class="text-success fa fa-history" style="cursor:pointer" onclick="$(\'.cl'.$id_s.'\').toggle(200)"></span>';
                                                            foreach($logHis[$id_s] as $id_ppp => $his)
                                                            {
                                                                $hCRM    = '';
                                                                if(preg_match('#\D\_([0-9]+)#', $his['CRM'], $d))
                                                                    $hCRM = '<a href="/crm/deal/details/'.(int)$d[1].'/" target="_blank">'.$his['deal'].'</a>';
                                                                if(preg_match('#\L\_([0-9]+)#', $his['CRM'], $l))
                                                                    $hCRM = '<a href="/crm/lead/details/'.(int)$l[1].'/" target="_blank">'.$his['lead'].'</a>';
                                                                if(preg_match('#\T85\_([0-9]+)#', $his['CRM'], $m))
                                                                    $hCRM = '<a href="/crm/type/133//details/'.(int)$m[1].'/" target="_blank">'.$his['mtr'].'</a>';
                                                                
                                                                $hInv = '';
                                                                if(!empty($his['r_inv'])) $hInv = '<a href="/crm/type/128/details/'.(int)$his['r_inv'].'/" target="_blank">'.htmlspecialchars($his['r_invoice']).'</a>';
                                                                if(!empty($his['p_inv'])) $hInv = '<a href="/crm/type/159/details/'.(int)$his['p_inv'].'/" target="_blank">'.htmlspecialchars($his['p_invoice']).'</a>';

                                                                $hTask   = (!empty($his['id_task']))   ? '<a href="/company/personal/user/0/tasks/task/view/'.(int)$his['id_task'].'/" target="_blank">'.htmlspecialchars($his['task_title']).'</a>' : '';
                                                                $hComand = (!empty($his['id_comand'])) ? '<a href="/crm/type/167/details/'.(int)$his['id_comand'].'/" target="_blank">'.htmlspecialchars($his['comand']).'</a>' : '';
                                                                $hZP     = (!empty($his['id_zp']))     ? 'Разбито на зарплату' : '';

                                                                $hOper   = (!empty($his['id_oper']))   ? htmlspecialchars($his['oper']) : '';
                                                                $pHis .= '<tr class="cl'.$id_s.'" style="display:none; background:#eee">
                                                                            <td><span class="text-success fa fa-arrow-up"></span></td>
                                                                            <td colspan="4"></td>
                                                                            <td>'.$hCRM.'</td>
                                                                            <td>'.$hInv.'</td>
                                                                            <td>'.$hTask.$hComand.$hZP.'</td>
                                                                            <td>'.number_format($his['sum'], 2, '.', ' ').'</td>
                                                                            <td></td>
                                                                            <td>'.date('d.m.Y H:i:s', strtotime($his['date'])).'</td>
                                                                            <td>'.$hOper.'</td>
                                                                        </tr>';
                                                            }
                                                        }
                                                        
                                                        $ls       = ($value_s['ls'] > 0) ? '<br><small>'.htmlspecialchars($value_s['ls']).'</small>' : '';
                                                        $inv_comm = (!empty($value_s['ORDER_TOPIC'])) ? htmlspecialchars($value_s['ORDER_TOPIC']) : '';

                                                        if($value_s['invoice'] > 0)
                                                        {
                                                            if($value_s['sum'] > 0)
                                                                $inv ='<a target="_blank" href="/crm/type/159/details/'.(int)$value_s['invoice'].'/" title="'.$inv_comm.'">'.(int)$value_s['invoice'].'</a>';
                                                            else
                                                                $inv ='<a target="_blank" href="/crm/type/128/details/'.(int)$value_s['invoice'].'/" title="'.$inv_comm.'">'.(int)$value_s['invoice'].'</a>';
                                                        }
                                                        else
                                                            $inv = '';
                                                        
                                                        if($value_s['deal_id'] > 0)
                                                            $crm = '<a target="_blank" href="/crm/deal/details/'.(int)$value_s['deal_id'].'/">Сделка:'.htmlspecialchars($pArDeal[$value_s['deal_id']]).'</a>';
                                                        elseif($value_s['lead_id'] > 0)
                                                            $crm = '<a target="_blank" href="/crm/lead/details/'.(int)$value_s['lead_id'].'/">ЛИД: '.htmlspecialchars($pArLead[$value_s['lead_id']]).'</a>';
                                                        elseif($value_s['mtr_id'] > 0)
                                                            $crm = '<a target="_blank" href="/crm/type/133/details/'.(int)$value_s['mtr_id'].'/">МТР: '.htmlspecialchars($pArMTR[$value_s['mtr_id']]).'</a>';
                                                        elseif($value_s['cmp_id'] > 0)
                                                            $crm = '<a target="_blank" href="/crm/company/details/'.(int)$value_s['cmp_id'].'/">Компания: '.htmlspecialchars($value_s['cmpTitle']).'</a>';
                                                        else
                                                            $crm = '';
                                                        
                                                        $task     = ($value_s['task_id'] > 0) ? '<a target="_blank" href="/company/personal/user/0/tasks/task/view/'.(int)$value_s['task_id'].'/">'.htmlspecialchars($pArTask[$value_s['task_id']]).'</a>' : '';
                                                        $errStr   = ((int)$value_s['task_id'] == 0 && (int)$value_s['invoice'] == 0 && (int)$value_s['id_zp'] == 0 && (int)$value_s['rent'] == 0) ? 'class="alert-danger"' : '';
                                                        $comment  = (!empty($value_s['comment']) && $value_s['comment'] != "''") ? trim($value_s['comment'], "'") : '';
                                                        $comment2 = trim($value_s['comment2']);
                                                        $username = $users[$value_s['oper']];
                                                        $salary_n = (!empty($value_s['salary_user'])) ? '<span class="text-success">ЗП: '.htmlspecialchars($users[$value_s['salary_user']]).'</span>' : '';

                                                        if($value_s['comand'] > 0)
                                                            $task .= '<br><small>(Командировка <a target="_blank" href="/crm/type/167/details/'.(int)$value_s['comand'].'/">'.$value_s['comandTitle'].'</a> с '.date('d.m.Y', strtotime($value_s['comStart'])).' по '.date('d.m.Y', strtotime($value_s['comStop'])).')</small>';
                                                        
                                                        if($value_s['rent'] > 0)
                                                            $crm = 'Аренда ТС: <a target="_blank" href="/crm/type/176/details/'.(int)$value_s['rent'].'/">'.htmlspecialchars($value_s['rent_name']).'</a>';    
                                                            #$task = 'Аренда ТС: '.htmlspecialchars($value_s['rent_name']);

                                                        if(!empty($taskIDs) && in_array($id_s, $taskIDs))
                                                        {
                                                            $proccess = 'class="alert-warning"';
                                                            $checkbox = '<i class="fa fa-spinner fa-spin"></i>';
                                                        }
                                                        else
                                                        {
                                                            $proccess = '';
                                                            $checkbox = '<input id="check'.$id_s.'" type="checkbox" name="MorePart" onclick="showMorePart()" value="'.$id_s.'">';
                                                        }
                                                        
                                                        if($_SESSION['bitAppPayment']['ADMIN'] == 1)
                                                            $iconEdit = '<br><a href="/services/lists/28/element/0/'.$id_s.'/" target="_blank"><span class="text-success fa fa-pencil"></span></a>';

                                                        echo '<tr '.$errStr.' id="str'.$id_s.'">
                                                                <td id="chk'.$id_s.'">'.$checkbox.$iconHis.$iconEdit.' <input type="hidden" id="type'.$id_s.'" value="283"></td>
                                                                <td>'.$crm.$salary_n.'</td>
                                                                <td id="strI'.$id_s.'">'.$inv.'</td>
                                                                <td id="strT'.$id_s.'">'.$task.'</td>
                                                                <td>'.number_format($value_s['sum'], 2, '.', ' ').' <br><small class="text-danger">'.htmlspecialchars($comment2).'</small> <input type="hidden" id="smtr'.$id_s.'" value="'.(float)$value_s['sum'].'"></td>
                                                                <td>'.htmlspecialchars($value_s['card']).'</td>
                                                                <td>'.date('d.m.Y', strtotime($value_s['date_edit'])).'</td>
                                                                <td>'.htmlspecialchars($username).'</td>
                                                             </tr>'.$pHis;
                                                    }
                                                }
                                                else
                                                {
                                                    foreach($val4['IDS'] as $id_s => $value_s)
                                                    {
                                                        $iconHis = '';
                                                        $iconEdit = '';
                                                        $pHis = '';
                                                        if(isset($logHis[$id_s]))
                                                        {
                                                            $iconHis = '<br><span class="text-success fa fa-history" style="cursor:pointer" onclick="$(\'.cl'.$id_s.'\').toggle(200)"></span>';
                                                            foreach($logHis[$id_s] as $id_ppp => $his)
                                                            {
                                                                $hCRM    = '';
                                                                if(preg_match('#\D\_([0-9]+)#', $his['CRM'], $d))
                                                                    $hCRM = '<a href="/crm/deal/details/'.(int)$d[1].'/" target="_blank">'.$his['deal'].'</a>';
                                                                if(preg_match('#\L\_([0-9]+)#', $his['CRM'], $l))
                                                                    $hCRM = '<a href="/crm/lead/details/'.(int)$l[1].'/" target="_blank">'.$his['lead'].'</a>';
                                                                if(preg_match('#\T85\_([0-9]+)#', $his['CRM'], $m))
                                                                    $hCRM = '<a href="/crm/type/133//details/'.(int)$m[1].'/" target="_blank">'.$his['mtr'].'</a>';
                                                                
                                                                $hInv = '';
                                                                if(!empty($his['r_inv'])) $hInv = '<a href="/crm/type/128/details/'.(int)$his['r_inv'].'/" target="_blank">'.htmlspecialchars($his['r_invoice']).'</a>';
                                                                if(!empty($his['p_inv'])) $hInv = '<a href="/crm/type/159/details/'.(int)$his['p_inv'].'/" target="_blank">'.htmlspecialchars($his['p_invoice']).'</a>';

                                                                $hTask   = (!empty($his['id_task']))   ? '<a href="/company/personal/user/0/tasks/task/view/'.(int)$his['id_task'].'/" target="_blank">'.htmlspecialchars($his['task_title']).'</a>' : '';
                                                                $hComand = (!empty($his['id_comand'])) ? '<a href="/crm/type/167/details/'.(int)$his['id_comand'].'/" target="_blank">'.htmlspecialchars($his['comand']).'</a>' : '';
                                                                $hZP     = (!empty($his['id_zp']))     ? 'Разбито на зарплату' : '';

                                                                $hOper   = (!empty($his['id_oper']))   ? htmlspecialchars($his['oper']) : '';
                                                                $pHis .= '<tr class="cl'.$id_s.'" style="display:none; background:#eee">
                                                                            <td><span class="text-success fa fa-arrow-up"></span></td>
                                                                            <td colspan="4"></td>
                                                                            <td>'.$hCRM.'</td>
                                                                            <td>'.$hInv.'</td>
                                                                            <td>'.$hTask.$hComand.$hZP.'</td>
                                                                            <td>'.number_format($his['sum'], 2, '.', ' ').'</td>
                                                                            <td></td>
                                                                            <td>'.date('d.m.Y H:i:s', strtotime($his['date'])).'</td>
                                                                            <td>'.$hOper.'</td>
                                                                        </tr>';
                                                            }
                                                        }
                                                        
                                                        if($value_s['deal_id'] > 0)
                                                            $crm = '<a target="_blank" href="/crm/deal/details/'.(int)$value_s['deal_id'].'/">Сделка:'.htmlspecialchars($pArDeal[$value_s['deal_id']]).'</a>';
                                                        elseif($value_s['lead_id'] > 0)
                                                            $crm = '<a target="_blank" href="/crm/lead/details/'.(int)$value_s['lead_id'].'/">ЛИД: '.htmlspecialchars($pArLead[$value_s['lead_id']]).'</a>';
                                                        elseif($value_s['mtr_id'] > 0)
                                                            $crm = '<a target="_blank" href="/crm/type/133/details/'.(int)$value_s['mtr_id'].'/">МТР: '.htmlspecialchars($pArMTR[$value_s['mtr_id']]).'</a>';
                                                        elseif($value_s['cmp_id'] > 0)
                                                            $crm = '<a target="_blank" href="/crm/company/details/'.(int)$value_s['cmp_id'].'/">Компания: '.htmlspecialchars($value_s['cmpTitle']).'</a>';
                                                        else
                                                            $crm = '';
                                                            
                                                        $ls       = ($value_s['ls'] > 0) ? '<br><small>'.htmlspecialchars($value_s['ls']).'</small>' : '';
                                                        $inv_comm = (!empty($value_s['ORDER_TOPIC'])) ? htmlspecialchars($value_s['ORDER_TOPIC']) : '';
                                                        
                                                        if($value_s['invoice'] > 0)
                                                        {
                                                            if($value_s['sum'] > 0)
                                                                $inv ='<a target="_blank" href="/crm/type/159/details/'.(int)$value_s['invoice'].'/" title="'.$inv_comm.'">'.(int)$value_s['invoice'].'</a>';
                                                            else
                                                                $inv ='<a target="_blank" href="/crm/type/128/details/'.(int)$value_s['invoice'].'/" title="'.$inv_comm.'">'.(int)$value_s['invoice'].'</a>';
                                                        }
                                                        else
                                                            $inv = '';

                                                        $task     = ($value_s['task_id'] > 0) ? '<a target="_blank" href="/company/personal/user/0/tasks/task/view/'.(int)$value_s['task_id'].'/">'.htmlspecialchars($pArTask[$value_s['task_id']]).'</a>' : '';
                                                        $errStr   = ((int)$value_s['task_id'] == 0 && (int)$value_s['invoice'] == 0 && (int)$value_s['id_zp'] == 0 && (int)$value_s['rent'] == 0) ? 'class="alert-danger"' : '';
                                                        $comment  = (!empty($value_s['comment']) && $value_s['comment'] != "''") ? trim($value_s['comment'], "'") : '';
                                                        $comment2 = (!empty($value_s['comment2'])) ? trim($value_s['comment2']) : '';
                                                        $username = (!empty($value_s['oper'])) ? $users[$value_s['oper']] : '';
                                                        $salary_n = (!empty($value_s['salary_user'])) ? '<span class="text-success">ЗП: '.htmlspecialchars($users[$value_s['salary_user']]).'</span>' : '';

                                                        if($value_s['comand'] > 0)
                                                            $task .= '<br><small>(Командировка <a target="_blank" href="/crm/type/167/details/'.(int)$value_s['comand'].'/">'.$value_s['comandTitle'].'</a> с '.date('d.m.Y', strtotime($value_s['comStart'])).' по '.date('d.m.Y', strtotime($value_s['comStop'])).')</small>';

                                                        if($value_s['rent'] > 0)
                                                            $crm = 'Аренда ТС: <a target="_blank" href="/crm/type/176/details/'.(int)$value_s['rent'].'/">'.htmlspecialchars($value_s['rent_name']).'</a>';
                                                            #$task = 'Аренда ТС: '.htmlspecialchars($value_s['rent_name']);

                                                        if(!empty($taskIDs) && in_array($value_s['ID'], $taskIDs))
                                                        {
                                                            $checkbox = '<i class="fa fa-spinner fa-spin"></i>';
                                                            $sumProc  = '<strong class="text-primary">'.number_format($value_s['sum'], 2, '.', ' ').'</strong>';
                                                        }
                                                        else
                                                        {
                                                            $checkbox = '<input id="check'.$id_s.'" type="checkbox" name="MorePart" onclick="showMorePart()" value="'.$id_s.'">';
                                                            $sumProc  = '<strong class="text-primary" id="sm'.$id_s.'" style="cursor:pointer" onclick="showPartForm('.$id_s.')">'.number_format($value_s['sum'], 2, '.', ' ').'</strong>';
                                                        }
                                                    
                                                        if($_SESSION['bitAppPayment']['ADMIN'] == 1)
                                                            $iconEdit = '<br><a href="/services/lists/28/element/0/'.$id_s.'/" target="_blank"><span class="text-success fa fa-pencil"></span></a>';

                                                        echo '<tr '.$errStr.' id="str'.$id_s.'">
                                                                <td id="chk'.$id_s.'">'.$checkbox.$iconHis.$iconEdit.'<input type="hidden" id="type'.$id_s.'" value="283"></td>
                                                                <td><strong>'.date('d.m.Y', strtotime($value_s['date'])).'</strong><br><small>пп: '.htmlspecialchars($pp).'</small><br><small>'.(float)$value_s['sum_osn'].'</small></td>
                                                                <td><strong>'.htmlspecialchars($value_s['company'] ?? '').'</strong>'.$ls.'</td>
                                                                <td><strong title="ИНН '.htmlspecialchars($value_s['INN_contr'] ?? '').'">'.htmlspecialchars($value_s['contragent']).'</strong> <br>ИНН: '.htmlspecialchars($value_s['INN_contr']).'<br><p><small>'.htmlspecialchars($comment).'</small></p></td>
                                                                <td>'.$sumProc.'</td>
                                                                <td>'.$crm.$salary_n.'</td>
                                                                <td id="strI'.$id_s.'">'.$inv.'</td>
                                                                <td id="strT'.$id_s.'">'.$task.'</td>
                                                                <td>'.number_format($value_s['sum'], 2, '.', ' ').' <br><small class="text-danger">'.htmlspecialchars($comment2).'</small> <input type="hidden" id="smtr'.$id_s.'" value="'.(float)$value_s['sum'].'"></td>
                                                                <td>'.htmlspecialchars($value_s['card']).'</td>
                                                                <td>'.date('d.m.Y', strtotime($value_s['date_edit'])).'</td>
                                                                <td>'.htmlspecialchars($username).'</td>
                                                             </tr>'.$pHis;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    if($group == 0)
                                    {
                                        $dEdit = date('d.m.Y', strtotime($date));
                                    }
                                }
                                
                                if($group == 1 && (!isset($dEdit1) || $dEdit1 != date('d.m.Y', strtotime($date_edit))))
                                {
                                    $dEdit1 = date('d.m.Y', strtotime($date_edit));
                                }
                            }
                        }
                        else
                            echo '<tr><td colspan="12" class="alert-danger text-center">По выбранному фильтру платежей не найдено</td></tr>';
                    }
                    else
                        echo '<tr><td colspan="12" class="alert-danger text-center">По выбранному фильтру платежей не найдено</td></tr>';
                }
                
                exit;
            }
        }
    }

    private function calcComand()
    {
        $comand = array();
        $sql = $this->db->query("SELECT `p1`.`VALUE` AS `comand`, SUM(`p2`.`VALUE_NUM`) AS `sum`
                                         FROM `b_iblock_element_property` AS `p1`
                                         LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `p1`.`IBLOCK_ELEMENT_ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 147
                                         WHERE `p1`.`IBLOCK_PROPERTY_ID` = 254 AND `p1`.`VALUE` > 0
                                         GROUP BY `p1`.`VALUE`");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $comand[$result['comand']] = $result['sum'];
            }
        }

        $sql = $this->db->query("SELECT `p1`.`VALUE` AS `comand`, SUM(`p2`.`VALUE_NUM`) AS `sum`
                                         FROM `b_iblock_element_property` AS `p1`
                                         LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `p1`.`IBLOCK_ELEMENT_ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 213
                                         WHERE `p1`.`IBLOCK_PROPERTY_ID` = 252 AND `p1`.`VALUE` > 0
                                         GROUP BY `p1`.`VALUE`");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if(isset($comand[$result['comand']]))
                    $comand[$result['comand']] += $result['sum'];
                else
                    $comand[$result['comand']] = $result['sum'];
            }
        }

        if(!empty($comand))
        {
            $query = array();
            $i = 0;
            foreach($comand as $id_c => $c)
            {
                $query[$id_c] = array('method' => 'crm.item.update', 'params' => array('entityTypeId' => 167, 'id' => $id_c, 'fields' => array('ufCrm6Payments' => $c)));
                $i++;

                if($i >= 49)
                {
                    sleep(1);
                    CRest::callBatch($query);
                    $i = 0;
                    $query = array();
                }
            }

            if(!empty($query))
            {
                sleep(1);
                CRest::callBatch($query);
                $i = 0;
                $query = array();
            }
        }
    }

    private function calcRent()
    {
        $arRent = array();
        $sql = $this->db->query("SELECT `p2`.`VALUE` AS `rent`, SUM(`p1`.`VALUE`) AS `sum`
                                 FROM `b_iblock_element` AS `e`
                                 LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 432
                                 LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 434
                                 WHERE `e`.`IBLOCK_ID` = 103 AND `p2`.`VALUE` > 0
                                 GROUP BY `p2`.`VALUE`");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arRent[$result['rent']] = (float)$result['sum'];
            }
        }

        $sql = $this->db->query("SELECT `p2`.`VALUE` AS `rent`, SUM(`p1`.`VALUE`) AS `sum`
                                 FROM `b_iblock_element` AS `e`
                                 LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 147
                                 LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 436
                                 WHERE `e`.`IBLOCK_ID` = 28 AND `p2`.`VALUE` > 0
                                 GROUP BY `p2`.`VALUE`");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if(isset($arRent[$result['rent']]))
                    $arRent[$result['rent']] += $result['sum'];
            }
        }

        if(!empty($arRent))
        {
            $qRent = array();
            foreach($arRent as $r => $s)
            {
                $qRent[$r] = array('method' => 'crm.item.update', 'params' => array('entityTypeId' => 176, 'id' => $r, 'fields' => array('ufCrm10_1699974848411' => $s)));
                if(count($qRent) >= 48)
                {
                    sleep(1);
                    CRest::callBatch($qRent);
                    $qRent = array();
                }
            }

            if(!empty($qRent))
            {
                sleep(1);
                CRest::callBatch($qRent);
            }
        }
    }

    public function getRentList()
    {
        $return = '';
        $arRent = array();
        $sql = $this->db->query("SELECT `a`.`ID` AS `auto`, `a`.`TITLE` AS `auto_name`, ROUND(SUM(`p1`.`VALUE`), 2) AS `sum`, `a`.`ASSIGNED_BY_ID`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user` 
                                 FROM `b_iblock_element_property` AS `p1`
                                 LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `p1`.`IBLOCK_ELEMENT_ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 434
                                 LEFT JOIN `b_crm_dynamic_items_176` AS `a` ON `a`.`ID` = `p2`.`VALUE`
                                 LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `a`.`ASSIGNED_BY_ID`
                                 WHERE `p1`.`IBLOCK_PROPERTY_ID` = 432
                                 GROUP BY `p2`.`VALUE`");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if($result['auto'] > 0)
                {
                    $arRent[$result['auto']]['name']  = $result['auto_name'];
                    $arRent[$result['auto']]['assig'] = $result['ASSIGNED_BY_ID'];
                    $arRent[$result['auto']]['assig_name'] = $result['user'];
                    $arRent[$result['auto']]['sum']  = $result['sum'];
                }
            }

            if(!empty($arRent))
            {
                $sql = $this->db->query("SELECT `p2`.`VALUE` AS `rent`, SUM(`p1`.`VALUE`) AS `sum`
                                         FROM `b_iblock_element` AS `e`
                                         LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 147
                                         LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 436
                                         WHERE `e`.`IBLOCK_ID` = 28 AND `p2`.`VALUE` IN(".implode(',', array_keys($arRent)).")
                                         GROUP BY `p2`.`VALUE`");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        #if(isset($arRent[$result['rent']]) && ($arRent[$result['rent']]['sum'] + $result['sum']) != 0)
                        #{
                            #$sum = $arRent[$result['rent']]['sum'] + $result['sum'];
                            $arRent[$result['rent']]['sum'] += $result['sum'];
                            #$return .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.$result['rent'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($arRent[$result['rent']]['name']).' / '.htmlspecialchars($arRent['assig_name']).' / <small>('.round($sum, 2).' руб.)</small></div>';
                        #}
                    }
                }
                foreach($arRent as $id => $r)
                {
                    if($r['sum'] != 0)
                        $return .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.$id.');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.htmlspecialchars($r['name']).' / '.htmlspecialchars($r['assig_name']).' / '.round($r['sum'], 2).' руб.</div>';
                }
            }
        }

        return $return;
    }

    public function delPart($id, $type)
    {
        $query = array();
                if(($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['partdel'] == 1) && $id > 0)
                {
                    if($type == 283)
                    {
                        $pay = $this->getPay($id, 283);


                            $query[] = array('method' => 'lists.element.update',
                                             'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id,
                                                               'FIELDS' => array(
                                                                    'NAME'         => $pay['NAME'],
                                                                    'PROPERTY_149' => $pay['pp'],                       #   ПП
                                                                    'PROPERTY_146' => $pay['date'],                     #   Дата
                                                                    'PROPERTY_147' => $pay['sum'],  #   Сумма
                                                                    'PROPERTY_152' => $pay['company'],                  #   Компания
                                                                    'PROPERTY_150' => $pay['INN'],                      #   ИНН
                                                                    'PROPERTY_151' => trim($pay['contr_name']),         #   Контрагент
                                                                    'PROPERTY_154' => trim($pay['naznach']),            #   Назначение платежа
                                                                    'PROPERTY_153' => $pay['LS'],                       #   Л/С
                                                                    'PROPERTY_155' => 0,                               #   Сделка
                                                                    'PROPERTY_156' => 0,                                   #   ID счёта
                                                                    'PROPERTY_160' => 0,                                   #   ID счёта
                                                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                    'PROPERTY_157' => 0,                           #   Задача
                                                                    'PROPERTY_159' => '',                               #   Ссылка на задачу
                                                                    'PROPERTY_158' => '',                                  #   Ссылка на счёт
                                                                    'PROPERTY_148' => $pay['sum_osn'],                  #   Сумма (осн)
                                                                    'PROPERTY_161' => 0,                                   #   Зарплата
                                                                    'PROPERTY_165' => $pay['comment'],                 #   комментарий
                                                                    'PROPERTY_166' => $pay['card'],                     #   ответственный за карту
                                                                    'PROPERTY_260' => $pay['contr2'],
                                                                    'PROPERTY_420' => $pay['nds'],
                                                                    'PROPERTY_436' => '',
                                                               )));

                    }
                }
            
            if(!empty($query))
            {
                 CRest::callBatch($query);
            }
    }
    
    public function getAcc()
    {
        $company = array();
        $balance = array();

        $sql = $this->db->query("SELECT (`e1`.`VALUE_NUM`) AS `SUM`, `e2`.`VALUE` AS `ACCOUNT`
                                 FROM `b_iblock_element` AS `e`
                                 LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 147
                                 LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 153
                                 WHERE `e`.`IBLOCK_ID` = 28
                                 GROUP BY `e`.`ID`");
                                 #GROUP BY `e2`.`VALUE`
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ID'] == 96 || $_SESSION['bitAppPayment']['ID'] == 60 || $_SESSION['bitAppPayment']['ACCESS']['bnal_sum'] == 1)
                {
                    if(!isset($balance[$result['ACCOUNT']]))
                        $balance[$result['ACCOUNT']] = 0;
                        
                    $balance[$result['ACCOUNT']] += $result['SUM'];
                }
            }

            foreach($balance as $a => $s)
            {
                $balance[$a] = $s = round($s, 2);
                if($_SESSION['bitAppPayment']['sumAcc'] == 0 && $s <= 0)
                    unset($balance[$a]);
            }
        }
        
        #$arHideAcc = array();

        $sql = $this->db->query("SELECT `b`.`ID`, `c`.`TITLE`, `r`.`ENTITY_ID`,  `b`.`RQ_BANK_NAME`, `b`.`RQ_ACC_NUM`,
                                        CONCAT(LEFT(`b`.`RQ_ACC_NUM`, 3), '...', RIGHT(`b`.`RQ_ACC_NUM`, 4)) AS `account`
                                 FROM `b_crm_bank_detail` AS `b` 
                                 LEFT JOIN `b_crm_requisite` AS `r` ON `r`.`ID` = `b`.`ENTITY_ID`
                                 LEFT JOIN `b_crm_company` AS `c` ON `c`.`ID` = `r`.`ENTITY_ID`
                                 WHERE `c`.`IS_MY_COMPANY` = 'Y'");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || in_array($result['ID'], $_SESSION['bitAppPayment']['ACCESS']['acc']))
                {
                    $company[$result['ENTITY_ID']]['COMPANY'] = $result['TITLE'];
                    $company[$result['ENTITY_ID']]['ACCOUNT'][$result['ID']]['BANK']    = $result['RQ_BANK_NAME'];
                    $company[$result['ENTITY_ID']]['ACCOUNT'][$result['ID']]['ACCOUNT'] = $result['RQ_ACC_NUM'];
                    $company[$result['ENTITY_ID']]['ACCOUNT'][$result['ID']]['ACC']     = $result['account'].' ('.$result['RQ_BANK_NAME'].')';
                    $company[$result['ENTITY_ID']]['ACCOUNT'][$result['ID']]['BALANCE'] = (isset($balance[$result['RQ_ACC_NUM']])) ? $balance[$result['RQ_ACC_NUM']] : 0;
                    
                    if($_SESSION['bitAppPayment']['sumAcc'] == 0 && $company[$result['ENTITY_ID']]['ACCOUNT'][$result['ID']]['BALANCE'] <= 0)
                    {
                        unset($company[$result['ENTITY_ID']]['ACCOUNT'][$result['ID']]);
                    }
                }
            }

            foreach($company as $id_c => $c)
            {
                if(empty($c['ACCOUNT']))
                    unset($company[$id_c]);
            }
        }
        
        return $company;
    }
    
    public function getSafe()
    {
        if($_SESSION['bitAppPayment']['ID'] == 26 || $_SESSION['bitAppPayment']['ID'] == 3 || $_SESSION['bitAppPayment']['ID'] == 7 || $_SESSION['bitAppPayment']['ID'] == 121 || $_SESSION['bitAppPayment']['ID'] == 128)
        {
            $sql = $this->db->query("SELECT SUM(`p1`.`VALUE`) AS `sum`, CONCAT(`u`.`LAST_NAME`, ' ', LEFT(`u`.`NAME`, 1), '.') AS `user`, `p2`.`VALUE` AS `user_id`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 147
                                     LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 163
                                     LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 160
                                     LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `p2`.`VALUE`
                                     WHERE `e`.`IBLOCK_ID` = 28 AND `p3`.`VALUE` = 1 AND `u`.`ACTIVE` = 'Y'
                                     GROUP BY `p2`.`VALUE`");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $this->safe[$result['user_id']]['user'] = $result['user'];
                    $this->safe[$result['user_id']]['sum']  = $result['sum'];
                }
            }
            
            #   История
            $whereSafe = array('`e`.`IBLOCK_ID` = 28', "`p3`.`VALUE` BETWEEN '".date('Y-m-d', strtotime('-7 day'))."' AND '".date('Y-m-d')."'");
            $sql = $this->db->query("SELECT `e`.`ID`, `p1`.`VALUE_NUM` AS `sum`, CONCAT(`u`.`LAST_NAME`, ' ', LEFT(`u`.`NAME`, 1), '.') AS `user`, `p2`.`VALUE` AS `user_id`,
                                                `p3`.`VALUE` AS `date`, `p4`.`VALUE` AS `comment`, `p6`.`VALUE` AS `inv`, `p7`.`VALUE` AS `task`, `p8`.`VALUE` AS `zp`,
                                                CONCAT(`u2`.`LAST_NAME`, ' ', LEFT(`u2`.`NAME`, 1), '.') AS `u_zp`
                                        FROM `b_iblock_element` AS `e`
                                        LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 147
                                        LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 163
                                        LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 146
                                        LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = 165
                                        LEFT JOIN `b_iblock_element_property` AS `p5` ON `p5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p5`.`IBLOCK_PROPERTY_ID` = 160
                                        LEFT JOIN `b_iblock_element_property` AS `p6` ON `p6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p6`.`IBLOCK_PROPERTY_ID` = 156
                                        LEFT JOIN `b_iblock_element_property` AS `p7` ON `p7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p7`.`IBLOCK_PROPERTY_ID` = 157
                                        LEFT JOIN `b_iblock_element_property` AS `p8` ON `p8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p8`.`IBLOCK_PROPERTY_ID` = 161
                                        LEFT JOIN `b_iblock_element_property` AS `p9` ON `p9`.`IBLOCK_ELEMENT_ID` = `p8`.`VALUE` AND `p9`.`IBLOCK_PROPERTY_ID` = 139
                                        LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `p2`.`VALUE`
                                        LEFT JOIN `b_user` AS `u2` ON `u2`.`ID` = `p9`.`VALUE`
                                        WHERE `p5`.`VALUE` = '1' AND ".implode(' AND ', $whereSafe)."
                                        ORDER BY `e`.`ID` DESC
                                        LIMIT 163");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!empty($result['comment']))
                        $comment = htmlspecialchars($result['comment']);
                    elseif($result['inv'] > 0)
                        $comment = ($result['sum'] > 0) ? 'Разбит на <a href="/crm/type/159/details/'.(int)$result['inv'].'/" target="_blank">счёт</a>' : 'Разбит на <a href="/crm/type/128/details/'.(int)$result['inv'].'/" target="_blank">счёт на оплату</a>';
                    elseif($result['task'] > 0)
                        $comment = 'Разбит на <a href="/company/personal/user/0/tasks/task/view/'.(int)$result['task'].'/" target="_blank">задачу</a>';
                    elseif($result['zp'] > 0)
                        $comment = 'Зарплата <strong>'.htmlspecialchars($result['u_zp']).'</strong>';
                    else
                        $comment = '';

                    $this->safeHis .= '<tr><td>'.$result['ID'].'</td><td>'.date('d.m.Y', strtotime($result['date'])).'</td><td>'.number_format($result['sum'], 2, '.', ' ').'</td><td>'.$comment.'</td><td>'.htmlspecialchars($result['user']).'</td></tr>';
                }
            }
        }
        else
        {
            /*
            $sql = $this->db->query("SELECT SUM(`p1`.`VALUE_NUM`) AS `sum`, CONCAT(`u`.`LAST_NAME`, ' ', LEFT(`u`.`NAME`, 1), '.') AS `user`, `p2`.`VALUE` AS `user_id`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 147
                                     LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 163
                                     LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `p2`.`VALUE`
                                     WHERE `e`.`IBLOCK_ID` = 274 AND `p2`.`VALUE` = ".(int)$_SESSION['bitAppPayment']['ID']."
                                     GROUP BY `p2`.`VALUE`");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $this->safe[$result['user_id']]['user'] = $result['user'];
                    $this->safe[$result['user_id']]['sum']  = $result['sum'];
                }
            }
            
            #   История
            $sql = $this->db->query("SELECT `e`.`ID`, `p1`.`VALUE_NUM` AS `sum`, CONCAT(`u`.`LAST_NAME`, ' ', LEFT(`u`.`NAME`, 1), '.') AS `user`, `p2`.`VALUE` AS `user_id`,
                                            `p3`.`VALUE` AS `date`, `p4`.`VALUE` AS `comment`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 147
                                     LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 163
                                     LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 146
                                     LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = 165
                                     LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `p2`.`VALUE`
                                     WHERE `e`.`IBLOCK_ID` = 274 AND `p2`.`VALUE` = ".(int)$_SESSION['bitAppPayment']['ID']." AND `p3`.`VALUE` BETWEEN '".date('Y-m-d', strtotime('-7 day'))." 00:00:00' AND '".date('Y-m-d')." 23:59:59'
                                     ORDER BY `p3`.`VALUE` DESC");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $this->safeHis .= '<tr><td>'.$result['ID'].'</td><td>'.date('d.m.Y', strtotime($result['date'])).'</td><td>'.number_format($result['sum'], 2, '.', ' ').'</td><td>'.htmlspecialchars($result['comment']).'</td><td>'.htmlspecialchars($result['user']).'</td></tr>';
                }
            }
            */
        }
    }
    
    public function getSafeHis()
    {
        if($_SESSION['bitAppPayment']['ID'] != 26 && $_SESSION['bitAppPayment']['ID'] != 3 && $_SESSION['bitAppPayment']['ID'] != 7 && $_SESSION['bitAppPayment']['ID'] != 121 && $_SESSION['bitAppPayment']['ID'] != 128)
            exit;
        
        $whereSafe = array('`e`.`IBLOCK_ID` = 28');
        if(!empty($_POST['date']))
        {
            $tmp = explode('.', $_POST['date']);
            if(isset($tmp[2]))
            {
                $whereSafe[] = "`p3`.`VALUE` = '".$this->db->real_escape_string($tmp[2].'-'.$tmp[1].'-'.$tmp[0])."'";
            }
        }
        
        $out = '';
        if($_SESSION['bitAppPayment']['ADMIN'] == 1)
        {
            if(!empty($_POST['user']) && $_POST['user'] > 0)
            {
                $whereSafe[] = "`p2`.`VALUE` = ".(int)$_POST['user'];
            }
        }
        else
        {
            $whereSafe[] = "`p2`.`VALUE` = ".(int)$_SESSION['bitAppPayment']['ID'];
        }
        
        $sql = $this->db->query("SELECT `e`.`ID`, `p1`.`VALUE_NUM` AS `sum`, CONCAT(`u`.`LAST_NAME`, ' ', LEFT(`u`.`NAME`, 1), '.') AS `user`, `p2`.`VALUE` AS `user_id`,
                                            `p3`.`VALUE` AS `date`, `p4`.`VALUE` AS `comment`, `p6`.`VALUE` AS `inv`, `p7`.`VALUE` AS `task`, `p8`.`VALUE` AS `zp`,
                                            CONCAT(`u2`.`LAST_NAME`, ' ', LEFT(`u2`.`NAME`, 1), '.') AS `u_zp`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 147
                                     LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 163
                                     LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 146
                                     LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = 165
                                     LEFT JOIN `b_iblock_element_property` AS `p5` ON `p5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p5`.`IBLOCK_PROPERTY_ID` = 160
                                     LEFT JOIN `b_iblock_element_property` AS `p6` ON `p6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p6`.`IBLOCK_PROPERTY_ID` = 156
                                     LEFT JOIN `b_iblock_element_property` AS `p7` ON `p7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p7`.`IBLOCK_PROPERTY_ID` = 157
                                     LEFT JOIN `b_iblock_element_property` AS `p8` ON `p8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p8`.`IBLOCK_PROPERTY_ID` = 161
                                     LEFT JOIN `b_iblock_element_property` AS `p9` ON `p9`.`IBLOCK_ELEMENT_ID` = `p8`.`VALUE` AND `p9`.`IBLOCK_PROPERTY_ID` = 139
                                     LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `p2`.`VALUE`
                                     LEFT JOIN `b_user` AS `u2` ON `u2`.`ID` = `p9`.`VALUE`
                                     WHERE `p5`.`VALUE` = '1' AND ".implode(' AND ', $whereSafe)."
                                     ORDER BY `e`.`ID` DESC
                                     LIMIT 163");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if(!empty($result['comment']))
                    $comment = htmlspecialchars($result['comment']);
                elseif($result['inv'] > 0)
                    $comment = ($result['sum'] > 0) ? 'Разбит на <a href="/crm/type/159/details/'.(int)$result['inv'].'/" target="_blank">счёт</a>' : 'Разбит на <a href="/crm/type/128/details/'.(int)$result['inv'].'/" target="_blank">счёт на оплату</a>';
                elseif($result['task'] > 0)
                    $comment = 'Разбит на <a href="/company/personal/user/0/tasks/task/view/'.(int)$result['task'].'/" target="_blank">задачу</a>';
                elseif($result['zp'] > 0)
                    $comment = 'Зарплата <strong>'.htmlspecialchars($result['u_zp']).'</strong>';
                else
                    $comment = '';

                $out .= '<tr><td>'.$result['ID'].'</td><td>'.date('d.m.Y', strtotime($result['date'])).'</td><td>'.number_format($result['sum'], 2, '.', ' ').'</td><td>'.$comment.'</td><td>'.htmlspecialchars($result['user']).'</td></tr>';
            }
        }
        
        echo $out;
        exit;
    }
    
    #   Просмотр очереди
    public function getQuenue($type = 1)
    {
            $out['count'] = 0;
            $out['data']  = '';
            $out['rules'] = 0;
            $return = array();
    
        if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['quenue'] == 1 || $type == 2)
        {
return;
            if(file_exists('./quenue.db'))
            {
                $file = file('./quenue.db');
                if(!empty($file))
                {
                    if(!empty($file[0]))
                    {
                        foreach($file as $str)
                        {
                            $str = trim($str);
                            $tmp = explode('|', $str);
                            
                            if($tmp[0] == '283')
                            {
                                $out['count'] ++;
                                
                                if($type == 1)
                                {
                                    if($tmp[23] == 'wait')
                                        $status = 'Ожидание';
                                    elseif($tmp[23] == 'done')
                                        $status = 'Завершено';
                                    else
                                        $status = 'Ошибка';
                                    
                                    if($tmp[22] == 'update')
                                        $action = 'Изменение';
                                    elseif($tmp[22] == 'delete')
                                        $action = 'Удаление';
                                    elseif($tmp[22] == 'import')
                                        $action = 'Выписка';
                                    else
                                        $action = 'Добавление';
                                        
                                    $out['data'] .= '<tr><td>'.date('d.m.Y', strtotime($tmp[5])).'</td>
                                                         <td>'.(float)$tmp[6].'</td>
                                                         <td>'.htmlspecialchars($tmp[10]).'</td>
                                                         <td>'.$action.'</td>
                                                         <td>'.$status.'</td>
                                                     </tr>';
                                }
                                elseif($tmp[2] > 0)
                                    $return[] = $tmp[2];
                            }
                            elseif($tmp[0] == '274')
                            {
                                $out['count'] ++;
                                
                                if($type == 1)
                                {
                                    if($tmp[16] == 'wait')
                                        $status = 'Ожидание';
                                    elseif($tmp[16] == 'done')
                                        $status = 'Завершено';
                                    else
                                        $status = 'Ошибка';
                                    
                                    if($tmp[15] == 'update')
                                        $action = 'Изменение';
                                    elseif($tmp[15] == 'delete')
                                        $action = 'Удаление';
                                    else
                                        $action = 'Добавление';
                                        
                                    $out['data'] .= '<tr><td>'.date('d.m.Y', strtotime($tmp[7])).'</td>
                                                         <td>'.(float)$tmp[4].'</td>
                                                         <td>'.htmlspecialchars($tmp[13]).'</td>
                                                         <td>'.$action.'</td>
                                                         <td>'.$status.'</td>
                                                     </tr>';
                                }
                                elseif($tmp[2] > 0)
                                    $return[] = $tmp[2];
                            }
                        }
                    }
                }
            }
        }
        
        if($type == 1)
        {
            #   Количество найденных платежей
            if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['rules'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['allrules'] == 1)
            {
                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['allrules'] == 1)
                {
                    $tmp = scandir(_PATH .'/rules');
                    foreach($tmp as $file)
                    {
                        if($file != '.' && $file != '..')
                        {
                            $f = file_get_contents(_PATH .'/rules/'.$file);
                            $rule = unserialize($f);
                            
                            foreach($rule as $id_r => $r)
                            {
                                $out['rules'] += $r['count'];
                            }
                        }
                    }
                }
                else
                {
                    $f = file_get_contents(_PATH .'/rules/'.$_SESSION['bitAppPayment']['ID'].'.db');
                    $rule = unserialize($f);
                    
                    foreach($rule as $id_r => $r)
                    {
                        $out['rules'] += $r['count'];
                    }
                }
            }
            
            echo json_encode($out);
        }
        else
            return $return;
        
        exit;
    }
    
    #   Информация о платеже
    public function getPay($pay, $type)
    {
        $return = array();
        if($type > 0)
        {
            if(is_array($pay))
            {
                foreach($pay as $id => $p)
                {
                    $pay[$id] = (int)$p;
                }
                
                $where = "`e`.`ID` IN(".implode(',', $pay).")";
            }
            else
                $where = "`e`.`ID` = ".(int)$pay;
            
            if($type == 283)
            {
                $where .= ' AND `e18`.`VALUE` = 0';
                $sql = $this->db->query("SELECT `e`.`ID`, `e`.`NAME`, `e`.`CODE`, `e1`.`VALUE` AS `pp`, `e2`.`VALUE` AS `date`, `e3`.`VALUE_NUM` AS `sum`, `e4`.`VALUE` AS `company`,
                                                    `e5`.`VALUE` AS `INN`, `e6`.`VALUE` AS `contr_name`, `e7`.`VALUE` AS `naznach`, `e8`.`VALUE` AS `LS`, `c`.`TITLE` AS `comp_name`,
                                                    `e9`.`VALUE` AS `deal`, `e10`.`VALUE` AS `inv_id`, `e10`.`VALUE` AS `inv`, `e12`.`VALUE` AS `operator`, `e22`.`VALUE` AS `comand`,
                                                    `e13`.`VALUE` AS `date_edit`, `e14`.`VALUE` AS `task`, `e15`.`VALUE` AS `task_link`, `e16`.`VALUE` AS `inv_link`,
                                                    `e17`.`VALUE` AS `sum_osn`, `e18`.`VALUE` AS `nal_pay`, `e19`.`VALUE` AS `ZP`, `e20`.`VALUE` AS `comment`, `e21`.`VALUE` AS `card`,
                                                    `e23`.`VALUE` AS `contr2`, `e24`.`VALUE` AS `file_a`, `e25`.`VALUE` AS `file_url`, `e26`.`VALUE` AS `nds`, `e27`.`VALUE` AS `rent`
                                             FROM `b_iblock_element` AS `e`
                                             LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 149
                                             LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 146
                                             LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 147
                                             LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 152
                                             LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 150
                                             LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 151
                                             LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 154
                                             LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 153
                                             LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 155
                                             LEFT JOIN `b_iblock_element_property` AS `e10` ON `e10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e10`.`IBLOCK_PROPERTY_ID` = 156
                                             LEFT JOIN `b_iblock_element_property` AS `e12` ON `e12`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e12`.`IBLOCK_PROPERTY_ID` = 163
                                             LEFT JOIN `b_iblock_element_property` AS `e13` ON `e13`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e13`.`IBLOCK_PROPERTY_ID` = 162
                                             LEFT JOIN `b_iblock_element_property` AS `e14` ON `e14`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e14`.`IBLOCK_PROPERTY_ID` = 157
                                             LEFT JOIN `b_iblock_element_property` AS `e15` ON `e15`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e15`.`IBLOCK_PROPERTY_ID` = 159
                                             LEFT JOIN `b_iblock_element_property` AS `e16` ON `e16`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e16`.`IBLOCK_PROPERTY_ID` = 158
                                             LEFT JOIN `b_iblock_element_property` AS `e17` ON `e17`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e17`.`IBLOCK_PROPERTY_ID` = 148
                                             LEFT JOIN `b_iblock_element_property` AS `e18` ON `e18`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e18`.`IBLOCK_PROPERTY_ID` = 160
                                             LEFT JOIN `b_iblock_element_property` AS `e19` ON `e19`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e19`.`IBLOCK_PROPERTY_ID` = 161
                                             LEFT JOIN `b_iblock_element_property` AS `e20` ON `e20`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e20`.`IBLOCK_PROPERTY_ID` = 165
                                             LEFT JOIN `b_iblock_element_property` AS `e21` ON `e21`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e21`.`IBLOCK_PROPERTY_ID` = 166
                                             LEFT JOIN `b_iblock_element_property` AS `e22` ON `e22`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e22`.`IBLOCK_PROPERTY_ID` = 254
                                             LEFT JOIN `b_iblock_element_property` AS `e23` ON `e23`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e23`.`IBLOCK_PROPERTY_ID` = 260
                                             LEFT JOIN `b_iblock_element_property` AS `e24` ON `e24`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e24`.`IBLOCK_PROPERTY_ID` = 271
                                             LEFT JOIN `b_iblock_element_property` AS `e25` ON `e25`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e25`.`IBLOCK_PROPERTY_ID` = 270
                                             LEFT JOIN `b_iblock_element_property` AS `e26` ON `e26`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e26`.`IBLOCK_PROPERTY_ID` = 420
                                             LEFT JOIN `b_iblock_element_property` AS `e27` ON `e27`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e27`.`IBLOCK_PROPERTY_ID` = 436
                                             LEFT JOIN `b_crm_company` AS `c` ON `c`.`ID` = `e4`.`VALUE`
                                             WHERE `e`.`IBLOCK_ID` = 28  AND ".$where);
                if($sql->num_rows > 0)
                {
                    if(is_array($pay))
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $return[$result['ID']] = $result;
                        }
                    }
                    else
                    {
                        $return = $sql->fetch_assoc();
                    }
                    
                }
            }
            elseif($type == 274)
            {
                $where .= ' AND `e12`.`VALUE` = 1';
                $sql = $this->db->query("SELECT `e`.`ID`,`e`.`NAME`, `e`.`CODE`, `e1`.`VALUE_NUM` AS `sum`, `e2`.`VALUE` AS `invoice`, 
                                                `e3`.`VALUE` AS `otvetstv`, `e4`.`VALUE` AS `date`, `e5`.`VALUE` AS `kassir`,
                                                `e6`.`VALUE` AS `task`, `e7`.`VALUE` AS `deal`, `e8`.`VALUE` AS `task_link`, `e9`.`VALUE` AS `invoice_link`,
                                                `e10`.`VALUE` AS `comment`, `e11`.`VALUE` AS `ZP`, `e12`.`VALUE` AS `nal_pay`, `e13`.`VALUE` AS `comand`,
                                                `e14`.`VALUE` AS `contr2`, `e15`.`VALUE` AS `file_a`, `e16`.`VALUE` AS `file_url`, `e27`.`VALUE` AS `rent`
                                         FROM `b_iblock_element` AS `e`
                                         LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 147
                                         LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 156
                                         LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 164
                                         LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 146
                                         LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 163
                                         LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 157
                                         LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 155
                                         LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 159
                                         LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 158
                                         LEFT JOIN `b_iblock_element_property` AS `e10` ON `e10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e10`.`IBLOCK_PROPERTY_ID` = 165
                                         LEFT JOIN `b_iblock_element_property` AS `e11` ON `e11`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e11`.`IBLOCK_PROPERTY_ID` = 161
                                         LEFT JOIN `b_iblock_element_property` AS `e12` ON `e12`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e12`.`IBLOCK_PROPERTY_ID` = 160
                                         LEFT JOIN `b_iblock_element_property` AS `e13` ON `e13`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e13`.`IBLOCK_PROPERTY_ID` = 254
                                         LEFT JOIN `b_iblock_element_property` AS `e14` ON `e14`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e14`.`IBLOCK_PROPERTY_ID` = 260
                                         LEFT JOIN `b_iblock_element_property` AS `e15` ON `e15`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e15`.`IBLOCK_PROPERTY_ID` = 271
                                         LEFT JOIN `b_iblock_element_property` AS `e16` ON `e16`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e16`.`IBLOCK_PROPERTY_ID` = 270
                                         LEFT JOIN `b_iblock_element_property` AS `e27` ON `e27`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e27`.`IBLOCK_PROPERTY_ID` = 436
                                         WHERE `e`.`IBLOCK_ID` = 28  AND ".$where);
                if($sql->num_rows > 0)
                {
                    if(is_array($pay))
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $return[$result['ID']] = $result;
                        }
                    }
                    else
                    {
                        $return = $sql->fetch_assoc();
                    }
                }
            }
        }
        
        return $return;
    }
    
    #   Загрузка выписки
    public function uploadVB()
    {
        if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['vb'] == 1)
        {
            if($_FILES['txt']['type'] != 'text/plain' || $_FILES['txt']['error'] > 0)
            {
                echo 'Не удалось загрузить файл';
            }
            else
            {
                $name = '_!'.$_FILES['txt']['name'];
                if(move_uploaded_file($_FILES['txt']['tmp_name'], './upload/'.$name))
                {
                    $file = file_get_contents('./upload/'.$name);
                    $file = iconv('CP1251', 'UTF-8', $file);
                    
                    preg_match('#1CClientBankExchange(.*)СекцияРасчСчет#Us', $file, $out);
                    
                    if(isset($out[1]))
                        preg_match_all('#РасчСчет=([0-9]{20})#U', $out[1], $acc);
                    else
                    {
                        echo 'Неверный формат выписки';
                        exit;
                    }
                    
                    $account = $insertOrder = $insertPP = $date_update = array();
                    
                    if(!empty($acc[1]))
                    {
                            #   Список счетов и компаний
                            $company = array();
                            $sql = $this->db->query("SELECT `c`.`ID`, `b`.`RQ_ACC_NUM`
                                                     FROM `b_crm_bank_detail` AS `b` 
                                                     LEFT JOIN `b_crm_requisite` AS `r` ON `r`.`ID` = `b`.`ENTITY_ID`
                                                     LEFT JOIN `b_crm_company` AS `c` ON `c`.`ID` = `r`.`ENTITY_ID`
                                                     WHERE `c`.`IS_MY_COMPANY` = 'Y'");
                            if($sql->num_rows > 0)
                            {
                                while($result = $sql->fetch_assoc())
                                {
                                    $company[trim($result['RQ_ACC_NUM'])] = (int)$result['ID'];
                                }
                            }
                            $fields = array();
                            $fields['payPP']        = 'PROPERTY_149';
                            $fields['payDate']      = 'PROPERTY_146';
                            $fields['paySum']       = 'PROPERTY_147';
                            $fields['payContrINN']  = 'PROPERTY_150';
                            $fields['payContrName'] = 'PROPERTY_151';
                            $fields['payMyCompany'] = 'PROPERTY_152';
                            $fields['payRS']        = 'PROPERTY_153';
                            $fields['payNote']      = 'PROPERTY_154';
                            $fields['paySumOsn']    = 'PROPERTY_148';
                            $fields['payCash']      = 'PROPERTY_160';
                            $fields['payDateEdit']  = 'PROPERTY_162';
                            $fields['payOper']      = 'PROPERTY_163';
                            $fields['payCard']      = 'PROPERTY_166';
                            #$fields['payComp']      = 'PROPERTY_260';
                            
                            #   Карты
                            $cards = array();
                            $cardID = array();
                            $sql = $this->db->query("SELECT `d`.`ID`, `d`.`TITLE`, `d`.`CREATED_BY`, `d`.`CREATED_TIME`, `d`.`ASSIGNED_BY_ID`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`
                                               FROM `b_crm_dynamic_items_133` AS `d`
                                               LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `d`.`ASSIGNED_BY_ID`
                                               WHERE `d`.`TITLE` LIKE 'Банковская карта%' AND `d`.`STAGE_ID` = 'DT133_8:PREPARATION'");
                            while($result = $sql->fetch_assoc())
                            {
                                preg_match('#([0-9]{4})$#', $result['TITLE'], $out);
                                if(isset($out[1]))
                                    $result['card'] = $out[1];
                                
                                $cards[$result['ID']] = $result;
                                $cardID[$result['ID']] = $result['ID'];
                            }

                            $sql = $this->db->query("SELECT `ASSOCIATED_ENTITY_ID`, `SETTINGS`, `CREATED` FROM `b_crm_timeline` WHERE `ASSOCIATED_ENTITY_TYPE_ID` = 133 AND `ASSOCIATED_ENTITY_ID` IN(".implode(',', array_keys($cards)).") ORDER BY `CREATED` DESC");
                            while($result = $sql->fetch_assoc())
                            {
                                $tmp = unserialize($result['SETTINGS']);
                                if(isset($tmp['FIELD']) && $tmp['FIELD'] == 'ASSIGNED_BY_ID')
                                {
                                    $cards[$result['ASSOCIATED_ENTITY_ID']]['ASSIGNED'][date('Y-m-d', strtotime($result['CREATED']))] = $tmp['FINISH'];
                                }
                            }
                           
                            preg_match_all('#СекцияДокумент=(.*)КонецДокумента#uUs', $file, $out);
                            #   Обработка ПП
                            if(isset($out[1]) && !empty($fields))
                            {
                                $total = 0;
                                $match = $match1 = $match2 = array();
                                $insert1 = array();
                                $insert2 = array();
                                $arUINN = array();
                                $arFINN = array();
                                
                                foreach($out[1] as $value)
                                {
                                    $crd = 0;
                                    $dateP = array();
                                    $dateR = array();
                                    $total ++;
                                    
                                    #   Реквизиты ПП
                                        preg_match('#Сумма=([0-9\.,]+)#us', $value, $m_sum);
                                        preg_match('#Дата=([0-9\.,]+)#us', $value, $m_date);
                                        preg_match('#Номер=(.*)\n#Uus', $value, $m_num);
                                        preg_match('#НазначениеПлатежа=(.*)\n#Uus', $value, $cm);
                                        preg_match('#КвитанцияДата=([0-9]{2}\.[0-9]{2}\.[0-9]{4})#uUs', $value, $date1);

                                        $date2 = $m_date[1];

                                        $pp['num']  = trim($m_num[1]);
                                        $pp['sum']  = trim($m_sum[1]);
                                        $pp['comm'] = trim($cm[1]);
                                        $pp['card'] = 0;
                                        
                                        preg_match('#(Плательщик|Плательщик1)=(.*)\n#uUs', $value, $match);
                                        preg_match('#ПолучательСчет=(.*)\n#uUs', $value, $match1);
                                        preg_match('#ПлательщикСчет=(.*)\n#uUs', $value, $match2);
                                        preg_match('#ДатаПоступило=([0-9]{2}\.[0-9]{2}\.[0-9]{4})#uUs', $value, $dateP);
                                        preg_match('#ПлательщикИНН=(.*)\n#iuUs', $value, $innR);
                                        preg_match('#ДатаСписано=([0-9]{2}\.[0-9]{2}\.[0-9]{4})#uUs', $value, $dateR);
                                        $match1[1] = trim($match1[1]);
                                        $match2[1] = trim($match2[1]);
                                    
                                        
                                    #   Приход
                                        if(isset($company[$match1[1]]) && !empty($dateP[1]))
                                        {
                                            $p['inn'] = trim($innR[1]);
                                            
                                            $tmpDate = explode('.', trim($dateP[1]));

                                            $p['sum']     = $pp['sum'];
                                            $p['name']    = trim($match[2]);
                                            $p['date']    = $tmpDate[2] .'-'. $tmpDate[1].'-'.$tmpDate[0];
                                            $p['acc']     = $match1[1];
                                            $p['company'] = $company[$match1[1]];
                                            
                                            if(strlen($p['inn']) == 10 || strlen($p['inn']) == 12 && preg_match('#^ИП(.*)#iuUs', $p['name']))
                                                $arUINN[$p['inn']] = $p['name'];
                                            else
                                                $arFINN[$p['inn']] = $p['name'];
                                            
                                            #   Карта
                                            preg_match('#(\+\+\+\+\+)([0-9]{4})#', $pp['comm'], $out11);
                                            if(isset($out11[2]))
                                            {
                                                $crd = $out11[2];
                                                
                                                foreach($cards as $id => $card)
                                                {
                                                    if(isset($card['card']) && $card['card'] == $crd)
                                                    {
                                                        $assigned = $card['ASSIGNED_BY_ID'];
                                                        if(!empty($card['ASSIGNED']))
                                                        {
                                                            foreach($card['ASSIGNED'] as $d => $user)
                                                            {
                                                                if($p['date'] >= $d)
                                                                {
                                                                    $assigned = $user;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        
                                                        $pp['card'] = $assigned;
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                preg_match('#(\*\*\*\*\*)([0-9]{4})#', $pp['comm'], $out11);
                                                if(isset($out11[2]))
                                                {
                                                    $crd = $out11[2];
                                                    
                                                    foreach($cards as $id => $card)
                                                    {
                                                        if(isset($card['card']) && $card['card'] == $crd)
                                                        {
                                                            $assigned = $card['ASSIGNED_BY_ID'];
                                                            if(!empty($card['ASSIGNED']))
                                                            {
                                                                foreach($card['ASSIGNED'] as $d => $user)
                                                                {
                                                                    if($p['date'] >= $d)
                                                                    {
                                                                        $assigned = $user;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            
                                                            $pp['card'] = $assigned;
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            if($p['date'] >= $this->dateUpload)
                                            {
                                                $sql = $this->db->query("SELECT CONCAT('CO_', `r`.`ENTITY_ID`) AS `comp`, `c`.`ASSIGNED_BY_ID`
                                                                         FROM `b_crm_requisite` AS `r`
                                                                         LEFT JOIN `b_crm_company` AS `c` ON `c`.`ID` = `r`.`ENTITY_ID`
                                                                         WHERE `r`.`PRESET_ID` IN(1,2) AND `r`.`ENTITY_TYPE_ID` = 4 AND `r`.`RQ_INN` = '".$this->db->real_escape_string(trim($p['inn']))."'");
                                                if($sql->num_rows > 0)
                                                {
                                                    $resInn = $sql->fetch_assoc();
                                                    $insert1[$total] = array('method' => 'lists.element.add',
                                                        'params' => array('IBLOCK_TYPE_ID' => 'lists',
                                                            'IBLOCK_ID' => 28,
                                                            'ELEMENT_CODE' => md5(trim($pp['num']).trim($p['date']).trim($p['sum']).trim($p['inn']).trim($p['acc'])),
                                                            'FIELDS' => array(
                                                                'NAME' => trim($pp['num']),
                                                                $fields['payPP']        => trim($pp['num']),
                                                                $fields['payDate']      => trim($p['date']),
                                                                $fields['paySum']       => trim($p['sum']),
                                                                $fields['payContrINN']  => trim($p['inn']),
                                                                $fields['payContrName'] => trim($p['name']),
                                                                $fields['payMyCompany'] => (int)$p['company'],
                                                                $fields['payRS']        => trim($p['acc']),
                                                                $fields['payNote']      => trim($pp['comm']),
                                                                $fields['paySumOsn']    => trim($p['sum']),
                                                                $fields['payCash']      => 0,
                                                                $fields['payDateEdit']  => date('d.m.Y'),
                                                                $fields['payOper']      => $_SESSION['bitAppPayment']['ID'],
                                                                $fields['payCard']      => ($pp['card'] == 0 || empty($pp['card'])) ? $resInn['ASSIGNED_BY_ID'] : $pp['card'],
                                                                'PROPERTY_260'          => $resInn['comp']
                                                            )));
                                                }
                                                else
                                                {
                                                    $insert1[$total] = array('method' => 'lists.element.add',
                                                        'params' => array('IBLOCK_TYPE_ID' => 'lists',
                                                            'IBLOCK_ID' => 28,
                                                            'ELEMENT_CODE' => md5(trim($pp['num']).trim($p['date']).trim($p['sum']).trim($p['inn']).trim($p['acc'])),
                                                            'FIELDS' => array(
                                                                'NAME' => trim($pp['num']),
                                                                $fields['payPP']        => trim($pp['num']),
                                                                $fields['payDate']      => trim($p['date']),
                                                                $fields['paySum']       => trim($p['sum']),
                                                                $fields['payContrINN']  => trim($p['inn']),
                                                                $fields['payContrName'] => trim($p['name']),
                                                                $fields['payMyCompany'] => (int)$p['company'],
                                                                $fields['payRS']        => trim($p['acc']),
                                                                $fields['payNote']      => trim($pp['comm']),
                                                                $fields['paySumOsn']    => trim($p['sum']),
                                                                $fields['payCash']      => 0,
                                                                $fields['payDateEdit']  => date('d.m.Y'),
                                                                $fields['payCard']      => $pp['card'],
                                                                $fields['payOper']      => $_SESSION['bitAppPayment']['ID']
                                                            )));
                                                }
                                            }
                                        }
                                        
                                    #   Расход
                                        if(isset($company[$match2[1]]) && !empty($dateR[1]))
                                        {
                                            preg_match('#(Получатель|Получатель1)=(.*)\n#Us', $value, $match);
                                            preg_match('#ПолучательИНН=(.*)\n#uUs', $value, $innP);
                                            $r['inn'] = trim($innP[1]);

                                            $tmpDate = explode('.', trim($dateR[1]));

                                            $r['sum']     = $pp['sum'] * -1;
                                            $r['name']    = trim($match[2]);
                                            $r['date']    = $tmpDate[2] .'-'. $tmpDate[1].'-'.$tmpDate[0];
                                            $r['acc']     = $match2[1];
                                            $r['company'] = $company[$match2[1]];
                                            
                                            if(strlen($r['inn']) == 10 || strlen($r['inn']) == 12 && preg_match('#^ИП(.*)#iuUs', $r['name']))
                                                $arUINN[$r['inn']] = $r['name'];
                                            else
                                                $arFINN[$r['inn']] = $r['name'];

                                            #   Карта
                                            preg_match('#(\+\+\+\+\+)([0-9]{4})#', $pp['comm'], $out11);
                                            if(isset($out11[2]))
                                            {
                                                $crd = $out11[2];
                                                
                                                foreach($cards as $id => $card)
                                                {
                                                    if(isset($card['card']) && $card['card'] == $crd)
                                                    {
                                                        $assigned = $card['ASSIGNED_BY_ID'];
                                                        if(!empty($card['ASSIGNED']))
                                                        {
                                                            foreach($card['ASSIGNED'] as $d => $user)
                                                            {
                                                                if($r['date'] >= $d)
                                                                {
                                                                    $assigned = $user;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        
                                                        $pp['card'] = $assigned;
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                preg_match('#(\*\*\*\*\*)([0-9]{4})#', $pp['comm'], $out11);
                                                if(isset($out11[2]))
                                                {
                                                    $crd = $out11[2];
                                                    
                                                    foreach($cards as $id => $card)
                                                    {
                                                        if(isset($card['card']) && $card['card'] == $crd)
                                                        {
                                                            $assigned = $card['ASSIGNED_BY_ID'];
                                                            if(!empty($card['ASSIGNED']))
                                                            {
                                                                foreach($card['ASSIGNED'] as $d => $user)
                                                                {
                                                                    if($r['date'] >= $d)
                                                                    {
                                                                        $assigned = $user;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            
                                                            $pp['card'] = $assigned;
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            if($r['date'] >= $this->dateUpload)
                                            {
                                                $sql = $this->db->query("SELECT CONCAT('CO_', `ENTITY_ID`) AS `comp` FROM `b_crm_requisite` WHERE `PRESET_ID` IN(1,2) AND `ENTITY_TYPE_ID` = 4 AND `RQ_INN` = '".$this->db->real_escape_string(trim($r['inn']))."'");
                                                if($sql->num_rows > 0)
                                                {
                                                    $resInn = $sql->fetch_assoc();
                                                    $insert2[$total] = array('method' => 'lists.element.add',
                                                                            'params' => array('IBLOCK_TYPE_ID' => 'lists',
                                                                                              'IBLOCK_ID' => 28,
                                                                                              'ELEMENT_CODE' => md5(trim($pp['num']).trim($r['date']).trim($r['sum']).trim($r['inn']).trim($r['acc'])),
                                                                                              'FIELDS' => array(
                                                                                                    'NAME' => trim($pp['num']),
                                                                                                    $fields['payPP']        => trim($pp['num']),
                                                                                                    $fields['payDate']      => trim($r['date']),
                                                                                                    $fields['paySum']       => trim($r['sum']),
                                                                                                    $fields['payContrINN']  => trim($r['inn']),
                                                                                                    $fields['payContrName'] => trim($r['name']),
                                                                                                    $fields['payMyCompany'] => (int)$r['company'],
                                                                                                    $fields['payRS']        => trim($r['acc']),
                                                                                                    $fields['payNote']      => trim($pp['comm']),
                                                                                                    $fields['paySumOsn']    => trim($r['sum']),
                                                                                                    $fields['payCash']      => 0,
                                                                                                    $fields['payDateEdit']  => date('d.m.Y'),
                                                                                                    $fields['payOper']      => $_SESSION['bitAppPayment']['ID'],
                                                                                                    $fields['payCard']      => $pp['card'],
                                                                                                  'PROPERTY_260'            => $resInn['comp']
                                                                            )));
                                                }
                                                else
                                                {
                                                    $insert2[$total] = array('method' => 'lists.element.add',
                                                        'params' => array('IBLOCK_TYPE_ID' => 'lists',
                                                            'IBLOCK_ID' => 28,
                                                            'ELEMENT_CODE' => md5(trim($pp['num']).trim($r['date']).trim($r['sum']).trim($r['inn']).trim($r['acc'])),
                                                            'FIELDS' => array(
                                                                'NAME' => trim($pp['num']),
                                                                $fields['payPP']        => trim($pp['num']),
                                                                $fields['payDate']      => trim($r['date']),
                                                                $fields['paySum']       => trim($r['sum']),
                                                                $fields['payContrINN']  => trim($r['inn']),
                                                                $fields['payContrName'] => trim($r['name']),
                                                                $fields['payMyCompany'] => (int)$r['company'],
                                                                $fields['payRS']        => trim($r['acc']),
                                                                $fields['payNote']      => trim($pp['comm']),
                                                                $fields['paySumOsn']    => trim($r['sum']),
                                                                $fields['payCash']      => 0,
                                                                $fields['payDateEdit']  => date('d.m.Y'),
                                                                $fields['payCard']      => $pp['card'],
                                                                $fields['payOper']      => $_SESSION['bitAppPayment']['ID']
                                                            )));
                                                }

                                                if(abs($r['sum']) >= 15000 && $pp['card'] > 0 && preg_match('#(.*)(Снятие по карте|Выдача наличных)(.*)#s', $pp['comm']))
                                                {
                                                    $sqlU = $this->db->query("SELECT CONCAT(`LAST_NAME`, ' ', `NAME`) AS `user` FROM `b_user` WHERE `ID` = ".(int)$pp['card']);
                                                    if($sqlU->num_rows > 0)
                                                    {
                                                        $resU = $sqlU->fetch_assoc();
                                                        CRest::call('im.message.add', array(
                                                            'DIALOG_ID' => 'chat95037',
                                                            'MESSAGE' => 'Сотрудник [b]'.$resU['user'].'[/b] '.date('d.m.Y', strtotime($r['date'])).' снял с карты (...'.$crd.') [b]'.number_format(abs($r['sum']), 0, '.', ' ').'[/b] руб.',
                                                            'SYSTEM' => 'N',
                                                            'ATTACH' => '',
                                                            'URL_PREVIEW' => 'Y',
                                                            'KEYBOARD' => '',
                                                            'MENU' => '',
                                                        ));
                                                    }
                                                }
                                            }
                                        }
                                    
                                    if(count($insert1) >= 49)
                                    {
                                        $request = CRest::callBatch($insert1);
                                        $insert1 = array();
                                    }
                                    
                                    if(count($insert2) >= 49)
                                    {
                                        $request = CRest::callBatch($insert2);
                                        $insert2 = array();
                                    }
                                }
                                
                                if(!empty($insert1))
                                {
                                    $request = CRest::callBatch($insert1);
                                }
                                
                                if(!empty($insert2))
                                {
                                    $request = CRest::callBatch($insert2);
                                }
                                
                                if(!empty($arUINN))
                                {
                                    $arUnn2 = array();
                                    foreach($arUINN as $ii2 => $nn2)
                                    {
                                        $arUnn2[$ii2] = "'".$ii2."'";
                                    }
                                    
                                    $sql = $this->db->query("SELECT `RQ_INN`, `ENTITY_ID` FROM `b_crm_requisite` WHERE `RQ_INN` IN(".implode(',', $arUnn2).")");
                                    if($sql->num_rows > 0)
                                    {
                                        while($result = $sql->fetch_assoc())
                                        {
                                            unset($arUINN[$result['RQ_INN']]);
                                        }
                                    }
                                    
                                    if(!empty($arUINN))
                                    {
                                        $q1INN = array();
                                        $q2INN = array();
                                        foreach($arUINN as $uinn => $uname)
                                        {
                                            $q1INN[$uinn] = array('method' => 'crm.company.add', 'params' => array('fields' => array('TITLE' => $uname, 'ASSIGNED_BY_ID' => 1)));
                                        }
                                        
                                        $reqResult = CRest::callBatch($q1INN);
                                        if(!empty($reqResult['result']))
                                        {
                                            foreach($reqResult['result']['result'] as $i => $x)
                                            {
                                                if(preg_match('#^ип(.*)#iuU', $arUINN[$i]))
                                                {
                                                    $preset = 2;
                                                    $preName = 'ИП';
                                                }
                                                else
                                                {
                                                    $preset = 1;
                                                    $preName = 'Организация';
                                                }
                                                
                                                $q2INN[$i] = array('method' => 'crm.requisite.add', 'params' => array('fields' => array('ENTITY_TYPE_ID' => 4, 'ENTITY_ID' => $x, 'PRESET_ID' => $preset, 'NAME' => $preName, 'RQ_INN' => $i)));
                                            }
                                            
                                            if(!empty($q2INN))
                                            {
                                                $req2Result = CRest::callBatch($q2INN);
                                            }
                                        }
                                    }
                                }
                                
                                if(!empty($arFINN))
                                {
                                    $arUnn3 = array();
                                    foreach($arFINN as $ii3 => $nn3)
                                    {
                                        $arUnn3[$ii3] = "'".$ii3."'";
                                    }
                                    
                                    $sql = $this->db->query("SELECT `UF_CRM_61F9334DCA986`, `VALUE_ID` FROM `b_uts_crm_contact` WHERE `UF_CRM_61F9334DCA986` IN(".implode(',', $arUnn3).")");
                                    if($sql->num_rows > 0)
                                    {
                                        while($result = $sql->fetch_assoc())
                                        {
                                            unset($arFINN[$result['UF_CRM_61F9334DCA986']]);
                                        }
                                    }

                                    if(!empty($arFINN))
                                    {
                                        foreach($arFINN as $ii => $xx)
                                        {
					                        if($ii > 0)
                                                $q3INN[$ii] = array('method' => 'crm.contact.add', 'params' => array('fields' => array('LAST_NAME' => $xx, 'UF_CRM_61F9334DCA986' => $ii)));
                                        }
                                        
                                        if(!empty($q3INN))
                                        {
                                            $req3Result = CRest::callBatch($q3INN);
                                        }
                                    }
                                }
                                
                                echo $this->ending($total, 'Обработан', 'Обработано', 'Обработано').' '.$total.' '.$this->ending($total, 'платёж', 'платежа', 'платежей');
                            }
                        
                    }
                }
            }
        }
    }
    
    public function ending($n, $n1, $n2, $n5)
    {
        if($n >= 11 and $n <= 19) return $n5;
    
        $n = $n % 10;
    
        if($n == 1) return $n1;
    
        if($n >= 2 and $n <= 4) return $n2;
    
        return $n5;
    }
    
    #   Разбиение платежа
    public function savePart($data)
    {
        $createInvoice = 0;
        $file_id  = '';
        $file_url = '';
        $out['status'] = 0;
        $out['zp1'] = '';
        $out['zp2'] = '';
        $data['PFpartComand'] = '';
        $out['log'] = '';
        $partInvoice = 0;
        $calcComand = 0;
        $calcRent = 0;

        if($data['PFpayId'] > 0 && $data['PFpartTask'] > 0)
        {
            if(!empty($_FILES['FILE']) && $_FILES['FILE']['error'] == 0)
            {
                $name = $_FILES['FILE']['name'];
                if(move_uploaded_file($_FILES['FILE']['tmp_name'], './upload/'.$name))
                {
                    $file = file_get_contents('./upload/'.$name);
                    $req = CRest::call('disk.folder.uploadfile',
                        array('id' => 724726,
                            'fileContent' => array($name, base64_encode($file)),
                            'data' => array('NAME' => $name),
                            'generateUniqueName' => true));
                    if(!empty($req['result']['ID']))
                    {
                        $file_id  = array('n0' => 'n'.$req['result']['ID']);
                        $file_url = $req['result']['DETAIL_URL'];
                    }
                    unlink('./upload/'.$name);
                }
            }

            $data['PFpartSum'] = str_replace(',', '.', $data['PFpartSum']);
            $query = array();

            #   Разбиение на Аренду ТС
            if($data['selTypeList'] == 5 && $data['PFpartTask'] > 0 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['part'] == 1))
            {
                #   Инфо об аренде
                #$sql = $this->db->query("SELECT `IBLOCK_ELEMENT_ID`, `IBLOCK_PROPERTY_ID`, `VALUE` FROM `b_iblock_element_property` WHERE `IBLOCK_ELEMENT_ID` = ".(int)$data['PFpartTask']);
                #if($sql->num_rows > 0)
                #{
                    $arPayFields = array();
                    $payCode = '';


                    #   Инфо о платеже
                    $sql = $this->db->query("SELECT `e`.`NAME`, `e`.`CODE`, `p`.`IBLOCK_ELEMENT_ID`, `p`.`IBLOCK_PROPERTY_ID`, `p`.`VALUE`
                                             FROM `b_iblock_element` AS `e`
                                             LEFT JOIN `b_iblock_element_property` AS `p` ON `p`.`IBLOCK_ELEMENT_ID` = `e`.`ID`
                                             WHERE `e`.`IBLOCK_ID` = 28 AND `e`.`ID` = ".(int)$data['PFpayId']);
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $payCode = $result['CODE'].'rent'.time();
                            $arPayFields['NAME'] = $result['NAME'];
                            $arPayFields['PROPERTY_'.$result['IBLOCK_PROPERTY_ID']] = $result['VALUE'];
                        }
                    }

                    $arPayFields['PROPERTY_436'] = (int)$data['PFpartTask'];
                    
                    if($data['PFpartSum'] == $arPayFields['PROPERTY_147'])
                    {
                        $query[] = array('method' => 'lists.element.update', 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'], 'FIELDS' => $arPayFields));
                        $out['status'] = 2;
                    }
                    elseif($arPayFields['PROPERTY_147'] < 0 && $data['PFpartSum'] > $arPayFields['PROPERTY_147'] || $arPayFields['PROPERTY_147'] > 0 && $data['PFpartSum'] < $arPayFields['PROPERTY_147'])
                    {
                        $sum = $arPayFields['PROPERTY_147'] - $data['PFpartSum'];
                        $arPayFields['PROPERTY_147'] = $data['PFpartSum'];
                        $query[] = array('method' => 'lists.element.update', 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'], 'FIELDS' => $arPayFields));
                        $arPayFields['PROPERTY_147'] = $sum;
                        $arPayFields['PROPERTY_155'] = '';
                        $arPayFields['PROPERTY_156'] = 0;
                        $arPayFields['PROPERTY_157'] = 0;
                        $arPayFields['PROPERTY_158'] = '';
                        $arPayFields['PROPERTY_161'] = 0;
                        $arPayFields['PROPERTY_165'] = '';
                        $arPayFields['PROPERTY_254'] = '';
                        $arPayFields['PROPERTY_436'] = 0;
                        $query[] = array('method' => 'lists.element.add', 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => $payCode, 'FIELDS' => $arPayFields));

                        $out['status'] = 2;
                    }

                $calcRent = 1;
                #}
            }
            
            #   Разбиение на задачу + командировку
            if($data['selTypeList'] == 4 && $data['PFpartTask'] > 0)
            {
                $sql = $this->db->query("SELECT `UF_TASK_ID` FROM `b_crm_dynamic_items_167` WHERE `ID` = ".(int)$data['PFpartTask']);
                if($sql->num_rows > 0)
                {
                    $result = $sql->fetch_assoc();
                    $data['PFpartComand'] = $data['PFpartTask'];
                    $data['PFpartTask']   = $result['UF_TASK_ID'];
                    $data['selTypeList']  = 1;
                    $calcComand = 1;
                }
            }

            if($data['selTypeList'] == 1 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['part'] == 1))
            {
                if($data['PFpayTypeList'] == 283)
                {
                    $result = $this->getPay($data['PFpayId'], 283);
                    if(!empty($result))
                    {
                        if(empty($file_id))
                        {
                            $file_id  = $result['file_a'];
                            $file_url = $result['file_url'];
                        }

                        #   Лог
                        if($result['task'] > 0 || $result['inv_id'] > 0 || $result['task'] > 0 && $result['task'] != $data['PFpartTask'])
                        {
                            /*
                            $query[] = array('method' => 'lists.element.add',
                                             'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                               'FIELDS' => array(
                                                                                 'NAME' => $data['PFpayId'],
                                                                                 'PROPERTY_2717' => $result['date'],
                                                                                 'PROPERTY_2718' => $result['sum'],
                                                                                 'PROPERTY_2723' => $result['task'],
                                                                                 'PROPERTY_2724' => $result['inv_id'],
                                                                                 'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                                 'PROPERTY_2726' => $result['ZP']
                                                                                 )));
                            */
                        }

                        #   Задача
                        $sql1 = $this->db->query("SELECT `TITLE` FROM `b_tasks` WHERE `ID` = ".(int)$data['PFpartTask']);
                        if($sql1->num_rows > 0)
                        {
                            $result1 = $sql1->fetch_assoc();
                            $taskTitle = $result1['TITLE'];
                            $task = '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$data['PFpartTask'].'/" target="_blank">'.htmlspecialchars($taskTitle).'</a>';
                        }
                        else
                        {
                            $taskTitle = '';
                            $task = '';
                        }

                        #   Сделка
                        $sql1 = $this->db->query("SELECT `UF_CRM_TASK` AS `deal` FROM `b_uts_tasks_task` WHERE `VALUE_ID` = ".(int)$data['PFpartTask']." LIMIT 1");
                        if($sql1->num_rows > 0)
                        {
                            $result1 = $sql1->fetch_assoc();
                            $tmpDeal = unserialize($result1['deal']);
                            if(!empty($tmpDeal[0]))
                            {
                                $tmp = explode('_', $tmpDeal[0]);
                                if(!empty($tmp[1]))
                                {
                                    if($tmp[0] == 'D')
                                    {
                                        $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_deal` WHERE `ID` = ".(int)$tmp[1]." AND `STAGE_ID` NOT LIKE '%WON' AND `STAGE_ID` NOT LIKE '%LOSE'");
                                        if($sql2->num_rows > 0)
                                        {
                                            $deal = $tmpDeal[0];
                                        }
                                        else
                                        {
                                            echo json_encode($out);
                                            exit;
                                        }
                                    }
                                    elseif($tmp[0] == 'L')
                                    {
                                        $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_lead` WHERE `ID` = ".(int)$tmp[1]." AND `STATUS_ID` != 3 AND `STATUS_ID` != 4 AND `STATUS_ID` != 5 AND `STATUS_ID` != 'CONVERTED' AND `STATUS_ID` != 'JUNK'");
                                        if($sql2->num_rows > 0)
                                        {
                                            $deal = $tmpDeal[0];
                                        }
                                        else
                                        {
                                            echo json_encode($out);
                                            exit;
                                        }
                                    }
                                    elseif($tmp[0] == 'T85')
                                    {
                                        $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_dynamic_items_133` WHERE `ID` = ".(int)$tmp[1]." AND `STAGE_ID` != 'DT133_8:SUCCESS' AND `STAGE_ID` != 'DT133_8:FAIL' AND `STAGE_ID` != 'DT133_8:2'");
                                        if($sql2->num_rows > 0)
                                        {
                                            $deal = $tmpDeal[0];
                                        }
                                        else
                                        {
                                            echo json_encode($out);
                                            exit;
                                        }
                                    }
                                    else
                                    {
                                        $deal = $tmpDeal[0];
                                    }
                                }
                            }
                        }
                        else
                            $deal = '';
                            
                            if(!empty($task) && $data['PFpartSum'] < 0 && isset($_POST['PFci']) && $_POST['PFci'] == 1)
                            {
                                $sql = $this->db->query("SELECT `ID` 
                                                         FROM `b_crm_dynamic_items_128` 
                                                         WHERE `STAGE_ID` != 'DT128_3:SUCCESS' 
                                                            AND `STAGE_ID` != 'DT128_3:FAIL' 
                                                            AND `UF_PP_NUM` = '".trim($this->db->real_escape_string($result['pp']))."'
                                                            AND `UF_TO_PAY` = '".abs($data['PFpartSum'])."'
                                                            AND `OPPORTUNITY` = '".abs($data['PFpartSum'])."'
                                                            AND `UF_TASK_NUM` = ".(int)$data['PFpartTask']);
                                if($sql->num_rows <= 0)
                                    $createInvoice = 1;
                            }

                            if($data['PFpartSum'] == $result['sum'])
                            {
                                $sql = $this->db->query("SELECT * FROM `b_crm_dynamic_items_159` WHERE `ID` = ".(int)$result['inv_id']);
                                if($sql->num_rows > 0)
                                {
                                    $dyn = $sql->fetch_assoc();
                                    if($dyn['ID'] != $data['PFpartTask'])
                                    {
                                        if($dyn['STAGE_ID'] == 'DT159_1:SUCCESS' && $data['PFpartTask'] != $result['inv_id'])
                                        {
                                            #$this->db->query("UPDATE `b_crm_dynamic_items_159` SET `UF_PAY_ID` = 0, `STAGE_ID` = 'DT159_1:NEW' WHERE `ID` = ".(int)$result['inv_id']);
                                        }
                                    }
                                }

                                $fields = array('FIELDS' => array('NAME'         => $result['NAME'],
                                                            'PROPERTY_149' => $result['pp'],                       #   ПП
                                                            'PROPERTY_146' => $result['date'],                     #   Дата
                                                            'PROPERTY_147' => $result['sum'],                      #   Сумма
                                                            'PROPERTY_152' => $result['company'],                  #   Компания
                                                            'PROPERTY_150' => $result['INN'],                      #   ИНН
                                                            'PROPERTY_151' => trim($result['contr_name']),         #   Контрагент
                                                            'PROPERTY_154' => trim($result['naznach']),            #   Назначение платежа
                                                            'PROPERTY_153' => $result['LS'],                       #   Л/С
                                                            'PROPERTY_155' => $deal,                               #   Сделка
                                                            'PROPERTY_156' => 0,                                   #   ID счёта
                                                            'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                            'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                            'PROPERTY_157' => (int)$data['PFpartTask'],            #   Задача
                                                            'PROPERTY_159' => $task,                               #   Ссылка на задачу
                                                            'PROPERTY_158' => '',                                  #   Ссылка на счёт
                                                            'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                                            'PROPERTY_160' => 0,                                   #   Наличка
                                                            'PROPERTY_161' => 0,                                   #   Зарплата
                                                            'PROPERTY_165' => $data['PFpartComm'],                 #   комментарий
                                                            'PROPERTY_166' => $result['card'],                     #   ответственный за карту
                                                            'PROPERTY_254' => $data['PFpartComand'],               #   командировка
                                                            'PROPERTY_260' => $result['contr2'],               #   контрагент
                                                            'PROPERTY_271' => $file_id,               #   файл
                                                            'PROPERTY_270' => $file_url));
                                if($createInvoice == 1)
                                    $fields['FIELDS']['PROPERTY_474'] = 1;

                                $query[] = array('method' => 'lists.element.update', 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'], 'FIELDS' => $fields['FIELDS']));
                                $out['status'] = 2;
                            }
                            elseif($result['sum'] > 0 && $data['PFpartSum'] > 0 && $data['PFpartSum'] < $result['sum'] || $result['sum'] < 0 && $data['PFpartSum'] < 0 && $data['PFpartSum'] > $result['sum'])
                            {
                                $sum = $result['sum'] - $data['PFpartSum'];
                                
                                #   Ищем неразбитый платёж 
                                $sql2 = $this->db->query("SELECT `e`.`ID`, `e`.`NAME`, `e`.`CODE`, `e3`.`VALUE_NUM` AS `sum`
                                                          FROM `b_iblock_element` AS `e`
                                                          LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 149
                                                          LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 146
                                                          LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 147
                                                          LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 151
                                                          LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 153
                                                          LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 157
                                                          LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 156
                                                          LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 161
                                                          LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 160
                                                          WHERE `e`.`IBLOCK_ID` = 28 AND `e9`.`VALUE` = 0
                                                            AND `e1`.`VALUE` = '".$result['pp']."'
                                                            AND `e2`.`VALUE` = '".$result['date']."'
                                                            AND `e4`.`VALUE` = '".$result['contr_name']."'
                                                            AND `e5`.`VALUE` = '".$result['LS']."'
                                                            AND (`e6`.`VALUE_NUM` = 0 OR `e6`.`VALUE` IS NULL)
                                                            AND (`e7`.`VALUE_NUM` = 0 OR `e7`.`VALUE` IS NULL)
                                                            AND (`e8`.`VALUE_NUM` = 0 OR `e8`.`VALUE` IS NULL)
                                                            AND `e`.`ID` != ".(int)$data['PFpayId']);
                                if($sql2->num_rows > 0)
                                {
                                    $result2 = $sql2->fetch_assoc();
                                    $sql = $this->db->query("SELECT * FROM `b_crm_dynamic_items_159` WHERE `ID` = ".(int)$result['inv_id']);
                                    if($sql->num_rows > 0)
                                    {
                                        $dyn = $sql->fetch_assoc();
                                        if($dyn['ID'] != $data['PFpartTask'])
                                        {
                                            if($dyn['STAGE_ID'] == 'DT159_1:SUCCESS' && $data['PFpartTask'] != $result['inv_id'])
                                            {
                                                #$this->db->query("UPDATE `b_crm_dynamic_items_159` SET `UF_PAY_ID` = 0, `STAGE_ID` = 'DT159_1:NEW' WHERE `ID` = ".(int)$result['inv_id']);
                                            }
                                        }
                                    }
                                    #   Обновляем платёж
                                    $fields = array('FIELDS' => array(
                                                                        'NAME'         => $result2['NAME'],
                                                                        'PROPERTY_149' => $result['pp'],                       #   ПП
                                                                        'PROPERTY_146' => $result['date'],                     #   Дата
                                                                        'PROPERTY_147' => $data['PFpartSum'],                  #   Сумма
                                                                        'PROPERTY_152' => $result['company'],                  #   Компания
                                                                        'PROPERTY_150' => $result['INN'],                      #   ИНН
                                                                        'PROPERTY_151' => trim($result['contr_name']),               #   Контрагент
                                                                        'PROPERTY_154' => trim($result['naznach']),                  #   Назначение платежа
                                                                        'PROPERTY_153' => $result['LS'],                       #   Л/С
                                                                        'PROPERTY_155' => $deal,                               #   Сделка
                                                                        'PROPERTY_156' => 0,                   #   ID счёта
                                                                        'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                        'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                        'PROPERTY_157' => (int)$data['PFpartTask'],            #   Задача
                                                                        'PROPERTY_159' => $task,
                                                                        'PROPERTY_158' => '',                 #   Ссылка на счёт
                                                                        'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                                                        'PROPERTY_160' => 0,                       #   Наличка
                                                                        'PROPERTY_161' => 0,                       #   Зарплата
                                                                        'PROPERTY_166' => $result['card'],                     #   ответственный за карту
                                                                        'PROPERTY_165' => trim($data['PFpartComm']),
                                                                        'PROPERTY_254' => $data['PFpartComand'],               #   командировка
                                                                        'PROPERTY_260' => $result['contr2'],               #   командировка
                                                                        'PROPERTY_271' => $file_id,               #   командировка
                                                                        'PROPERTY_270' => $file_url,               #   командировка
                                                                    ));
                                    if($createInvoice == 1)
                                        $fields['FIELDS']['PROPERTY_474'] = 1;

                                    $query[] = array('method' => 'lists.element.update', 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'], 'FIELDS' => $fields['FIELDS']));
                                    $query[] = array('method' => 'lists.element.update',
                                                     'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $result2['ID'],
                                                                       'FIELDS' => array(
                                                                            'NAME'         => $result2['NAME'],
                                                                            'PROPERTY_149' => $result['pp'],                       #   ПП
                                                                            'PROPERTY_146' => $result['date'],                     #   Дата
                                                                            'PROPERTY_147' => ($sum + $result2['sum']),            #   Сумма
                                                                            'PROPERTY_152' => $result['company'],                  #   Компания
                                                                            'PROPERTY_150' => $result['INN'],                      #   ИНН
                                                                            'PROPERTY_151' => trim($result['contr_name']),               #   Контрагент
                                                                            'PROPERTY_154' => trim($result['naznach']),                  #   Назначение платежа
                                                                            'PROPERTY_153' => $result['LS'],                       #   Л/С
                                                                            'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                            'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                            'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                                                            'PROPERTY_160' => 0,
                                                                            'PROPERTY_166' => $result['card'],                     #   ответственный за карту
                                                                            'PROPERTY_165' => trim($result['comment']),
                                                                            'PROPERTY_254' => $result['comand'],
                                                                            'PROPERTY_260' => $result['contr2'],               #   командировка
                                                                            'PROPERTY_271' => $result['file_a'],
                                                                            'PROPERTY_270' => $result['file_url'])
                                                                       ));
                                }
                                else
                                {
                                    $sql = $this->db->query("SELECT * FROM `b_crm_dynamic_items_159` WHERE `ID` = ".(int)$result['inv_id']);
                                    if($sql->num_rows > 0)
                                    {
                                        $dyn = $sql->fetch_assoc();
                                        if($dyn['ID'] != $data['PFpartTask'])
                                        {
                                            if($dyn['STAGE_ID'] == 'DT159_1:SUCCESS' && $data['PFpartTask'] != $result['inv_id'])
                                            {
                                                #$this->db->query("UPDATE `b_crm_dynamic_items_159` SET `UF_PAY_ID` = 0, `STAGE_ID` = 'DT159_1:NEW' WHERE `ID` = ".(int)$result['inv_id']);
                                            }
                                        }
                                    }
                                    #   Обновляем остаток суммы у платежа
                                    $fields = array('FIELDS' => array(
                                                                        'NAME'         => $result['NAME'],
                                                                        'PROPERTY_149' => $result['pp'],                       #   ПП
                                                                        'PROPERTY_146' => $result['date'],                     #   Дата
                                                                        'PROPERTY_147' => $data['PFpartSum'],                  #   Сумма
                                                                        'PROPERTY_152' => $result['company'],                  #   Компания
                                                                        'PROPERTY_150' => $result['INN'],                      #   ИНН
                                                                        'PROPERTY_151' => trim($result['contr_name']),               #   Контрагент
                                                                        'PROPERTY_154' => trim($result['naznach']),                  #   Назначение платежа
                                                                        'PROPERTY_153' => $result['LS'],                       #   Л/С
                                                                        'PROPERTY_155' => $deal,                               #   Сделка
                                                                        'PROPERTY_156' => 0,                   #   ID счёта
                                                                        'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                        'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                        'PROPERTY_157' => (int)$data['PFpartTask'],            #   Задача
                                                                        'PROPERTY_159' => (!empty($taskTitle)) ? '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$data['PFpartTask'].'/" target="_blank">'.htmlspecialchars($taskTitle).'</a>' : '',
                                                                        'PROPERTY_158' => '',                 #   Ссылка на счёт
                                                                        'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                                                        'PROPERTY_160' => 0,                       #   Наличка
                                                                        'PROPERTY_161' => 0,                       #   Зарплата
                                                                        'PROPERTY_166' => $result['card'],                     #   ответственный за карту
                                                                        'PROPERTY_165' => trim($data['PFpartComm']),
                                                                        'PROPERTY_254' => $data['PFpartComand'],
                                                                        'PROPERTY_260' => $result['contr2'],               #   командировка
                                                                        'PROPERTY_271' => $file_id,
                                                                        'PROPERTY_270' => $file_url,
                                                                    ));
                                    if($createInvoice == 1)
                                        $fields['FIELDS']['PROPERTY_474'] = 1;

                                    $query[] = array('method' => 'lists.element.update', 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'], 'FIELDS' => $fields['FIELDS']));
                                    $query[] = array('method' => 'lists.element.add',
                                                     'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => microtime(true),
                                                                       'FIELDS' => array(
                                                                            'NAME'         => $result['NAME'],
                                                                            'PROPERTY_149' => $result['pp'],                       #   ПП
                                                                            'PROPERTY_146' => $result['date'],                     #   Дата
                                                                            'PROPERTY_147' => $sum,                                #   Сумма
                                                                            'PROPERTY_152' => $result['company'],                  #   Компания
                                                                            'PROPERTY_150' => $result['INN'],                      #   ИНН
                                                                            'PROPERTY_151' => trim($result['contr_name']),               #   Контрагент
                                                                            'PROPERTY_154' => trim($result['naznach']),                  #   Назначение платежа
                                                                            'PROPERTY_153' => $result['LS'],                       #   Л/С
                                                                            'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                            'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                            'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                                                            'PROPERTY_160' => 0,
                                                                            'PROPERTY_166' => $result['card'],                     #   ответственный за карту
                                                                            'PROPERTY_165' => '',
                                                                            'PROPERTY_254' => ''
                                                                       )));
                                    $out['status'] = 1;
                                }
                            }
                            else
                                $out['text'] = 'Сумма не соответствует платежу';
                    }
                }
                
                if($data['PFpayTypeList'] == 274)
                {
                    $result = $this->getPay($data['PFpayId'], 274);
                    if(!empty($result))
                    {
                        if(empty($file_id))
                        {
                            $file_id  = $result['file_a'];
                            $file_url = $result['file_url'];
                        }

                        #   Лог
                        if($result['task'] > 0 || $result['invoice'] > 0 || $result['task']  > 0 && $result['task'] != $data['PFpartTask'])
                        {
                            /*
                            $query[] = array('method' => 'lists.element.add',
                                             'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                               'FIELDS' => array(
                                                                                 'NAME' => $data['PFpayId'],
                                                                                 'PROPERTY_2717' => $result['date'],
                                                                                 'PROPERTY_2718' => $result['sum'],
                                                                                 'PROPERTY_2723' => $result['task'],
                                                                                 'PROPERTY_2724' => $result['invoice'],
                                                                                 'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                                 'PROPERTY_2726' => $result['ZP']
                                                                                 )));
                            */
                        }
                        
                        #   Задача
                        $sql1 = $this->db->query("SELECT `TITLE` FROM `b_tasks` WHERE `ID` = ".(int)$data['PFpartTask']);
                        if($sql1->num_rows > 0)
                        {
                            $result1 = $sql1->fetch_assoc();
                            $taskTitle = $result1['TITLE'];
                            $task = '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$data['PFpartTask'].'/">'.htmlspecialchars($taskTitle).'</a>';
                        }
                        else
                        {
                            $task = 0;
                            $taskTitle = '';
                        }
                        
                        #   Сделка
                        $sql1 = $this->db->query("SELECT `UF_CRM_TASK` AS `deal` FROM `b_uts_tasks_task` WHERE `VALUE_ID` = ".(int)$data['PFpartTask']." LIMIT 1");
                        if($sql1->num_rows > 0)
                        {
                            $result1 = $sql1->fetch_assoc();
                            $tmpDeal = unserialize($result1['deal']);
                            if(!empty($tmpDeal[0]))
                            {
                                $tmp = explode('_', $tmpDeal[0]);
                                if(!empty($tmp[1]))
                                {
                                    if($tmp[0] == 'D')
                                    {
                                        $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_deal` WHERE `ID` = ".(int)$tmp[1]." AND `STAGE_ID` NOT LIKE '%WON' AND `STAGE_ID` NOT LIKE '%LOSE'");
                                        if($sql2->num_rows > 0)
                                        {
                                            $deal = $tmpDeal[0];
                                        }
                                        else
                                        {
                                            echo json_encode($out);
                                            exit;
                                        }
                                    }
                                    elseif($tmp[0] == 'L')
                                    {
                                        $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_lead` WHERE `ID` = ".(int)$tmp[1]." AND `STATUS_ID` != 3 AND `STATUS_ID` != 4 AND `STATUS_ID` != 5 AND `STATUS_ID` != 'CONVERTED' AND `STATUS_ID` != 'JUNK'");
                                        if($sql2->num_rows > 0)
                                        {
                                            $deal = $tmpDeal[0];
                                        }
                                        else
                                        {
                                            echo json_encode($out);
                                            exit;
                                        }
                                    }
                                    elseif($tmp[0] == 'T85')
                                    {
                                        $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_dynamic_items_133` WHERE `ID` = ".(int)$tmp[1]." AND `STAGE_ID` != 'DT133_8:SUCCESS' AND `STAGE_ID` != 'DT133_8:FAIL' AND `STAGE_ID` != 'DT133_8:2'");
                                        if($sql2->num_rows > 0)
                                        {
                                            $deal = $tmpDeal[0];
                                        }
                                        else
                                        {
                                            echo json_encode($out);
                                            exit;
                                        }
                                    }
                                    else
                                    {
                                        $deal = $tmpDeal[0];
                                    }
                                }
                            }
                        }
                        else
                            $deal = '';

                            $query[] = array('method' => 'lists.element.update',
                                         'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                                           'FIELDS' => array(
                                                                    'NAME'         => $result['NAME'],
                                                                    'PROPERTY_147' => (float)$result['sum'],
                                                                    'PROPERTY_156' => 0,
                                                                    'PROPERTY_164' => $result['otvetstv'],
                                                                    'PROPERTY_146' => $result['date'],
                                                                    'PROPERTY_163' => $result['kassir'],
                                                                    'PROPERTY_157' => (int)$data['PFpartTask'],
                                                                    'PROPERTY_155' => $deal,
                                                                    'PROPERTY_159' => (!empty($task)) ? $task : '',
                                                                    'PROPERTY_158' => '',
                                                                    'PROPERTY_165' => (!empty($result['comment'])) ? trim($result['comment']) : '',
                                                                    'PROPERTY_160' => 1,
                                                                    'PROPERTY_161' => 0,
                                                                    'PROPERTY_254' => $data['PFpartComand'],
                                                                    'PROPERTY_260' => $result['contr2'],               #   командировка
                                                                    'PROPERTY_271' => $file_id,
                                                                    'PROPERTY_270' => $file_url,
                                                           )));

                        
                        
                        $out['status'] = 2;
                    }
                }
            }

            #   Разбиение на зарплату
            if($data['selTypeList'] == 2 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1))
            {
                $partSum = 0;
                $sql = $this->db->query("SELECT `VALUE_NUM` AS `sum` 
                                         FROM `b_iblock_element_property` 
                                         WHERE `IBLOCK_PROPERTY_ID` = 138 AND `IBLOCK_ELEMENT_ID` = ".(int)$data['PFpartTask']);
                if($sql->num_rows > 0)
                {
                    $result = $sql->fetch_assoc();
                    $partSum += $result['sum'];
                }

                $sql = $this->db->query("SELECT `VALUE_NUM` AS `sum` 
                                         FROM `b_iblock_element_property` 
                                         WHERE `IBLOCK_PROPERTY_ID` = 258 AND `IBLOCK_ELEMENT_ID` = ".(int)$data['PFpartTask']);
                if($sql->num_rows > 0)
                {
                    $result = $sql->fetch_assoc();
                    $partSum += $result['sum'];
                }

                $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum`
                                         FROM `b_iblock_element_property`
                                         WHERE `IBLOCK_PROPERTY_ID` = 147 AND `IBLOCK_ELEMENT_ID` IN(SELECT `IBLOCK_ELEMENT_ID`
                                                                                                     FROM `b_iblock_element_property`
                                                                                                     WHERE `IBLOCK_PROPERTY_ID` = 161 AND `VALUE` = ".(int)$data['PFpartTask'].")");
                if($sql->num_rows > 0)
                {
                    $resSum = $sql->fetch_assoc();
                    $partSum += $resSum['sum'];
                }

                $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum`
                                         FROM `b_iblock_element_property`
                                         WHERE `IBLOCK_PROPERTY_ID` = 213 AND `IBLOCK_ELEMENT_ID` IN(SELECT `IBLOCK_ELEMENT_ID`
                                                                                                     FROM `b_iblock_element_property`
                                                                                                     WHERE `IBLOCK_PROPERTY_ID` = 223 AND `VALUE` = ".(int)$data['PFpartTask'].")");
                if($sql->num_rows > 0)
                {
                    $resSum = $sql->fetch_assoc();
                    if($resSum['sum'] <> 0)
                        $partSum += $resSum['sum'];
                }
                
                $partSum = (string)$partSum + (string)$data['PFpartSum'];
                $insert = array();
                if($data['PFpayTypeList'] == 283)
                {
                    if($partSum < 0 && ($_SESSION['bitAppPayment']['ID'] != 96 && $_SESSION['bitAppPayment']['ADMIN'] != 1))
                        exit;
                    
                    $payment = $this->getPay($data['PFpayId'], 283);

                    #   Лог
                    /*
                    if($result['task'] > 0 || $result['inv_id'] > 0 || $result['ZP'] > 0 && $result['ZP'] != $data['PFpartTask'])
                    {
                        $query[] = array('method' => 'lists.element.add',
                                         'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                           'FIELDS' => array(
                                                                             'NAME' => $data['PFpayId'],
                                                                             'PROPERTY_2717' => $result['date'],
                                                                             'PROPERTY_2718' => $result['sum'],
                                                                             'PROPERTY_2723' => $result['task'],
                                                                             'PROPERTY_2724' => $result['inv_id'],
                                                                             'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                             'PROPERTY_2726' => $result['ZP']
                                                                             )));
                    }
                    */

                        if($payment['sum'] == $data['PFpartSum'])
                        {
                            $query[] = array('method' => 'lists.element.update',
                                             'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                                               'FIELDS' => array(
                                                                                 'NAME'         => $payment['NAME'],
                                                                                 'PROPERTY_149' => $payment['pp'],                       #   ПП
                                                                                 'PROPERTY_146' => $payment['date'],                     #   Дата
                                                                                 'PROPERTY_147' => $payment['sum'],                      #   Сумма
                                                                                 'PROPERTY_152' => $payment['company'],                  #   Компания
                                                                                 'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                                                                 'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                                                                 'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                                                                 'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                                                                 'PROPERTY_155' => 0,                               #   Сделка
                                                                                 'PROPERTY_156' => 0,                   #   ID счёта
                                                                                 'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                                 'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                                 'PROPERTY_157' => 0,            #   Задача
                                                                                 'PROPERTY_159' => '',    #   Ссылка на задачу
                                                                                 'PROPERTY_158' => '',                 #   Ссылка на счёт
                                                                                 'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                                                                 'PROPERTY_165' => trim($data['PFpartComm']),                       #   комментарий
                                                                                 'PROPERTY_160' => 0,
                                                                                 'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                                                                 'PROPERTY_161' => $data['PFpartTask'],
                                                                                 'PROPERTY_254' => 0,
                                                                                 'PROPERTY_271' => $file_id,
                                                                                 'PROPERTY_270' => $file_url
                                                                   )));
                            $out['status'] = 2;
                        }
                        elseif($payment['sum'] > 0 && $data['PFpartSum'] > 0 && $payment['sum'] > $data['PFpartSum'] || $payment['sum'] < 0 && $data['PFpartSum'] < 0 && $payment['sum'] < $data['PFpartSum'])
                        {
                                #   Ищем неразбитый платёж 
                                $sql2 = $this->db->query("SELECT `e`.`ID`, `e3`.`VALUE_NUM` AS `sum`
                                                          FROM `b_iblock_element` AS `e`
                                                          LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 149
                                                          LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 146
                                                          LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 147
                                                          LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 151
                                                          LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 153
                                                          LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 157
                                                          LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 156
                                                          LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 161
                                                          LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 160
                                                          WHERE `e`.`IBLOCK_ID` = 28 AND `e9`.`VALUE` = 0 
                                                            AND `e1`.`VALUE` = '".$payment['pp']."'
                                                            AND `e2`.`VALUE` = '".$payment['date']."'
                                                            AND `e4`.`VALUE` = '".$payment['contr_name']."'
                                                            AND `e5`.`VALUE` = '".$payment['LS']."'
                                                            AND (`e6`.`VALUE_NUM` = 0 OR `e6`.`VALUE` IS NULL)
                                                            AND (`e7`.`VALUE_NUM` = 0 OR `e7`.`VALUE` IS NULL)
                                                            AND (`e8`.`VALUE_NUM` = 0 OR `e8`.`VALUE` IS NULL)
                                                            AND `e`.`ID` != ".(int)$data['PFpayId']);
                                if($sql2->num_rows > 0)
                                {
                                    $result2 = $sql2->fetch_assoc();
                                    $sum = $result2['sum'] + ($payment['sum'] - $data['PFpartSum']);
                                    
                                    #   Обновляем платёж
                                    $query[] = array('method' => 'lists.element.update',
                                                     'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                                                       'FIELDS' => array(
                                                                                        'NAME'         => $payment['NAME'],
                                                                                        'PROPERTY_149' => $payment['pp'],                       #   ПП
                                                                                        'PROPERTY_146' => $payment['date'],                     #   Дата
                                                                                        'PROPERTY_147' => $data['PFpartSum'],                  #   Сумма
                                                                                        'PROPERTY_152' => $payment['company'],                  #   Компания
                                                                                        'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                                                                        'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                                                                        'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                                                                        'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                                                                        'PROPERTY_155' => 0,                               #   Сделка
                                                                                        'PROPERTY_156' => 0,                   #   ID счёта
                                                                                        #'PROPERTY_1047' => '',                      #   Счёт
                                                                                        'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                                        'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                                        'PROPERTY_157' => 0,            #   Задача
                                                                                        'PROPERTY_160' => 0,            #   
                                                                                        'PROPERTY_159' => 0,
                                                                                        'PROPERTY_158' => '',                 #   Ссылка на счёт
                                                                                        'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                                                                        'PROPERTY_165' => trim($data['PFpartComm']),                  #   комментарий
                                                                                        'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                                                                        'PROPERTY_161' => (int)$data['PFpartTask'],
                                                                                        'PROPERTY_254' => 0,
                                                                                        'PROPERTY_271' => $file_id,
                                                                                        'PROPERTY_270' => $file_url
                                                                           )));
                                    $query[] = array('method' => 'lists.element.update',
                                                     'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                                                       'FIELDS' => array(
                                                                                        'NAME'          => $result2['NAME'],
                                                                                        'PROPERTY_149' => $payment['pp'],                       #   ПП
                                                                                        'PROPERTY_146' => $payment['date'],                     #   Дата
                                                                                        'PROPERTY_147' => ($sum + $result2['sum']),            #   Сумма
                                                                                        'PROPERTY_152' => $payment['company'],                  #   Компания
                                                                                        'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                                                                        'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                                                                        'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                                                                        'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                                                                        'PROPERTY_160' => 0,                       #   Л/С
                                                                                        'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                                        'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                                        'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                                                                        'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                                                                        'PROPERTY_165' => trim($payment['comment']),
                                                                                        'PROPERTY_254' => $payment['comand'],
                                                                                        'PROPERTY_271' => $payment['file_a'],
                                                                                        'PROPERTY_270' => $payment['file_url']
                                                                           )));
                                }
                                else
                                {
                                    $sum = $payment['sum'] - $data['PFpartSum'];
                                    
                                    $query[] = array('method' => 'lists.element.update',
                                                     'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                                                       'FIELDS' => array(
                                                                                        'NAME'         => $payment['NAME'],
                                                                                        'PROPERTY_149' => $payment['pp'],                       #   ПП
                                                                                        'PROPERTY_146' => $payment['date'],                     #   Дата
                                                                                        'PROPERTY_147' => $data['PFpartSum'],                  #   Сумма
                                                                                        'PROPERTY_152' => $payment['company'],                  #   Компания
                                                                                        'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                                                                        'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                                                                        'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                                                                        'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                                                                        'PROPERTY_155' => 0,                               #   Сделка
                                                                                        'PROPERTY_156' => 0,                   #   ID счёта
                                                                                        'PROPERTY_1047' => '',                      #   Счёт
                                                                                        'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                                        'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                                        'PROPERTY_160' => 0,            #   
                                                                                        'PROPERTY_157' => 0,            #   Задача
                                                                                        'PROPERTY_159' => '',
                                                                                        'PROPERTY_158' => '',                 #   Ссылка на счёт
                                                                                        'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                                                                        'PROPERTY_165' => trim($data['PFpartComm']),                  #   комментарий
                                                                                        'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                                                                        'PROPERTY_161' => (int)$data['PFpartTask'],
                                                                                        'PROPERTY_254' => 0,
                                                                                        'PROPERTY_271' => $file_id,
                                                                                        'PROPERTY_270' => $file_url
                                                                           )));
                                    $query[] = array('method' => 'lists.element.add',
                                                     'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => microtime(true),
                                                                       'FIELDS' => array(
                                                                                        'NAME'         => $payment['NAME'],
                                                                                        'PROPERTY_149' => $payment['pp'],                       #   ПП
                                                                                        'PROPERTY_146' => $payment['date'],                     #   Дата
                                                                                        'PROPERTY_147' => $sum,                                #   Сумма
                                                                                        'PROPERTY_152' => $payment['company'],                  #   Компания
                                                                                        'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                                                                        'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                                                                        'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                                                                        'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                                                                        'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                                        'PROPERTY_160' => 0,                      #   Дата редактирования
                                                                                        'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                                        'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                                                                        'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                                                                        'PROPERTY_165' => trim($payment['comment']),
                                                                           )));
                                }
                                
                                $out['status'] = 1;
                        }

                }
                elseif($data['PFpayTypeList'] == 274)
                {
                    $payment = $this->getPay($data['PFpayId'], 274);
                    if($payment['sum'] != $data['PFpartSum'] || $partSum < 0)
                        exit;

                    #   Лог
                    if($result['task'] > 0 || $result['invoice'] > 0 || $result['ZP'] > 0 && $result['ZP'] != $data['PFpartTask'])
                    {
                        /*
                        $query[] = array('method' => 'lists.element.add',
                                         'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                           'FIELDS' => array(
                                                                             'NAME' => $data['PFpayId'],
                                                                             'PROPERTY_2717' => $result['date'],
                                                                             'PROPERTY_2718' => $result['sum'],
                                                                             'PROPERTY_2723' => $result['task'],
                                                                             'PROPERTY_2724' => $result['invoice'],
                                                                             'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                             'PROPERTY_2726' => $result['ZP']
                                                                             )));
                        */
                    }
                    


                        $query[] = array('method' => 'lists.element.update',
                                     'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                                       'FIELDS' => array(
                                                                        'NAME'         => $payment['NAME'],
                                                                        'PROPERTY_147' => (float)$payment['sum'],
                                                                        'PROPERTY_156' => 0,
                                                                        'PROPERTY_164' => $payment['otvetstv'],
                                                                        'PROPERTY_146' => $payment['date'],
                                                                        'PROPERTY_163' => $payment['kassir'],
                                                                        'PROPERTY_157' => (int)$data['PFpartTask'],
                                                                        'PROPERTY_155' => 0,
                                                                        'PROPERTY_159' => 0,
                                                                        'PROPERTY_158' => '',
                                                                        'PROPERTY_165' => trim($payment['comment']),
                                                                        'PROPERTY_161' => $data['PFpartTask'],
                                                                        'PROPERTY_160' => 1,
                                                                        'PROPERTY_254' => 0,
                                                                        'PROPERTY_271' => $file_id,
                                                                        'PROPERTY_270' => $file_url
                                                                       )));

                    
                    $out['status'] = 2;
                }
            }
            elseif($data['selTypeList'] == 2 && ($_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp3'] == 1) && $data['PFpayTypeList'] == 283)
            {
                $z = $this->zpList(0, 0, $data['PFpartTask']);
                if($z <= 0 || abs($data['PFpartSum']) > $z)
                    exit;

                $payment = $this->getPay($data['PFpayId'], 283);

                $partSum = 0;
                $sql = $this->db->query("SELECT `VALUE_NUM` AS `sum` 
                                         FROM `b_iblock_element_property` 
                                         WHERE `IBLOCK_PROPERTY_ID` = 138 AND `IBLOCK_ELEMENT_ID` = ".(int)$data['PFpartTask']);
                if($sql->num_rows > 0)
                {
                    $result = $sql->fetch_assoc();
                    $partSum += $result['sum'];
                }
                
                $sql = $this->db->query("SELECT `VALUE_NUM` AS `sum` 
                                         FROM `b_iblock_element_property` 
                                         WHERE `IBLOCK_PROPERTY_ID` = 258 AND `IBLOCK_ELEMENT_ID` = ".(int)$data['PFpartTask']);
                if($sql->num_rows > 0)
                {
                    $result = $sql->fetch_assoc();
                    $partSum += $result['sum'];
                }
                
                $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum`
                                         FROM `b_iblock_element_property`
                                         WHERE `IBLOCK_PROPERTY_ID` = 147 AND `IBLOCK_ELEMENT_ID` IN(SELECT `IBLOCK_ELEMENT_ID`
                                                                                                     FROM `b_iblock_element_property`
                                                                                                     WHERE `IBLOCK_PROPERTY_ID` = 161 AND `VALUE` = ".(int)$data['PFpartTask'].")");
                if($sql->num_rows > 0)
                {
                    $resSum = $sql->fetch_assoc();
                    $partSum += $resSum['sum'];
                }
                
                $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum`
                                         FROM `b_iblock_element_property`
                                         WHERE `IBLOCK_PROPERTY_ID` = 213 AND `IBLOCK_ELEMENT_ID` IN(SELECT `IBLOCK_ELEMENT_ID`
                                                                                                     FROM `b_iblock_element_property`
                                                                                                     WHERE `IBLOCK_PROPERTY_ID` = 223 AND `VALUE` = ".(int)$data['PFpartTask'].")");
                if($sql->num_rows > 0)
                {
                    $resSum = $sql->fetch_assoc();
                    $partSum += $resSum['sum'];
                }
                
                $partSum += $data['PFpartSum'];

                if($partSum < 0 && $_SESSION['bitAppPayment']['ADMIN'] != 1)
                    exit;

                if($payment['sum'] == $data['PFpartSum'])
                {
                    $query[] = array('method' => 'lists.element.update',
                        'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                            'FIELDS' => array(
                                'NAME'         => $payment['NAME'],
                                'PROPERTY_149' => $payment['pp'],                       #   ПП
                                'PROPERTY_146' => $payment['date'],                     #   Дата
                                'PROPERTY_147' => $payment['sum'],                      #   Сумма
                                'PROPERTY_152' => $payment['company'],                  #   Компания
                                'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                'PROPERTY_155' => 0,                               #   Сделка
                                'PROPERTY_156' => 0,                   #   ID счёта
                                'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                'PROPERTY_157' => 0,            #   Задача
                                'PROPERTY_159' => '',    #   Ссылка на задачу
                                'PROPERTY_158' => '',                 #   Ссылка на счёт
                                'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                'PROPERTY_165' => trim($data['PFpartComm']),                       #   комментарий
                                'PROPERTY_160' => 0,
                                'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                'PROPERTY_161' => $data['PFpartTask'],
                                'PROPERTY_254' => 0,
                                'PROPERTY_271' => $file_id,
                                'PROPERTY_270' => $file_url
                            )));
                    $out['status'] = 2;
                }
                elseif($payment['sum'] > 0 && $data['PFpartSum'] > 0 && $payment['sum'] > $data['PFpartSum'] || $payment['sum'] < 0 && $data['PFpartSum'] < 0 && $payment['sum'] < $data['PFpartSum'])
                {
                    #   Ищем неразбитый платёж
                    $sql2 = $this->db->query("SELECT `e`.`ID`, `e3`.`VALUE_NUM` AS `sum`
                                                          FROM `b_iblock_element` AS `e`
                                                          LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 149
                                                          LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 146
                                                          LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 147
                                                          LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 151
                                                          LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 153
                                                          LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 157
                                                          LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 156
                                                          LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 161
                                                          LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 160
                                                          WHERE `e`.`IBLOCK_ID` = 28 AND `e9`.`VALUE` = 0 
                                                            AND `e1`.`VALUE` = '".$payment['pp']."'
                                                            AND `e2`.`VALUE` = '".$payment['date']."'
                                                            AND `e4`.`VALUE` = '".$payment['contr_name']."'
                                                            AND `e5`.`VALUE` = '".$payment['LS']."'
                                                            AND (`e6`.`VALUE_NUM` = 0 OR `e6`.`VALUE` IS NULL)
                                                            AND (`e7`.`VALUE_NUM` = 0 OR `e7`.`VALUE` IS NULL)
                                                            AND (`e8`.`VALUE_NUM` = 0 OR `e8`.`VALUE` IS NULL)
                                                            AND `e`.`ID` != ".(int)$data['PFpayId']);
                    if($sql2->num_rows > 0)
                    {
                        $result2 = $sql2->fetch_assoc();
                        $sum = $result2['sum'] + ($payment['sum'] - $data['PFpartSum']);

                        #   Обновляем платёж
                        $query[] = array('method' => 'lists.element.update',
                            'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                'FIELDS' => array(
                                    'NAME'          => $payment['NAME'],
                                    'PROPERTY_149' => $payment['pp'],                       #   ПП
                                    'PROPERTY_146' => $payment['date'],                     #   Дата
                                    'PROPERTY_147' => $data['PFpartSum'],                  #   Сумма
                                    'PROPERTY_152' => $payment['company'],                  #   Компания
                                    'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                    'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                    'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                    'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                    'PROPERTY_155' => 0,                               #   Сделка
                                    'PROPERTY_156' => 0,                   #   ID счёта
                                    #'PROPERTY_1047' => '',                      #   Счёт
                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                    'PROPERTY_157' => 0,            #   Задача
                                    'PROPERTY_160' => 0,            #
                                    'PROPERTY_159' => 0,
                                    'PROPERTY_158' => '',                 #   Ссылка на счёт
                                    'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                    'PROPERTY_165' => trim($data['PFpartComm']),                  #   комментарий
                                    'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                    'PROPERTY_161' => (int)$data['PFpartTask'],
                                    'PROPERTY_254' => 0,
                                    'PROPERTY_271' => $file_id,
                                    'PROPERTY_270' => $file_url
                                )));
                        $query[] = array('method' => 'lists.element.update',
                            'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                'FIELDS' => array(
                                    'NAME'          => $result2['NAME'],
                                    'PROPERTY_149' => $payment['pp'],                       #   ПП
                                    'PROPERTY_146' => $payment['date'],                     #   Дата
                                    'PROPERTY_147' => ($sum + $result2['sum']),            #   Сумма
                                    'PROPERTY_152' => $payment['company'],                  #   Компания
                                    'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                    'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                    'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                    'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                    'PROPERTY_160' => 0,                       #   Л/С
                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                    'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                    'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                    'PROPERTY_165' => trim($payment['comment']),
                                    'PROPERTY_254' => $payment['comand'],
                                    'PROPERTY_271' => $payment['file_a'],
                                    'PROPERTY_270' => $payment['file_url']
                                )));
                    }
                    else
                    {
                        $sum = $payment['sum'] - $data['PFpartSum'];

                        $query[] = array('method' => 'lists.element.update',
                            'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                'FIELDS' => array(
                                    'NAME'          => $payment['NAME'],
                                    'PROPERTY_149' => $payment['pp'],                       #   ПП
                                    'PROPERTY_146' => $payment['date'],                     #   Дата
                                    'PROPERTY_147' => $data['PFpartSum'],                  #   Сумма
                                    'PROPERTY_152' => $payment['company'],                  #   Компания
                                    'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                    'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                    'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                    'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                    'PROPERTY_155' => 0,                               #   Сделка
                                    'PROPERTY_156' => 0,                   #   ID счёта
                                    'PROPERTY_1047' => '',                      #   Счёт
                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                    'PROPERTY_160' => 0,            #
                                    'PROPERTY_157' => 0,            #   Задача
                                    'PROPERTY_159' => '',
                                    'PROPERTY_158' => '',                 #   Ссылка на счёт
                                    'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                    'PROPERTY_165' => trim($data['PFpartComm']),                  #   комментарий
                                    'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                    'PROPERTY_161' => (int)$data['PFpartTask'],
                                    'PROPERTY_254' => 0,
                                    'PROPERTY_271' => $file_id,
                                    'PROPERTY_270' => $file_url
                                )));
                        $query[] = array('method' => 'lists.element.add',
                            'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => microtime(true),
                                'FIELDS' => array(
                                    'NAME'          => $payment['NAME'],
                                    'PROPERTY_149' => $payment['pp'],                       #   ПП
                                    'PROPERTY_146' => $payment['date'],                     #   Дата
                                    'PROPERTY_147' => $sum,                                #   Сумма
                                    'PROPERTY_152' => $payment['company'],                  #   Компания
                                    'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                    'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                    'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                    'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                    'PROPERTY_160' => 0,                      #   Дата редактирования
                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                    'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                    'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                    'PROPERTY_165' => trim($payment['comment']),
                                )));
                    }

                    $out['status'] = 1;
                }
            }

            #   Разбиваем на счёт
            if($data['selTypeList'] == 3 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['part'] == 1))
            {
                $out['log'] .= 1;
                if($data['PFpayTypeList'] == 283)
                {
                    $partInvoice = 1;
                    $sum = 0;
                    $result = $this->getPay($data['PFpayId'], 283);
                    if(empty($file_id))
                    {
                        $file_id  = $result['file_a'];
                        $file_url = $result['file_url'];
                    }
                    if($result['sum'] == $data['PFpartSum'] && $data['PFpartSum'] > 0 && $data['PFpartTask'] > 0)
                    {
                        $invLink = '';
                        $sql = $this->db->query("SELECT * FROM `b_crm_dynamic_items_159` WHERE `ID` = ".(int)$data['PFpartTask']);
                        if($sql->num_rows > 0)
                        {
                            $dyn = $sql->fetch_assoc();
                            if($dyn['OPPORTUNITY'] == $data['PFpartSum'])
                                $sum = $data['PFpartSum'];
                            
                            $invLink = '<a href="/crm/type/159/details/'.$dyn['ID'].'/">'.$dyn['TITLE'].'</a>';
                        }

                        #   Сделка
                        $sql1 = $this->db->query("SELECT `SRC_ENTITY_ID` AS `deal` FROM `b_crm_entity_relation` WHERE `DST_ENTITY_ID` = ".(int)$data['PFpartTask']." AND `DST_ENTITY_TYPE_ID` = 159");
                        if($sql1->num_rows > 0)
                        {
                            $result1 = $sql1->fetch_assoc();
                            $deal = 'D_'.$result1['deal'];
                            #$sql2 = $this->db->query("SELECT `ID` FROM `b_crm_deal` WHERE `ID` = ".(int)$result1['deal']." AND `STAGE_ID` NOT LIKE '%WON' AND `STAGE_ID` NOT LIKE '%LOSE'");
                            #if($sql2->num_rows <= 0)
                            #{
                            #    echo json_encode($out);
                            #    exit;
                            #}
                        }
                        else
                            $deal = '';
                        
                        if($sum > 0)
                        {
                            $sql = $this->db->query("SELECT * FROM `b_crm_dynamic_items_159` WHERE `ID` = ".(int)$result['inv_id']);
                            if($sql->num_rows > 0)
                            {
                                $dyn = $sql->fetch_assoc();
                                if($dyn['ID'] != $data['PFpartTask'])
                                {
                                    if($dyn['STAGE_ID'] == 'DT159_1:SUCCESS' && $data['PFpartTask'] != $result['inv_id'])
                                    {
                                        #$this->db->query("UPDATE `b_crm_dynamic_items_159` SET `UF_PAY_ID` = 0, `STAGE_ID` = 'DT159_1:NEW' WHERE `ID` = ".(int)$result['inv_id']);
                                    }
                                }
                            }
                            
                            $query[$data['PFpartTask']] = array('method' => 'lists.element.update',
                                'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                    'FIELDS' => array(
                                        'NAME'         => $result['NAME'],
                                        'PROPERTY_149' => $result['pp'],                       #   ПП
                                        'PROPERTY_146' => $result['date'],                     #   Дата
                                        'PROPERTY_147' => $result['sum'],                      #   Сумма
                                        'PROPERTY_152' => $result['company'],                  #   Компания
                                        'PROPERTY_150' => $result['INN'],                      #   ИНН
                                        'PROPERTY_151' => trim($result['contr_name']),         #   Контрагент
                                        'PROPERTY_154' => trim($result['naznach']),            #   Назначение платежа
                                        'PROPERTY_153' => $result['LS'],                       #   Л/С
                                        'PROPERTY_155' => $deal,                               #   Сделка
                                        'PROPERTY_156' => (int)$data['PFpartTask'],            #   ID счёта
                                        'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                        'PROPERTY_162' => date('Y-m-d'),                #   Дата редактирования
                                        'PROPERTY_157' => 0,                                   #   Задача
                                        'PROPERTY_159' => '',                                  #   Ссылка на задачу
                                        'PROPERTY_158' => $invLink,                            #   Ссылка на счёт
                                        'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                        'PROPERTY_160' => 0,                                   #   Наличка
                                        'PROPERTY_161' => 0,                                   #   Зарплата
                                        'PROPERTY_165' => $data['PFpartComm'],                 #   комментарий
                                        'PROPERTY_166' => $result['card'],                     #   ответственный за карту
                                        'PROPERTY_254' => $result['comand'],
                                        'PROPERTY_260' => $result['contr2'],               #   контрагент
                                        'PROPERTY_271' => $file_id,                     #   ответственный за карту
                                        'PROPERTY_270' => $file_url
                                    )));

                            $out['status'] = 2;
                        }
                    }
                    elseif($result['sum'] > $data['PFpartSum'] && $data['PFpartSum'] > 0 && $data['PFpartTask'] > 0)
                    {
                        $invLink = '';
                        $sql = $this->db->query("SELECT * FROM `b_crm_dynamic_items_159` WHERE `ID` = " . (int)$data['PFpartTask']);
                        if ($sql->num_rows > 0) {
                            $dyn = $sql->fetch_assoc();

                            if ($dyn['OPPORTUNITY'] == $data['PFpartSum'])
                                $sum = $data['PFpartSum'];

                            $invLink = '<a href="/crm/type/159/details/' . $dyn['ID'] . '/">' . $dyn['TITLE'] . '</a>';
                        }

                        #   Сделка
                        $sql1 = $this->db->query("SELECT `SRC_ENTITY_ID` AS `deal` FROM `b_crm_entity_relation` WHERE `DST_ENTITY_ID` = " . (int)$data['PFpartTask'] . " AND `DST_ENTITY_TYPE_ID` = 159");
                        if ($sql1->num_rows > 0) 
                        {
                            $result1 = $sql1->fetch_assoc();
                            $deal = 'D_' . $result1['deal'];
                            #$sql2 = $this->db->query("SELECT `ID` FROM `b_crm_deal` WHERE `ID` = ".(int)$result1['deal']." AND `STAGE_ID` NOT LIKE '%WON' AND `STAGE_ID` NOT LIKE '%LOSE'");
                            #if($sql2->num_rows <= 0)
                            #{
                            #    echo json_encode($out);
                            #    exit;
                            #}
                        } 
                        else
                            $deal = '';

                        if ($sum > 0)
                        {
                            $sql = $this->db->query("SELECT * FROM `b_crm_dynamic_items_159` WHERE `ID` = " . (int)$result['inv_id']);
                            if ($sql->num_rows > 0)
                            {
                                $dyn = $sql->fetch_assoc();
                                if ($dyn['ID'] != $data['PFpartTask']) {
                                    if ($dyn['STAGE_ID'] == 'DT159_1:SUCCESS' && $data['PFpartTask'] != $result['inv_id']) {
                                        #$this->db->query("UPDATE `b_crm_dynamic_items_159` SET `UF_PAY_ID` = 0, `STAGE_ID` = 'DT159_1:NEW' WHERE `ID` = " . (int)$result['inv_id']);
                                    }
                                }
                            }

                            $query[$data['PFpartTask']] = array('method' => 'lists.element.update',
                            'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $data['PFpayId'],
                                'FIELDS' => array(
                                    'NAME' => $result['NAME'],
                                    'PROPERTY_149' => $result['pp'],                       #   ПП
                                    'PROPERTY_146' => $result['date'],                     #   Дата
                                    'PROPERTY_147' => $sum,                                #   Сумма
                                    'PROPERTY_152' => $result['company'],                  #   Компания
                                    'PROPERTY_150' => $result['INN'],                      #   ИНН
                                    'PROPERTY_151' => trim($result['contr_name']),         #   Контрагент
                                    'PROPERTY_154' => trim($result['naznach']),            #   Назначение платежа
                                    'PROPERTY_153' => $result['LS'],                       #   Л/С
                                    'PROPERTY_155' => $deal,                               #   Сделка
                                    'PROPERTY_156' => (int)$data['PFpartTask'],            #   ID счёта
                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                    'PROPERTY_162' => date('Y-m-d'),                #   Дата редактирования
                                    'PROPERTY_157' => 0,                                   #   Задача
                                    'PROPERTY_159' => '',                                  #   Ссылка на задачу
                                    'PROPERTY_158' => $invLink,                            #   Ссылка на счёт
                                    'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                    'PROPERTY_160' => 0,                                   #   Наличка
                                    'PROPERTY_161' => 0,                                   #   Зарплата
                                    'PROPERTY_165' => $data['PFpartComm'],                 #   комментарий
                                    'PROPERTY_166' => $result['card'],                     #   ответственный за карту
                                    'PROPERTY_254' => $result['comand'],
                                    'PROPERTY_260' => $result['contr2'],               #   контрагент
                                    'PROPERTY_271' => $file_id,                     #   ответственный за карту
                                    'PROPERTY_270' => $file_url
                                )));

                            $query[] = array('method' => 'lists.element.add',
                                'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_CODE' => md5($result['pp'].$result['date'].($result['sum'] - $sum).$result['INN'].$result['LS']),
                                    'FIELDS' => array(
                                        'NAME' => $result['NAME'],
                                        'PROPERTY_149' => $result['pp'],                       #   ПП
                                        'PROPERTY_146' => $result['date'],                     #   Дата
                                        'PROPERTY_147' => ($result['sum'] - $sum),             #   Сумма
                                        'PROPERTY_152' => $result['company'],                  #   Компания
                                        'PROPERTY_150' => $result['INN'],                      #   ИНН
                                        'PROPERTY_151' => trim($result['contr_name']),         #   Контрагент
                                        'PROPERTY_154' => trim($result['naznach']),            #   Назначение платежа
                                        'PROPERTY_153' => $result['LS'],                       #   Л/С
                                        'PROPERTY_155' => 0,                                   #   Сделка
                                        'PROPERTY_156' => 0,                                   #   ID счёта
                                        'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                        'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                        'PROPERTY_157' => 0,                                   #   Задача
                                        'PROPERTY_159' => '',                                  #   Ссылка на задачу
                                        'PROPERTY_158' => '',                                  #   Ссылка на счёт
                                        'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                        'PROPERTY_160' => 0,                                   #   Наличка
                                        'PROPERTY_161' => 0,                                   #   Зарплата
                                        'PROPERTY_165' => '',                                  #   комментарий
                                        'PROPERTY_166' => '',                                  #   ответственный за карту
                                        'PROPERTY_254' => '',                                  #   ответственный за карту
                                    )));

                            $out['status'] = 2;
                        }
                    }
                }
            }

            if(!empty($query))
            {
                /*
                if(isset($payment['invoice']) && $payment['invoice'] > 0)
                {
                    $query[] = array('method' => 'crm.item.update',
                        'params' => array('entityTypeId' => 159,
                            'id' => $payment['invoice'],
                            'fields' => array('STAGE_ID' => 'DT159_1:NEW', 'UF_ID_PAY' => 0)));
                }

                if(isset($result['invoice']) && $result['invoice'] > 0)
                {
                    $query[] = array('method' => 'crm.item.update',
                        'params' => array('entityTypeId' => 159,
                            'id' => $result['invoice'],
                            'fields' => array('STAGE_ID' => 'DT159_1:NEW', 'UF_ID_PAY' => 0)));
                }

                if(isset($payment['inv_id']) && $payment['inv_id'] > 0)
                {
                    $query[] = array('method' => 'crm.item.update',
                        'params' => array('entityTypeId' => 159,
                            'id' => $payment['inv_id'],
                            'fields' => array('STAGE_ID' => 'DT159_1:NEW', 'UF_ID_PAY' => 0)));
                }

                if(isset($result['inv_id']) && $result['inv_id'] > 0)
                {
                    $query[] = array('method' => 'crm.item.update',
                        'params' => array('entityTypeId' => 159,
                            'id' => $result['inv_id'],
                            'fields' => array('STAGE_ID' => 'DT159_1:NEW', 'UF_ID_PAY' => 0)));
                }*/
                $res = CRest::callBatch($query);
                if($partInvoice == 1 && !empty($res['result']['result'][$data['PFpartTask']]))
                {
                    $fld = array('ufIdPay' => $data['PFpayId'], 'ufPayDate' => $result['date'], 'ufPayNote' => trim($result['naznach']));
                    if($dyn['OPPORTUNITY'] == $data['PFpartSum'])
                        $fld['stageId'] = 'DT159_1:SUCCESS';

                    CRest::call('crm.item.update', array('entityTypeId' => 159,
                                'id' => $data['PFpartTask'],
                                'fields' => $fld));
                }

                if($data['selTypeList'] == 2)
                {
                    $out['zp1'] = $this->zpList(1);
                    $out['zp2'] = $this->zpList();
                }
                elseif($calcComand == 1)
                {
                    $this->calcComand();
                }
                elseif($calcRent == 1)
                {
                    $this->calcRent();
                }
            }

        }
        else
            echo 'Введены не все данные';
            
        if($out['status'] == 2)
        {
            if($data['PFpayTypeList'] == 283)
            {
                $result = $this->getPay($data['PFpayId'], 283);
                
                if(!empty($result['task_link']))
                {
                    $t = unserialize($result['task_link']);
                    $out['task'] = $t['TEXT'];
                }
                else
                    $out['task'] = '';
                
                if(!empty($result['inv_link']))
                {
                    $i = unserialize($result['inv_link']);
                    $out['inv'] = $i['TEXT'];
                }
                else
                    $out['inv'] = '';
            }
            else
            {
                $result = $this->getPay($data['PFpayId'], 274);
                if(!empty($result['task_link']))
                {
                    $t = unserialize($result['task_link']);
                    $out['task'] = $t['TEXT'];
                }
                else
                    $out['task'] = '';
                    
                if(!empty($result['invoice_link']))
                {
                    $i = unserialize($result['invoice_link']);
                    $out['inv'] = $i['TEXT'];
                }
                else
                    $out['inv'] = '';
            }
        }

        #$this->calcComand();
        echo json_encode($out);
        exit;
    }
    
    #   Множественное разбиение на зарплату
    public function saveMoreZP($post)
    {
        $query = array();
        $query2 = array();
        
        if($post['task'] > 0 && $post['payments'] > 0 && ($post['type'] == 283 || $post['type'] == 274) && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp3'] == 1))
        {
                $tmpP = explode(',', $post['payments']);
                
                $i = 0;
                foreach($tmpP as $id_p)
                {
                    $payment = array();
                    $insert  = array();
                    
                    $payment = $this->getPay($id_p, $post['type']);
                    
                    $partSum = 0;
                    $sql = $this->db->query("SELECT `VALUE_NUM` AS `sum` 
                                             FROM `b_iblock_element_property` 
                                             WHERE `IBLOCK_PROPERTY_ID` = 138 AND `IBLOCK_ELEMENT_ID` = ".(int)$post['task']);
                    if($sql->num_rows > 0)
                    {
                        $result = $sql->fetch_assoc();
                        $partSum += $result['sum'];
                    }
                    
                    $sql = $this->db->query("SELECT `VALUE_NUM` AS `sum` 
                                             FROM `b_iblock_element_property` 
                                             WHERE `IBLOCK_PROPERTY_ID` = 258 AND `IBLOCK_ELEMENT_ID` = ".(int)$post['task']);
                    if($sql->num_rows > 0)
                    {
                        $result = $sql->fetch_assoc();
                        $partSum += $result['sum'];
                    }
                    
                    $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum`
                                             FROM `b_iblock_element_property`
                                             WHERE `IBLOCK_PROPERTY_ID` = 147 AND `IBLOCK_ELEMENT_ID` IN(SELECT `IBLOCK_ELEMENT_ID`
                                                                                                         FROM `b_iblock_element_property`
                                                                                                         WHERE `IBLOCK_PROPERTY_ID` = 161 AND `VALUE` = ".(int)$post['task'].")");
                    if($sql->num_rows > 0)
                    {
                        $resSum = $sql->fetch_assoc();
                        $partSum += $resSum['sum'];
                    }
                    
                    $sql = $this->db->query("SELECT SUM(`VALUE_NUM`) AS `sum`
                                             FROM `b_iblock_element_property`
                                             WHERE `IBLOCK_PROPERTY_ID` = 213 AND `IBLOCK_ELEMENT_ID` IN(SELECT `IBLOCK_ELEMENT_ID`
                                                                                                         FROM `b_iblock_element_property`
                                                                                                         WHERE `IBLOCK_PROPERTY_ID` = 223 AND `VALUE` = ".(int)$post['task'].")");
                    if($sql->num_rows > 0)
                    {
                        $resSum = $sql->fetch_assoc();
                        $partSum += $resSum['sum'];
                    }
                    
                    $partSum += $post['PFpartSum'];

                    if($partSum < 0)
                    {
                        continue;
                    }

                    if(!empty($payment) && (int)$payment['task'] == 0 && (int)$payment['ZP'] == 0 && empty($payment['inv_id']) && empty($payment['invoice']))
                    {
                        $i++;
                        
                        if($post['type'] == 283)
                        {
                            if($i < 50)
                            {
                                $query[] = array('method' => 'lists.element.update',
                                                 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id_p,
                                                                   'FIELDS' => array(
                                                                                    'NAME'         => $payment['NAME'],
                                                                                    'PROPERTY_149' => $payment['pp'],                       #   ПП
                                                                                    'PROPERTY_146' => $payment['date'],                     #   Дата
                                                                                    'PROPERTY_147' => $payment['sum'],                  #   Сумма
                                                                                    'PROPERTY_152' => $payment['company'],                  #   Компания
                                                                                    'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                                                                    'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                                                                    'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                                                                    'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                                                                    'PROPERTY_155' => 0,                               #   Сделка
                                                                                    'PROPERTY_156' => 0,                   #   ID счётаs
                                                                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                                    'PROPERTY_160' => 0,            #   
                                                                                    'PROPERTY_157' => 0,            #   Задача
                                                                                    'PROPERTY_159' => '',
                                                                                    'PROPERTY_158' => '',#INVOICE_LINK
                                                                                    'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                                                                    'PROPERTY_165' => trim($payment['comment']),                  #   комментарий
                                                                                    'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                                                                    'PROPERTY_161' => (int)$post['task'],
                                                                                    'PROPERTY_254' => $payment['comand'],               #   контрагент
                                                                                    'PROPERTY_260' => $payment['contr2'],               #   контрагент
                                                                                    'PROPERTY_271' => $payment['file_a'],
                                                                                   )));
                                #   Лог
                                if($payment['task'] > 0 || $payment['inv_id'] > 0 || $payment['ZP'] > 0 && $payment['ZP'] != $post['task'])
                                {
                                    /*
                                    $query2[] = array('method' => 'lists.element.add',
                                                      'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                                        'FIELDS' => array(
                                                                                          'NAME' => $id_p,
                                                                                          'PROPERTY_2717' => $payment['date'],
                                                                                          'PROPERTY_2718' => $payment['sum'],
                                                                                          'PROPERTY_2723' => $payment['task'],
                                                                                          'PROPERTY_2724' => $payment['inv_id'],
                                                                                          'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                                          'PROPERTY_2726' => $payment['ZP']
                                                                                          )));
                                                                                          */
                                }
                            }
                            else
                            {
                                CRest::callBatch($query);
                                $i = 0;
                                $query = array();
                                sleep(2);
                                CRest::callBatch($query2);
                                $query2 = array();
                            }
                        }
                        elseif($post['type'] == 274)
                        {
                            if($i < 50)
                            {
                                $query[] = array('method' => 'lists.element.update',
                                                 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id_p,
                                                                   'FIELDS' => array(
                                                                                'NAME'          => $payment['NAME'],
                                                                                'PROPERTY_147'  => (float)$payment['sum'],
                                                                                'PROPERTY_156'  => 0,
                                                                                'PROPERTY_164'  => $payment['otvetstv'],
                                                                                'PROPERTY_146'  => $payment['date'],
                                                                                'PROPERTY_163' => $payment['kassir'],
                                                                                'PROPERTY_157' => 0,
                                                                                'PROPERTY_155' => 0,
                                                                                'PROPERTY_159' => 0,
                                                                                'PROPERTY_158' => '',
                                                                                'PROPERTY_165' => trim($payment['comment']),
                                                                                'PROPERTY_161' => $post['task'],
                                                                                'PROPERTY_160' => 1,
                                                                                'PROPERTY_260' => $payment['contr2'],               #   контрагент
                                                                                'PROPERTY_271' => $payment['file_a'],
                                                                       )));
                                #   Лог
                                if($payment['task'] > 0 || $payment['invoice'] > 0 || $payment['ZP'] > 0 && $payment['ZP'] != $post['task'])
                                {
                                    /*
                                    $query2[] = array('method' => 'lists.element.add',
                                                      'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                                        'FIELDS' => array(
                                                                                          'NAME' => $id_p,
                                                                                          'PROPERTY_2717' => $payment['date'],
                                                                                          'PROPERTY_2718' => $payment['sum'],
                                                                                          'PROPERTY_2723' => $payment['task'],
                                                                                          'PROPERTY_2724' => $payment['invoice'],
                                                                                          'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                                          'PROPERTY_2726' => $payment['ZP']
                                                                                          )));
                                    */
                                }
                            }
                            else
                            {
                                CRest::callBatch($query);
                                $i = 0;
                                $query = array();
                                sleep(2);
                                CRest::callBatch($query2);
                                $query2 = array();
                            }

                        }

                    }
                }
                
                if(!empty($query))
                {
                    CRest::callBatch($query);
                    $i = 0;
                    $query = array();
                    sleep(2);
                    CRest::callBatch($query2);
                    $query2 = array();
                }
                
                #$this->calcComand();
                #if($ins == 1)
                    echo "Изменения приняты.\r\nОбновление списка платежей может занять несколько минут";
                #else
                #    echo 'Ошибка записи';
        }
        else
            echo 'Что-то пошло не так';
        
        exit;
    }
    
    #   Множественное разбиение на задачу
    public function saveMorePart($post)
    {
        $ins = 0;
        if($post['task'] > 0 && $post['payments'] > 0 && ($post['type'] == 283 || $post['type'] == 274) && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['part'] == 1))
        {
            $deal = 0;
            $sql = $this->db->query("SELECT `b`.`ID`, LEFT(`b`.`TITLE`, 60) AS `TITLE`, `ut`.`UF_CRM_TASK`
                                     FROM `b_tasks` AS `b`
                                     LEFT JOIN `b_uts_tasks_task` AS `ut` ON `ut`.`VALUE_ID` = `b`.`ID`
                                     WHERE `b`.`ID` = ".(int)$post['task']);
            if($sql->num_rows > 0)
            {
                $result = $sql->fetch_assoc();
                
                $task = $result['TITLE'];
                
                if(!empty($result['UF_CRM_TASK']))
                {
                    $tmp = unserialize($result['UF_CRM_TASK']);
                    if(isset($tmp[0]))
                    {
                        $deal = $tmp[0];
                        $tmp2 = explode('_', $deal);
                        if(!empty($tmp2[1]) && $tmp[0] == 'D')
                        {
                            $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_deal` WHERE `ID` = ".(int)$tmp2[1]." AND `STAGE_ID` NOT LIKE '%WON' AND `STAGE_ID` NOT LIKE '%LOSE'");
                            if($sql2->num_rows <= 0)
                            {
                                echo 'Нельзя разбить платежи на закрытую сделку';
                                exit;
                            }
                        }
                        elseif($tmp[0] == 'L')
                        {
                            $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_lead` WHERE `ID` = ".(int)$tmp[1]." AND `STATUS_ID` != 3 AND `STATUS_ID` != 4 AND `STATUS_ID` != 5 AND `STATUS_ID` != 'CONVERTED' AND `STATUS_ID` != 'JUNK'");
                            if($sql2->num_rows <= 0)
                            {
                                echo 'Нельзя разбить платежи на закрытый лид';
                                exit;
                            }
                        }
                        elseif($tmp[0] == 'T85')
                        {
                            $sql2 = $this->db->query("SELECT `ID` FROM `b_crm_dynamic_items_133` WHERE `ID` = ".(int)$tmp[1]." AND `STAGE_ID` != 'DT133_8:SUCCESS' AND `STAGE_ID` != 'DT133_8:FAIL' AND `STAGE_ID` != 'DT133_8:2'");
                            if($sql2->num_rows <= 0)
                            {
                                echo 'Нельзя разбить платежи на закрытый МТР';
                                exit;
                            }
                        }
                    }
                }
                
                $tmpP = explode(',', $post['payments']);
                $i = 0;
                $query = array();
                
                foreach($tmpP as $id_p)
                {
                    $payment = array();
                    $insert  = array();
                    
                    $payment = $this->getPay($id_p, $post['type']);
                    
                    if(!empty($payment))
                    {
                        $i++;
                        $payment['comment'] = (!empty($post['comment'])) ? $post['comment'] : $payment['comment'];
                        if($post['type'] == 283)
                        {
                            if($i < 50)
                            {
                                $query[] = array('method' => 'lists.element.update',
                                                 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id_p,
                                                                   'FIELDS' => array(
                                                                                    'NAME'         => $payment['NAME'],
                                                                                    'PROPERTY_149' => $payment['pp'],                       #   ПП
                                                                                    'PROPERTY_146' => $payment['date'],                     #   Дата
                                                                                    'PROPERTY_147' => $payment['sum'],                  #   Сумма
                                                                                    'PROPERTY_152' => $payment['company'],                  #   Компания
                                                                                    'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                                                                    'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                                                                    'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                                                                    'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                                                                    'PROPERTY_155' => $deal,                               #   Сделка
                                                                                    'PROPERTY_160' => 0,                   #  
                                                                                    'PROPERTY_156' => 0,                   #   ID счётаs
                                                                                    'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                                                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                                    'PROPERTY_157' => $post['task'],            #   Задача
                                                                                    'PROPERTY_159' => '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$post['task'].'/">'.htmlspecialchars($task).'</a>',#TASK_LINK,
                                                                                    'PROPERTY_158' => '',#INVOICE_LINK
                                                                                    'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                                                                    'PROPERTY_165' => trim($payment['comment']),                  #   комментарий
                                                                                    'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                                                                    'PROPERTY_161' => 0,
                                                                                    'PROPERTY_260' => $payment['contr2'],               #   контрагент
                                                                                    'PROPERTY_271' => $payment['file_a'],
                                                                                   )));

                            }
                            else
                            {
                                CRest::callBatch($query);
                                $i = 0;
                                $query = array();
                                sleep(2);
                            }
                        }
                        elseif($post['type'] == 274)
                        {
                            if($i < 50)
                            {
                                $query[] = array('method' => 'lists.element.update',
                                                 'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id_p,
                                                                   'FIELDS' => array(
                                                                                'NAME'          => $payment['NAME'],
                                                                                'PROPERTY_147'  => (float)$payment['sum'],
                                                                                'PROPERTY_156'  => 0,
                                                                                'PROPERTY_164'  => $payment['otvetstv'],
                                                                                'PROPERTY_146'  => $payment['date'],
                                                                                'PROPERTY_163' => $payment['kassir'],
                                                                                'PROPERTY_157' => (int)$post['task'],
                                                                                'PROPERTY_155' => $deal,
                                                                                'PROPERTY_159' => '<a href="/company/personal/user/'.(int)$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$post['task'].'/">'.htmlspecialchars($task).'</a>',
                                                                                'PROPERTY_158' => '',
                                                                                'PROPERTY_165' => trim($payment['comment']),
                                                                                'PROPERTY_161' => 0,
                                                                                'PROPERTY_160' => 1,
                                                                                'PROPERTY_260' => $payment['contr2'],               #   контрагент
                                                                                'PROPERTY_271' => $payment['file_a'],
                                                                       )));
                                #   Лог
                                if($payment['ZP'] > 0 || $payment['invoice'] > 0 || $payment['task'] > 0 && $payment['task'] != $post['task'])
                                {
                                    /*
                                    $query2[] = array('method' => 'lists.element.add',
                                                      'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                                        'FIELDS' => array(
                                                                                          'NAME' => $id_p,
                                                                                          'PROPERTY_2717' => $payment['date'],
                                                                                          'PROPERTY_2718' => $payment['sum'],
                                                                                          'PROPERTY_2723' => $payment['task'],
                                                                                          'PROPERTY_2724' => $payment['invoice'],
                                                                                          'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                                          'PROPERTY_2726' => $payment['ZP']
                                                                                          )));
                                    */
                                }
                            }
                            else
                            {
                                CRest::callBatch($query);
                                $i = 0;
                                $query = array();
                                sleep(2);
                                if(!empty($query2))
                                {
                                    CRest::callBatch($query2);
                                    $query2 = array();
                                }
                            }
                            
                            /*
                            $insert['type']      = 274;
                            $insert['code']      = $payment['CODE'];
                            $insert['id']        = $id_p;
                            $insert['name']      = $payment['NAME'];
                            $insert['sum']       = $payment['sum'];
                            $insert['inv']       = $payment['invoice'];
                            $insert['otv']       = $payment['otvetstv'];
                            $insert['date']      = $payment['date'];
                            $insert['kass']      = $payment['kassir'];
                            $insert['task']      = (int)$post['task'];
                            $insert['deal']      = $deal;
                            $insert['task_link'] = ($post['task'] > 0) ? '<a href="/company/personal/user/'.(int)$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$post['task'].'/">'.htmlspecialchars($task).'</a>' : '';#TASK_LINK;
                            $insert['inv_link']  = $payment['invoice_link'];
                            $insert['comment']   = trim($payment['comment']);
                            $insert['zp']        = $payment['ZP'];
                            $insert['act']       = 'update';
                            $insert['status']    = 'wait';
                            */
                        }
                    }
                }
                
                if(!empty($query))
                {
                    CRest::callBatch($query);
                    $i = 0;
                    $query = array();
                    sleep(2);
                    if(!empty($query2))
                    {
                        CRest::callBatch($query2);
                        $query2 = array();
                    }
                }
                #$this->calcComand();
                #if($ins == 1)
                    echo "Изменения приняты.\r\nОбновление списка платежей может занять несколько минут";
            }
            else
                echo 'Задача не найдена';
        }
        else
            echo 'Не указан платёж или задача';
        
        exit;
    }

    #   Множественное разбиение на командировку
    public function saveMoreComand($post)
    {
        /*
        $ins = 0;
        if($post['task'] > 0 && $post['payments'] > 0 && ($post['type'] == 283 || $post['type'] == 274) && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['part'] == 1))
        {
            $sql = $this->db->query("SELECT `d`.`ID`, `d`.`UF_TASK_ID`, `t`.`TITLE`, `ut`.`UF_CRM_TASK`
                                     FROM `b_crm_dynamic_items_167` AS `d`
                                     LEFT JOIN `b_tasks` AS `t` ON `t`.`ID` = `d`.`UF_TASK_ID`
                                     LEFT JOIN `b_uts_tasks_task` AS `ut` ON `ut`.`VALUE_ID` = `d`.`UF_TASK_ID`
                                     WHERE `d`.`ID` = ".(int)$post['task']);
            if($sql->num_rows > 0)
            {
                $result = $sql->fetch_assoc();

                if(!empty($result['UF_CRM_TASK']))
                {
                    $tmp  = unserialize($result['UF_CRM_TASK']);
                    $deal = $tmp[0];
                }
                else
                    $deal = '';

                $tmpP = explode(',', $post['payments']);
                $i = 0;
                $query = array();

                foreach($tmpP as $id_p)
                {
                    $payment = array();
                    $insert  = array();

                    $payment = $this->getPay($id_p, $post['type']);

                    if(!empty($payment))
                    {
                        $i++;

                        if($post['type'] == 283)
                        {
                            if($i < 50)
                            {
                                $query[] = array('method' => 'lists.element.update',
                                    'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id_p,
                                        'FIELDS' => array(
                                            'NAME'         => $payment['NAME'],
                                            'PROPERTY_149' => $payment['pp'],                       #   ПП
                                            'PROPERTY_146' => $payment['date'],                     #   Дата
                                            'PROPERTY_147' => $payment['sum'],                  #   Сумма
                                            'PROPERTY_152' => $payment['company'],                  #   Компания
                                            'PROPERTY_150' => $payment['INN'],                      #   ИНН
                                            'PROPERTY_151' => trim($payment['contr_name']),               #   Контрагент
                                            'PROPERTY_154' => trim($payment['naznach']),                  #   Назначение платежа
                                            'PROPERTY_153' => $payment['LS'],                       #   Л/С
                                            'PROPERTY_155' => $deal,                               #   Сделка
                                            'PROPERTY_160' => $payment['nal_pay'],                   #
                                            'PROPERTY_156' => $payment['inv'],                   #   ID счётаs
                                            'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],    #   Оператор
                                            'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                            'PROPERTY_157' => $result['UF_TASK_ID'],            #   Задача
                                            'PROPERTY_159' => '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$result['UF_TASK_ID'].'/">'.htmlspecialchars($result['TITLE']).'</a>',#TASK_LINK,],#TASK_LINK,
                                            'PROPERTY_158' => $payment['inv_link'],#INVOICE_LINK
                                            'PROPERTY_148' => $payment['sum_osn'],                  #   Сумма (осн)
                                            'PROPERTY_165' => trim($payment['comment']),                  #   комментарий
                                            'PROPERTY_166' => $payment['card'],                     #   ответственный за карту
                                            'PROPERTY_161' => $payment['ZP'],
                                            'PROPERTY_254' => (int)$post['task']
                                        )));
                                #   Лог
                                if($payment['ZP'] > 0 || $payment['inv_id'] > 0 || $payment['task'] > 0 && $payment['task'] != $post['task'])
                                {
                                    /*
                                    $query2[] = array('method' => 'lists.element.add',
                                                      'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                                        'FIELDS' => array(
                                                                                          'NAME' => $id_p,
                                                                                          'PROPERTY_2717' => $payment['date'],
                                                                                          'PROPERTY_2718' => $payment['sum'],
                                                                                          'PROPERTY_2723' => $payment['task'],
                                                                                          'PROPERTY_2724' => $payment['inv_id'],
                                                                                          'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                                          'PROPERTY_2726' => $payment['ZP']
                                                                                          )));
                                    */
                                    /*
                                }
                            }
                            else
                            {
                                CRest::callBatch($query);
                                $i = 0;
                                $query = array();
                                sleep(2);
                                #CRest::callBatch($query2);
                                #$query2 = array();
                            }

                        }
                        elseif($post['type'] == 274)
                        {
                            if($i < 50)
                            {
                                $query[] = array('method' => 'lists.element.update',
                                    'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $id_p,
                                        'FIELDS' => array(
                                            'NAME'         => $payment['NAME'],
                                            'PROPERTY_147' => (float)$payment['sum'],
                                            'PROPERTY_156' => $payment['invoice'],
                                            'PROPERTY_164' => $payment['otvetstv'],
                                            'PROPERTY_146' => $payment['date'],
                                            'PROPERTY_163' => $_SESSION['bitAppPayment']['ID'],
                                            'PROPERTY_157' => (int)$post['task'],
                                            'PROPERTY_155' => $deal,
                                            'PROPERTY_159' => '<a href="/company/personal/user/'.$_SESSION['bitAppPayment']['ID'].'/tasks/task/view/'.(int)$result['UF_TASK_ID'].'/">'.htmlspecialchars($result['TITLE']).'</a>',#TASK_LINK,],#TASK_LINK,
                                            'PROPERTY_158' => $payment['invoice_link'],
                                            'PROPERTY_165' => trim($payment['comment']),
                                            'PROPERTY_161' => $payment['ZP'],
                                            'PROPERTY_160' => $payment['nal_pay'],
                                            'PROPERTY_254' => (int)$post['task']
                                        )));
                                #   Лог
                                if($payment['ZP'] > 0 || $payment['invoice'] > 0 || $payment['task'] > 0 && $payment['task'] != $post['task'])
                                {
                                    /*
                                    $query2[] = array('method' => 'lists.element.add',
                                                      'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 1041, 'ELEMENT_CODE' => microtime(true).rand(163, 1630),
                                                                        'FIELDS' => array(
                                                                                          'NAME' => $id_p,
                                                                                          'PROPERTY_2717' => $payment['date'],
                                                                                          'PROPERTY_2718' => $payment['sum'],
                                                                                          'PROPERTY_2723' => $payment['task'],
                                                                                          'PROPERTY_2724' => $payment['invoice'],
                                                                                          'PROPERTY_2725' => $_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME'],
                                                                                          'PROPERTY_2726' => $payment['ZP']
                                                                                          )));
                                    */
                                    /*
                                }
                            }
                            else
                            {
                                CRest::callBatch($query);
                                $i = 0;
                                $query = array();
                                sleep(2);
                                if(!empty($query2))
                                {
                                    CRest::callBatch($query2);
                                    $query2 = array();
                                }
                            }
                        }
                    }
                }

                if(!empty($query))
                {
                    CRest::callBatch($query);
                    $i = 0;
                    $query = array();
                    sleep(2);
                    if(!empty($query2))
                    {
                        CRest::callBatch($query2);
                        $query2 = array();
                    }
                }

                $this->calcComand();
                #if($ins == 1)
                echo "Изменения приняты.\r\nОбновление списка платежей может занять несколько минут";
            }
            else
                echo 'Команлировка не найдена';
        }
        else
            echo 'Не указан платёж или команлировка';
        */
        exit;
    }
    
    #   Сумма платежа
    public function getSum($id)
    {
        $return = 0;
        
        $sql = $this->db->query("SELECT `e0`.`VALUE_NUM` AS `ZP`, `e3`.`VALUE_NUM` AS `ZP2`, `e1`.`VALUE_NUM` AS `NAL`, `e2`.`VALUE_NUM` AS `BNAL`
                                 FROM `b_iblock_element` AS `e`
                                 LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 138
                                 LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 258
                                 LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 147
                                 LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 147
                                 WHERE `e`.`ID` = ".(int)$id);
        if($sql->num_rows > 0)
        {
            $result = $sql->fetch_assoc();
            $result['ZP'] = $result['ZP'] + $result['ZP2'];
            if(!is_null($result['ZP']) || $result['ZP'] <> 0)
                $return = (float)$result['ZP'];
            elseif(!is_null($result['NAL']) || $result['NAL'] <> 0)
                $return = (float)$result['NAL'];
            elseif(!is_null($result['BNAL']) || $result['BNAL'] <> 0)
                $return = (float)$result['BNAL'];
        }
        
        return $return;
    }
    
    #   Список задач
    public function taskList()
    {
        $return = array();
        $closeDeal = '';
        $arDeal = array();
        $sql = $this->db->query("SELECT `ID` FROM `b_crm_deal` WHERE `STAGE_ID` LIKE '%WON' OR `STAGE_ID` LIKE '%LOSE'");
        if($sql->num_rows> 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arDeal[] = "'".serialize(array('D_'.$result['ID']))."'";
            }
        }

        $sql = $this->db->query("SELECT `ID` FROM `b_crm_lead` WHERE `STATUS_ID` = 3 OR `STATUS_ID` = 4 OR `STATUS_ID` = 5 OR `STATUS_ID` = 'CONVERTED' OR `STATUS_ID` = 'JUNK'");
        if($sql->num_rows> 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arDeal[] = "'".serialize(array('L_'.$result['ID']))."'";
            }
        }

        $sql = $this->db->query("SELECT `ID` FROM `b_crm_dynamic_items_133` WHERE `STAGE_ID` = 'DT133_8:SUCCESS' OR `STAGE_ID` = 'DT133_8:FAIL' OR `STAGE_ID` = 'DT133_8:2'");
        if($sql->num_rows> 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arDeal[] = "'".serialize(array('T85_'.$result['ID']))."'";
            }
        }

        if(!empty($arDeal))
        {
            $closeDeal = " AND `tt`.`UF_CRM_TASK` NOT IN(".implode(',', $arDeal).")";
        }
        
        $sql = $this->db->query("SELECT `t`.`ID`, LEFT(`t`.`TITLE`, 60) AS `TITLE`, `t`.`STATUS`
                                 FROM `b_tasks` AS `t`
                                 LEFT JOIN `b_uts_tasks_task` AS `tt` ON `tt`.`VALUE_ID` = `t`.`ID`
                                 WHERE `t`.`STATUS` != 2 AND (`t`.`CREATED_BY` = ".(int)$_SESSION['bitAppPayment']['ID']." OR `t`.`RESPONSIBLE_ID` = ".(int)$_SESSION['bitAppPayment']['ID']." OR `t`.`ID` IN(SELECT DISTINCT(`TASK_ID`) FROM `b_tasks_member` WHERE `USER_ID` = ".(int)$_SESSION['bitAppPayment']['ID'].")) AND `t`.`ZOMBIE` = 'N' 
                                    ".$closeDeal."
                                 ORDER BY `t`.`STATUS` ASC, `t`.`TITLE` ASC");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $return[$result['ID']]['title'] = $result['TITLE'];
                $return[$result['ID']]['status'] = $result['STATUS'];
            }
        }
        
        return $return;
    }
    
    #   Список зарплаты
    public function zpList($type = 0, $arr = 0, $id = 0)
    {
        $return  = ($type == 0 || $type == 2) ? '' : '<optgroup label="{DEBT}"><option></option></optgroup>';
        $debtSum = 0;
        $retSum  = 0;
        
        if($_SESSION['bitAppPayment']['ADMIN'] == 1 || isset($_SESSION['bitAppPayment']['ACCESS']['zp']) && $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1)
        {
            $paid = array();
            $sql = $this->db->query("SELECT `e0`.`VALUE` AS `ID`, `e1`.`VALUE_NUM` AS `sum`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 161
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 147
                                     WHERE `e`.`IBLOCK_ID` = 28 AND `e0`.`VALUE` > 0
                                     GROUP BY `e`.`ID`");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!isset($paid[$result['ID']]))
                        $paid[$result['ID']] = 0;
                    
                    $paid[$result['ID']] += $result['sum'];
                }
            }
/*  Топливо */
            $sql = $this->db->query("SELECT `e0`.`VALUE` AS `ID`, `e1`.`VALUE_NUM` AS `sum`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 223
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 213
                                     WHERE `e`.`IBLOCK_ID` = 42 AND `e0`.`VALUE` > 0");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!isset($paid[$result['ID']]))
                        $paid[$result['ID']] = 0;

                    $paid[$result['ID']] += $result['sum'];
                }
            }

            $sql = $this->db->query("SELECT `e`.`ID`, `e2`.`VALUE` AS `date`, `e0`.`VALUE_NUM` AS `ZP`, `e3`.`VALUE_NUM` AS `ZP2`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`, `u`.`SECOND_NAME`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 138
                                     LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 258
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 142
                                     LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                     LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                     WHERE `e`.`IBLOCK_ID` = 27 AND `e0`.`VALUE_NUM` > 0
                                     GROUP BY `e`.`ID`
                                     ORDER BY `u`.`LAST_NAME` ASC, ' ', `u`.`NAME` ASC, `e2`.`VALUE` ASC");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    #$result['ZP'] = $result['ZP'] + $result['ZP2'];
                    if($_SESSION['bitAppPayment']['ADMIN'] == 1)
                        $zp = (isset($paid[$result['ID']])) ? ($result['ZP'] + $result['ZP2'] + $paid[$result['ID']]) : $result['ZP'] + $result['ZP2'] ;
                    else
                        $zp = (isset($paid[$result['ID']])) ? ($result['ZP'] + $paid[$result['ID']]) : $result['ZP'];

                    if((int)$zp >= 500)
                    {
                        $debtSum += $zp;
                        $zp = ($zp < 0) ? '<span class="text-error">Оклад: '.number_format($result['ZP'], 2, '.', ' ').' / Остаток: '.number_format($zp, 2, '.', ' ').'</span>' : 'Оклад: '.number_format($result['ZP'], 2, '.', ' ').' / Остаток: '.((!empty($zp)) ? number_format($zp, 2, '.', ' ') : '');

                        if($type == 0)
                            $return .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME'] ?? '').' ('.$zp.')</div>';
                        elseif($type == 2)
                            $return .= '<div class="objList" onclick="$(\'#PFpartTask2\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME'] ?? '').' ('.$zp.')</div>';
                        else
                            $return .= '<option value="'.(int)$result['ID'].'">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME'] ?? '').' ('.$zp.')</option>';
                    }
                }
            }
        }
        
        if(isset($_SESSION['bitAppPayment']['ACCESS']['zp1']) && $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1 && !empty($_SESSION['bitAppPayment']['arZPlist']))
        {
            $paid = array();
            $sql = $this->db->query("SELECT `e0`.`VALUE` AS `ID`, `e1`.`VALUE_NUM` AS `sum`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 161
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 147
                                     WHERE `e`.`IBLOCK_ID` = 28 AND `e0`.`VALUE` > 0
                                     GROUP BY `e`.`ID`");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!isset($paid[$result['ID']]))
                        $paid[$result['ID']] = 0;
                    
                    $paid[$result['ID']] += $result['sum'];
                }
            }
/*  Топливо */
            $sql = $this->db->query("SELECT `e0`.`VALUE` AS `ID`, `e1`.`VALUE_NUM` AS `sum`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 223
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 213
                                     WHERE `e`.`IBLOCK_ID` = 42 AND `e0`.`VALUE` IN(".implode(',', $_SESSION['bitAppPayment']['arZPlist']).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!isset($paid[$result['ID']]))
                        $paid[$result['ID']] = 0;

                    $paid[$result['ID']] += $result['sum'];
                }
            }

            $sql = $this->db->query("SELECT `e`.`ID`, `e2`.`VALUE` AS `date`, `e0`.`VALUE_NUM` AS `ZP`, `e3`.`VALUE_NUM` AS `ZP2`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`, `u`.`SECOND_NAME`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 138
                                     LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 258
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 142
                                     LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                     LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                     WHERE `e`.`IBLOCK_ID` = 27 AND `e0`.`VALUE_NUM` > 0
                                     GROUP BY `e`.`ID`
                                     ORDER BY `u`.`LAST_NAME` ASC, ' ', `u`.`NAME` ASC, `e2`.`VALUE` ASC");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    #$result['ZP'] = $result['ZP'] + $result['ZP2'];                    
                    $zp = (isset($paid[$result['ID']])) ? ($result['ZP'] + $result['ZP2'] + $paid[$result['ID']]) : $result['ZP'] + $result['ZP2'] ;
                    if((int)$zp >= 500 || (int)$zp < 0)
                    {
                        #$zp = ($zp < 0) ? '<span class="text-error">Оклад: '.number_format($result['ZP'], 2, '.', ' ').'</span>' : 'Оклад: '.number_format($result['ZP'], 2, '.', ' ');
                        $zp = '';

                        if($type == 0)
                            $return .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME'] ?? '').'</div>';
                        elseif($type == 2)
                            $return .= '<div class="objList" onclick="$(\'#PFpartTask2\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME'] ?? '').'</div>';
                        else
                            $return .= '<option value="'.(int)$result['ID'].'">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME'] ?? '').'</option>';
                    }
                }
            }
        }
        
        if(isset($_SESSION['bitAppPayment']['ACCESS']['zp2']) && $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 && !empty($_SESSION['bitAppPayment']['arZPlist']))
        {
            $paid = array();
            $sql = $this->db->query("SELECT `e0`.`VALUE` AS `ID`, `e1`.`VALUE_NUM` AS `sum`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 161
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 147
                                     WHERE `e`.`IBLOCK_ID` = 28 AND `e0`.`VALUE` IN(".implode(',', $_SESSION['bitAppPayment']['arZPlist']).")
                                     GROUP BY `e`.`ID`");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!isset($paid[$result['ID']]))
                        $paid[$result['ID']] = 0;

                    $paid[$result['ID']] += $result['sum'];
                }
            }
/*  Топливо */
            $sql = $this->db->query("SELECT `e0`.`VALUE` AS `ID`, `e1`.`VALUE_NUM` AS `sum`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 223
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 213
                                     WHERE `e`.`IBLOCK_ID` = 42 AND `e0`.`VALUE` > 0");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!isset($paid[$result['ID']]))
                        $paid[$result['ID']] = 0;

                    $paid[$result['ID']] += $result['sum'];
                }
            }


            $sql = $this->db->query("SELECT `e`.`ID`, `e2`.`VALUE` AS `date`, `e0`.`VALUE_NUM` AS `ZP`, `e3`.`VALUE_NUM` AS `ZP2`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`, `u`.`SECOND_NAME`
                                     FROM `b_iblock_element` AS `e`
                                     LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 138
                                     LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 258
                                     LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 142
                                     LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                     LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                     WHERE `e`.`IBLOCK_ID` = 27 AND `e0`.`VALUE_NUM` > 0 AND `e`.`ID` IN(".implode(',', $_SESSION['bitAppPayment']['arZPlist']).")
                                     GROUP BY `e`.`ID`
                                     ORDER BY `u`.`LAST_NAME` ASC, ' ', `u`.`NAME` ASC, `e2`.`VALUE` ASC");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    #$result['ZP'] = $result['ZP'] + $result['ZP2'];
                    $zp = (isset($paid[$result['ID']])) ? ($result['ZP'] + $result['ZP2'] + $paid[$result['ID']]) : $result['ZP'] + $result['ZP2'] ;
                    if($id == $result['ID'])
                        $retSum = $zp;

                    if((int)$zp >= 500 || (int)$zp < 0)
                    {
                        $debtSum += $zp;
                        $zp = ($zp < 0) ? '<span class="text-error">Оклад: '.number_format($result['ZP'], 2, '.', ' ').' / Премия: '.number_format($result['ZP2'], 2, '.', ' ').' / Остаток: '.((!empty($zp)) ? number_format($zp, 2, '.', ' ') : '').'</span>' : 'Оклад: '.number_format($result['ZP'], 2, '.', ' ').' / Премия: '.number_format($result['ZP2'], 2, '.', ' ').' / Остаток: '.((!empty($zp)) ? number_format($zp, 2, '.', ' ') : '');

                        if($type == 0)
                            $return .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME']).' ('.$zp.')</div>';
                        elseif($type == 2)
                            $return .= '<div class="objList" onclick="$(\'#PFpartTask2\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME']).' ('.$zp.')</div>';
                        else
                            $return .= '<option value="'.(int)$result['ID'].'">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME']).' ('.$zp.')</option>';
                    }
                }
            }
        }
        
        if(isset($_SESSION['bitAppPayment']['ACCESS']['zp3']) && $_SESSION['bitAppPayment']['ACCESS']['zp3'] == 1 && !empty($_SESSION['bitAppPayment']['arZPlist']))
        {
            #   Структура и подчинённые
            $arDep = array();
            $sql = $this->db->query("SELECT `VALUE_ID` FROM `b_uts_iblock_3_section` WHERE `UF_HEAD` = ".(int)$_SESSION['bitAppPayment']['ID']);
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $arDep[$result['VALUE_ID']] = $result['VALUE_ID'];
                }
                
                $sql = $this->db->query("SELECT `ID` FROM `b_iblock_section` WHERE `IBLOCK_SECTION_ID` IN(".implode(',', $arDep).")");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $arDep[$result['ID']] = $result['ID'];
                    }
                    
                    $sql = $this->db->query("SELECT `ID` FROM `b_iblock_section` WHERE `IBLOCK_SECTION_ID` IN(".implode(',', $arDep).")");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $arDep[$result['ID']] = $result['ID'];
                        }
                    }
                }
                
                $tmpSec = array();
                $sql = $this->db->query("SELECT `s`.`ID`, `s`.`IBLOCK_SECTION_ID`
                                FROM `b_iblock_section` AS `s`
                                LEFT JOIN `b_uts_iblock_3_section` AS `ss` ON `ss`.`VALUE_ID` = `s`.`ID`
                                WHERE (`s`.`IBLOCK_SECTION_ID` = 1 OR `s`.`IBLOCK_SECTION_ID` IS NULL) AND `ss`.`UF_HEAD` = ".(int)$_SESSION['bitAppPayment']['ID']);
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $tmpSec[$result['ID']] = $result['ID'];
                        $arDep[$result['ID']] = (int)$result['IBLOCK_SECTION_ID'];
                    }
                    
                    $sql = $this->db->query("SELECT `ID`, `IBLOCK_SECTION_ID` FROM `b_iblock_section` WHERE `IBLOCK_SECTION_ID` IN(".implode(',', $tmpSec).")");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $tmpSec[$result['ID']] = $result['ID'];
                        }
                    
                        $sql = $this->db->query("SELECT `ID`, `IBLOCK_SECTION_ID` FROM `b_iblock_section` WHERE `IBLOCK_SECTION_ID` IN(".implode(',', $tmpSec).")");
                        if($sql->num_rows > 0)
                        {
                            while($result = $sql->fetch_assoc())
                            {
                                $arDep[$result['ID']] = $result['IBLOCK_SECTION_ID'];
                            }
                        }
                    }
                }
            }

            if(!empty($arDep))
            {
                $arUser = array();
                $arZP = array();
                $sql = $this->db->query("SELECT `VALUE_ID` FROM `b_utm_user` WHERE `VALUE_INT` IN(".implode(',', array_keys($arDep)).")");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        if($result['VALUE_ID'] != $_SESSION['bitAppPayment']['ID'])
                            $arUser[$result['VALUE_ID']] = $result['VALUE_ID'];
                    }

                    $sql = $this->db->query("SELECT `IBLOCK_ELEMENT_ID` FROM `b_iblock_element_property` WHERE `IBLOCK_PROPERTY_ID` = 142 AND `VALUE` IN(".implode(',', $arUser).")");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $arZP[$result['IBLOCK_ELEMENT_ID']] = $result['IBLOCK_ELEMENT_ID'];
                        }
                    }

                    $paid = array();
                    $sql = $this->db->query("SELECT `e0`.`VALUE` AS `ID`, `e1`.`VALUE_NUM` AS `sum`
                                            FROM `b_iblock_element` AS `e`
                                            LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 161
                                            LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 147
                                            LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 163
                                            LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 146
                                            WHERE `e`.`IBLOCK_ID` = 28 AND `e3`.`VALUE` >= '2023-03-01' AND `e0`.`VALUE` IN(".implode(',', $arZP).")
                                            GROUP BY `e`.`ID`");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            if(!isset($paid[$result['ID']]))
                                $paid[$result['ID']] = 0;

                            $paid[$result['ID']] += $result['sum'];
                        }
                    }
        /*  Топливо */
        
                    $sql = $this->db->query("SELECT `e0`.`VALUE` AS `ID`, `e1`.`VALUE_NUM` AS `sum`
                                            FROM `b_iblock_element` AS `e`
                                            LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 223
                                            LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 213
                                            WHERE `e`.`IBLOCK_ID` = 42 AND `e0`.`VALUE` IN(".implode(',', $arZP).")");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            if(!isset($paid[$result['ID']]))
                                $paid[$result['ID']] = 0;

                            $paid[$result['ID']] += $result['sum'];
                        }
                    }


                    $sql = $this->db->query("SELECT `e`.`ID`, `e2`.`VALUE` AS `date`, `e0`.`VALUE_NUM` AS `ZP`, `e3`.`VALUE_NUM` AS `ZP2`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`, `u`.`SECOND_NAME`
                                            FROM `b_iblock_element` AS `e`
                                            LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 138
                                            LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 258
                                            LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 142
                                            LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 137
                                            LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e1`.`VALUE`
                                            WHERE `e`.`IBLOCK_ID` = 27 AND `e2`.`VALUE` >= '2023-03-01' AND `e0`.`VALUE_NUM` > 0 AND `e`.`ID` IN(".implode(',', $arZP).")
                                            GROUP BY `e`.`ID`
                                            ORDER BY `u`.`LAST_NAME` ASC, ' ', `u`.`NAME` ASC, `e2`.`VALUE` ASC");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            #$result['ZP'] = $result['ZP'] + $result['ZP2'];
                            #$zp = (isset($paid[$result['ID']])) ? ($result['ZP'] + $paid[$result['ID']]) : $result['ZP'];
                            #$zp = (isset($paid[$result['ID']]) && $result['ZP2'] > 0) ? ($result['ZP2'] + $paid[$result['ID']]) : $result['ZP2'];
                            $zp = (isset($paid[$result['ID']]) && abs($paid[$result['ID']]) > $result['ZP']) ? $paid[$result['ID']] + $result['ZP'] + $result['ZP2'] : $result['ZP2'];
                            #$zp = (isset($paid[$result['ID']])) ? ($result['ZP'] + $result['ZP2'] + $paid[$result['ID']]) : $result['ZP'] + $result['ZP2'] ;
                            
                            if((int)$zp >= 500)
                            {
                                $debtSum += $zp;
                                if($id == $result['ID'])
                                    $retSum = $zp;

                                $zp = 'Премия: '.number_format($result['ZP2'], 2, '.', ' ').' / Остаток: '.number_format($zp, 2, '.', ' ');

                                if($type == 0)
                                    $return .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME']).' ('.$zp.')</div>';
                                elseif($type == 2)
                                    $return .= '<div class="objList" onclick="$(\'#PFpartTask2\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME']).' ('.$zp.')</div>';
                                else
                                    $return .= '<option value="'.(int)$result['ID'].'">'.date('m/Y', strtotime($result['date'])).' '.htmlspecialchars($result['user'].' '.$result['SECOND_NAME']).' ('.$zp.')</option>';
                            }
                        }
                    }
                }
            }
        }
        
        $debtSum = 'Задолженность: '.number_format($debtSum, 2, '.', ' ');
        $return  = str_replace('{DEBT}', $debtSum, $return);

        if($id > 0)
            return $retSum;
        elseif($arr == 0)
            return $return;
        else
            return array($return, $debtSum);
    }
    
    #   Список неоплаченных счетов
    public function invList($contr = '')
    {
        $return = 'Счета не найдены';
        
        if($contr != '')
        {
            $sql = $this->db->query("SELECT `d`.`ID`, `d`.`TITLE`, `d`.`OPPORTUNITY`, `r`.`SRC_ENTITY_ID` AS `deal`
                                       FROM `b_crm_dynamic_items_159` AS `d`
                                       LEFT JOIN `b_crm_requisite` AS `c` ON `c`.`ENTITY_ID` = `d`.`COMPANY_ID`
                                       LEFT JOIN `b_crm_entity_relation` AS `r` ON `r`.`DST_ENTITY_ID` = `d`.`ID` AND `r`.`DST_ENTITY_TYPE_ID` = 159
                                       WHERE `d`.`STAGE_ID` != 'DT159_1:SUCCESS' AND `d`.`STAGE_ID` != 'DT159_1:FAIL'
                                            AND `d`.`UF_SMART_PAY_TYPE` = '29'
                                            AND `d`.`OPPORTUNITY` > 0
                                            AND `c`.`RQ_INN` = '".$this->db->real_escape_string($contr)."'");
            if($sql->num_rows > 0)
            {
                $return = '';
                while($result = $sql->fetch_assoc())
                {
                    $return .= '<div class="objList" onclick="$(\'#PFpartTask\').val('.(int)$result['ID'].');$(\'.objList\').removeClass(\'alert-success\');$(this).addClass(\'alert-success\')"><small>['.(int)$result['ID'].']'.htmlspecialchars($result['TITLE']).' ('.(float)$result['OPPORTUNITY'].' руб.)</small></div>';
                }
            }
        }
        
        return $return;
    }
    
    #   Список компаний
    public function companyList()
    {
        $return = array(0 =>'<option></option>', 1 => '<option></option>');
        $tmp = '';
        $sql = $this->db->query("SELECT `ID`, LEFT(`TITLE`, 40) AS `TITLE`, `IS_MY_COMPANY`, `ASSIGNED_BY_ID` FROM `b_crm_company` ORDER BY `TITLE` ASC");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if($result['IS_MY_COMPANY'] == 'Y')
                    $return[0] .= '<option value="'.$result['ID'].'">'. htmlspecialchars($result['TITLE']) .'</option>';
                
                if($result['ASSIGNED_BY_ID'] == $_SESSION['bitAppPayment']['ID'])
                    $return[1] .= '<option value="'.$result['ID'].'">'. htmlspecialchars($result['TITLE']) .'</option>';
                else
                    $tmp .= '<option value="'.$result['ID'].'">'. htmlspecialchars($result['TITLE']) .'</option>';
            }
            
            if(!empty($tmp))
                $return[1] .= $tmp;
        }
        
        return $return;
    }
    
    #   Список сделок
    public function dealList()
    {
        $return = '<option></option>';
        
        $sql = $this->db->query("SELECT `ID`, LEFT(`TITLE`, 60) AS `TITLE` 
                                 FROM `b_crm_deal` 
                                 WHERE `CLOSED` = 'N' 
                                    AND (`ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppPayment']['ID']." OR 
                                         `ID` IN(SELECT DISTINCT(`ENTITY_ID`) FROM `b_crm_observer` WHERE `USER_ID` = ".(int)$_SESSION['bitAppPayment']['ID']."))");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $return .= '<option value="'.$result['ID'].'">'. htmlspecialchars($result['TITLE']) .'</option>';
            }
        }
        
        return $return;
    }
    
    #   Список сотрудников
    public function empList()
    {
        $return = '<option></option>';

        #   Extranet
        $extr = array();
        $sql = $this->db->query("SELECT `u`.`ID`
                                 FROM `b_user` AS `u`
                                 LEFT JOIN `b_utm_user`   AS `ut` ON `ut`.`VALUE_ID` = `u`.`ID`
                                 LEFT JOIN `b_user_group` AS `ug` ON `ug`.`USER_ID`  = `u`.`ID`
                                 WHERE `ut`.`VALUE_ID` IS NULL AND `u`.`ACTIVE` = 'Y' AND `ug`.`GROUP_ID` = 18");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $extr[$result['ID']] = $result['ID'];
            }
        }
        
        $sql = $this->db->query("SELECT `ID`, `LAST_NAME`, `NAME` 
                                 FROM  `b_user` 
                                 WHERE `ACTIVE` = 'Y' AND `ID` NOT IN(".implode(',', $extr).") AND `EXTERNAL_AUTH_ID` IS NULL
                                 ORDER BY `LAST_NAME` ASC, `NAME` ASC");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $return .= '<option value="'.$result['ID'].'">'. htmlspecialchars($result['LAST_NAME'] .' '. $result['NAME']) .'</option>';
            }
        }
        
/*
        $sql = $this->db->query("SELECT `u`.`ID`, `u`.`LAST_NAME`, `u`.`NAME`
                                         FROM `b_iblock_element_property` AS `e`
                                         LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `e`.`VALUE`
                                         WHERE (`e`.`IBLOCK_PROPERTY_ID` = 163 OR `e`.`IBLOCK_PROPERTY_ID` = 163) AND `e`.`VALUE` > 0
                                         GROUP BY `e`.`VALUE`
                                         ORDER BY `u`.`LAST_NAME` ASC, `u`.`NAME` ASC");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $return .= '<option value="'.$result['ID'].'">'. htmlspecialchars($result['LAST_NAME'] .' '. $result['NAME']) .'</option>';
            }
        }
        */
        return $return;
    }
    
    public function buildWhere($post)
    {
        $where = array();
        
        if($post['f_part_r'] == 274)
        {
            if( $_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['nal'] == 1)
            {
                $where[] = "`e`.`IBLOCK_ID` = 28";

                #   Зарплата
                if(isset($post['f_salary']) && (int)$post['f_salary'] > 0 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1))
                {
                    $where[] = "`e10`.`VALUE` = '".(int)$post['f_salary']."'";
                }
                else
                {
                    $where[] = "`e11`.`VALUE` = 1";

                    #   Период год
                    if (isset($post['f_year']) && (int)$post['f_year'] > 0)
                        $where[] = "YEAR(`e1`.`VALUE`) = '" . (int)$post['f_year'] . "'";

                    #   Оператор
                    if (isset($post['f_oper']) && (int)$post['f_oper'] > 0)
                        $where[] = "`e7`.`VALUE` = '" . (int)$post['f_oper'] . "'";

                    #   Период месяц
                    if (isset($post['f_month']) && (int)$post['f_month'] > 0)
                        $where[] = "MONTH(`e1`.`VALUE`) = '" . (int)$post['f_month'] . "'";

                    #   Дата разбиения
                    if (isset($post['f_date2']) && strtotime($post['f_date2']) > 0)
                        $where[] = "`e`.`TIMESTAMP_X` LIKE '" . date('Y-m-d', strtotime($post['f_date2'])) . "%'";

                    #   Дата
                    if (isset($post['f_date1']) && strtotime($post['f_date1']) > 0)
                        $where[] = "`e1`.`VALUE` LIKE '" . date('Y-m-d', strtotime($post['f_date1'])) . "%'";

                    #   Разбитые платежи
                    if (in_array(5, $post['f_part']) && !in_array(6, $post['f_part']))
                        $where[] = "(`e4`.`VALUE` > 0 OR `e5`.`VALUE` > 0 OR `e9`.`VALUE` > 0 OR `e14`.`VALUE` > 0)";

                    #   Неразбитые платежи
                    if (in_array(6, $post['f_part']) && !in_array(5, $post['f_part']))
                        $where[] = "((`e4`.`VALUE` = 0 OR `e4`.`VALUE` IS NULL) AND (`e5`.`VALUE` = 0 OR `e5`.`VALUE` IS NULL) AND (`e5`.`VALUE` = 0 OR `e5`.`VALUE` IS NULL) AND (`e9`.`VALUE` = 0 OR `e9`.`VALUE` IS NULL) AND (`e14`.`VALUE` = 0 OR `e14`.`VALUE` IS NULL))";

                    #   Ответственный сотрудник
                    if (isset($post['f_employee']) && (int)$post['f_employee'] > 0)
                        $where[] = "`e2`.`VALUE` = '" . (int)$post['f_employee'] . "'";

                    #   Сделка
                    if (isset($post['f_deal']) && (int)$post['f_deal'] > 0)
                        $where[] = "`e3`.`VALUE` = 'D_" . (int)$post['f_deal'] . "'";

                    #  Счёт
                    if (isset($post['f_invoice']) && (int)$post['f_invoice'] > 0)
                        $where[] = "`e4`.`VALUE` = '" . (int)$post['f_invoice'] . "'";

                    #  Задача
                    if (isset($post['f_task']) && (int)$post['f_task'] > 0)
                        $where[] = "`e5`.`VALUE` = '" . (int)$post['f_task'] . "'";

                    #  Сумма
                    if (isset($post['f_sum']) && (float)$post['f_sum'] <> 0)
                        $where[] = "(`e6`.`VALUE_NUM` = '" . abs((float)$post['f_sum']) . "' OR `e6`.`VALUE_NUM` = '-" . abs((float)$post['f_sum']) . "' OR `e66`.`VALUE_NUM` = '" . abs((float)$post['f_sum']) . "' OR `e66`.`VALUE_NUM` = '-" . abs((float)$post['f_sum']) . "')";

                    #   Приход
                    if (in_array(7, $post['f_part']) && !in_array(8, $post['f_part']))
                        $where[] = "`e6`.`VALUE_NUM` > 0";

                    #   Расход
                    if (in_array(8, $post['f_part']) && !in_array(7, $post['f_part']))
                        $where[] = "`e6`.`VALUE_NUM` < 0";

                    #  Назначение платежа
                    if (isset($post['f_comment']) && !empty($post['f_comment']))
                        $where[] = "`e8`.`VALUE` LIKE '%" . $this->db->real_escape_string(trim(strip_tags($post['f_comment']))) . "%'";
                }
            }
        }
        else
        {
            if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['bnal'] == 1)
            {
                $where[] = "`e`.`IBLOCK_ID` = 28";

                #   Зарплата
                if(isset($post['f_salary']) && (int)$post['f_salary'] > 0 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 && $_SESSION['bitAppPayment']['ACCESS']['nal'] == 1))
                {
                    $where[] = "`e10`.`VALUE` = '".(int)$post['f_salary']."'";
                }
                elseif(isset($post['f_salary']) && (int)$post['f_salary'] > 0 && ($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1))
                {
                    $where[] = "`e10`.`VALUE` = '".(int)$post['f_salary']."'";
                    $where[] = "`e18`.`VALUE` != 1";
                }
                else
                {
                    $where[] = "`e18`.`VALUE` != 1";

                    #   Ответственный за платёж
                    if(!empty($_POST['f_resp']))
                    {
                        $where[] = "`e17`.`VALUE` IN(SELECT `ID` FROM `b_user` WHERE `NAME` LIKE '%".$this->db->real_escape_string($_POST['f_resp'])."%' OR `LAST_NAME` LIKE '%".$this->db->real_escape_string($_POST['f_resp'])."%' OR `SECOND_NAME` LIKE '%".$this->db->real_escape_string($_POST['f_resp'])."%')";
                    }

                    #   Период год
                    if (isset($post['f_year']) && (int)$post['f_year'] > 0)
                        $where[] = "YEAR(`e1`.`VALUE`) = '" . (int)$post['f_year'] . "'";

                    #   Держатель карты
                    if (isset($post['f_card']) && (int)$post['f_card'] > 0)
                        $where[] = "`e17`.`VALUE` = '" . (int)$post['f_card'] . "'";

                    #   Оператор
                    if (isset($post['f_oper']) && (int)$post['f_oper'] > 0)
                        $where[] = "`e12`.`VALUE` = '" . (int)$post['f_oper'] . "'";

                    #   Период месяц
                    #if (date('d') < 7 && isset($post['f_month']) && (int)$post['f_month'] > 0)
                    #    $where[] = "(MONTH(`e1`.`VALUE`) = '" . (int)$post['f_month'] . "' OR `e1`.`VALUE` >= '".date('Y-m-d', strtotime('-7 day'))."')";
                    #else
                    if(isset($post['f_month']) && (int)$post['f_month'] > 0)
                        $where[] = "MONTH(`e1`.`VALUE`) = '" . (int)$post['f_month'] . "'";

                    #   Дата разбиения
                    if (!empty($post['f_date2']) && strtotime($post['f_date2']) > 0)
                        $where[] = "`e`.`TIMESTAMP_X` LIKE '" . date('Y-m-d', strtotime($post['f_date2'])) . "%'";

                    #   Разбитые платежи
                    if (in_array(1, $post['f_part']) && !in_array(2, $post['f_part']))
                        $where[] = "(`e7`.`VALUE` > 0 OR `e6`.`VALUE` > 0 OR `e10`.`VALUE` > 0 OR `e20`.`VALUE` > 0)";

                    #   Неразбитые платежи
                    if (in_array(2, $post['f_part']) && !in_array(1, $post['f_part']))
                        $where[] = "((`e7`.`VALUE` = 0 OR `e7`.`VALUE` IS NULL) AND (`e6`.`VALUE` = 0 OR `e6`.`VALUE` IS NULL) AND (`e10`.`VALUE` = 0 OR `e10`.`VALUE` IS NULL) AND (`e20`.`VALUE` = 0 OR `e20`.`VALUE` IS NULL))";

                    #   Дата
                    if (!empty($post['f_date1']) && strtotime($post['f_date1']) > 0)
                        $where[] = "`e1`.`VALUE` LIKE '" . date('Y-m-d', strtotime($post['f_date1'])) . "%'";

                    #   Компания
                    if (isset($post['f_company']) && (int)$post['f_company'] > 0)
                        $where[] = "`e4`.`VALUE` = '" . (int)$post['f_company'] . "'";

                    #  Контрагент
                    if (isset($post['f_contragent']) && !empty($post['f_contragent']))
                        $where[] = "`e3`.`VALUE` LIKE '%" . $this->db->real_escape_string(trim(strip_tags($post['f_contragent']))) . "%'";

                    #  Контрагент 2
                    if (isset($post['f_contragent2']) && (int)$post['f_contragent2'] > 0)
                        $where[] = "`e14`.`VALUE` = " . (int)$post['f_contragent2'];

                    #  Сделка
                    if (isset($post['f_deal']) && (int)$post['f_deal'] > 0)
                        $where[] = "`e5`.`VALUE` = 'D_" . (int)$post['f_deal'] . "'";

                    #  Задача
                    if (isset($post['f_task']) && (int)$post['f_task'] > 0)
                        $where[] = "`e7`.`VALUE` = '" . (int)$post['f_task'] . "'";

                    #  Сумма
                    if (!empty($post['f_sum']) && $post['f_sum'] <> 0)
                        $where[] = "(`e2`.`VALUE_NUM` = '" . ($post['f_sum'] * 1) . "' OR `e2`.`VALUE_NUM` = '" . ($post['f_sum'] * -1) . "' OR `e22`.`VALUE_NUM` = '" . ($post['f_sum'] * 1) . "' OR `e22`.`VALUE_NUM` = '" .($post['f_sum'] * -1) . "')";

                    #   Приход
                    if (in_array(3, $post['f_part']) && !in_array(4, $post['f_part']))
                        $where[] = "`e2`.`VALUE_NUM` > 0";

                    #   Расход
                    if (in_array(4, $post['f_part']) && !in_array(3, $post['f_part']))
                        $where[] = "`e2`.`VALUE_NUM` < 0";

                    #  Назначение платежа
                    if (isset($post['f_comment']) && !empty($post['f_comment']))
                        $where[] = "`e9`.`VALUE` LIKE '%" . $this->db->real_escape_string(trim(strip_tags($post['f_comment']))) . "%'";

                    #  Счёт
                    if (isset($post['f_invoice']) && (int)$post['f_invoice'] > 0)
                        $where[] = "`e6`.`VALUE` = '" . (int)$post['f_invoice'] . "'";

                    #   ЛС
                    if ((int)$post['invoice'] > 0 && (!isset($post['f_part']) || isset($post['f_part']) && $post['f_part'] != 3))
                    {
                        #$where[] = "`e8`.`VALUE` = (SELECT `RQ_ACC_NUM` FROM `b_crm_bank_detail` WHERE `ID` = " . (int)$post['invoice'] . ")";
                        if ($_SESSION['bitAppPayment']['ADMIN'] == 1)
                            $where[] = "`e8`.`VALUE` = (SELECT `RQ_ACC_NUM` FROM `b_crm_bank_detail` WHERE `ID` = " . (int)$post['invoice'] . ")";
                        else
                        {
                            if (in_array($post['invoice'], $_SESSION['bitAppPayment']['ACCESS']['acc']))
                                $where[] = "`e8`.`VALUE` = (SELECT `RQ_ACC_NUM` FROM `b_crm_bank_detail` WHERE `ID` = " . (int)$post['invoice'] . ")";
                            else
                                $where[] = "`e8`.`VALUE` IN (SELECT `RQ_ACC_NUM` FROM `b_crm_bank_detail` WHERE `ID` IN(" . implode(',', $_SESSION['bitAppPayment']['ACCESS']['acc']) . ")";
                        }
                    }
                    elseif ($_SESSION['bitAppPayment']['ADMIN'] != 1)
                    {
                        $where[] = "`e8`.`VALUE` IN (SELECT `RQ_ACC_NUM` FROM `b_crm_bank_detail` WHERE `ID` IN(" . implode(',', $_SESSION['bitAppPayment']['ACCESS']['acc']) . "))";
                    }
                }
            }
        }

        return implode(' AND ', $where);
    }
    
    public function quenue()
    {
        #   Обработка очереди
        if(file_exists(_PATH .'/quenue.db'))
        {
            $start = file_get_contents(_PATH .'/q.start');
            if($start == 0)
            {
                file_put_contents(_PATH .'/q.start', '1');
                
                $file = file(_PATH .'/quenue.db');
                if(!empty($file))
                {
                    foreach($file as $id_str => $str)
                    {
                        $str = trim($str);
                        $tmp = explode('|', $str);
                        
                        if(empty($str) || $tmp[0] == 283 && $tmp[24] == 'done' || $tmp[0] == 274 && $tmp[16] == 'done' || $tmp[0] == 'invoice' && $tmp[8] == 'done')
                            unset($file[$id_str]);
                            
                    }
                    
                    file_put_contents(_PATH .'/quenue.db', implode("\r\n", $file));
                }
                unset($file, $id_str, $str);
                
                #   Импорт выписки
                $vb = array();
                $date_start = '';
                $date_stop  = '';
                    
                $file = file(_PATH .'/quenue.db');
                if(!empty($file))
                {
                    $i = 0;
                    foreach($file as $id_str => $str)
                    {
                        $str = trim($str);
                        $tmp = explode('|', $str);

                        foreach($tmp as $tId => $tS)
                        {
                            $tmp[$tId] = trim($tS);
                        }
                        
                        if($tmp[0] == 283)
                        {
                            if($tmp[23] == 'import' && $tmp[24] == 'wait')
                            {
                                if(empty($date_start) || $date_start > $tmp[5])
                                    $date_start = $tmp[5];
                                
                                if(empty($date_stop) || $date_stop < $tmp[5])
                                    $date_stop = $tmp[5];
                                
                                $vb[$id_str] = $tmp;
                                $i++;
                            }
                        }
                        
                        if($i > 25)
                            break;
                    }
                    unset($file, $id_str, $str, $tmp);

                    if(!empty($vb) && !empty($date_start) && !empty($date_stop))
                    {
                        $sql = $this->db->query("SELECT `e`.`ID`, `e0`.`VALUE` AS `pp`, `e1`.`VALUE` AS `date`, `e2`.`VALUE` AS `inn`, `e3`.`VALUE` AS `ls`, `e4`.`VALUE_NUM` AS `sum`
                                                 FROM `b_iblock_element` AS `e`
                                                 LEFT JOIN `b_iblock_element_property` AS `e0` ON `e0`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e0`.`IBLOCK_PROPERTY_ID` = 149
                                                 LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 146
                                                 LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 150
                                                 LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 153
                                                 LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 148
                                                 WHERE `e`.`IBLOCK_ID` = 28 AND `e1`.`VALUE` BETWEEN '".$this->db->real_escape_string($date_start)."' AND '".$this->db->real_escape_string($date_stop)."'");
                        if($sql->num_rows > 0)
                        {
                            while($result = $sql->fetch_assoc())
                            {
                                foreach($vb as $id_s => $val)
                                {
                                    if($val[4] == $result['pp'] && $val[11] == $result['ls'] && (strtotime($val[5]) == strtotime($result['date']) || $val[5] == $result['date']) && $val[8] == $result['inn'] && $val[6] == $result['sum'])
                                    {
                                        $vb[$id_s][24] = 'done';
                                    }
                                }
                            }
                        }
                    }
                }
                        
                    if(!empty($vb))
                    {
                        $file = file(_PATH .'/quenue.db');
                        $query = array();
                        foreach($vb as $ids => $tmp)
                        {
                            $code = md5($tmp[4].$tmp[5].$tmp[6].$tmp[8].$tmp[11]);
                            if($tmp[24] == 'wait')
                            {
                                $query[$ids] = array('method' => 'lists.element.add',
                                                     'params' => array('IBLOCK_TYPE_ID' => 'lists',
                                                                       'IBLOCK_ID' => 28,
                                                                       'ELEMENT_CODE' => $code,
                                                                       'FIELDS' => array(
                                                                                        'NAME'          => $tmp[3],
                                                                                        'PROPERTY_149' => $tmp[4],  #   ПП
                                                                                        'PROPERTY_146' => $tmp[5],  #   Дата
                                                                                        'PROPERTY_147' => $tmp[6],  #   Сумма
                                                                                        'PROPERTY_152' => $tmp[7],  #   Компания
                                                                                        'PROPERTY_150' => $tmp[8],  #   ИНН
                                                                                        'PROPERTY_151' => $tmp[9],  #   Контрагент
                                                                                        'PROPERTY_154' => $tmp[10], #   Назначение платежа
                                                                                        'PROPERTY_153' => $tmp[11], #   Л/С
                                                                                        'PROPERTY_155' => $tmp[12], #   Сделка
                                                                                        'PROPERTY_156' => (int)$tmp[13], #   ID счёта
                                                                                        #'PROPERTY_1047' => $result['inv'], #   Счёт
                                                                                        'PROPERTY_163' => $tmp[14], #   Оператор
                                                                                        'PROPERTY_162' => $tmp[15], #   Дата редактирования
                                                                                        'PROPERTY_157' => (int)$tmp[16], #   Задача
                                                                                        'PROPERTY_159' => $tmp[17], #   Ссылка на задачу
                                                                                        'PROPERTY_158' => $tmp[18], #   Ссылка на счёт
                                                                                        'PROPERTY_148' => $tmp[19], #   Сумма (осн)
                                                                                        #'PROPERTY_1370' => $result['nal_pay'], #   Наличный платёж
                                                                                        'PROPERTY_161' => $tmp[20], #   Зарплата
                                                                                        'PROPERTY_166' => $tmp[22], #   Карта
                                                                                         )));
                            }
                            
                            if($tmp[24] == 'done')
                            {
                                $file[$ids] = str_replace('|wait', '|done', $file[$ids]);;
                            }
                        }
                            
                            if(!empty($query))
                            {
                                $request = CRest::callBatch($query);
                                if(!empty($request['result']['result']))
                                {
                                    foreach($request['result']['result'] as $id => $total)
                                    {
                                        $file[$id] = str_replace('|wait', '|done', $file[$id]);
                                        unset($vb[$id]);
                                    }
                                }
                                
                                if(!empty($request['result']['result_error']))
                                {
                                    foreach($request['result']['result_error'] as $id => $total)
                                    {
                                        if($total['error'] == 'ERROR_ELEMENT_ALREADY_EXISTS')
                                        {
                                            $file[$id] = str_replace('|wait', '|done', $file[$id]);
                                            unset($vb[$id]);
                                        }
                                    }
                                }
                                
                                if(!empty($vb))
                                {
                                    foreach($vb as $idv => $vbv)
                                    {
                                        $file[$idv] = str_replace('|wait', '|error', $file[$idv]);
                                    }
                                }
                            }
                            
                        file_put_contents(_PATH .'/quenue.db', implode("\r\n", $file));
                    }
                    
            
                unset($file);
                $file = file(_PATH .'/quenue.db');
                
                        $array = array();
                        
                        $i = 0;
                        foreach($file as $id_str => $str)
                        {
                            $str = trim($str);
                            $tmp = explode('|', $str);
                            
                            #   Безнал
                            if($tmp[0] == 283)
                            {
                                if($tmp[23] == 'wait')
                                {
                                    #   Новая запись
                                    if($tmp[22] == 'add')
                                    {
                                            sleep(1);
                                            $request = CRest::call('lists.element.add', 
                                                             array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $tmp[0], 'ELEMENT_CODE' => time(),
                                                                   'FIELDS' => array(
                                                                            'NAME'          => $tmp[3],
                                                                            'PROPERTY_149' => $tmp[4],  #   ПП
                                                                            'PROPERTY_146' => $tmp[5],  #   Дата
                                                                            'PROPERTY_147' => $tmp[6],  #   Сумма
                                                                            'PROPERTY_152' => $tmp[7],  #   Компания
                                                                            'PROPERTY_150' => $tmp[8],  #   ИНН
                                                                            'PROPERTY_151' => $tmp[9],  #   Контрагент
                                                                            'PROPERTY_154' => $tmp[10], #   Назначение платежа
                                                                            'PROPERTY_153' => $tmp[11], #   Л/С
                                                                            'PROPERTY_155' => $tmp[12], #   Сделка
                                                                            'PROPERTY_156' => (int)$tmp[13], #   ID счёта
                                                                            #'PROPERTY_1047' => $result['inv'], #   Счёт
                                                                            'PROPERTY_163' => $tmp[14], #   Оператор
                                                                            'PROPERTY_162' => $tmp[15], #   Дата редактирования
                                                                            'PROPERTY_157' => (int)$tmp[16], #   Задача
                                                                            'PROPERTY_159' => $tmp[17], #   Ссылка на задачу
                                                                            'PROPERTY_158' => $tmp[18], #   Ссылка на счёт
                                                                            'PROPERTY_148' => $tmp[19], #   Сумма (осн)
                                                                            #'PROPERTY_1370' => $result['nal_pay'], #   Наличный платёж
                                                                            'PROPERTY_161' => $tmp[20], #   Зарплата
                                                                            'PROPERTY_165' => $tmp[21], #   Комментарий
                                            )));
                                            
                                            if($request['result'])
                                                $tmp[23] = 'done';
                                            else
                                                $tmp[23] = 'error';
                                            
                                            $file[$id_str] = implode('|', $tmp);

                                    }
                                    
                                    #   Обновление
                                    if($tmp[22] == 'update')
                                    {
                                        sleep(1);
                                        $request = CRest::call('lists.element.update', 
                                                         array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $tmp[0], 'ELEMENT_ID' => $tmp[2],
                                                               'FIELDS' => array(
                                                                        'NAME'          => $tmp[3],
                                                                        'PROPERTY_149' => $tmp[4],  #   ПП
                                                                        'PROPERTY_146' => $tmp[5],  #   Дата
                                                                        'PROPERTY_147' => $tmp[6],  #   Сумма
                                                                        'PROPERTY_152' => $tmp[7],  #   Компания
                                                                        'PROPERTY_150' => $tmp[8],  #   ИНН
                                                                        'PROPERTY_151' => $tmp[9],  #   Контрагент
                                                                        'PROPERTY_154' => $tmp[10], #   Назначение платежа
                                                                        'PROPERTY_153' => $tmp[11], #   Л/С
                                                                        'PROPERTY_155' => $tmp[12], #   Сделка
                                                                        'PROPERTY_156' => (int)$tmp[13], #   ID счёта
                                                                        #'PROPERTY_1047' => $result['inv'], #   Счёт
                                                                        'PROPERTY_163' => $tmp[14], #   Оператор
                                                                        'PROPERTY_162' => $tmp[15], #   Дата редактирования
                                                                        'PROPERTY_157' => (int)$tmp[16], #   Задача
                                                                        'PROPERTY_159' => $tmp[17], #   Ссылка на задачу
                                                                        'PROPERTY_158' => $tmp[18], #   Ссылка на счёт
                                                                        'PROPERTY_148' => $tmp[19], #   Сумма (осн)
                                                                        #'PROPERTY_1370' => $result['nal_pay'], #   Наличный платёж
                                                                        'PROPERTY_161' => $tmp[20], #   Зарплата
                                                                        'PROPERTY_165' => $tmp[21], #   Комментарий
                                        )));
                                        
                                        if($request['result'])
                                            $tmp[23] = 'done';
                                        else
                                            $tmp[23] = 'error';
                                        
                                        $file[$id_str] = implode('|', $tmp);
                                    }
                                    
                                    #   Удаление
                                    if($tmp[22] == 'delete')
                                    {
                                        sleep(1);
                                        $request = CRest::call('lists.element.delete', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $tmp[0], 'ELEMENT_ID' => $tmp[2]));
                                        if($request['result'])
                                            $tmp[23] = 'done';
                                        else
                                            $tmp[23] = 'error';
                                        
                                        $file[$id_str] = implode('|', $tmp);
                                    }
                                    
                                    $i++;
                                }
                            }
                            
                            #   Нал
                            if($tmp[0] == 274)
                            {
                                if($tmp[16] == 'wait')
                                {
                                    #   Новая запись
                                    if($tmp[15] == 'add')
                                    {
                                        sleep(1);
                                        $request = CRest::call('lists.element.add', 
                                                         array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $tmp[0], 'ELEMENT_CODE' => time(),
                                                               'FIELDS' => array(
                                                                        'NAME'          => $tmp[3],
                                                                        'PROPERTY_147'  => $tmp[4],
                                                                        'PROPERTY_156'  => $tmp[5],
                                                                        'PROPERTY_164'  => $tmp[6],
                                                                        'PROPERTY_146'  => $tmp[7],
                                                                        'PROPERTY_163' => $tmp[8],
                                                                        'PROPERTY_157' => $tmp[9],
                                                                        'PROPERTY_155' => $tmp[10],
                                                                        'PROPERTY_159' => $tmp[11],
                                                                        'PROPERTY_158' => $tmp[12],
                                                                        'PROPERTY_165' => $tmp[13],
                                                                        'PROPERTY_161' => $tmp[14],
                                                                        'PROPERTY_160' => 1
                                        )));
                                        
                                        if($request['result'])
                                            $tmp[16] = 'done';
                                        else
                                            $tmp[16] = 'error';
                                        
                                        $file[$id_str] = implode('|', $tmp);
                                    }
                                    
                                    #   Обновление
                                    if($tmp[15] == 'update')
                                    {
                                        sleep(1);
                                        $request = CRest::call('lists.element.update', 
                                                         array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $tmp[0], 'ELEMENT_ID' => $tmp[2],
                                                               'FIELDS' => array(
                                                                        'NAME'          => $tmp[3],
                                                                        'PROPERTY_147'  => $tmp[4],
                                                                        'PROPERTY_156'  => $tmp[5],
                                                                        'PROPERTY_164'  => $tmp[6],
                                                                        'PROPERTY_146'  => $tmp[7],
                                                                        'PROPERTY_163' => $tmp[8],
                                                                        'PROPERTY_157' => $tmp[9],
                                                                        'PROPERTY_155' => $tmp[10],
                                                                        'PROPERTY_159' => $tmp[11],
                                                                        'PROPERTY_158' => $tmp[12],
                                                                        'PROPERTY_165' => $tmp[13],
                                                                        'PROPERTY_161' => $tmp[14]
                                        )));
                                        if($request['result'])
                                            $tmp[16] = 'done';
                                        else
                                            $tmp[16] = 'error';
                                        
                                        $file[$id_str] = implode('|', $tmp);
                                    }
                                    
                                    #   Удаление
                                    if($tmp[15] == 'delete')
                                    {
                                        sleep(1);
                                        $request = CRest::call('lists.element.delete', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $tmp[0], 'ELEMENT_ID' => $tmp[2]));
                                        if($request['result'])
                                            $tmp[16] = 'done';
                                        else
                                            $tmp[16] = 'error';
                                        
                                        $file[$id_str] = implode('|', $tmp);
                                    }
                                    
                                    $i++;
                                }
                            }
                            
                            #   Счёт
                            if($tmp[0] == 'invoice')
                            {
                                if($tmp[8] == 'wait')
                                {
                                    $request = CRest::call('crm.invoice.update', 
                                                     array('id' => $tmp[1],
                                                           'fields' => array(
                                                                'STATUS_ID'             => $tmp[2],
                                                                'PAYED'                 => $tmp[3],
                                                                'DATE_PAYED'            => $tmp[4],
                                                                'PAY_VOUCHER_DATE'      => $tmp[4],
                                                                'UF_CRM_5D4189724CE12'  => $tmp[5],
                                                                'PAY_VOUCHER_NUM'       => $tmp[6],
                                                                'DATE_PAY_BEFORE'       => $tmp[4],
                                                     )));
                                            
                                    if($request['result'])
                                        $tmp[8] = 'done';
                                    else
                                        $tmp[8] = 'error';
                                    
                                    $file[$id_str] = implode('|', $tmp);
                                    
                                    $i++;
                                }
                            }
                            
                            if($i > 5)
                                break;
                        }
                file_put_contents(_PATH .'/quenue.db', implode("\r\n", $file));
            }
                
            file_put_contents(_PATH .'/q.start', '0');
        }

        #   Проверка платежей по правилам
        if(file_exists(_PATH .'/r.start'))
        {
            $startRules = file_get_contents(_PATH.'/r.start');
            if(empty($startRules) || $startRules <= time())
            {
                file_put_contents(_PATH .'/r.start', (time()+300));
                
                #require_once '/home/bitrix/www/bitApp/payAuto.php';
                #$this->autoPay();

                $tmp = scandir(_PATH .'/rules');
                foreach($tmp as $file)
                {
                    if($file != '.' && $file != '..')
                    {
                        $f = file_get_contents(_PATH .'/rules/'.$file);
                        $rule = unserialize($f);

                        if(!empty($rule))
                        {
                            $ids = array();
                            foreach($rule as $id_r => $r)
                            {
                                $rule[$id_r]['count'] = 0;
                                $rule[$id_r]['IDs'] = array();
                                
                                $where = array();
                                
                                if(!empty($r['BRdateStart']))
                                {
                                    $tmpD1 = explode('.', $r['BRdateStart']);
                                    $where[] = "(`p4`.`VALUE` >= '".$this->db->real_escape_string($tmpD1[2].'-'.$tmpD1[1].'-'.$tmpD1[0])."')";
                                }
                                
                                if(!empty($r['BRdateStop']))
                                {
                                    $tmpD2 = explode('.', $r['BRdateStop']);
                                    $where[] = "(`p4`.`VALUE` <= '".$this->db->real_escape_string($tmpD2[2].'-'.$tmpD2[1].'-'.$tmpD2[0])."')";
                                }
                                
                                if(!empty($r['BRcompany']) && $r['BRcompany'] > 0)
                                {
                                    $where[] = "(`p5`.`VALUE` = (SELECT `RQ_ACC_NUM` FROM `b_crm_bank_detail` WHERE `ID` = ".(int)$r['BRcompany']."))";
                                }
                                
                                if(!empty($r['BRcontr']))
                                {
                                    $where[] = "(`p6`.`VALUE` LIKE '%".$this->db->real_escape_string($r['BRcontr'])."%')";
                                }
                                
                                if(!empty($r['BRsum']))
                                {
                                    $where[] = "(`p7`.`VALUE_NUM` = ".(float)$r['BRsum'].")";
                                }
                                
                                if(!empty($r['BRnazn']))
                                {
                                    $where[] = "(`p8`.`VALUE` LIKE '%".$this->db->real_escape_string($r['BRnazn'])."%')";
                                }
                                
                                if(!empty($where))
                                {
                                    $sql = $this->db->query("SELECT `e`.`ID`
                                                             FROM `b_iblock_element` AS `e`
                                                             LEFT JOIN  `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 156
                                                             LEFT JOIN  `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 157
                                                             LEFT JOIN  `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 161
                                                             LEFT JOIN  `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = 146
                                                             LEFT JOIN  `b_iblock_element_property` AS `p5` ON `p5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p5`.`IBLOCK_PROPERTY_ID` = 153
                                                             LEFT JOIN  `b_iblock_element_property` AS `p6` ON `p6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p6`.`IBLOCK_PROPERTY_ID` = 151
                                                             LEFT JOIN  `b_iblock_element_property` AS `p7` ON `p7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p7`.`IBLOCK_PROPERTY_ID` = 147
                                                             LEFT JOIN  `b_iblock_element_property` AS `p8` ON `p8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p8`.`IBLOCK_PROPERTY_ID` = 154
                                                             WHERE `e`.`IBLOCK_ID` = 28
                                                                 AND (`p1`.`VALUE` = '' OR `p1`.`VALUE` = 0 OR `p1`.`VALUE` IS NULL) 
                                                                 AND (`p2`.`VALUE` = '' OR `p2`.`VALUE` = 0 OR `p2`.`VALUE` IS NULL) 
                                                                 AND (`p3`.`VALUE` = '' OR `p3`.`VALUE` = 0 OR `p3`.`VALUE` IS NULL) 
                                                                 AND ".implode(' AND ', $where)."
                                                            GROUP BY `e`.`ID`");
                                    if($sql->num_rows > 0)
                                    {
                                        $rule[$id_r]['count'] = $sql->num_rows;
                                        
                                        while($result = $sql->fetch_assoc())
                                        {
                                            $rule[$id_r]['IDs'][] = $result['ID'];
                                        }
                                    }
                                }
                            }
                            file_put_contents(_PATH .'/rules/'.$file, serialize($rule));
                        }
                    }
                }
            }
        }
        else
        {
            file_put_contents(_PATH .'/r.start', time());
        }
    }
    
    public function autoPay()
    {
                $arInvoice = array();
                $arWhere   = array();
                $sql = $this->db->query("SELECT `d`.`ID`, `d`.`OPPORTUNITY`, `d`.`MYCOMPANY_ID`, `d`.`UF_CRM_3_ID_TASK`, `d`.`UF_CRM_3_NUMBER_PP`,
                                          `t`.`TITLE`, `t`.`CREATED_BY`, `ut`.`VALUE` AS `CRM`
                                   FROM `b_crm_dynamic_items_176` AS `d`
                                   LEFT JOIN `b_tasks` AS `t` ON `t`.`ID` = `d`.`UF_CRM_3_ID_TASK`
                                   LEFT JOIN `b_utm_tasks_task` AS `ut` ON `ut`.`VALUE_ID` = `t`.`ID`
                                   WHERE `d`.`STAGE_ID` = 'DT176_3:2' AND `d`.`UF_CRM_3_ID_TASK` > 0 AND (`d`.`UF_CRM_3_PAY_ELEMENT` IS NULL OR `d`.`UF_CRM_3_PAY_ELEMENT` = 0 OR `d`.`UF_CRM_3_PAY_ELEMENT` = '')
                                   HAVING `CRM` IS NOT NULL
                                   LIMIT 45");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $hash = md5(number_format(abs($result['OPPORTUNITY']) * -1, 2).$result['UF_CRM_3_NUMBER_PP'].$result['MYCOMPANY_ID']);
                        $arInvoice[$hash]['id']      = $result['ID'];
                        $arInvoice[$hash]['sum']     = abs($result['OPPORTUNITY']) * -1;
                        $arInvoice[$hash]['pp']      = $result['UF_CRM_3_NUMBER_PP'];
                        $arInvoice[$hash]['company'] = $result['MYCOMPANY_ID'];
                        $arInvoice[$hash]['task_id'] = $result['UF_CRM_3_ID_TASK'];
                        $arInvoice[$hash]['task']    = $result['TITLE'];
                        
                        $tmp = array();
                        $tmp = explode('D_', $result['CRM']);
                        if(isset($tmp[1]))
                        {
                            $arInvoice[$hash]['crm'] = $tmp[1];
                        }
                        else
                        {
                            $tmp = explode('L_', $result['CRM']);
                            if(isset($tmp[1]))
                                $arInvoice[$hash]['crm']= $tmp[1];
                        }
                        
                        $arWhere[$result['ID']] = "(`e3`.`VALUE_NUM` = ".(abs($result['OPPORTUNITY']) * -1)." AND `e4`.`VALUE` = ".(int)$result['MYCOMPANY_ID']." AND `e1`.`VALUE` = ".$result['UF_CRM_3_NUMBER_PP'].")";
                    }
                    
                    $sql = $this->db->query("SELECT `e`.`ID`, `e`.`NAME`, `e`.`CODE`, `e1`.`VALUE` AS `pp`, `e2`.`VALUE` AS `date`, `e3`.`VALUE_NUM` AS `sum`, `e4`.`VALUE` AS `company`,
                                                    `e5`.`VALUE` AS `INN`, `e6`.`VALUE` AS `contr_name`, `e7`.`VALUE` AS `naznach`, `e8`.`VALUE` AS `LS`, `c`.`TITLE` AS `comp_name`,
                                                    `e9`.`VALUE` AS `deal`, `e10`.`VALUE` AS `inv_id`, `e11`.`VALUE` AS `inv`, `e12`.`VALUE` AS `operator`,
                                                    `e13`.`VALUE` AS `date_edit`, `e14`.`VALUE` AS `task`, `e15`.`VALUE` AS `task_link`, `e16`.`VALUE` AS `inv_link`,
                                                    `e17`.`VALUE` AS `sum_osn`, `e18`.`VALUE` AS `nal_pay`, `e19`.`VALUE` AS `ZP`, `e20`.`VALUE` AS `comment`, `e21`.`VALUE` AS `card`
                                             FROM `b_iblock_element` AS `e`
                                             LEFT JOIN `b_iblock_element_property` AS `e1` ON `e1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e1`.`IBLOCK_PROPERTY_ID` = 149
                                             LEFT JOIN `b_iblock_element_property` AS `e2` ON `e2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e2`.`IBLOCK_PROPERTY_ID` = 146
                                             LEFT JOIN `b_iblock_element_property` AS `e3` ON `e3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e3`.`IBLOCK_PROPERTY_ID` = 147
                                             LEFT JOIN `b_iblock_element_property` AS `e4` ON `e4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e4`.`IBLOCK_PROPERTY_ID` = 152
                                             LEFT JOIN `b_iblock_element_property` AS `e5` ON `e5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e5`.`IBLOCK_PROPERTY_ID` = 150
                                             LEFT JOIN `b_iblock_element_property` AS `e6` ON `e6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e6`.`IBLOCK_PROPERTY_ID` = 151
                                             LEFT JOIN `b_iblock_element_property` AS `e7` ON `e7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e7`.`IBLOCK_PROPERTY_ID` = 154
                                             LEFT JOIN `b_iblock_element_property` AS `e8` ON `e8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e8`.`IBLOCK_PROPERTY_ID` = 153
                                             LEFT JOIN `b_iblock_element_property` AS `e9` ON `e9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e9`.`IBLOCK_PROPERTY_ID` = 155
                                             LEFT JOIN `b_iblock_element_property` AS `e10` ON `e10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e10`.`IBLOCK_PROPERTY_ID` = 156
                                             LEFT JOIN `b_iblock_element_property` AS `e11` ON `e11`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e11`.`IBLOCK_PROPERTY_ID` = 1047
                                             LEFT JOIN `b_iblock_element_property` AS `e12` ON `e12`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e12`.`IBLOCK_PROPERTY_ID` = 163
                                             LEFT JOIN `b_iblock_element_property` AS `e13` ON `e13`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e13`.`IBLOCK_PROPERTY_ID` = 162
                                             LEFT JOIN `b_iblock_element_property` AS `e14` ON `e14`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e14`.`IBLOCK_PROPERTY_ID` = 157
                                             LEFT JOIN `b_iblock_element_property` AS `e15` ON `e15`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e15`.`IBLOCK_PROPERTY_ID` = 159
                                             LEFT JOIN `b_iblock_element_property` AS `e16` ON `e16`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e16`.`IBLOCK_PROPERTY_ID` = 158
                                             LEFT JOIN `b_iblock_element_property` AS `e17` ON `e17`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e17`.`IBLOCK_PROPERTY_ID` = 148
                                             LEFT JOIN `b_iblock_element_property` AS `e18` ON `e18`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e18`.`IBLOCK_PROPERTY_ID` = 1370
                                             LEFT JOIN `b_iblock_element_property` AS `e19` ON `e19`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e19`.`IBLOCK_PROPERTY_ID` = 161
                                             LEFT JOIN `b_iblock_element_property` AS `e20` ON `e20`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e20`.`IBLOCK_PROPERTY_ID` = 165
                                             LEFT JOIN `b_iblock_element_property` AS `e21` ON `e21`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `e21`.`IBLOCK_PROPERTY_ID` = 166
                                             LEFT JOIN `b_crm_company` AS `c` ON `c`.`ID` = `e4`.`VALUE`
                                             WHERE `e`.`IBLOCK_ID` = 28 AND `e2`.`VALUE` >= '2020-01-01'
                                                                         AND (`e14`.`VALUE` = '' OR `e14`.`VALUE` = 0 OR `e14`.`VALUE` IS NULL)
                                                                         AND (`e19`.`VALUE` = '' OR `e19`.`VALUE` = 0 OR `e19`.`VALUE` IS NULL)
                                                                         AND (`e10`.`VALUE` = '' OR `e10`.`VALUE` = 0 OR `e10`.`VALUE` IS NULL)
                                             AND (".implode(' OR ', $arWhere).")");
                    if($sql->num_rows > 0)
                    {
                        $i = 0;
                        $query = array();
                        $query2 = array();
                        while($result = $sql->fetch_assoc())
                        {
                            $i++;
                            if($i < 50)
                            {
                                $hash = md5(number_format(abs($result['sum']) * -1, 2).$result['pp'].$result['company']);
                                $query[$arInvoice[$hash]['id']] = array('method' => 'lists.element.update',
                                             'params' => array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 28, 'ELEMENT_ID' => $result['ID'],
                                                               'FIELDS' => array(
                                                                    'NAME'          => $result['NAME'],
                                                                    'PROPERTY_149' => $result['pp'],                       #   ПП
                                                                    'PROPERTY_146' => $result['date'],                     #   Дата
                                                                    'PROPERTY_147' => $result['sum'],                      #   Сумма
                                                                    'PROPERTY_152' => $result['company'],                  #   Компания
                                                                    'PROPERTY_150' => $result['INN'],                      #   ИНН
                                                                    'PROPERTY_151' => trim($result['contr_name']),         #   Контрагент
                                                                    'PROPERTY_154' => trim($result['naznach']),            #   Назначение платежа
                                                                    'PROPERTY_153' => $result['LS'],                       #   Л/С
                                                                    'PROPERTY_155' => $result['deal'],                     #   Сделка
                                                                    'PROPERTY_156' => 0,                                   #   ID счёта
                                                                    'PROPERTY_163' => $result['operator'],    #   Оператор
                                                                    'PROPERTY_162' => date('Y-m-d'),                       #   Дата редактирования
                                                                    'PROPERTY_157' => (int)$arInvoice[$hash]['task_id'],   #   Задача
                                                                    'PROPERTY_159' => '<a href="/company/personal/user/26/tasks/task/view/'.(int)$arInvoice[$hash]['task_id'].'/">'.$arInvoice[$hash]['task'].'</a>',                               #   Ссылка на задачу
                                                                    'PROPERTY_158' => '',                                  #   Ссылка на счёт
                                                                    'PROPERTY_148' => $result['sum_osn'],                  #   Сумма (осн)
                                                                    'PROPERTY_161' => 0,                                   #   Зарплата
                                                                    'PROPERTY_165' => $result['comment'],                  #   комментарий
                                                                    'PROPERTY_166' => $result['card'],                  #   карта
                                                               )));
                                $query2[$arInvoice[$hash]['id']] = $result['ID'];
                            }
                            else
                            {
                                $request = CRest::callBatch($query);
                                foreach($request['result']['result'] as $k => $q)
                                {
                                    file_put_contents('./autopay0.txt', "UPDATE `b_crm_dynamic_items_176` SET `UF_CRM_3_PAY_ELEMENT` = ".$query2[$k]." WHERE `ID` = ".(int)$k.';'."\r\n", FILE_APPEND);
                                    #$this->db->query("UPDATE `b_crm_dynamic_items_176` SET `UF_CRM_3_PAY_ELEMENT` = ".$query2[$k]." WHERE `ID` = ".(int)$k);
                                    /*
                                    foreach($q as $kk => $qq)
                                    {
                                        file_put_contents('./autopay1.txt', "UPDATE `b_crm_dynamic_items_176` SET `UF_CRM_3_PAY_ELEMENT` = ".$query2[$kk]." WHERE `ID` = ".(int)$kk.';'."\r\n", FILE_APPEND);
                                        $this->db->query("UPDATE `b_crm_dynamic_items_176` SET `UF_CRM_3_PAY_ELEMENT` = ".$query2[$kk]." WHERE `ID` = ".(int)$kk);
                                    }*/
                                }
                            }
                        }
                        
                        if(!empty($query))
                        {
                            $request = CRest::callBatch($query);
                            foreach($request['result']['result'] as $k => $q)
                            {
                                file_put_contents('./autopay0.txt', "UPDATE `b_crm_dynamic_items_176` SET `UF_CRM_3_PAY_ELEMENT` = ".$query2[$k]." WHERE `ID` = ".(int)$k.';'."\r\n", FILE_APPEND);
                                #$this->db->query("UPDATE `b_crm_dynamic_items_176` SET `UF_CRM_3_PAY_ELEMENT` = ".$query2[$k]." WHERE `ID` = ".(int)$k);
                                 /*foreach($q as $kk => $qq)
                                 {
                                    file_put_contents('./autopay1.txt', "UPDATE `b_crm_dynamic_items_176` SET `UF_CRM_3_PAY_ELEMENT` = ".$query2[$kk]." WHERE `ID` = ".(int)$kk.';'."\r\n", FILE_APPEND);
                                    $this->db->query("UPDATE `b_crm_dynamic_items_176` SET `UF_CRM_3_PAY_ELEMENT` = ".$query2[$kk]." WHERE `ID` = ".(int)$kk);
                                 }*/
                            }
                        }
                    }
                }
    }
}