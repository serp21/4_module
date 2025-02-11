<?php
header('Content-Type:Text/Html;charset=utf-8');
session_start();

include '/home/bitrix/www/constants.php';

error_reporting(0);
if($_SESSION['bitAppFot']['ID'] == USER_BELOGLAZOV)
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

function getTask($filter = '', $content = array(), $start = 0)
{
    sleep(1);
    #$reqTask = CRest::call('tasks.task.list', array('select' => array('ID', 'TITLE','CLOSED_DATE', 'UF_FOT_RESPONSE'), 'filter' => $filter, 'order' => array('CLOSED_DATE' => 'DESC'), 'start'=> $start));
    $reqTask = CRest::call('tasks.task.list', array('select' => array('ID', 'TITLE', ZADACHI_DATA_PERVOGO_ZAKRYTIYA, ZADACHI_FOT_OTVETSTVENNOGO), 
        'filter' => $filter, 'order' => array('CLOSED_DATE' => 'DESC'), 'start'=> $start));
    if($reqTask['result']['tasks'])
    {
        $totalQuery = ceil($reqTask['total'] /50);
        if($totalQuery > 1)
        {
            if($totalQuery <= 50)
            {
                $s = 0;
                $query = array();
                for($i = 1; $i <= $totalQuery; $i++)
                {
                    #$query[$i] = array('method' => 'tasks.task.list', 'params' => array('select' => array('ID', 'TITLE','CLOSED_DATE', 'UF_FOT_RESPONSE'), 'filter' => $filter, 'order' => array('CLOSED_DATE' => 'DESC'), 'start'=> $s));
                    $query[$i] = array('method' => 'tasks.task.list', 
                        'params' => array('select' => array('ID', 'TITLE',ZADACHI_DATA_PERVOGO_ZAKRYTIYA, ZADACHI_FOT_OTVETSTVENNOGO), 
                        'filter' => $filter, 'order' => array('CLOSED_DATE' => 'DESC'), 'start'=> $s));
                    $s += 50;
                }
                
                sleep(1);
                $request = CRest::callBatch($query);
                foreach($request['result']['result'] as $value)
                {
                    foreach($value['tasks'] as $val)
                    {
                        $content[] = $val;
                    }
                }
            }
        }
        else
        {
            foreach($reqTask['result']['tasks'] as $value)
            {
                $content[] = $value;
            }
        }
    }

    if(!empty($content))
    {
        $content2 = array();
        $tmp = array();
        foreach($content as $k => $v)
        {
            $tmp[$k] = $v['ufClosedateFirst'];
        }

        arsort($tmp);
        foreach($tmp as $k2 => $v2)
        {
            $content2[] = $content[$k2];
        }
    }
    else
        $content2 = $content;

    return $content2;
}

function getList($list = 0, $select = array(), $filter = array(), $order = 'ID', $start = 0, $content = array())
{
    sleep(1);
    $reqList = CRest::call('lists.element.get', array('select' => $select, 
        'IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $list, 'FILTER' => $filter, 'ELEMENT_ORDER' => array($order => 'DESC'), 'start' => $start));
    if($reqList['result'])
    {
        $totalQuery = ceil($reqList['total'] /50);
        if($totalQuery > 1)
        {
            if($totalQuery <= 50)
            {
                $s = 0;
                $query = array();
                for($i = 1; $i <= $totalQuery; $i++)
                {
                    $query[$i] = array('method' => 'lists.element.get', 
                        'params' => array('select' => $select, 'IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $list, 
                        'FILTER' => $filter, 'ELEMENT_ORDER' => array($order => 'DESC'), 'start' => $s));
                    $s += 50;
                }
                
                sleep(1);
                $request = CRest::callBatch($query);
                foreach($request['result']['result'] as $value)
                {
                    foreach($value as $val)
                    {
                        $content[] = $val;
                    }
                }
            }
            
            
        }
        else
        {
            foreach($reqList['result'] as $value)
            {
                $content[] = $value;
            }
        }
    }
    
    return $content;
}

function getPay($db, $type, $start = 0, $stop = 0)
{
    $return = array();
    $ids = array();

    if($type == 'year')
    {
        $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_SUMMA."
                           LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA."
                           WHERE `e`.`IBLOCK_ID` = ".IB_ZARABOTNAYA_PLATA." AND `p3`.`VALUE` = ".(int)$_SESSION['bitAppFot']['ID']." AND `p1`.`VALUE` BETWEEN '".date('Y-01-01')."' AND '".date('Y-12-31')."'");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ids[$result['ID']] = date('Y-m-01', strtotime($result['date']));
            }
        }
    }

    if($type == 'all')
    {
        $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_SUMMA."
                           LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA."
                           WHERE `e`.`IBLOCK_ID` = ".IB_ZARABOTNAYA_PLATA." AND `p3`.`VALUE` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ids[$result['ID']] = date('Y-m-01', strtotime($result['date']));
            }
        }
    }

    if($type == 'date' && $start > 0 && $stop > 0)
    {
        $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_SUMMA."
                           LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA."
                           WHERE `e`.`IBLOCK_ID` = ".IB_ZARABOTNAYA_PLATA." AND `p3`.`VALUE` = ".(int)$_SESSION['bitAppFot']['ID']." AND `p1`.`VALUE` BETWEEN '".date('Y-m-d', strtotime($start))."' AND '".date('Y-m-d', strtotime($stop))."'");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ids[$result['ID']] = date('Y-m-01', strtotime($result['date']));
            }
        }
    }

    if(!empty($ids))
    {
        $data = array();
        $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`, `p2`.`VALUE_NUM` AS `sum`, `p3`.`VALUE` AS `zp`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_DATA."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_SUMMA."
                           LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_NACHISLENIE_ZP."
                           WHERE `e`.`IBLOCK_ID` = ".IB_PLATEJI." AND `p3`.`VALUE` IN(".implode(',', array_keys($ids)).")");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if(!isset($data[$result['zp']]))
                {
                    $data[$result['zp']]['date'] = $ids[$result['zp']];
                    $data[$result['zp']]['sum']  = 0;
                }

                $data[$result['zp']]['sum'] += $result['sum'];
            }
        }
        
        $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`, `p2`.`VALUE_NUM` AS `sum`, `p3`.`VALUE` AS `zp`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_DATA."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_STOIMOST."
                           LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_ZARPLATA."
                           WHERE `e`.`IBLOCK_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE." AND `p3`.`VALUE` IN(".implode(',', array_keys($ids)).")");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if(!isset($data[$result['zp']]))
                {
                    $data[$result['zp']]['date'] = $ids[$result['zp']];
                    $data[$result['zp']]['sum']  = 0;
                }

                $data[$result['zp']]['sum'] += $result['sum'];
            }
        }

        /*
        $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`, `p2`.`VALUE_NUM` AS `sum`, `p3`.`VALUE` AS `zp`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 146
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 147
                           LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = 161
                           WHERE `e`.`IBLOCK_ID` = 28 AND `p3`.`VALUE` IN(".implode(',', array_keys($ids)).")");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if(!isset($data[$result['zp']]))
                {
                    $data[$result['zp']]['date'] = $ids[$result['zp']];
                    $data[$result['zp']]['sum']  = 0;
                }

                $data[$result['zp']]['sum'] += $result['sum'];
            }
        }*/

        foreach($data as $val)
        {
            if(!isset($return[$val['date']]))
                $return[$val['date']] = 0;

            $return[$val['date']] += $val['sum'];
        }
    }

    return $return;
}

function getBalance($db)
{
    $out = 0;
    $ids = array();

    if($_SESSION['bitAppFot']['manager'] != POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER && $_SESSION['bitAppFot']['S_BALANCE'] == 1)
    {
        #   зарплата
        $sql = $db->query("SELECT `p2`.`VALUE_NUM` AS `sum`, `e`.`ID`, `p3`.`VALUE_NUM` AS `prem`, `p4`.`VALUE` AS `manager`
                                   FROM `b_iblock_element` AS `e`
                                   LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA."
                                   LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_SUMMA."
                                   LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_PREMIYA."
                                   LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA."
                                   WHERE `e`.`IBLOCK_ID` = ".IB_ZARABOTNAYA_PLATA." AND `p1`.`VALUE` IN(".(int)$_SESSION['bitAppFot']['ID'].")");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if($result['manager'] != IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER
                     && $result['manager'] != IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR
                     && $result['manager'] != IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA)
                {
                    $out += ($result['sum'] + $result['prem']) * -1;
                    $ids[$result['ID']] = $result['ID'];
                }
            }
        }

        #   переводы
        $sql = $db->query("SELECT SUM(`p2`.`VALUE_NUM`) AS `sum`
                                   FROM `b_iblock_element` AS `e`
                                   LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_PEREVODY_POLUCHATEL."
                                   LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_PEREVODY_SUMMA."
                                   WHERE `e`.`IBLOCK_ID` = ".IB_PEREVODY." AND `p1`.`VALUE` IN(".(int)$_SESSION['bitAppFot']['ID'].")");
        if($sql->num_rows > 0)
        {
            $result = $sql->fetch_assoc();
            $out += $result['sum'];
        }

        $sql = $db->query("SELECT SUM(`p2`.`VALUE_NUM`) AS `sum`
                                   FROM `b_iblock_element` AS `e`
                                   LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_PEREVODY_OTPRAVITEL."
                                   LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_PEREVODY_SUMMA."
                                   WHERE `e`.`IBLOCK_ID` = ".IB_PEREVODY." AND `p1`.`VALUE` IN(".(int)$_SESSION['bitAppFot']['ID'].")");
        if($sql->num_rows > 0)
        {
            $result = $sql->fetch_assoc();
            $out += $result['sum'] * -1;
        }
    /*
        #   Топливо
        $sql = $db->query("SELECT SUM(`p2`.`VALUE_NUM`) AS `sum`
                                   FROM `b_iblock_element` AS `e`
                                   LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = 223
                                   LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = 213
                                   WHERE `e`.`IBLOCK_ID` = 42 AND `p1`.`VALUE` IN(".implode(',', $ids).")");
        if($sql->num_rows > 0)
        {
            $result = $sql->fetch_assoc();
            $out += $result['sum'];
        }
    */

        #   задачи
        $sql = $db->query("SELECT SUM(`t1`.`".ZADACHI_FOT_OTVETSTVENNOGO."`) AS `sum`
                                   FROM `b_tasks` AS `t`
                                   INNER JOIN `b_uts_tasks_task` AS `t1` ON `t1`.`VALUE_ID` = `t`.`ID`
                                   WHERE `t1`.`".ZADACHI_DATA_PERVOGO_ZAKRYTIYA."` > 0 AND  `t1`.`".ZADACHI_FOT_OTVETSTVENNOGO."` != 0 AND `t`.`RESPONSIBLE_ID` IN(".(int)$_SESSION['bitAppFot']['ID'].")");
        if($sql->num_rows > 0)
        {
            $result = $sql->fetch_assoc();
            $out += $result['sum'];
        }

        if($out < 0)
            $out = 'Баланс: <strong class="text-danger">'.number_format($out, 2, '.', ' ').'</strong> руб.';
        else
            $out = 'Баланс: <strong>'.number_format($out, 2, '.', ' ').'</strong> руб.';
    }
    
    return $out;
}

function getMTR($db)
{
    $out = '';
    $sql = $db->query("SELECT `ID`, `TITLE`, `".MTR_OPISANIE."`, `STAGE_ID`,  `".MTR_MAC_ADRES."` AS `MAC`, `".MTR_IP_ADRES."` AS `IP`
                       FROM `".CRM_MTR."`
                       WHERE `ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppFot']['ID']." AND `STAGE_ID` != '".MTR_OBSHCHEE_SPISANO_ZA_BESPLATNO."' 
                        AND `STAGE_ID` != '".MTR_OBSHCHEE_UDALENO."' AND `STAGE_ID` != '".MTR_OBSHCHEE_REALIZOVANO."'");
    if($sql->num_rows > 0)
    {
        $out = '<p><strong>За вами числятся следующие материально-технические ресурсы:</strong></p>';
        while($result = $sql->fetch_assoc())
        {
            $ip  = (!empty($result['IP'])) ? ' IP: '.htmlspecialchars($result['IP']).' ' : '';
            $mac = (!empty($result['MAC'])) ? ' MAC: '.htmlspecialchars($result['MAC']) : '';
            $result[MTR_OPISANIE] = (!empty($result[MTR_OPISANIE])) ? '<small>('.htmlspecialchars($result[MTR_OPISANIE]).') '.$ip.$mac.'</small>' : '<small>'.$ip.$mac.'</small>';
            $invent_text = ($result['STAGE_ID'] == MTR_OBSHCHEE_INVENTARIZACIYA) ? '<span class="text-danger"> (проводится инвентаризация)</span>' : '';
            $out .= '<div><a href="/crm/type/'.CRM_MTR_ID.'/details/'.$result['ID'].'/" target="_blank">'.htmlspecialchars($result['TITLE']).'</a> '.$result[MTR_OPISANIE].' '.$invent_text.'</div>';
        }
    }
    /*
    $reqMTR = getList(101, array('NAME', 'ID', 'PROPERTY_1394'), array('PROPERTY_407' => $_SESSION['bitAppFot']['ID']));
    if(!empty($reqMTR))
    {
        $out = '<p><strong>За вами числятся следующие материально-технические ресурсы:</strong></p>';
        foreach($reqMTR as $mtr)
        {
            if(current($mtr['PROPERTY_1394']) != 471)
                $out .= '<div>'.(int)$mtr['ID'].' '.htmlspecialchars($mtr['NAME']).'</div>';
        }
    }
    */
    
    return $out;
}

function ending($n, $n1, $n2, $n5)
{
    if($n >= 11 and $n <= 19) return $n5;    
    
    $n = $n % 10;
    
    if($n == 1) return $n1;
    
    if($n >= 2 and $n <= 4) return $n2;
    
    return $n5;
}

function getVacation($db, $id)
{   ////////////// Отпуск поменял цикл if и $ret['days'] = 14;  $days = -14; и количество на "доступных"
    $ret = array();
    $ret['days'] = 14;
    $isEmp = 0;

    $days = -14;
    $date_register = '2021-01-01';
    if($id > 0)
    {
        $sql = $db->query("SELECT `".POLZOVATELI_DATA_RASCHETA_OTPUSKA."` FROM `b_uts_user` WHERE `VALUE_ID` = ".(int)$id);
        if($sql->num_rows > 0)
        {
            $result = $sql->fetch_assoc();
            if(!empty($result[POLZOVATELI_DATA_RASCHETA_OTPUSKA]) && $result[POLZOVATELI_DATA_RASCHETA_OTPUSKA] >= $date_register)
                $date_register = $result[POLZOVATELI_DATA_RASCHETA_OTPUSKA];
            elseif(!empty($result[POLZOVATELI_DATA_RASCHETA_OTPUSKA]) && $result[POLZOVATELI_DATA_RASCHETA_OTPUSKA] < $date_register)
                $date_register = date('2021-m-d', strtotime($result[POLZOVATELI_DATA_RASCHETA_OTPUSKA]));
            
            if($result[POLZOVATELI_DATA_RASCHETA_OTPUSKA] > 0)
                $isEmp = 1;
        }

        $sql = $db->query("SELECT `e`.`ID`, `e`.`NAME`, `e`.`ACTIVE_FROM`, `e`.`ACTIVE_TO`, DATEDIFF(`e`.`ACTIVE_TO`, `e`.`ACTIVE_FROM`) +1 AS `days`, `p2`.`VALUE` AS `type`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_GRAFIK_OTSUTSTVIY_POLZOVATEL."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA."
                           WHERE `e`.`IBLOCK_ID` = ".IB_GRAFIK_OTSUTSTVIY." AND `e`.`ACTIVE_FROM` >= '".$date_register."'
                            AND (`p2`.`VALUE` = ".IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_OTPUSK_EJEGODNYY." OR `p2`.`VALUE` = ".IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_OTGUL_ZA_SVOY_SCHET." 
                            OR `p2`.`VALUE` = ".IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_PROGUL.") AND `p1`.`VALUE` = ".(int)$id."
                           ORDER BY `e`.`ACTIVE_FROM` ASC");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ret['info'][date('Y', strtotime($result['ACTIVE_FROM']))][$result['ID']] = $result;
                $days += $result['days'];
            }
        }
    }
    $date1 = new DateTime($date_register);
    $date2 = new DateTime();
    $year0 = 365;
    $year02 = 365/2;
    $yearDiff = date_diff(new DateTime(), new DateTime($date_register))->y;
    $dateDiff = date_diff(new DateTime(), new DateTime($date_register))->days;
    $diff  = $date2->diff($date1);
    $sumDays = intdiv(($diff->format('%y') * 12) + $diff->format('%m'), 6) * 14;
    $ret['message'] = '';
    if($isEmp == 1) {
        $ret['days'] = $sumDays - $days;
    } else {
        $ret['message'] = 'Не установлена дата принятия на работу';
    }
    
    if($ret['days'] > 70){
        $ret['message'] = $ret['days'].' - Превышено количество доступных дней отпуска.';
    }
    $flagzero = 0;
    $colDays = 14;
    if ($yearDiff >= 1) {
        $dateDiff = $dateDiff - $year0*$yearDiff;
        $dateDiff = intval($year0 - $dateDiff);
        $colDays += 14;
        if ($flagzero == 0){
            $ret['days'] += 14;
            $flagzero = 1;
        }
    } else{
        if($dateDiff > $year02){
            $dateDiff = intval($year0 - $dateDiff);
        } else{
            $dateDiff = intval($year0/2 - $dateDiff);
        }
    }
    $ret['start'] = $date_register;
    $ret['add'] = $dateDiff;
    $ret['daysadd'] = $colDays;
    return $ret;
    ////////////// Отпуск поменял цикл if и $ret['days'] = 14;  $days = -14; и количество на "доступных"
}

function getNormTime($db, $id = 0)
{
    $id = ($id == 0) ? $_SESSION['bitAppFot']['ID'] : $id;
    
    $return['normH'] = 0;
    $return['normD'] = 0; // кол-во рабочих дней в месяце с учетом отсутствий (отгул, командировка и др.)
    $return['normWorkDay'] = 0; // кол-во рабочих дней в месяце
    $return['normDayFirstMonth'] = 0; // кол-во рабочих дней в первом месяце с момента трудоустройства // разраб
    $return['isFirstMonth'] = false; // работает ли первый месяц 
    $return['workH'] = 0;
    $return['workD'] = 0;
    // $return['currH'] = 0;
    $return['holyd'] = 0;
    $return['users'] = array();
    $return['schedule'] = '';
    $return['schedule_id'] = 0;
    $return['showInfo'] = 0;

    $firstDay = date('Y-m-d', strtotime('first day of now'));
    $start   = date('Y-m-d', strtotime('first day of now'));
    $stop    = date('d.m.Y', strtotime('last day of now'));
    $absence = 0;
    $works   = 0;
    $norm    = 0;
    $holyday = 0;
    $currDay = 0;

    $sql = $db->query("SELECT `s`.`NAME`, `t`.`SCHEDULE_ID`
                       FROM `b_timeman_work_schedule_user` AS `t`
                       LEFT JOIN `b_timeman_work_schedule` AS `s` ON `s`.`ID` = `t`.`SCHEDULE_ID`
                       WHERE `t`.`STATUS` = 0 AND `t`.`USER_ID` = ".(int)$id);
    if($sql->num_rows > 0)
    {
        $result = $sql->fetch_assoc();
        $return['schedule'] = $result['NAME'];
        $return['schedule_id'] = $result['SCHEDULE_ID'];
        
        if($result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_REMOTE || $result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_FACE || $result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_BROWSER) # for test @|| $result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_BROWSER@
        {
            $sql = $db->query("SELECT `VALUE_ID`, `".POLZOVATELI_DATA_NACHALA_RABOTY."` FROM `b_uts_user` WHERE (`".POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA."` = ".POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR."
                 OR `".POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA."` = ".POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR.") AND `VALUE_ID` =".(int)$id);
            if($sql->num_rows > 0)
            {
                $result = $sql->fetch_assoc();
                $return['showInfo'] = 1;
                
                if(date('Y-m') == date('Y-m', strtotime($result[POLZOVATELI_DATA_NACHALA_RABOTY]))) {
                    $start = $result[POLZOVATELI_DATA_NACHALA_RABOTY];
                    $return['isFirstMonth'] = true; // === 02/08/2024 === // разраб
                    $firstDay = $result[POLZOVATELI_DATA_NACHALA_RABOTY]; // === 02/08/2024 === // разраб
                }
                    
            }
        }
    }

        if($return['showInfo'] == 1)
        {
            $forStop = date('d', strtotime('last day of now'));
            $dayStop = date('d');
            $holDay2024 = array(
                '2024-01-1','2024-01-2','2024-01-3','2024-01-4','2024-01-5','2024-01-6','2024-01-7','2024-01-8','2024-02-23',
                '2024-03-8','2024-04-29','2024-05-1','2024-05-9','2024-05-10','2024-06-12','2024-11-4','2024-12-31'
            );

            for($i = 1; $i <= $forStop; $i++)
            {
                if(date('Y-m-'.$i) >= $start)
                {
                    $day  = date('N', strtotime(date('Y-m-'.$i)));
                    if($day == 6 || $day == 7 || in_array(date('Y-m-'.$i), $holDay2024))
                        $holyday++;
                    else
                    {
                        $norm++;

                        $return['normWorkDay']++; // разраб

                        if($return['isFirstMonth'] && strtotime(date('Y-m-'.$i)) >= strtotime($firstDay)) {
                            $return['normDayFirstMonth']++; // === 02/08/2024 === // разраб
                        }

                        if($i < $dayStop)
                        {
                            $currDay++;
                        }
                    }
                }
            }
            $return['holyd'] = $holyday;

            $sql = $db->query("SELECT `RECORDED_DURATION` AS `DURATION`
                       FROM `b_timeman_entries`
                       WHERE `DATE_START` >= '".$start.' 00-00-00'."'
                        AND `DATE_FINISH` <= '".date('Y-m-d 23:59:59', strtotime('last day of now'))."'
                        AND `USER_ID` = ".(int)$id);
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $works += $result['DURATION'];
                }

                $return['workH'] = round($works /60 /60, 1);
                $return['workD'] = round($works /60 /60, 1);
                $return['сurrWorkDay'] = round($works /60 /60 /8, 1);
            }
            
            $sql = $db->query("SELECT `".POLZOVATELI_MINIMALNAYA_PRODOLJITELNOST_RABOCHEGO_DNYA."` FROM `b_uts_user` WHERE `VALUE_ID` = ".(int)$id);
            if($sql->num_rows > 0)
            {
                $result = $sql->fetch_assoc();
                if((int)$result[POLZOVATELI_MINIMALNAYA_PRODOLJITELNOST_RABOCHEGO_DNYA] > 0)
                {
                    
                  //  echo $result['UF_TM_MIN_DURATION'];

                    $tmp = explode(':', $result[POLZOVATELI_MINIMALNAYA_PRODOLJITELNOST_RABOCHEGO_DNYA]);

                    
                  //  echo "<pre>".print_r($tmp, true)."</pre>";

                    $duration = (isset($tmp[0], $tmp[1])) ? $tmp[0] + ($tmp[1] /60) : 8;

                }
                else
                    $duration = 8;
            }
            else
                $duration = 8;

            $sql = $db->query("SELECT `e`.`ACTIVE_FROM`, `e`.`ACTIVE_TO`
                               FROM `b_iblock_element` AS `e`
                               LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_GRAFIK_OTSUTSTVIY_POLZOVATEL."
                               WHERE `e`.`IBLOCK_ID` = ".IB_GRAFIK_OTSUTSTVIY." AND `p1`.`VALUE` = ".(int)$id);
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $tmpStart = explode(' ', $result['ACTIVE_FROM']);
                    $tmpStop  = explode(' ', $result['ACTIVE_TO']);

                    if(isset($tmpStart[1]) && isset($tmpStop[1]))
                    {
                        $aStart = $tmpStart[0];
                        $aStop  = $tmpStop[0];

                        for($i = 1; $i <= $forStop; $i++)
                        {
                            $ii = ($i < 10) ? '0'.$i : $i;
                            if($aStart <= date('Y-m-'.$ii) && $aStop >= date('Y-m-'.$ii))
                            {
                                $day = date('N', strtotime(date('Y-m-'.$ii)));
                                if($day != 6 && $day != 7)
                                {
                                    if(date('Y-m-'.$ii) <= date('Y-m-d'))
                                    {
                                        $absence++;
                                        $currDay--;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            $return['normD'] = ($norm - $absence);
            $return['dur']   = $duration;
            $return['abs']   = $absence;
            $return['normH'] = $return['normD'] * $duration;
            $return['currH'] = $currDay  * $duration;
        }

       // echo "<pre>".print_r($return, true)."</pre>";

    return $return;
}

function getErrors($db)
{
    $ret = array();
    #   Выговор
    $sql = $db->query("SELECT `e`.`ID`, DATE_FORMAT(`e`.`ACTIVE_FROM`, '%Y-%m') AS `date`, `p2`.`VALUE` AS `user`
                       FROM `b_iblock_element` AS `e`
                       LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_DOSKA_POCHETA_BLAGODARNOST."
                       LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_DOSKA_POCHETA_POLZOVATELI."
                       WHERE `e`.`IBLOCK_ID` = ".IB_DOSKA_POCHETA." AND `p1`.`VALUE` = ".IB_DOSKA_POCHETA_BLAGODARNOST_VYGOVOR." AND `p2`.`VALUE` = ".(int)$_SESSION['bitAppFot']['ID']);
    if($sql->num_rows > 0)
    {
        while($result = $sql->fetch_assoc())
        {
            $ret['reb'][$result['date']] = $result['user'];
        }
    }

    #   Не заполнены поля в сделках
        $sql = $db->query("SELECT `ID`, `TITLE`
                           FROM b_crm_deal
                           INNER JOIN b_uts_crm_deal ON b_crm_deal.ID = b_uts_crm_deal.VALUE_ID
                           WHERE (".SDELKI_VID_DOGOVORA." IS NULL OR ".SDELKI_DATA_ZAKLYUCHENIYA_DOGOVORA." IS NULL OR ".SDELKI_SROK_PO_DOGOVORU." IS NULL
                                  OR CLOSEDATE IS NULL OR ".SDELKI_MYCOMPANY_ID_WITHOUT_NAME." IS NULL OR COMMENTS IS NULL OR ".SDELKI_NOMER_DOGOVORA." IS NULL OR (COMPANY_ID IS NULL AND CONTACT_ID IS NULL))
                                  AND STAGE_ID NOT LIKE '%LOSE%' AND STAGE_ID NOT LIKE '%WON%' AND (CATEGORY_ID = ".SDELKI_OBEKT." OR CATEGORY_ID = ".SDELKI_OBEKT_ISKLYUCHENIE.") 
                                  AND ".SDELKI_DATA_ZAKLYUCHENIYA_DOGOVORA." >= '2019-01-01'
                                  AND ASSIGNED_BY_ID = ".(int)$_SESSION['bitAppFot']['ID']."
                           ORDER BY OPPORTUNITY DESC
                           LIMIT 50");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ret['dealRow'][$result['ID']] = $result['TITLE'];
            }
        }

        #   МТР
        $sql = $db->query("SELECT `ID`, `TITLE` FROM `".CRM_MTR."` WHERE `STAGE_ID` = '".MTR_OBSHCHEE_INVENTARIZACIYA."' 
            AND `ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppFot']['ID']);

        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ret['mtr'][$result['ID']] = $result['TITLE'];
            }
        }

        #   АВР
        $sql = $db->query("SELECT `ID`, `TITLE` FROM `".CRM_AVR."` WHERE `STAGE_ID` = '".AVR_OBSHCHEE_DOKUMENTY_NE_PREDOSTAVLENY."' 
            AND `ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppFot']['ID']);

        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ret['avr'][$result['ID']] = $result['TITLE'];
            }
        }

        #   Счета на оплату
        $sql = $db->query("SELECT `ID`, `TITLE` FROM `".CRM_SCHET_NA_OPLATU."` WHERE `STAGE_ID` = '".SCHET_NA_OPLATU_PO_SCHETU_DOKUMENTY_NE_PREDOSTAVLENY."' 
            AND `ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppFot']['ID']);

        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ret['invoice'][$result['ID']] = $result['TITLE'];
            }
        }

        #   Счета 
        $sql = $db->query("SELECT `ID`, `TITLE` FROM `".CRM_SCHETA."` WHERE `STAGE_ID` = '".SCHETA_OBSHCHEE_PROSROCHENO."' AND `ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ret['invoicep'][$result['ID']] = $result['TITLE'];
            }
        }

        $sql = $db->query("SELECT `ID`, `TITLE` FROM `b_tasks` WHERE `STATUS` != ".TASK_STATUS_CLOSE." AND `ZOMBIE` = 'N' AND `DEADLINE` < NOW() AND `RESPONSIBLE_ID` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ret['tasks'][$result['ID']] = $result['TITLE'];
            }
        }
        // 13.08.24
        $sql = $db->query("SELECT `ID`, `TITLE` FROM `b_crm_deal` WHERE `STAGE_ID` NOT LIKE '%WON' AND `STAGE_ID` NOT LIKE '%LOSE' AND `STAGE_ID` NOT LIKE '%1' AND `CLOSEDATE` < NOW() AND `ASSIGNED_BY_ID` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ret['dealDate'][$result['ID']] = $result['TITLE'];
            }
        }

        $sql = $db->query("SELECT `SCHEDULE_ID` FROM `b_timeman_work_schedule_user` WHERE `STATUS` = 0 AND `USER_ID` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($sql->num_rows > 0)
        {
            $result = $sql->fetch_assoc();
            $ret['schedule'] = 0;
        }
        else
            $ret['schedule'] = 1;

        return $ret;
}

function getZPlist($db)
{
    $zp = array();
    $ids = array();
    $idsZP = array();
    $extIds = array();
    $tmpExt = array();
    $arErrTasks = array();
    $arErrDeals = array();
    $arErrCRM   = array();
    $arErrReb   = array();
    $scheduleErr = '';

    if(!empty($_SESSION['bitAppFot']['HEAD']))
    {
        #   АВР
        $sql = $db->query("SELECT `ID`, `TITLE`, `ASSIGNED_BY_ID` FROM `".CRM_AVR."` WHERE `STAGE_ID` = '".AVR_OBSHCHEE_DOKUMENTY_NE_PREDOSTAVLENY."'");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arErrCRM['avr'][$result['ASSIGNED_BY_ID']][$result['ID']] = $result['TITLE'];
            }
        }

        #   Выговор
        $sql = $db->query("SELECT `e`.`ID`, DATE_FORMAT(`e`.`ACTIVE_FROM`, '%Y-%m') AS `date`, `p2`.`VALUE` AS `user`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_DOSKA_POCHETA_BLAGODARNOST."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_DOSKA_POCHETA_POLZOVATELI."
                           WHERE `e`.`IBLOCK_ID` = ".IB_DOSKA_POCHETA." AND `p1`.`VALUE` = ".IB_DOSKA_POCHETA_BLAGODARNOST_VYGOVOR." AND `p2`.`VALUE` 
                           IN(SELECT DISTINCT(`VALUE_ID`) FROM `b_utm_user` WHERE `VALUE_INT` IN(".implode(',', $_SESSION['bitAppFot']['HEAD'])."))");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arErrReb[$result['user']][$result['date']] = $result['user'];
            }
        }

        #   Не заполнены поля в сделках
        $sql = $db->query("SELECT ID, TITLE, ASSIGNED_BY_ID
                           FROM b_crm_deal
                           INNER JOIN b_uts_crm_deal ON b_crm_deal.ID = b_uts_crm_deal.VALUE_ID
                           WHERE (".SDELKI_NOMER_DOGOVORA." IS NULL OR ".SDELKI_DATA_ZAKLYUCHENIYA_DOGOVORA." IS NULL OR ".SDELKI_SROK_PO_DOGOVORU." IS NULL
                                  OR CLOSEDATE IS NULL OR ".SDELKI_MYCOMPANY_ID_WITHOUT_NAME." IS NULL OR COMMENTS IS NULL OR ".SDELKI_NOMER_DOGOVORA." IS NULL OR (COMPANY_ID IS NULL AND CONTACT_ID IS NULL))
                                  AND STAGE_ID NOT LIKE '%LOSE%' AND STAGE_ID NOT LIKE '%WON%' AND (CATEGORY_ID = ".SDELKI_OBEKT." OR CATEGORY_ID = ".SDELKI_OBEKT_ISKLYUCHENIE.") 
                                  AND ".SDELKI_DATA_ZAKLYUCHENIYA_DOGOVORA." >= '2019-01-01'
                           ORDER BY OPPORTUNITY DESC
                           LIMIT 50");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arErrCRM['deal'][$result['ASSIGNED_BY_ID']][$result['ID']] = $result['TITLE'];
            }
        }

        #   МТР
        $sql = $db->query("SELECT `ID`, `TITLE`, `ASSIGNED_BY_ID` FROM `".CRM_MTR."` WHERE `STAGE_ID` = '".MTR_OBSHCHEE_INVENTARIZACIYA."'");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arErrCRM['mtr'][$result['ASSIGNED_BY_ID']][$result['ID']] = $result['TITLE'];
            }
        }

        #   Счета на оплату
        $sql = $db->query("SELECT `ID`, `TITLE`, `ASSIGNED_BY_ID` FROM `".CRM_SCHET_NA_OPLATU."` WHERE `STAGE_ID` = '".SCHET_NA_OPLATU_PO_SCHETU_DOKUMENTY_NE_PREDOSTAVLENY."'");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arErrCRM['inv'][$result['ASSIGNED_BY_ID']][$result['ID']] = $result['TITLE'];
            }
        }

        #   Счета
        $sql = $db->query("SELECT `ID`, `TITLE`, `ASSIGNED_BY_ID` FROM `".CRM_SCHETA."` WHERE `STAGE_ID` = '".SCHETA_OBSHCHEE_PROSROCHENO."'");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arErrCRM['invp'][$result['ASSIGNED_BY_ID']][$result['ID']] = $result['TITLE'];
            }
        }

        $sql = $db->query("SELECT `ID`, `TITLE`, `RESPONSIBLE_ID` FROM `b_tasks` WHERE `STATUS` = ".TASK_STATUS_RUNNING." AND `ZOMBIE` = 'N' AND `DEADLINE` < NOW()");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arErrTasks[$result['RESPONSIBLE_ID']][$result['ID']] = $result['TITLE'];
            }
        }

        $sql = $db->query("SELECT `ID`, `TITLE`, `ASSIGNED_BY_ID` FROM `b_crm_deal` WHERE `STAGE_ID` NOT LIKE '%WON' AND `STAGE_ID` NOT LIKE '%LOSE' AND `CLOSEDATE` < NOW()");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $arErrDeals[$result['ASSIGNED_BY_ID']][$result['ID']] = $result['TITLE'];
            }
        }

        $arStructure = array(POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR, 
        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR, 
        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER, 
        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA);

        $sql = $db->query("SELECT `u`.`ID`, CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`,
         DATE_FORMAT(`u`.`DATE_REGISTER`, '%Y-%m') AS `DATE_REGISTER`, `up`.`NAME` AS `position`,
                                  `pp`.`VALUE` AS `".POLZOVATELI_MINIMALNAYA_ZP."`, `uu`.`".POLZOVATELI_MAKSIMALNAYA_ZP."`, `um`.`VALUE_INT` AS `department`,
                                   `uu`.`".POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA."` AS `manager`, `uu`.`".POLZOVATELI_SDELNAYA_OPLATA."` AS `sdel`, `sch`.`SCHEDULE_ID`
                           FROM `b_user` AS `u`
                           LEFT JOIN `b_uts_user` AS `uu` ON `uu`.`VALUE_ID` = `u`.`ID`
                           LEFT JOIN `b_utm_user` AS `um` ON `um`.`VALUE_ID` = `u`.`ID`
                           LEFT JOIN `b_iblock_element` AS `up` ON `up`.`ID` = `uu`.`".POLZOVATELI_ID_DOLJNOSTI."`
                           LEFT JOIN `b_iblock_element_property` AS `pp` ON `pp`.`IBLOCK_ELEMENT_ID` = `uu`.`".POLZOVATELI_ID_DOLJNOSTI."` AND `pp`.`IBLOCK_PROPERTY_ID` = ".IB_SHTATNOE_RASPISANIE_OKLAD."
                           LEFT JOIN `b_timeman_work_schedule_user` AS `sch` ON `USER_ID` = `u`.`ID` AND `sch`.`STATUS` = 0
                           WHERE `u`.`ACTIVE` = 'Y' AND `uu`.`".POLZOVATELI_PODRAZDELENIYA."` IS NOT NULL AND `uu`.`".POLZOVATELI_PODRAZDELENIYA."` != 'a:0:{}'
                            AND `um`.`VALUE_INT` IN(".implode(',', $_SESSION['bitAppFot']['HEAD']).")
                           ORDER BY `u`.`LAST_NAME` ASC, `u`.`NAME` ASC");
        if($sql->num_rows > 0)
        {

            

            #   Не установлен рабочий график
            while($result = $sql->fetch_assoc())
            {
                #if($result['ID'] != $_SESSION['bitAppFot']['ID'])

                    $normTime = getNormTime($db, $result['ID']); // Разраб
                    $zp[$result['ID']]['normWorkDay'] = $normTime['normWorkDay']; // Разраб
                    $zp[$result['ID']]['normDayFirstMonth'] = $normTime['normDayFirstMonth']; // Разраб
                    $zp[$result['ID']]['isFirstMonth'] = $normTime['isFirstMonth']; // Разраб
                #{
                    $ids[$result['ID']] = $result['ID'];
                    //$tmpDp = unserialize($result['tmpDep']);

                    //elseif(!empty($tmpDp))
                    //    $zp[$result['ID']]['dep']  = $tmpDp[0];
                    if(!empty($result['department']))
                        $zp[$result['ID']]['dep']  = $result['department'];
                    else
                        $zp[$result['ID']]['dep']  = '';

                    $zp[$result['ID']]['user'] = $result['user'];

                    $zp[$result['ID']]['zp']   = number_format($result[POLZOVATELI_MINIMALNAYA_ZP], 0, '.', ' ').' / '.number_format($result[POLZOVATELI_MAKSIMALNAYA_ZP], 0, '.', ' ');
                    $zp[$result['ID']]['max']  = $result[POLZOVATELI_MINIMALNAYA_ZP]; // === 22/08/2024 ===
                    $zp[$result['ID']]['manager']  = $result['manager'];
                    $zp[$result['ID']]['position'] = $result['position'];
                    $zp[$result['ID']]['taskErr']  = (isset($arErrTasks[$result['ID']])) ? $arErrTasks[$result['ID']] : array();
                    $zp[$result['ID']]['dealErr']  = (isset($arErrDeals[$result['ID']])) ? $arErrDeals[$result['ID']] : array();
                    $zp[$result['ID']]['avrErr']   = (isset($arErrCRM['avr'][$result['ID']])) ? $arErrCRM['avr'][$result['ID']] : array();
                    $zp[$result['ID']]['mtrErr']   = (isset($arErrCRM['mtr'][$result['ID']])) ? $arErrCRM['mtr'][$result['ID']] : array();
                    $zp[$result['ID']]['invErr']   = (isset($arErrCRM['inv'][$result['ID']])) ? $arErrCRM['inv'][$result['ID']] : array();
                    $zp[$result['ID']]['invpErr']  = (isset($arErrCRM['invp'][$result['ID']])) ? $arErrCRM['invp'][$result['ID']] : array();
                    $zp[$result['ID']]['crmErr']   = (isset($arErrCRM['deal'][$result['ID']])) ? $arErrCRM['deal'][$result['ID']] : array();
                    $zp[$result['ID']]['rebErr']   = (isset($arErrReb[$result['ID']])) ? $arErrReb[$result['ID']] : array();
                    
                    $zp[$result['ID']]['scheduleErr'] = ($result['SCHEDULE_ID'] > 0) ? 0 : 1;
                    #$zp[$result['ID']]['get_pay'] = $result['UF_GET_PAY'];
                    $zp[$result['ID']]['fuel']    = 0;
                    $zp[$result['ID']]['sdel']    = $result['sdel'];
                    $zp[$result['ID']]['time']    = 0;
                    $zp[$result['ID']]['balance'] = 0;
                    #$zp[$result['ID']]['manager'] = 0;
                    $zp[$result['ID']]['workFOT'] = 0;
                    $zp[$result['ID']]['workFOTMore'] = array();
                    $zp[$result['ID']]['register'] = $result['DATE_REGISTER'];

                    if($zp[$result['ID']]['isFirstMonth']) {
                        $salaryCurrent = $result[POLZOVATELI_MINIMALNAYA_ZP] / $zp[$result['ID']]['normWorkDay'] * $zp[$result['ID']]['normDayFirstMonth']; // Разраб
                        $zp[$result['ID']]['min'] = $salaryCurrent;                        
                    } else {
                        $zp[$result['ID']]['min']  = $result[POLZOVATELI_MINIMALNAYA_ZP];
                    }
                    
                #}
            }
            
            #   Норма часов
            foreach($ids as $k => $v)
            {
                $n = getNormTime($db, $k);
                
                if($n['showInfo'] == 1)
                    $zp[$k]['time'] = 1;
                
                if(isset($n['currH']) && $n['workH'] < $n['currH'])#($n['schedule_id'] == 14 || $n['schedule_id'] == 15) &&
                {
                    $zp[$k]['workH'] = $n['workH'];
                    $zp[$k]['currH'] = $n['currH'];
                    $zp[$k]['workD'] = $n['workD'];
                    
                }

                ########### смена ###########

                if(isset($n['currH']))#($n['schedule_id'] == 14 || $n['schedule_id'] == 15) &&
                {
                    $zp[$k]['workH'] = $n['workH'];
                    $zp[$k]['currH'] = $n['currH'];
                    $zp[$k]['workD'] = $n['workD'];
                }

                ##################################
            }

            #   Зарплата
            $sql = $db->query("SELECT `p1`.`VALUE` AS `ID`, `p2`.`VALUE_NUM` AS `sum`, `p3`.`VALUE` AS `date`, `p4`.`VALUE_NUM` AS `prem`, `p5`.`VALUE` AS `manager`, 
                                      `p6`.`VALUE_NUM` AS `pprem`, `p7`.`VALUE_NUM` AS `aprem`, `p8`.`VALUE` AS `head`, `p9`.`VALUE` AS `dateO`, `p10`.`VALUE` AS `dateP`,
                                      `p11`.`VALUE_NUM` AS `app_sum`
                               FROM `b_iblock_element` AS `e`
                               LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA."
                               LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_SUMMA."
                               LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA."
                               LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_PREMIYA."
                               LEFT JOIN `b_iblock_element_property` AS `p5` ON `p5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p5`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA."
                               LEFT JOIN `b_iblock_element_property` AS `p6` ON `p6`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p6`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_PREDVARITELNAYA_PREMIYA."
                               LEFT JOIN `b_iblock_element_property` AS `p7` ON `p7`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p7`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_PREMIYA_PODTVERJDENA."
                               LEFT JOIN `b_iblock_element_property` AS `p8` ON `p8`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p8`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY."
                               LEFT JOIN `b_iblock_element_property` AS `p9` ON `p9`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p9`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_OKLADA."
                               LEFT JOIN `b_iblock_element_property` AS `p10` ON `p10`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p10`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_PREMII."
                               LEFT JOIN `b_iblock_element_property` AS `p11` ON `p11`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p11`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_OKLAD."
                               WHERE `e`.`IBLOCK_ID` = ".IB_ZARABOTNAYA_PLATA." AND `p1`.`VALUE` IN(".implode(',', array_keys($ids)).")");
            if($sql->num_rows > 0)
            {
                $start = date('Y-m-d', strtotime('first day of -6 month'));
                while($result = $sql->fetch_assoc())
                {
                    $idsZP[$result['ID']] = $result['ID'];
                    if($result['manager'] != IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER
                         && $result['manager'] != IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR
                          && $result['manager'] != IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA)
                    {
                        if($result['sum'] != 0)
                            $zp[$result['ID']]['balance'] += $result['sum'] * -1;

                        if($result['prem'] != 0)
                            $zp[$result['ID']]['balance'] += $result['prem'] * -1;
                    }

                    if($start <= $result['date'])
                    {
                        $date = date('Y-m', strtotime($result['date']));

                        $zp[$result['ID']]['list'][$date] = $result['sum'] + $result['prem'];
                        $zp[$result['ID']]['list2'][$date]['manager'] = $result['manager'];
                        $zp[$result['ID']]['list2'][$date]['sum']   = $result['sum'];
                        $zp[$result['ID']]['list2'][$date]['psum']  = $result['app_sum'];
                        $zp[$result['ID']]['list2'][$date]['prem']  = $result['prem'];
                        $zp[$result['ID']]['list2'][$date]['pprem'] = $result['pprem'];
                        $zp[$result['ID']]['list2'][$date]['aprem'] = (int)$result['aprem'];
                        $zp[$result['ID']]['list2'][$date]['head']  = (int)$result['head'];
                        $zp[$result['ID']]['list2'][$date]['dateO'] = $result['dateO'];
                        $zp[$result['ID']]['list2'][$date]['dateP'] = $result['dateP'];
                        
                        
                        // === 31/07/2024 ===
                    }
                }
            }

            #   Топливо
            if(!empty($idsZP))
            {
                $sql = $db->query("SELECT `p1`.`VALUE` AS `ID`, `p2`.`VALUE` AS `task`, `p3`.`VALUE` AS `zp`, `p4`.`VALUE` AS `comand`, `p5`.`VALUE_NUM` AS `sum`,
                                          `p6`.`VALUE` AS `sal`
                                   FROM `b_iblock_element` AS `e`
                                   LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_DERJATEL_KARTY."
                                   LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_ZADACHA."
                                   LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_ZARPLATA."
                                   LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_ID_KOMANDIROVKI."
                                   LEFT JOIN `b_iblock_element_property` AS `p5` ON `p5`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p5`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_STOIMOST."
                                   LEFT JOIN `b_iblock_element_property` AS `p6` ON `p6`.`IBLOCK_ELEMENT_ID` = `p3`.`VALUE` AND `p6`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA." AND `p6`.`VALUE` = `p1`.`VALUE`
                                   WHERE `e`.`IBLOCK_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE." AND `p3`.`VALUE` IN(".implode(',', array_keys($idsZP)).")");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        if((int)$result['task'] == 0 && (int)$result['zp'] == 0 && (int)$result['comand'] == 0)
                        {
                            $zp[$result['ID']]['fuel'] = 1;
                        }

                        if($result['zp'] > 0 && $result['sal'] > 0)
                        {
                            $zp[$result['ID']]['balance'] += $result['sum'];
                        }
                    }
                }
            }

            #   Переводы
            $sql = $db->query("SELECT `p1`.`VALUE` AS `ID`, `p2`.`VALUE_NUM` AS `sum`
                               FROM `b_iblock_element` AS `e`
                               LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_PEREVODY_POLUCHATEL."
                               LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_PEREVODY_SUMMA."
                               WHERE `e`.`IBLOCK_ID` = ".IB_PEREVODY." AND `p1`.`VALUE` IN(".implode(',', array_keys($ids)).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $zp[$result['ID']]['balance'] += $result['sum'];
                }
            }

            $sql = $db->query("SELECT `p1`.`VALUE` AS `ID`, `p2`.`VALUE_NUM` AS `sum`
                               FROM `b_iblock_element` AS `e`
                               LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_PEREVODY_OTPRAVITEL."
                               LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_PEREVODY_SUMMA."
                               WHERE `e`.`IBLOCK_ID` = ".IB_PEREVODY." AND `p1`.`VALUE` IN(".implode(',', array_keys($ids)).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $zp[$result['ID']]['balance'] += $result['sum'] * -1;
                }
            }

            #   Задачи
            $sql = $db->query("SELECT `t`.`RESPONSIBLE_ID`, `t1`.`".ZADACHI_FOT_OTVETSTVENNOGO."`, `t`.`DEADLINE`
                               FROM `b_tasks` AS `t`
                               LEFT JOIN `b_uts_tasks_task` AS `t1` ON `t1`.`VALUE_ID` = `t`.`ID`
                               WHERE `t1`.`".ZADACHI_DATA_PERVOGO_ZAKRYTIYA."` > 0 AND `t1`.`".ZADACHI_FOT_OTVETSTVENNOGO."` != 0 AND `t`.`RESPONSIBLE_ID` IN(".implode(',', array_keys($ids)).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $zp[$result['RESPONSIBLE_ID']]['balance'] += $result[ZADACHI_FOT_OTVETSTVENNOGO];
                }
            }

            #   ФОТ в работе
            $arStatus = array(TASK_STATUS_WAITING_RUNNING => 'Ждет выполнения', TASK_STATUS_RUNNING => 'Выполняется', TASK_STATUS_WAITING_CONTROL => 'Ждет контроля', 
                TASK_STATUS_CLOSE => 'Завершена', TASK_STATUS_POSTPONED => 'Отложена');

            $sql = $db->query("SELECT `t`.`ID`, `t`.`RESPONSIBLE_ID`, `t1`.`".ZADACHI_FOT_OTVETSTVENNOGO."`, `t`.`TITLE`, `t`.`DEADLINE`, `t`.`STATUS`
                               FROM `b_tasks` AS `t`
                               LEFT JOIN `b_uts_tasks_task` AS `t1` ON `t1`.`VALUE_ID` = `t`.`ID`
                               WHERE `t`.`STATUS` != ".TASK_STATUS_WAITING_RUNNING." AND (`t1`.`".ZADACHI_DATA_PERVOGO_ZAKRYTIYA."` = 0 OR `t1`.`".ZADACHI_DATA_PERVOGO_ZAKRYTIYA."` IS NULL) 
                               AND `t1`.`".ZADACHI_FOT_OTVETSTVENNOGO."` != 0 AND `t`.`RESPONSIBLE_ID` IN(".implode(',', array_keys($ids)).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $zp[$result['RESPONSIBLE_ID']]['workFOT'] += $result[ZADACHI_FOT_OTVETSTVENNOGO];
                    $zp[$result['RESPONSIBLE_ID']]['workFOTMore'][$result['ID']]['title'] = $result['TITLE'];
                    $zp[$result['RESPONSIBLE_ID']]['workFOTMore'][$result['ID']]['fot'] = $result[ZADACHI_FOT_OTVETSTVENNOGO];
                    $zp[$result['RESPONSIBLE_ID']]['workFOTMore'][$result['ID']]['status'] = (!empty($result['DEADLINE'])) ? '('.$arStatus[$result['STATUS']].' до '.date('d.m.Y', strtotime($result['DEADLINE'])).')' : '(Без даты завершения)';
                }
            }
        }
    }

    return $zp;
}

function getMonth($m)
{
    switch ($m)
    {
        case 1:
            return 'Январь';
            break;
        case 2:
            return 'Февраль';
            break;
        case 3:
            return 'Март';
            break;
        case 4:
            return 'Апрель';
            break;
        case 5:
            return 'Май';
            break;
        case 6:
            return 'Июнь';
            break;
        case 7:
            return 'Июль';
            break;
        case 8:
            return 'Август';
            break;
        case 9:
            return 'Сентябрь';
            break;
        case 10:
            return 'Октябрь';
            break;
        case 11:
            return 'Ноябрь';
            break;
        case 12:
            return 'Декабрь';
            break;
    }
}

require_once './crest/crest.php';
$file = json_decode(file_get_contents('./crest/settings.json'));
if(!empty($file->expires) && $file->expires <= time())
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
        
        if(!empty($result->access_token))
        {
            $file->access_token  = $result->access_token;
            $file->expires       = $result->expires;
            $file->refresh_token = $result->refresh_token;
            file_put_contents('./crest/settings.json', json_encode($file));
        }
        
        curl_close($ch);
}
unset($file);

$db = new mysqli(DB_HOST, DB_USER_BD, DB_PASSWORD, DB_NAME);
$db->set_charset('utf8');

if(isset($_REQUEST['AUTH_ID']))
{
    unset($_SESSION['bitAppFot']);
    $_SESSION['bitAppFot'] = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $_REQUEST['DOMAIN'] .'/rest/profile?auth='.$_REQUEST['AUTH_ID']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = json_decode(curl_exec($ch));

    if(isset($result->result->ID))
    {
        // Заполнение сессии!!
        $_SESSION['bitAppFot']['ID']         = (int)$result->result->ID;
        $_SESSION['bitAppFot']['NAME']       = mb_substr($result->result->NAME,0,1,"UTF-8");
        $_SESSION['bitAppFot']['LAST_NAME']  = $result->result->LAST_NAME;
        $_SESSION['bitAppFot']['DEPARTMENT'] = array();
        $_SESSION['bitAppFot']['HEAD']       = array();
        $_SESSION['bitAppFot']['manager']    = 0;
        $_SESSION['bitAppFot']['HEAD_SAVED'] = 0;
        $_SESSION['bitAppFot']['DEP_NAME']   = array();
        $_SESSION['bitAppFot']['DEP_POS']    = 0;
        $_SESSION['bitAppFot']['POS_OKL']    = 0;
        $_SESSION['bitAppFot']['HEAD_UP']    = array();
        $_SESSION['bitAppFot']['S_BALANCE']  = 1;

        //$hideUser = array(680,699,454,378,379,748,564,675,610,16,71);
        $hideUser = array();
        if(in_array($_SESSION['bitAppFot']['ID'], $hideUser))
            $_SESSION['bitAppFot']['S_BALANCE']  = 0;

        $sql = $db->query("SELECT `uu`.`".POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA."`, `uu`.`".POLZOVATELI_OTOBRAJAT_BALANS."`, `uu`.`".POLZOVATELI_ID_DOLJNOSTI."`, 
        `pp`.`VALUE` AS `".POLZOVATELI_MINIMALNAYA_ZP."` 
                           FROM `b_uts_user` AS `uu`
                           LEFT JOIN `b_iblock_element_property` AS `pp` ON `pp`.`IBLOCK_ELEMENT_ID` = `uu`.`".POLZOVATELI_ID_DOLJNOSTI."` AND `pp`.`IBLOCK_PROPERTY_ID` = ".IB_SHTATNOE_RASPISANIE_OKLAD."
                           WHERE `uu`.`VALUE_ID` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $_SESSION['bitAppFot']['manager'] = $result[POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA];
                $_SESSION['bitAppFot']['DEP_POS'] = $result[POLZOVATELI_ID_DOLJNOSTI];
                $_SESSION['bitAppFot']['POS_OKL'] = $result[POLZOVATELI_MINIMALNAYA_ZP];
                if(($result[POLZOVATELI_OTOBRAJAT_BALANS] == 0))
                    $_SESSION['bitAppFot']['S_BALANCE'] = 0;
            }
        }

        $sql = $db->query("SELECT `VALUE_INT` FROM `b_utm_user` WHERE `VALUE_ID` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $_SESSION['bitAppFot']['DEPARTMENT'][$result['VALUE_INT']] = $result['VALUE_INT'];
            }
        }

        // Developed by WolfHound (06.09.24)
        $sql = $db->query("SELECT `UF_HEAD_SAVED` FROM `b_uts_iblock_3_section` WHERE `UF_HEAD` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($result = $sql->fetch_assoc())
        {
            $_SESSION['bitAppFot']['HEAD_SAVED'] = $result['UF_HEAD_SAVED'];
        }
        // ----- \\
        
        #$_SESSION['bitAppFot']['BALANCE']   = getBalance($db);
        $_SESSION['bitAppFot']['MTR'] = getMTR($db);

        #   Структура и подчинённые
        $sql = $db->query("SELECT `VALUE_ID` FROM `b_uts_iblock_3_section` WHERE `UF_HEAD` = ".(int)$_SESSION['bitAppFot']['ID']);
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $_SESSION['bitAppFot']['HEAD'][$result['VALUE_ID']] = $result['VALUE_ID'];
            }
            
            $sql = $db->query("SELECT `ID` FROM `b_iblock_section` WHERE `IBLOCK_SECTION_ID` IN(".implode(',', $_SESSION['bitAppFot']['HEAD']).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $_SESSION['bitAppFot']['HEAD'][$result['ID']] = $result['ID'];
                }
                
                $sql = $db->query("SELECT `ID` FROM `b_iblock_section` WHERE `IBLOCK_SECTION_ID` IN(".implode(',', $_SESSION['bitAppFot']['HEAD']).")");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $_SESSION['bitAppFot']['HEAD'][$result['ID']] = $result['ID'];
                    }
                }
            }
            
            $tmpSec = array();
            $sql = $db->query("SELECT `s`.`ID`, `s`.`IBLOCK_SECTION_ID`
                               FROM `b_iblock_section` AS `s`
                               LEFT JOIN `b_uts_iblock_3_section` AS `ss` ON `ss`.`VALUE_ID` = `s`.`ID`
                               WHERE (`s`.`IBLOCK_SECTION_ID` = ".IB_SECTION_UNIVEST." OR `s`.`IBLOCK_SECTION_ID` IS NULL) AND `ss`.`UF_HEAD` = ".(int)$_SESSION['bitAppFot']['ID']);
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $tmpSec[$result['ID']] = $result['ID'];
                    $_SESSION['bitAppFot']['HEAD_UP'][$result['ID']] = (int)$result['IBLOCK_SECTION_ID'];
                }
                
                $sql = $db->query("SELECT `ID`, `IBLOCK_SECTION_ID` FROM `b_iblock_section` WHERE `IBLOCK_SECTION_ID` IN(".implode(',', $tmpSec).")");
                if($sql->num_rows > 0)
                {
                    while($result = $sql->fetch_assoc())
                    {
                        $tmpSec[$result['ID']] = $result['ID'];
                    }
                
                    $sql = $db->query("SELECT `ID`, `IBLOCK_SECTION_ID` FROM `b_iblock_section` WHERE `IBLOCK_SECTION_ID` IN(".implode(',', $tmpSec).")");
                    if($sql->num_rows > 0)
                    {
                        while($result = $sql->fetch_assoc())
                        {
                            $_SESSION['bitAppFot']['HEAD_UP'][$result['ID']] = $result['IBLOCK_SECTION_ID'];
                        }
                    }
                }
            }
            
            $sql = $db->query("SELECT `ID`, `NAME` FROM `b_iblock_section` WHERE `ID` IN(".implode(',', $_SESSION['bitAppFot']['HEAD']).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $_SESSION['bitAppFot']['DEP_NAME'][$result['ID']]['dep'] = $result['NAME'];
                }
            }

            # ===== 06/06/2024 начало =====

            # `IBLOCK_PROPERTY_ID` = 273 - Оклад в списке Штатное расписание, `IBLOCK_PROPERTY_ID` = 274 - ИД подразделения в том же списке

            $parentDeparts = array(); // массив с информацией о управляющих подразделениях (которые на 2-ой позиции в иерархии)
            $parentPositions = array(); // массив с должностями из управляющих подразделений
            $parentDepartsID = array(); // массив с ИД родительских подразделений от доступных подразделений

            $currentDeparts = array(); // массив с информацией о доступных подразделениях (ИД, имя, родительское подразделение)
            
            # получение данных по доступным подразделениям
            $generalDepartID = 1;
            $currentDepartsIDArr = $_SESSION['bitAppFot']['HEAD'];

            foreach($currentDepartsIDArr as $departID) {
                $depart = array();

                while(empty($depart) || $depart["IBLOCK_SECTION_ID"] != IB_SECTION_UNIVEST) {
                    
                    $id = empty($depart) ? $departID : $depart['IBLOCK_SECTION_ID']; 
                    $departQ = $db->query("SELECT `ID`, `NAME`, `IBLOCK_SECTION_ID` FROM `b_iblock_section` WHERE `ID` = {$id}");
                    $depart = $departQ->fetch_assoc();
                    if($depart['ID'] == IB_SECTION_UNIVEST) {
                        break;
                    }
                }
                
                if(!empty($depart['ID']) && $depart['ID'] != IB_SECTION_UNIVEST) {
                    $parentDepartsID[$depart['ID']] = $depart['ID'];
                    $parentDeparts[$depart['ID']] = $depart;
                }
                
                
            }
            
            if(!empty($parentDepartsID)) {

                $parentDepartsIDStr = implode(',', $parentDepartsID);
                $parentPositionsQ = $db->query("SELECT `e`.`ID`, `e`.`NAME`, `p1`.`VALUE` AS `OKLAD`, `p2`.`VALUE` AS `PARENT_DEP`
                                                FROM `b_iblock_element` AS `e`
                                                LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_SHTATNOE_RASPISANIE_OKLAD."
                                                LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_SHTATNOE_RASPISANIE_PODRAZDELENIE."
                                                WHERE `e`.`IBLOCK_ID` = ".IB_SHTATNOE_RASPISANIE." AND `p2`.`VALUE` IN(".$parentDepartsIDStr.")
                                                ORDER BY `p1`.`VALUE_NUM` DESC");

                while($parentPositionsRes = $parentPositionsQ->fetch_assoc()) {
                    $parentPositions[$parentPositionsRes['ID']]['PARENT_DEP_NAME'] = $parentDeparts[$parentPositionsRes['PARENT_DEP']]['NAME'];
                    $parentPositions[$parentPositionsRes['ID']]['PARENT_DEP_ID'] = $parentPositionsRes['PARENT_DEP'];
                    $parentPositions[$parentPositionsRes['ID']]['OKLAD'] = $parentPositionsRes['OKLAD'];
                    $parentPositions[$parentPositionsRes['ID']]['NAME'] = $parentPositionsRes['NAME'];
                }

            }
            # ===== 06/06/2024 конец =====

            # ===== 09/07/2024 начало =====
            # Получение информации о Количестве сотрудников на должности для Управляющих подразеделений
            $countPositionEmployees = array();

            foreach($parentPositions as $postitonID => $parentPostiton) {
                $positionName = $parentPostiton["NAME"];
                $countPositionEmployeesQ = "SELECT COUNT(`VALUE_ID`) AS `COUNT` 
                                            FROM b_uts_user 
                                            LEFT JOIN b_user ON `b_user`.`ID` = `b_uts_user`.`VALUE_ID`
                                            WHERE `UF_ID_POSITION` = '$positionID' AND `ACTIVE` = 'Y'";
                $countPositionEmployeesRes = $db->query($countPositionEmployeesQ)->fetch_assoc();
                $countPositionEmployees[$postitonID] = $countPositionEmployeesRes['COUNT'];
                
            }
            # ===== 09/07/2024 конец =====

            $sql = $db->query("SELECT `e`.`ID`, `e`.`NAME`, `p1`.`VALUE` AS `oklad`, `p2`.`VALUE` AS `dep`
                               FROM `b_iblock_element` AS `e`
                               LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_SHTATNOE_RASPISANIE_OKLAD."
                               LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_SHTATNOE_RASPISANIE_PODRAZDELENIE."
                               WHERE `e`.`IBLOCK_ID` = ".IB_SHTATNOE_RASPISANIE." AND `p2`.`VALUE` IN(".implode(',', $_SESSION['bitAppFot']['HEAD']).")
			                   ORDER BY `p1`.`VALUE_NUM` DESC");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    #if($_SESSION['bitAppFot']['DEP_POS'] > 0 && $result['ID'] == $_SESSION['bitAppFot']['DEP_POS'])
                    #{
                    #    $_SESSION['bitAppFot']['POS_OKL'] = $result['oklad'];
                    #}

                    # ===== 09/07/2024 начало =====
                    $positionID = $result["ID"];
                    $countPositionEmployeesQ = "SELECT COUNT(`VALUE_ID`) AS `COUNT` 
                                                FROM b_uts_user 
                                                LEFT JOIN b_user ON `b_user`.`ID` = `b_uts_user`.`VALUE_ID`
                                                WHERE `UF_ID_POSITION` = '$positionID' AND `ACTIVE` = 'Y'";
                    $countPositionEmployeesRes = $db->query($countPositionEmployeesQ)->fetch_assoc();
                    # ===== 09/07/2024 конец =====

                    $_SESSION['bitAppFot']['DEP_SHTAT'][$result['dep']][$result['ID']]['name']  = $result['NAME'];
                    $_SESSION['bitAppFot']['DEP_SHTAT'][$result['dep']][$result['ID']]['oklad'] = $result['oklad'];

                    # ===== 06/06/2024 начало =====
                    $_SESSION['bitAppFot']['DEP_SHTAT'][$result['dep']][$result['ID']]['count'] = $countPositionEmployeesRes['COUNT'];
                    # ===== 06/06/2024 конец =====
                }
            }
        }
    }
    else
        unset($_SESSION['bitAppFot']);
    
    curl_close($ch);
}

//$hideUser = array(680,699,454,378,379,748,564,675,610,16,71);
$hideUser = array();
if(isset($_SESSION['bitAppFot']['ID']) && $_SESSION['bitAppFot']['ID'] > 0)
{
    if(isset($_POST['getMorePay'], $_POST['p']))
    {
        $data = array();
        $date  = substr($_POST['p'], 0, 4).'-'.substr($_POST['p'], -2).'-01';
        $start = date('Y-m-d', strtotime('first day of '.$date));
        $stop  = date('Y-m-d', strtotime('last day of '.$date));
        $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`, `p4`.`VALUE` AS `otv`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_SOTRUDNIK."
                           LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA."
                           LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = ".IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY."
                           WHERE `e`.`IBLOCK_ID` = ".IB_ZARABOTNAYA_PLATA." AND `p3`.`VALUE` = ".(int)$_SESSION['bitAppFot']['ID']." AND `p1`.`VALUE` BETWEEN '".$start."' AND '".$stop."'");
        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                $ids[$result['ID']] = $result['ID'];
                $otv = $result['otv'];
            }

            $sql = $db->query("SELECT `NAME` AS `name`, `LAST_NAME` AS `last_name` FROM `b_user` WHERE `ID` = $otv");
            while($result = $sql->fetch_assoc())
            {
                $fio = $result['name'] . " " .$result['last_name'];
            }
            
            $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`, `p2`.`VALUE_NUM` AS `sum`, `p3`.`VALUE` AS `zp`, `p4`.`VALUE` AS `comm`
                               FROM `b_iblock_element` AS `e`
                               LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_DATA."
                               LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_SUMMA."
                               LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_NACHISLENIE_ZP."
                               LEFT JOIN `b_iblock_element_property` AS `p4` ON `p4`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p4`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_KOMMENTARIY."
                               WHERE `e`.`IBLOCK_ID` = ".IB_PLATEJI." AND `p3`.`VALUE` IN(".implode(',', array_keys($ids)).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!isset($data[$result['date']]))
                        $data[$result['date']]  = 0;

                    $data[$result['date']] += $result['sum'];
                    $comm = $result['comm'];
                    if(isset($comm) && $comm !== ''){
                        $commentfull = $comm;
                    } else {
                        $commentfull = 'Начисление оклада,';
                    }
                }
            }
            
            $sql = $db->query("SELECT `e`.`ID`, `p1`.`VALUE` AS `date`, `p2`.`VALUE_NUM` AS `sum`, `p3`.`VALUE` AS `zp`
                               FROM `b_iblock_element` AS `e`
                               LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_DATA."
                               LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_STOIMOST."
                               LEFT JOIN `b_iblock_element_property` AS `p3` ON `p3`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p3`.`IBLOCK_PROPERTY_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE_ZARPLATA."
                               WHERE `e`.`IBLOCK_ID` = ".IB_TOPLIVO_TS_KORPORATIVNYE." AND `p3`.`VALUE` IN(".implode(',', array_keys($ids)).")");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    if(!isset($data[$result['date']]))
                        $data[$result['date']]  = 0;

                    $data[$result['date']] += $result['sum'];
                }
            }
        }
        
        if(!empty($data))
        {
            krsort($data);
            foreach($data as $d => $v)
            {
                echo date('d.m.Y', strtotime($d)) .': '.number_format($v, 2, '.', ' ')." ".$commentfull." Оператор - ".$fio."\r\n"; //17.07.24
            }
        }
        
        exit;
    }

    if(isset($_POST['addUser']))
    {
        $uLastName   = trim($_POST['APPlastName']);
        $uFirstName  = trim($_POST['APPfirstName']);
        $uSecondName = trim($_POST['APPsecondName']);
        $_POST['APPBday'] = str_replace(',', '.', $_POST['APPBday']);
        $tmp = explode('.', $_POST['APPBday']);
        $uBday   = (isset($tmp[2])) ? $tmp[2].'-'.$tmp[1].'-'.$tmp[0] : '0000-00-00';
        $uMail   = trim($_POST['APPmail']);
        $uPhone  = trim($_POST['APPphone']);
        $uWGraph = trim($_POST['APPwork']);
        $uWSys = trim($_POST['APPSys']);//Добавление
        $uWGend = trim($_POST['APPGend']);//Добавление
        $tmp = explode('|', $_POST['APPdepartment']);
        if(!empty($tmp[1]))
        {
            $uDep = $tmp[1];
        }

        if(!empty($tmp[0]))
        {
            $uPos = $tmp[0];
        }
        $tmp = explode('@', $uMail);
        if(!empty($tmp[1]))
        {
            $uPass = 'U_'.trim($tmp[0]).'!';
        }

        if(!empty($uLastName) && !empty($uFirstName) && !empty($uMail) && !empty($uPhone) && $uPhone > 0 && !empty($uDep) && !empty($uPos))
        {
            $sql = $db->query("SELECT `e`.`NAME`, CONCAT(`u`.`NAME`, ' ', `u`.`LAST_NAME`) AS `head`, `u`.`ID` AS `id`
                               FROM `b_iblock_section` AS `e`
                               LEFT JOIN `b_uts_iblock_3_section` AS `s` ON `s`.`VALUE_ID` = `e`.`ID`
                               LEFT JOIN `b_user` AS `u` ON `u`.`ID` = `s`.`UF_HEAD`
                               WHERE `e`.`IBLOCK_ID` = ".IB_PODRAZDELENIYA." AND `e`.`ID` =".(int)$uDep);
            if($sql->num_rows > 0)
            {
                $result = $sql->fetch_assoc();
                $uDepName = $result['NAME'];
                $uDepHead = $result['head'];
                $uIDres = $result['id'];
            }

            $sql = $db->query("SELECT `e`.`NAME`, `p1`.`VALUE` AS `oklad`
                               FROM `b_iblock_element` AS `e`
                               LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_SHTATNOE_RASPISANIE_OKLAD."
                               WHERE `e`.`IBLOCK_ID` = ".IB_SHTATNOE_RASPISANIE." AND `e`.`ID` = ". (int)$uPos);
            if($sql->num_rows > 0)
            {
                $result = $sql->fetch_assoc();
                $uPosName = $result['NAME'];
                $uOklad   = $result['oklad'];
            }

            
            $arFields = array(
                'NAME'              => $uFirstName,
                'LAST_NAME'         => $uLastName,
                'SECOND_NAME'       => $uSecondName,
                'EMAIL'             => $uMail,
                'LOGIN'             => $uMail,
                'LID'               => 'ru',
                'ACTIVE'            => 'Y',
                'WORK_POSITION'     => $uPosName,
                'PERSONAL_PHONE'    => '', // Убрать .$uPhone 01.08.24
                'PERSONAL_MOBILE'    => '+7'.$uPhone,//Добавление
                'WORK_PHONE'        => '', // Убрать .$uPhone 01.08.24
                'PERSONAL_BIRTHDAY' => $uBday,
                'GROUP_ID'          => array(GROUPS_WORKERS),
                 POLZOVATELI_PODRAZDELENIYA    => $uDep,
                 POLZOVATELI_ID_DOLJNOSTI   => $uPos, 
                 POLZOVATELI_MINIMALNAYA_ZP        => $uOklad,
                 POLZOVATELI_DATA_NACHALA_RABOTY => date('d.m.Y'),
                 POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA => $uWSys,//Добавление
                'PERSONAL_GENDER' => $uWGend,//Добавление
                'PASSWORD'          => $uPass,
                'CONFIRM_PASSWORD'  => $uPass,
                'MESSAGE_TEXT'      => 'Ознакомиться с правилами нашей организации можно по ссылке '.BITRIX_DOMAIN.'knowledge/standart_organizatsii/'
            );


            // print_r($uPos);
            // exit;

            $req = CRest::call('user.add', $arFields);
            //echo print_r($arFields, true)." ".print_r($req, true);
            if($req['result'] > 0)
            {
                $obCurl2 = curl_init();
				curl_setopt($obCurl2, CURLOPT_URL, BITRIX_DOMAIN.'?addNewUser=1&usr='.$req['result'].'&graph='.$uWGraph.'&o='.$uOklad.'&code='.md5('!'.$req['result'].'937r2gu(&').'&pos='.$uPos); // === 01/08/2024 ===
				curl_setopt($obCurl2, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($obCurl2, CURLOPT_POSTREDIR, 10);
				curl_setopt($obCurl2, CURLOPT_USERAGENT, 'Bitrix24 CRest PHP');
                curl_setopt($obCurl2, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($obCurl2, CURLOPT_SSL_VERIFYHOST, false);
                $out = curl_exec($obCurl2);
                curl_close($obCurl2);

                //$message = "У нас в коллективе новый сотрудник ".$uFirstName." ".$uLastName.".\nДолжность: ".$uPosName.".\nТелефон: ".'+7'.$uPhone.".\nНепосредственный руководитель: ".$uDepHead."\nПодразделение: ".$uDepName;
                //CRest::call('im.message.add', array('DIALOG_ID' => 'chat1', 'MESSAGE' => $message));
                $message = "У нас в коллективе новый сотрудник ".$uFirstName." ".$uLastName.".\nДолжность: ".$uPosName.".\nТелефон: ".'+7'.$uPhone.".\nНепосредственный руководитель: ".$uDepHead."\nПодразделение: ".$uDepName;
                $res = CRest::call('lists.element.add', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => IB_BOT, 'MESSAGE' => $message,'ELEMENT_CODE' => time(),
                'FIELDS' => array(
                    'NAME' => "Сообщение_От",
                    'PROPERTY_'.IB_BOT_ID_USER => $_REQUEST['data']['PARAMS']["FROM_USER_ID"],
                    'PROPERTY_'.IB_BOT_ID_COMMAND => 76,
                    'PROPERTY_'.IB_BOT_ID_DIALOG => $_REQUEST['data']['PARAMS']["DIALOG_ID"],
                    'PROPERTY_'.IB_BOT_PARAMS => $message,
                    'PROPERTY_'.IB_BOT_CRM => $uIDres,
                    )));
                echo 1;
            }
            else
                echo 'Что-то пошло не так';
        }
        else
            echo 'Проверьте введенные данные';
        
        exit;
    }
    
    if(isset($_POST['getUser']))
    {
        $out['month'] = '';
        $out['year']  = '';
        $out['user']  = '';
        $out['sdel']  = 0;
        $out['oklad'] = 0;

        $m = array();
        $sql = $db->query("SELECT CONCAT(`u`.`LAST_NAME`, ' ', `u`.`NAME`) AS `user`, DATE_FORMAT(`u`.`DATE_REGISTER`, '%Y-%m-01') AS `date`,
                                  `uu`.`".POLZOVATELI_SDELNAYA_OPLATA."` AS `sdel`, `pp`.`VALUE` AS `".POLZOVATELI_MINIMALNAYA_ZP."`
                           FROM `b_user` AS `u`
                           LEFT JOIN `b_uts_user` AS `uu` ON `uu`.`VALUE_ID` = `u`.`ID`
                           LEFT JOIN `b_iblock_element_property` AS `pp` ON `pp`.`IBLOCK_ELEMENT_ID` = `uu`.`".POLZOVATELI_ID_DOLJNOSTI."` AND `pp`.`IBLOCK_PROPERTY_ID` = ".IB_SHTATNOE_RASPISANIE_OKLAD."
                           WHERE `u`.`ID` = ".(int)$_POST['getUser']);
        if($sql->num_rows > 0)
        {
            $result = $sql->fetch_assoc();
            $out['user']  = $result['user'];
            $out['sdel']  = $result['sdel'];
            $out['oklad'] = $result[POLZOVATELI_MINIMALNAYA_ZP];

            $begin = new DateTime($result['date']);
            $end = new DateTime(date('Y-m-01'));
            $end = $end->modify('+1 month');

            $interval  = new DateInterval('P1D');
            $daterange = new DatePeriod($begin, $interval ,$end);

            foreach($daterange as $date)
            {
                $m[$date->format("n")] = $date->format("m");
            }
            
            for($i = 1; $i <= 12; $i++)
            {
                if(isset($m[$i]))
                {
                    if(date('n') == $i)
                        $out['month'] .= '<option value="'.$m[$i].'" selected="selected">'.getMonth($i).'</option>';
                    else
                        $out['month'] .= '<option value="'.$m[$i].'">'.getMonth($i).'</option>';
                }
            }
            
            $y = range(date('Y', strtotime($result['date'])), date('Y'));
            foreach($y as $v)
            {
                if(date('Y') == $v)
                    $out['year'] .= '<option value="'.$v.'" selected="selected">'.$v.'</option>';
                else
                    $out['year'] .= '<option value="'.$v.'">'.$v.'</option>';
            }
        }
        
        echo json_encode($out);
        exit;
    }
    
    if(isset($_POST['getBalance']))
    {
        if(!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) && $_SESSION['bitAppFot']['S_BALANCE'] == 1)
            echo getBalance($db);
        
        exit;
    }

    if(isset($_POST['setSdel'], $_POST['id'], $_POST['period']) && $_POST['id'] > 0 && $_POST['period'] > 0 && $_POST['sum'] > 0)
    {
        $out['status'] = 0;
        $out['msg'] = 'Не начислено!';
        $out['dt'] = str_replace('-', '', $_POST['period']);
        
        if(!empty($_SESSION['bitAppFot']['HEAD']))// && (!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93)
        {
            $zp = getZPlist($db);

            if(isset($zp[$_POST['id']]) && $zp[$_POST['id']]['sdel'] == 1 && $zp[$_POST['id']]['register'] <= $_POST['period'])
            {
                $backMonth = date('Y-m', strtotime('first day of -1 month', strtotime($_POST['period'].'-01')));
                $startWork = $zp[$_POST['id']]['register'];

                if(!empty($zp[$_POST['id']]['taskErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются просроченные задачи:[BR]';
                    foreach($zp[$_POST['id']]['taskErr'] as $id_task => $task_title)
                    {
                        $msg1 .= '[URL=/company/personal/user/'.(int)$_POST['id'].'/tasks/task/view/'.(int)$id_task.'/]'.htmlspecialchars($task_title).'[/URL][BR]';
                    }
                    $msg1 .= 'Вам нужно завершить данные задачи или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['dealErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются просроченные сделки:[BR]';
                    foreach($zp[$_POST['id']]['dealErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/deal/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL][BR]';
                    }
                    $msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['avrErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются АВР на стадии "Документы не предоставлены":[BR]';
                    foreach($zp[$_POST['id']]['avrErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/type/'.CRM_AVR_ID.'/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL][BR]';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['mtrErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются МТР на инвентаризации:[BR]';
                    foreach($zp[$_POST['id']]['mtrErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/type/'.CRM_MTR_ID.'/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL][BR]';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['crmErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются сделки с незаполненными полями:[BR]';
                    foreach($zp[$_POST['id']]['crmErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/deal/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL][BR]';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['invErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются счета на оплату на стадии "Документы не собраны":[BR]';
                    foreach($zp[$_POST['id']]['invErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/type/128/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL] ';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['invpErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются счета на стадии "Просрочено":[BR]';
                    foreach($zp[$_POST['id']]['invpErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/type/159/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL] ';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif($zp[$_POST['id']]['scheduleErr'] == 1)
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как у вас не установлен рабочий график';
                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                else
                {
                    // === 01/08/2024 ===
                    // if(gettype($_POST['sum']) == "string") {
                    //     $out['status'] = 0;
                    //     $out['msg'] = 'Ошибка. Некорректный тип данных!';
                    //     echo json_encode($out);
                    //     exit;
                    // }
                    // === 01/08/2024 ===
                    // === 31/07/2024 ===
                    if(!isset($zp[$_POST['id']]['list2'][$_POST['period']]))
                    {
                        $arStructure = array(POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR, 
                        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR, 
                        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER, 
                        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA);

                        $sql = $db->query("SELECT `".POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA."` FROM `b_uts_user` WHERE `VALUE_ID` = ".(int)$_POST['id']);
                        $resUser = $sql->fetch_assoc();
                        $manager = (isset($arStructure[$resUser[POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA]])) ? $arStructure[$resUser[POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA]] : '';
                        $wa = ($zp[$_POST['id']]['balance'] + $zp[$_POST['id']]['workFOT'] < 0
                         && $resUser[POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR) ? 1 : 0;

                        #$out['balance'] = ($zp[$_POST['id']]['manager'] == 74) ? '' : number_format(($zp[$_POST['id']]['balance'] - $_POST['sum']), 0, '.', ' ');
                        if($zp[$_POST['id']]['manager'] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER)
                            $out['balance'] =  '';
                        elseif($zp[$_POST['id']]['manager'] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR 
                            || $zp[$_POST['id']]['manager'] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA)
                            $out['balance'] =  $zp[$_POST['id']]['balance'];
                        else
                            $out['balance'] = number_format(($zp[$_POST['id']]['balance'] - $_POST['sum']), 0, '.', ' ');

                        $out['zp'] = number_format($_POST['sum'], 0, '.', ' ');

                        $request = CRest::call('lists.element.add', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => IB_ZARABOTNAYA_PLATA, 'ELEMENT_CODE' => $_POST['id'].$out['dt'],
                            'FIELDS' => array(
                                'NAME' => 'Начисление з/п',
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $_POST['id'],                    #   Сотрудник
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA => ($wa == 1) ? 0 : $_POST['sum'],        #   Сумма
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => date('d.m.Y', strtotime('last day of '.$_POST['period'].'-01')),                   #   Дата начисления
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY => $_SESSION['bitAppFot']['ID'],    #   Ответственный
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA => $zp[$_POST['id']]['balance'],    #   Баланс
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $_POST['id'],                    #   ID Сотрудника
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SOTRUDNIK => $_POST['id'],                    #   ID Сотрудника
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_STRUKTURNOE_PODRAZDELENIE => $zp[$_POST['id']]['dep'],        #   Структурное подразделение
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MINIMALNAYA_VYPLATA => $zp[$_POST['id']]['min'],        #   Минималка
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MAKSIMALNAYA_VYPLATA => $zp[$_POST['id']]['max'],        #   Максималка
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA => 0,         #   Премия
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA => $manager,  #   Менеджер
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREDVARITELNAYA_PREMIYA => 0,         #   Предварительная премия
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA_PODTVERJDENA => 0,         #   Подтверждение премии
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SDELNO => (int)$zp[$_POST['id']]['sdel'],
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OKLAD => ($wa == 1) ? $_POST['sum'] : 0
                            )));
                    
                        if(isset($request['result']))
                        {
                            if($wa == 0)
                            {
                                $out['status'] = 1;
                                CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. начислил Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).' в размере '.$out['zp'].' руб.'));
                            }
                            else
                            {
                                $out['status'] = 2;
                                if(!isset($_SESSION['bitAppFot']['HEAD_UP'][$zp[$_POST['id']]['dep']]) && $_SESSION['bitAppFot']['ID'] != USER_ROVNOV_A)
                                    $out['zp'] = '<strong class="text-danger">'.number_format($_POST['sum'], 2, '.', ' ').'</strong>';
                                elseif(isset($_SESSION['bitAppFot']['HEAD_UP'][$zp[$_POST['id']]['dep']]) || $_SESSION['bitAppFot']['ID'] == USER_ROVNOV_A)
                                    $out['zp'] = '<div id="'.$out['dt'].$_POST['id'].'prem" class="text-center"><strong class="text-danger">'.number_format($_POST['sum'], 2, '.', ' ').'</strong><br><button class="btn btn-sm btn-success pull-left" onclick="approveSum('.$_POST['id'].', '.$out['dt'].', 1)"><span class="fa fa-check"></span></button><button class="btn btn-sm btn-danger pull-right" onclick="approveSum('.$_POST['id'].', '.$out['dt'].', 0)"><span class="fa fa-times"></span></button></div>';
                                else
                                    $out['zp'] = number_format($_POST['sum'], 2, '.', ' ');
                            }
                        }
                    } else {
                        $out['msg'] = 'Ошибка. Оклад уже был начислен!';
                    }
                    // === 31/07/2024 ===
                }
            }
        }

        echo json_encode($out);
        exit;
    }

    if(isset($_POST['modalErrors'], $_POST['id']) && $_POST['id'] > 0)
    {
        $out = array('status' => 0, 'data' => '');
        $zp = getZPlist($db);
        if(!empty($zp[$_POST['id']]['taskErr']))
        {
            $out['data'] .= '<p><strong>Просроченные задачи</strong></p>';
            foreach($zp[$_POST['id']]['taskErr'] as $id_task => $task_title)
            {
                $out['data'] .= '<div><a target="_blank" href="/company/personal/user/'.(int)$_POST['id'].'/tasks/task/view/'.(int)$id_task.'/">'.htmlspecialchars($task_title).'</a></div>';
            }
            $out['data'] .= '<br>';
        }

        if(!empty($zp[$_POST['id']]['dealErr']))
        {
            $out['data'] .= '<p><strong>Просроченные сделки</strong></p>';
            foreach($zp[$_POST['id']]['dealErr'] as $id_deal => $deal_title)
            {
                $out['data'] .= '<div><a target="_blank" href="/crm/deal/details/'.(int)$id_deal.'/">'.htmlspecialchars($deal_title).'</a></div>';
            }
            $out['data'] .= '<br>';
        }

        if(!empty($zp[$_POST['id']]['avrErr']))
        {
            $out['data'] .= '<p><strong>АВР на стадии "Документы не предоставлены"</strong></p>';
            foreach($zp[$_POST['id']]['avrErr'] as $id_deal => $deal_title)
            {
                $out['data'] .= '<div><a target="_blank" href="/crm/type/'.CRM_AVR_ID.'/details/'.(int)$id_deal.'/">'.htmlspecialchars($deal_title).'</a></div>';
            }
            $out['data'] .= '<br>';
        }

        if(!empty($zp[$_POST['id']]['mtrErr']))
        {
            $out['data'] .= '<p><strong>МТР на инвентаризации</strong></p>';
            foreach($zp[$_POST['id']]['mtrErr'] as $id_deal => $deal_title)
            {
                $out['data'] .= '<div><a target="_blank" href="/crm/type/'.CRM_MTR_ID.'/details/'.(int)$id_deal.'/">'.htmlspecialchars($deal_title).'</a></div>';
            }
            $out['data'] .= '<br>';
        }

        if(!empty($zp[$_POST['id']]['invErr']))
        {
            $out['data'] .= '<p><strong>Счета на оплату на стадии "Документы не собраны"</strong></p>';
            foreach($zp[$_POST['id']]['invErr'] as $id_deal => $deal_title)
            {
                $out['data'] .= '<div><a target="_blank" href="/crm/type/128/details/'.(int)$id_deal.'/">'.htmlspecialchars($deal_title).'</a></div>';
            }
            $out['data'] .= '<br>';
        }

        if(!empty($zp[$_POST['id']]['invpErr']))
        {
            $out['data'] .= '<p><strong>Счета на стадии "Просрочено"</strong></p>';
            foreach($zp[$_POST['id']]['invpErr'] as $id_deal => $deal_title)
            {
                $out['data'] .= '<div><a target="_blank" href="/crm/type/159/details/'.(int)$id_deal.'/">'.htmlspecialchars($deal_title).'</a></div>';
            }
            $out['data'] .= '<br>';
        }

        if(!empty($zp[$_POST['id']]['crmErr']))
        {
            $out['data'] .= '<p><strong>Сделки с незаполненными полями</strong></p>';
            foreach($zp[$_POST['id']]['crmErr'] as $id_deal => $deal_title)
            {
                $out['data'] .= '<div><a target="_blank" href="/crm/deal/details/'.(int)$id_deal.'/">'.htmlspecialchars($deal_title).'</a></div>';
            }
            $out['data'] .= '<br>';
        }

        if($zp[$_POST['id']]['scheduleErr'] == 1)
        {
            $out['data'] .= '<p><strong>Не установлен рабочий график</strong></p>';
        }
        
        if(!empty($out['data']))
            $out['status'] = 1;

        echo json_encode($out);
        exit;
    }

    // Начисление оклада
    if(isset($_POST['setZp'], $_POST['id'], $_POST['period']) && $_POST['id'] > 0 && $_POST['period'] > 0)
    {
        $out['status'] = 0;
        $out['msg'] = 'Не начислено!';
        $out['dt'] = str_replace('-', '', $_POST['period']);
        
        if(!empty($_SESSION['bitAppFot']['HEAD']) ) //&& (!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93)
        {
            $zp = getZPlist($db);

            if(isset($zp[$_POST['id']]) && $zp[$_POST['id']]['min'] >= 0 && $zp[$_POST['id']]['register'] <= $_POST['period'])
            {
                $backMonth = date('Y-m', strtotime('first day of -1 month', strtotime($_POST['period'].'-01')));
                $startWork = $zp[$_POST['id']]['register'];

                if(!empty($zp[$_POST['id']]['taskErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются просроченные задачи:[BR]';
                    foreach($zp[$_POST['id']]['taskErr'] as $id_task => $task_title)
                    {
                        $msg1 .= '[URL=/company/personal/user/'.(int)$_POST['id'].'/tasks/task/view/'.(int)$id_task.'/]'.htmlspecialchars($task_title).'[/URL][BR]';
                    }
                    $msg1 .= 'Вам нужно завершить данные задачи или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['dealErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются просроченные сделки:[BR]';
                    foreach($zp[$_POST['id']]['dealErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/deal/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL][BR]';
                    }
                    $msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['avrErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются АВР на стадии "Документы не предоставлены":[BR]';
                    foreach($zp[$_POST['id']]['avrErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/type/'.CRM_AVR_ID.'/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL][BR]';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['mtrErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются МТР на инвентаризации:[BR]';
                    foreach($zp[$_POST['id']]['mtrErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/type/'.CRM_MTR_ID.'/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL][BR]';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['invErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются счета на оплату на стадии "Документы не собраны":[BR]';
                    foreach($zp[$_POST['id']]['invErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/type/128/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL] ';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['invpErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются счета на стадии "Просрочено":[BR]';
                    foreach($zp[$_POST['id']]['invpErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/type/159/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL] ';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif(!empty($zp[$_POST['id']]['crmErr']))
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как имеются сделки с незаполненными полями:[BR]';
                    foreach($zp[$_POST['id']]['crmErr'] as $id_deal => $deal_title)
                    {
                        $msg1 .= '[URL=/crm/deal/details/'.(int)$id_deal.'/]'.htmlspecialchars($deal_title).'[/URL][BR]';
                    }
                    #$msg1 .= 'Вам нужно закрыть данные сделки или передвинуть срок их выполнения';

                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                elseif($zp[$_POST['id']]['scheduleErr'] == 1)
                {
                    $msg1 = $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. не может начислить Вам зарплату за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).', так как у вас не установлен рабочий график';
                    CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $msg1));
                }
                else
                {
                    
                    // === 31/07/2024 ===
                    if($zp[$_POST['id']]['min'] <= 0)
                    {
                        $out['status'] = 0;
                        $out['msg'] = 'Не установлен оклад';
                    }
                    elseif(!isset($zp[$_POST['id']]['list2'][$_POST['period']]))
                    {
                        $arStructure = array(POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR, 
                        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR, 
                        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER, 
                        POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA);

                        $sql = $db->query("SELECT `".POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA."` FROM `b_uts_user` WHERE `VALUE_ID` = ".(int)$_POST['id']);
                        $resUser = $sql->fetch_assoc();
                        #$manager = ($resUser['UF_USR_1691651430895'] == 1) ? 84 : 85;
                        $manager = (isset($arStructure[$resUser[POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA]])) ? $arStructure[$resUser[POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA]] : '';
                        $wa = ($zp[$_POST['id']]['balance'] + $zp[$_POST['id']]['workFOT'] < 0 
                        && $resUser[POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR) ? 1 : 0;
                        
                        if($zp[$_POST['id']]['manager'] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER)
                            $out['balance'] =  '';
                        elseif($zp[$_POST['id']]['manager'] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR
                         || $zp[$_POST['id']]['manager'] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA)
                            $out['balance'] =  $zp[$_POST['id']]['balance'];
                        else
                            $out['balance'] = number_format(($zp[$_POST['id']]['balance'] - $zp[$_POST['id']]['min']), 0, '.', ' ');

                        if($wa == 1)
                        {
                            $sumZp = 0;
                            $sumZp2 = $zp[$_POST['id']]['min'];
                        }
                        else
                        {
                            $sumZp = $zp[$_POST['id']]['min'];
                            $sumZp2 = 0;
                        }

                        if(isset($zp[$_POST['id']]['rebErr'][$_POST['period']]))
                        {
                            $sumZp  = $sumZp * 0.7;
                            $sumZp2 = $sumZp2 * 0.7;
                            $out['zp'] = number_format($zp[$_POST['id']]['min'] * 0.7, 0, '.', ' ');
                        }  else {
                            $out['zp'] = number_format($sumZp, 0, '.', ' ');
                        }
                            

                        $request = CRest::call('lists.element.add', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => IB_ZARABOTNAYA_PLATA, 'ELEMENT_CODE' => $_POST['id'].$out['dt'],
                            'FIELDS' => array(
                                'NAME' => 'Начисление з/п',
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $_POST['id'],                    #   Сотрудник
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA => $sumZp,        #   Сумма
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => date('d.m.Y', strtotime('last day of '.$_POST['period'].'-01')),                   #   Дата начисления
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY => $_SESSION['bitAppFot']['ID'],    #   Ответственный
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA => $zp[$_POST['id']]['balance'],    #   Баланс
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $_POST['id'],                    #   ID Сотрудника
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SOTRUDNIK => $_POST['id'],                    #   ID Сотрудника
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_STRUKTURNOE_PODRAZDELENIE => $zp[$_POST['id']]['dep'],        #   Структурное подразделение
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MINIMALNAYA_VYPLATA => $zp[$_POST['id']]['min'],        #   Минималка
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MAKSIMALNAYA_VYPLATA => $zp[$_POST['id']]['max'],        #   Максималка
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA => 0,         #   Премия
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA => $manager,  #   Менеджер
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREDVARITELNAYA_PREMIYA => 0,         #   Предварительная премия
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA_PODTVERJDENA => 0,         #   Подтверждение премии
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SDELNO => (int)$zp[$_POST['id']]['sdel'],          #   Сдельно
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_OKLADA => date(DATE_ATOM),
                                'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OKLAD => $sumZp2
                            )));

                        
                        
                        if(isset($request['result']))
                        {
                            $out['status'] = 1;
                            if($wa == 0)
                                CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. начислил Вам оклад за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).' в размере '.$out['zp'].' руб.'));
                        }
                    } else {
                        $out['msg'] = 'Ошибка. Оклад уже был начислен!'; // Разраб
                    }
                    // === 31/07/2024 ===
                }
            }
        }
        
        echo json_encode($out);
        exit;
    }

    if(isset($_POST['approveSum']))
    {
        $idUser = $_POST['id'];
        $date   = $_POST['date'];
        $date2  = substr_replace($_POST['date'], '-', 4, 0);
        
        $app    = $_POST['app'];
        $out['status'] = 0;
        $out['zp'] = 0; 
        $oklad   = 0;
        $payOut  = 0;
        $minPrem = 0;

        $zp = getZPlist($db);
        
        //if($idUser > 0 && $date > 0 && !empty($_SESSION['bitAppFot']['HEAD']) && ($_SESSION['bitAppFot']['ID'] == USER_ROVNOV_A))//!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93 || 
        if($idUser > 0 && $date > 0 && (isset($_SESSION['bitAppFot']['HEAD_UP'][$zp[$idUser]['dep']]) || $_SESSION['bitAppFot']['ID'] == USER_ROVNOV_A))
        {
            $zpUser = (isset($zp[$idUser])) ? $zp[$idUser] : array();
            $zpList = array();
            if($app == 1)
            {
               if(!empty($zpUser['list2']))
                {
                    foreach($zpUser['list2'] as $d => $z)
                    {
                        $dd = str_replace('-', '', $d);
                        $zpList[$dd] = $z;
                    }
                }
                
                $request = CRest::call('lists.element.update', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => IB_ZARABOTNAYA_PLATA, 'ELEMENT_CODE' => $idUser.$date,
                                       'FIELDS' => array(
                                        'NAME' => 'Начисление з/п',
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $idUser,                                              #   Сотрудник
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA => $zpList[$date]['psum'],                 #   Сумма
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => date('d.m.Y', strtotime('last day of '.$date2.'-01')),#   Дата начисления
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY => $zpList[$date]['head'],                               #   Ответственный
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA => ($zp[$idUser]['balance']),                            #   Баланс
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $idUser,                                              #   ID Сотрудника
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SOTRUDNIK => $idUser,                                              #   ID Сотрудника
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_STRUKTURNOE_PODRAZDELENIE => $zp[$idUser]['dep'],                                  #   Структурное подразделение
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MINIMALNAYA_VYPLATA => $zp[$idUser]['min'],                                  #   Минималка
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MAKSIMALNAYA_VYPLATA => $zp[$idUser]['max'],                                  #   Максималка
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA => 0,                              #   Премия
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA => $zpList[$date]['manager'], #$zp[$idUser]['manager'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREDVARITELNAYA_PREMIYA => 0,            #   Предварительная премия
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA_PODTVERJDENA => 0,             #   Подтверждение премии
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SDELNO => (int)$zp[$idUser]['sdel'],          #   Сдельно
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_OKLADA => $zpList[$date]['dateO'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_PREMII => $zpList[$date]['dateP'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OKLAD => 0
                                    )));
                if(!empty($request['result']))
                {
                    $out['status'] = 1;
                    $out['zp'] = number_format($zpList[$date]['psum'], 2, '.', ' ');
                    $sql3 = $db->query("SELECT `LAST_NAME`, `NAME`, `PERSONAL_GENDER` FROM `b_user` WHERE `ID` = ".(int)$zpList[$date]['head']);
                    if($sql3->num_rows > 0)
                    {
                        $result3 = $sql3->fetch_assoc();
                        $msg = ($result3['PERSONAL_GENDER'] == 'F') ? 'начислила' : 'начислил';
                        CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $result3['LAST_NAME'] .' '. $result3['NAME'].' '.$msg.' Вам зарплату за '.getMonth(date('m', strtotime($date2.'-01'))).' '.date('Y', strtotime($date2.'-01')).' в размере '.$out['zp'].' руб.'));
                    }
                }
            }
            else
            {
                $request = CRest::call('lists.element.delete', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => IB_ZARABOTNAYA_PLATA, 'ELEMENT_CODE' => $idUser.$date));
                if(!empty($request['result']))
                {
                    $out['status'] = 1;                          
                    $out['zp'] = '<div>'.number_format($zp[$idUser]['min'], 2, '.', ' ').'</div>';
                }
            }
        }
        
        echo json_encode($out);
        exit;
    }

    if(isset($_POST['approvePrem']))
    {
        $idUser = $_POST['id'];
        $date   = $_POST['date'];
        $date2  = substr_replace($_POST['date'], '-', 4, 0);
        
        $app    = $_POST['app'];
        $out['status'] = 0;
        $out['zp'] = 0;
        $oklad   = 0;
        $payOut  = 0;
        $minPrem = 0;
        
        if($idUser > 0 && $date > 0 && !empty($_SESSION['bitAppFot']['HEAD']))// && (!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93)
        {
            $zp = getZPlist($db);
            $zpUser = (isset($zp[$idUser])) ? $zp[$idUser] : array();
            $zpList = array();
            
            if(!empty($zpUser['list2']))
            {
                foreach($zpUser['list2'] as $d => $z)
                {
                    $dd = str_replace('-', '', $d);
                    $zpList[$dd] = $z;
                }

                $sql = $db->query("SELECT SUM(`p1`.`VALUE`) AS `sum`
                                                   FROM `b_iblock_element` AS `e`
                                                   LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_SUMMA."
                                                   LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_NACHISLENIE_ZP."
                                                   WHERE `e`.`IBLOCK_ID` = ".IB_PLATEJI." AND `p2`.`VALUE` IN(SELECT `ID` FROM `b_iblock_element` WHERE `IBLOCK_ID` = ".IB_ZARABOTNAYA_PLATA." AND `CODE` = '".$db->real_escape_string($idUser.$date)."')");
                if($sql->num_rows > 0)
                {
                    $res = $sql->fetch_assoc();
                    $payOut = $res['sum'];
                }

                $oklad   = $zp[$idUser]['list2'][$date2]['sum'];
                $minPrem = ($payOut + $oklad < 0) ? abs($payOut + $oklad) : 0;

                if($app == 1)
                {
                    if(isset($zpList[$date]['pprem']) && $zpList[$date]['pprem'] > 0 && $zpList[$date]['aprem'] == 0)
                    {
                        if($minPrem > $zpList[$date]['pprem'])
                        {
                            $out['status'] = 0;
                            $out['msg']    = 'Уже выплачено '.number_format($minPrem, 0, '.', ' ').' руб. Премия не может быть меньше этой суммы';
                            echo json_encode($out);
                            exit;
                        }
                        $monthOklad = $_POST['date'];
                        $request = CRest::call('lists.element.update', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => IB_ZARABOTNAYA_PLATA, 'ELEMENT_CODE' => $idUser.$date,
                                    'FIELDS' => array(
                                        'NAME' => 'Начисление з/п',
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $idUser,                                              #   Сотрудник
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA => $zp[$idUser]['list2'][$date2]['sum'],                 #   Сумма
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => date('d.m.Y', strtotime('last day of '.$date2.'-01')),#   Дата начисления
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY => $zpList[$date]['head'],                               #   Ответственный
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA => ($zp[$idUser]['balance']),                            #   Баланс
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $idUser,                                              #   ID Сотрудника
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SOTRUDNIK => $idUser,                                              #   ID Сотрудника
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_STRUKTURNOE_PODRAZDELENIE => $zp[$idUser]['dep'],                                  #   Структурное подразделение
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MINIMALNAYA_VYPLATA => $zp[$idUser]['min'],                                  #   Минималка
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MAKSIMALNAYA_VYPLATA => $zp[$idUser]['max'],                                  #   Максималка
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA => $zpList[$date]['pprem'],                              #   Премия
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA => $zp[$idUser]['list2'][$date2]['manager'], #$zp[$idUser]['manager'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREDVARITELNAYA_PREMIYA => 0,            #   Предварительная премия
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA_PODTVERJDENA => 1,             #   Подтверждение премии
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SDELNO => (int)$zp[$idUser]['sdel'],          #   Сдельно 
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_OKLADA => $zp[$_POST['id']]['list2'][$date2]['dateO'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_PREMII => $zp[$_POST['id']]['list2'][$date2]['dateP'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OKLAD => $zp[$_POST['id']]['list2'][$_POST['period']]['psum']
                                    )));
                        if(!empty($request['result']))  
                        {
                            $out['status'] = 1;
                            $out['zp'] = '<div>'.number_format($zpList[$date]['sum'] + $zpList[$date]['pprem'], 2, '.', ' ').'</div>';
                            /*sleep(1);
                            $sql3 = $db->query("SELECT `LAST_NAME`, `NAME`, `PERSONAL_GENDER` FROM `b_user` WHERE `ID` = ".(int)$zpList[$date]['head']);
                            if($sql3->num_rows > 0)
                            {
                                $result3 = $sql3->fetch_assoc();
                                $msg = ($result3['PERSONAL_GENDER'] == 'F') ? 'начислила' : 'начислил';
                                #CRest::call('im.notify.personal.add', array('USER_ID' => $idUser, 'MESSAGE' => $result3['LAST_NAME'] .' '. $result3['NAME'].' '.$msg.' Вам премию за '.getMonth(date('m', strtotime($date2.'-01'))).' '.date('Y', strtotime($date2.'-01')).' в размере '.number_format($zpList[$date]['pprem'], 2, '.', ' ').' руб.[BR]Большое спасибо за работу.'));
                            }*/
                        }
                    }
                }
                else
                {
                    if(isset($zpList[$date]['pprem']) && $zpList[$date]['pprem'] > 0 && $zpList[$date]['aprem'] == 0)
                    {
                        $request = CRest::call('lists.element.update', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => IB_ZARABOTNAYA_PLATA, 'ELEMENT_CODE' => $idUser.$date,
                                    'FIELDS' => array(
                                        'NAME' => 'Начисление з/п',
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $idUser,                    #   Сотрудник
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA => $zp[$idUser]['list2'][$date2]['sum'],        #   Сумма
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => date('d.m.Y', strtotime('last day of '.$date2.'-01')),                   #   Дата начисления
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY => $zpList[$date]['head'],    #   Ответственный
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA => ($zp[$idUser]['balance']),    #   Баланс
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $idUser,                    #   ID Сотрудника
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SOTRUDNIK => $idUser,                    #   ID Сотрудника
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_STRUKTURNOE_PODRAZDELENIE => $zp[$idUser]['dep'],        #   Структурное подразделение
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MINIMALNAYA_VYPLATA => $zp[$idUser]['min'],        #   Минималка
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MAKSIMALNAYA_VYPLATA => $zp[$idUser]['max'],        #   Максималка
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA => 0,            #   Премия
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA => $zp[$idUser]['list2'][$date2]['manager'], #$zp[$idUser]['manager'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREDVARITELNAYA_PREMIYA => 0,            #   Предварительная премия
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA_PODTVERJDENA => 0,             #   Подтверждение премии
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SDELNO => (int)$zp[$idUser]['sdel'],          #   Сдельно
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_OKLADA => $zp[$_POST['id']]['list2'][$date2]['dateO'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_PREMII => $zp[$_POST['id']]['list2'][$date2]['dateP'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OKLAD => $zp[$_POST['id']]['list2'][$_POST['period']]['psum']
                                    )));
                        if(!empty($request['result']))
                        {
                            $out['status'] = 1;
                            $out['zp'] = '<div>'.number_format($zpList[$date]['sum'], 2, '.', ' ').'</div>';
                        }
                    }
                }
            }
        }
        
        echo json_encode($out);
        exit;
    }

    if(isset($_POST['setPrem'], $_POST['id'], $_POST['period'], $_POST['sum']) && $_POST['id'] > 0 && $_POST['period'] > 0 && $_POST['sum'] > 0)
    {
        $out['status'] = 0;
        $out['msg']  = 'Не начислено!';
        $out['dt']   = str_replace('-', '', $_POST['period']);
        $out['prem'] = 0;

        if(!empty($_SESSION['bitAppFot']['HEAD']))// && (!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93)
        {
            $zp = getZPlist($db);

            if(isset($zp[$_POST['id']]) && $zp[$_POST['id']]['min'] >= 0 && $zp[$_POST['id']]['register'] <= $_POST['period'])
            {
                $backMonth = date('Y-m', strtotime('first day of -1 month', strtotime($_POST['period'].'-01')));
                $startWork = $zp[$_POST['id']]['register'];

                /*
                if($backMonth >= $startWork && !isset($zp[$_POST['id']]['list'][$backMonth]))
                {
                    $out['msg'] = 'Нет начисления за предыдущий месяц!';
                }
                else
                {*/
                    if(isset($zp[$_POST['id']]['list2'][$_POST['period']]['sum']) && $zp[$_POST['id']]['list2'][$_POST['period']]['sum'] > 0)
                    {
                            $prem = (float)$_POST['sum'];
                            $minPrem = 0;
                            $payOut  = 0;

                            if($prem > 0)
                            {
                                $sql = $db->query("SELECT SUM(`p1`.`VALUE`) AS `sum`
                                                   FROM `b_iblock_element` AS `e`
                                                   LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_SUMMA."
                                                   LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_PLATEJI_NACHISLENIE_ZP."
                                                   WHERE `e`.`IBLOCK_ID` = ".IB_PLATEJI." AND `p2`.`VALUE` IN(SELECT `ID` FROM `b_iblock_element` WHERE `IBLOCK_ID` = ".IB_ZARABOTNAYA_PLATA." AND `CODE` = '".$db->real_escape_string($_POST['id'].$out['dt'])."')");
                                if($sql->num_rows > 0)
                                {
                                    $res = $sql->fetch_assoc();
                                    $payOut = $res['sum'];
                                }

                                $oklad   = $zp[$_POST['id']]['list2'][$_POST['period']]['sum'];
                                $minPrem = ($payOut + $oklad < 0) ? abs($payOut + $oklad) : 0;

                                if($minPrem > $prem)
                                {
                                    $out['status'] = 0;
                                    $out['msg'] = 'Уже выплачено '.number_format($minPrem, 0, '.', ' ').' руб. Премия не может быть меньше этой суммы';
                                    echo json_encode($out);
                                    exit;
                                }

                                $out['balance'] = ($zp[$_POST['id']]['manager'] == 74) ? '' : number_format(($zp[$_POST['id']]['balance']), 0, '.', ' ');
                                $out['zp'] = $zp[$_POST['id']]['list2'][$_POST['period']]['sum'];

                                $arStructure = array(POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR, 
                                POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR, 
                                POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER, 
                                POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA => IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA);

                                $manager = (isset($arStructure[$zp[$_POST['id']]['manager']])) ? $arStructure[$zp[$_POST['id']]['manager']] : '';

                                $request = CRest::call('lists.element.update', array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => IB_ZARABOTNAYA_PLATA, 'ELEMENT_CODE' => $_POST['id'].$out['dt'],
                                    'FIELDS' => array(
                                        'NAME' => 'Начисление з/п',
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $_POST['id'],                    #   Сотрудник
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA => $zp[$_POST['id']]['list2'][$_POST['period']]['sum'],        #   Сумма
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => date('d.m.Y', strtotime('last day of '.$_POST['period'].'-01')),                   #   Дата начисления
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY => $_SESSION['bitAppFot']['ID'],    #   Ответственный
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA => ($zp[$_POST['id']]['balance']),    #   Баланс
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $_POST['id'],                    #   ID Сотрудника
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SOTRUDNIK => $_POST['id'],                    #   ID Сотрудника
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_STRUKTURNOE_PODRAZDELENIE => $zp[$_POST['id']]['dep'],        #   Структурное подразделение
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MINIMALNAYA_VYPLATA => $zp[$_POST['id']]['min'],        #   Минималка
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_MAKSIMALNAYA_VYPLATA => $zp[$_POST['id']]['max'],        #   Максималка
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA => 0,            #   Премия
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA => $zp[$_POST['id']]['list2'][$_POST['period']]['manager'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREDVARITELNAYA_PREMIYA => $prem,        #   Предварительная премия
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA_PODTVERJDENA => 0,             #   Подтверждение премии
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SDELNO => (int)$zp[$_POST['id']]['sdel'],          #   Сдельно
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_OKLADA => $zp[$_POST['id']]['list2'][$_POST['period']]['dateO'],
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_PREMII => date(DATE_ATOM),
                                        'PROPERTY_'.IB_ZARABOTNAYA_PLATA_OKLAD => $zp[$_POST['id']]['list2'][$_POST['period']]['psum']
                                    )));
                                if(!empty($request['result']))
                                {
                                    $out['status'] = 1;
                                    $out['prem'] = '';
                                    
                                    if(!isset($_SESSION['bitAppFot']['HEAD_UP'][$zp[$_POST['id']]['dep']]))
                                        $out['zp'] = number_format($zp[$_POST['id']]['list2'][$_POST['period']]['sum'], 2, '.', ' ').'<br><strong class="text-danger">'.number_format($prem, 2, '.', ' ').'</strong>';
                                    elseif(isset($_SESSION['bitAppFot']['HEAD_UP'][$zp[$_POST['id']]['dep']]))
                                        $out['zp'] = number_format($zp[$_POST['id']]['list2'][$_POST['period']]['sum'], 2, '.', ' ').'<div id="'.$out['dt'].$_POST['id'].'prem" class="text-center"><strong class="text-danger">'.number_format($prem, 2, '.', ' ').'</strong><br><button class="btn btn-sm btn-success pull-left" onclick="approvePrem('.$_POST['id'].', '.$out['dt'].', 1)"><span class="fa fa-check"></span></button><button class="btn btn-sm btn-danger pull-right" onclick="approvePrem('.$_POST['id'].', '.$out['dt'].', 0)"><span class="fa fa-times"></span></button></div>';
                                    else
                                        $out['zp'] = number_format($zp[$_POST['id']]['list2'][$_POST['period']]['sum'], 2, '.', ' ');
                                    
                                    #CRest::call('im.notify.personal.add', array('USER_ID' => $_POST['id'], 'MESSAGE' => $_SESSION['bitAppFot']['LAST_NAME'] .' '. $_SESSION['bitAppFot']['NAME'].'. начислил Вам премию за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01')).' в размере '.$prem.' руб.'));
                                    #sleep(1);
                                    #CRest::call('im.notify.personal.add', array('USER_ID' => $_SESSION['bitAppFot']['ID'], 'MESSAGE' => 'Сотруднику '.$zp[$_POST['id']]['user'].' начислена премия '.$out['zp'].' руб. за '.getMonth(date('m', strtotime($_POST['period'].'-01'))).' '.date('Y', strtotime($_POST['period'].'-01'))));
                                }
                            }
                    }
                    else
                    {
                        $out['status'] = 0;
                        $out['msg'] = 'Не начислен оклад';
                    }
                #}
            }
        }
        
        echo json_encode($out);
        exit;
    }

    if(isset($_POST['getZpList']))
    {
        $zpOut = '';
        if(!empty($_SESSION['bitAppFot']['HEAD'])) // && (!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93)
        {
            $zp = getZpList($db);
            $zp2 = array();
            if(!empty($zp))
            {
                foreach($zp as $idU => $du)
                {
                    $zp2[$du['dep']][$idU] = $idU;
                }

                $zpOut .= '<table class="table table-sm table-hover"><thead><tr><th>Сотрудник</th>';
                for($i = 6; $i >= 0; $i--)
                {
                    $m = date('n', strtotime('first day of -'.$i.' month'));
                    $zpOut .= '<th style="min-width:90px">'.getMonth($m).'</th>';
                }

                $zpOut .= '<th style="min-width:90px">Баланс</th><th>ФОТ в работе</th><th>Оклад</th><th>Ограничения</th><th></th></tr></thead><tbody>';

                $error = 0;
                $d1 = date('Y-m', strtotime('first day of -1 month'));

                foreach($zp2 as $d => $d2)
                {
                    $zpOut .= '<tr><td colspan="13" class="alert-info">'.htmlspecialchars($_SESSION['bitAppFot']['DEP_NAME'][$d]['dep']).'</td></tr>';
                    foreach($d2 as $idUsr)
                    {
                        $z = $idUsr;
                        $p = $zp[$idUsr];
                        
                            $clsZp = '';
                            $setZp = '';
                            $errInfo = '';

                            #   Ограничения:
                            #   Выговор
                            if(!empty($p['rebErr'][date('Y-m')]))
                            {
                                $error++;
                                $errInfo .= '<div class="text-danger"><strong>&times;</strong> Выговор. Начисление 70% от оклада</div>';
                            }
                            #   Не установлена мин. выплата
                            if($p['min'] <= 0)
                            {
                                $error++;
                                $errInfo .= '<div class="text-danger"><strong>&times;</strong> Не установлен оклад</div>';
                            }
                            #   Неразбитые топливные платежи
                            if($p['fuel'] == 1)
                            {
                                $error++;
                                $errInfo .= '<div class="text-danger"><strong>&times;</strong> Не разбиты топливные платежи</div>';
                            }
                            #   Незакрытые задачи
                            if(!empty($p['taskErr']))
                            {
                                $error++;
                                $errInfo .= '<div class="text-danger"><strong>&times;</strong> Имеются просроченные задачи</div>';
                            }
                        #   АВР
                        if(!empty($p['avrErr']))
                        {
                            $error++;
                            $errInfo .= '<div class="text-danger"><strong>&times;</strong> Имеются просроченные АВР</div>';
                        }
                        #   МТР
                        if(!empty($p['mtrErr']))
                        {
                            $error++;
                            $errInfo .= '<div class="text-danger"><strong>&times;</strong> Имеются МТР на инвентаризации</div>';
                        }

                        #   Счета на оплату
                        if(!empty($p['invErr']))
                        {
                            $error++;
                            $errInfo .= '<div class="text-danger"><strong>&times;</strong> Имеются счета на оплату на статусе "Документы не собраны"</div>';
                        }

                        #   Счета
                        if(!empty($p['invpErr']))
                        {
                            $error++;
                            $errInfo .= '<div class="text-danger"><strong>&times;</strong> Имеются счета на статусе "Просрочено"</div>';
                        }

                        #   Поля
                        if(!empty($p['crmErr']))
                        {
                            $error++;
                            $errInfo .= '<div class="text-danger"><strong>&times;</strong> Имеются незаполненные поля в сделках</div>';
                        }

                        #   Рабочий график
                        if($p['scheduleErr'] == 1)
                        {
                            $error++;
                            $errInfo .= '<div class="text-danger"><strong>&times;</strong> Не установлен рабочий график</div>';
                        }
                            #   Незакрытые сделки
                            if(!empty($p['dealErr']))
                            {
                                $error++;
                                $errInfo .= '<div class="text-danger"><strong>&times;</strong> Имеются просроченные сделки</div>';
                            }
                            #   Не выработана норма часов
                            #if($p['min'] <= 0)


                            if($p['time'] == 1 && isset($p['workH'], $p['currH']) && $p['workH'] < $p['currH'])
                            {
                                if($p['currH'] < 0) {
                                    $p['currH'] = 0;
                                }
                                $error++;
                                #$errInfo .= '<div class="text-danger"><strong>&times;</strong> Не выработана норма часов</div>';
                                $errInfo .= '<div class="text-danger"><strong>&times;</strong> Отработано '.(int)$p['workH'].' '.ending((int)$p['workH'], 'час', 'часа', 'часов').' из '.$p['currH'].'</div>';
                            }

                            ########## смена ###########

                            if($p['time'] == 1 && isset($p['workH'], $p['currH']) && $p['workH'] > $p['currH'])
                            {
                                if($p['currH'] < 0) {
                                    $p['currH'] = 0;
                                }
                                $errInfo .= '<div class="text-success"><strong>&times;</strong> Отработано '.(int)$p['workH'].' '.ending((int)$p['workH'], 'час', 'часа', 'часов').' из '.$p['currH'].'</div>';
                            }
                            ####################

                            $sdel = ($p['sdel'] == 1) ? '<span style="color: #0000ff;">Сдельно</span>' : $p['max']; // === 22/08/2024 ===
                            $strong = ($idUsr == $_SESSION['bitAppFot']['ID'] && in_array($p['dep'], $_SESSION['bitAppFot']['HEAD'])) ? '<strong>'.htmlspecialchars($p['user']).'</strong><br><small style="color: #0000ff;">'.htmlspecialchars($p['position']).'</small>' : htmlspecialchars($p['user']).'<br><small style="color: #0000ff;">'.htmlspecialchars($p['position']).'</small>';

                            $zpOut .= '<tr><td>'.$strong.'</td>';
                            $is = 0;
                            for($i = 6; $i >= 0; $i--)
                            {
                                $d = date('Y-m', strtotime('first day of -'.$i.' month'));
                                $d2 = date('Ym', strtotime('first day of -'.$i.' month'));

                                $curZP = (!empty($p['rebErr'][date('Y-m')])) ? $p['min'] * 0.7 : $p['min'];#round($curZP / 500) * 500;
                                if($p['list2'][$d]['manager'] == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER)
                                    $clMng ='<div class="text-danger text-center">М</div>';
                                elseif($p['list2'][$d]['manager'] == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR)
                                    $clMng ='';
                                elseif($p['list2'][$d]['manager'] == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR)
                                    $clMng ='<div class="text-danger text-center">ВС</div>';
                                elseif($p['list2'][$d]['manager'] == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA)
                                    $clMng ='<div class="text-danger text-center">РП</div>';
                                else
                                    $clMng = '';
                                
                                if(isset($p['list2'][$d]['pprem']) && $p['list2'][$d]['pprem'] > 0 && isset($p['list2'][$d]['aprem']) && $p['list2'][$d]['aprem'] == 0 && !isset($_SESSION['bitAppFot']['HEAD_UP'][$p['dep']]))
                                    $prem = '<br><strong class="text-danger">'.number_format($p['list2'][$d]['pprem'], 2, '.', ' ').'</strong>';
                                elseif(isset($p['list2'][$d]['pprem']) && $p['list2'][$d]['pprem'] > 0 && isset($p['list2'][$d]['aprem']) && $p['list2'][$d]['aprem'] == 0 && isset($_SESSION['bitAppFot']['HEAD_UP'][$p['dep']]))
                                    $prem = '<div id="'.$d2.$z.'prem" class="text-center"><strong class="text-danger">'.number_format($p['list2'][$d]['pprem'], 2, '.', ' ').'</strong><br><button class="btn btn-sm btn-success pull-left" onclick="approvePrem('.$z.', '.$d2.', 1)"><span class="fa fa-check"></span></button><button class="btn btn-sm btn-danger pull-right" onclick="approvePrem('.$z.', '.$d2.', 0)"><span class="fa fa-times"></span></button></div>';
                                else
                                    $prem = '<div id="'.$d2.$z.'prem"></div>';

                                $psum = '<div id="'.$d2.$z.'psum"></div>';
                                if($p['list2'][$d]['manager'] == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR)
                                {
                                    if(isset($p['list2'][$d]['psum']) && $p['list2'][$d]['psum'] > 0 && !isset($_SESSION['bitAppFot']['HEAD_UP'][$p['dep']]) && $_SESSION['bitAppFot']['ID'] != 3)
                                        $psum = '<strong class="text-danger">'.number_format($p['list2'][$d]['psum'], 2, '.', ' ').'</strong>';
                                    elseif(isset($p['list2'][$d]['psum']) && $p['list2'][$d]['psum'] > 0 && (isset($_SESSION['bitAppFot']['HEAD_UP'][$p['dep']]) || $_SESSION['bitAppFot']['ID'] == 3))
                                        $psum = '<div id="'.$d2.$z.'psum" class="text-center"><strong class="text-danger">'.number_format($p['list2'][$d]['psum'], 2, '.', ' ').'</strong><br><button class="btn btn-sm btn-success pull-left" onclick="approveSum('.$z.', '.$d2.', 1)"><span class="fa fa-check"></span></button><button class="btn btn-sm btn-danger pull-right" onclick="approveSum('.$z.', '.$d2.', 0)"><span class="fa fa-times"></span></button></div><br>';
                                }

                                if(isset($p['list'][$d]))
                                {
                                    $appSum = ($p['list2'][$d]['manager'] == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR && $p['list2'][$d]['psum'] > 0) ? $psum : number_format($p['list'][$d], 0, '.', ' ') ;

                                    if($d == date('Y-m'))
                                    {
                                        $zpOut .= '<td class="alert-success" id="'.$d2.$z.'ZpInf">'.$appSum.$prem.'</td>';
                                        $clsZp = 'class="text-success"';
                                        #$setZp = 'Начислено';
                                        $setZp = ($idUsr == $_SESSION['bitAppFot']['ID']) ? '' : '<button class="btn btn-sm btn-warning" id="stzp'.$z.'" onclick="showModal('.$z.')">Начислить</button>';
                                    }
                                    else
                                        $zpOut .= '<td id="'.$d2.$z.'ZpInf">'.$appSum.$clMng.$prem.'</td>';
                                }
                                else
                                {
                                    if($d >= $p['register'] && $is == 0 && $d == date('Y-m'))
                                    {
                                        $clsZp = '';
                                        $setZp = ($idUsr == $_SESSION['bitAppFot']['ID']) ? '' : '<button class="btn btn-sm btn-warning" id="stzp'.$z.'" onclick="showModal('.$z.')">Начислить</button>';

                                        $is = 1;
                                        $zpOut .= '<td class="alert-warning" id="'.$d2.$z.'ZpInf">'.number_format($curZP, 0, '.', ' ').$prem.'</td>';
                                    }
                                    else
                                    {
                                        $zpOut .= '<td id="'.$d2.$z.'ZpInf"></td>';
                                    }
                                }
                            }

                            $wFot = ($p['workFOT'] != 0) ? number_format($p['workFOT'], 0, '.', ' ') : '';
                            $bl2  = ($p['manager'] != POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER || $_SESSION['bitAppFot']['ID'] == 3) ? number_format($p['balance'], 0, '.', ' ') : '';
                            $errMoreInfo = (!empty($errInfo)) ? '<button id="smeb'.$z.'" class="btn btn-sm btn-outline-danger pull-right" onclick="showMoreErr('.$z.')"><i class="fa fa-question"></i></button>' : '';

                            $warnAppr = ($p['balance'] + $p['workFOT'] < 0 && $p['manager'] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR) ? '<div class="text-warning"><strong>&times;</strong> Начисление з/п будет происходить с согласования с непосредственным руководителем</div>' : '';
                            
                            // разраб
                            if(!number_format((float)$sdel, 0, '.', ' ') == 0) {
                                $sdel = number_format((float)$sdel, 0, '.', ' ');
                            }
                            $zpOut .= '<td id="'.$z.'blns">'.$bl2.'</td>
                               <td id="'.$z.'wFot"><span style="color:#007bff;cursor:pointer;" onclick="$(\'.moreFot\').hide();$(\'#mf'.$z.'\').show();$(\'#ModalMoreFot\').modal(\'show\');">'.$wFot.'</span></td>
                               <td id="'.$z.'zp">'.$sdel.'</td>
                               <td>'.$errMoreInfo.$errInfo.$warnAppr.'</td>
                               <td id="'.$z.'act" '.$clsZp.'>'.$setZp.'</td></tr>';
                    }
                }

                $zpOut .= '</tbody></table>
                <div class="modal fade" id="ModalMoreFot" tabindex="-1" role="dialog" aria-hidden="true">
                 <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="text-muted"><i class="fa fa-files-o"></i>&nbsp;&nbsp;<strong class="modal-title"> ФОТ в работе</strong></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">';

                reset($zp);
                foreach($zp as $uid => $z)
                {
                    if(!empty($z['workFOTMore']))
                    {
                        $zSum = 0;
                        $zpOut .= '<table class="moreFot" id="mf'.$uid.'"><tr style="text-align:center;font-weight:bold"><td>ФОТ</td><td>Задача</td></tr>';
                        foreach($z['workFOTMore'] as $id_t => $f_more)
                        {
                            $zpOut .= '<tr><td>'.$f_more['fot'].'</td><td>&nbsp;&nbsp;&nbsp;<a href="/company/personal/user/'.$uid.'/tasks/task/view/'.$id_t.'/" target="_blank">'.htmlspecialchars($f_more['title']).'</a> <small>'.$f_more['status'].'</small></td></tr>';
                            $zSum += $f_more['fot'];
                        }
                        $zpOut .= '<tr><td><strong>'.$zSum.'</strong></td></td></tr></table>';
                    }
                }

                $zpOut .= '</div>
                        <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button></div>
                    </div>
                 </div>
                </div>';
            }
        }

        echo $zpOut;
        exit;
    }
    
    if(isset($_POST['BAFshow']))
    {
        $out['minDate']  = 0;
        $out['maxDate']  = 0;
        $out['sumTask']  = 0;
        $out['sumIn']    = 0;
        $out['sumOut']   = 0;
        $out['sumZp']    = 0;
        $out['taskList'] = '';
        $out['payList']  = '';
        $out['inList']   = '';
        $out['outList']  = '';
        $out['zpList']   = '';
        $out['roList']   = '';  #   список начислений для РО
        $out['cFot'] = array();
        $out['cZP']  = array();
        $out['cPay'] = array();

        if($_POST['BAFshow'] == 'year')
        {
            #$reqTask = getTask(array('RESPONSIBLE_ID' => $_SESSION['bitAppFot']['ID'], 'STATUS' => 5, '>=CLOSED_DATE' => date('Y-01-01')));
            $reqTask = getTask(array('RESPONSIBLE_ID' => $_SESSION['bitAppFot']['ID'], '>='.ZADACHI_DATA_PERVOGO_ZAKRYTIYA.'' => date('01.01.Y')));
            $reqIn   = getList(30, array('PROPERTY_'.IB_PEREVODY_DATA_PEREVODA, 'PROPERTY_'.IB_PEREVODY_SUMMA, 'PROPERTY_'.IB_PEREVODY_KOMMENTARIY), array('PROPERTY_'.IB_PEREVODY_POLUCHATEL => $_SESSION['bitAppFot']['ID'], '>=PROPERTY_'.IB_PEREVODY_DATA_PEREVODA => date('01.01.Y')), 'PROPERTY_'.IB_PEREVODY_DATA_PEREVODA);
            $reqOut  = getList(30, array('PROPERTY_'.IB_PEREVODY_DATA_PEREVODA, 'PROPERTY_'.IB_PEREVODY_SUMMA, 'PROPERTY_'.IB_PEREVODY_KOMMENTARIY), array('PROPERTY_'.IB_PEREVODY_OTPRAVITEL => $_SESSION['bitAppFot']['ID'], '>=PROPERTY_'.IB_PEREVODY_DATA_PEREVODA => date('01.01.Y')), 'PROPERTY_'.IB_PEREVODY_DATA_PEREVODA);
            $reqZP   = getList(27, array('PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA), array('PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA => $_SESSION['bitAppFot']['ID'], '>=PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => date('01.01.Y')), 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA);
            $reqPay  = getPay($db, 'year');
            $out['status'] = 1;
        }
        elseif($_POST['BAFshow'] == 'all')
        {
            #$reqTask = getTask(array('RESPONSIBLE_ID' => $_SESSION['bitAppFot']['ID'], 'STATUS' => 5));
            $reqTask = getTask(array('RESPONSIBLE_ID' => $_SESSION['bitAppFot']['ID'], '>'.ZADACHI_DATA_PERVOGO_ZAKRYTIYA.''  => 1));
            $reqIn   = getList(30, array('PROPERTY_'.IB_PEREVODY_DATA_PEREVODA, 'PROPERTY_'.IB_PEREVODY_SUMMA, 'PROPERTY_'.IB_PEREVODY_KOMMENTARIY), array('PROPERTY_'.IB_PEREVODY_POLUCHATEL => $_SESSION['bitAppFot']['ID']), 'PROPERTY_'.IB_PEREVODY_DATA_PEREVODA);
            $reqOut  = getList(30, array('PROPERTY_'.IB_PEREVODY_DATA_PEREVODA, 'PROPERTY_'.IB_PEREVODY_SUMMA, 'PROPERTY_'.IB_PEREVODY_KOMMENTARIY), array('PROPERTY_'.IB_PEREVODY_OTPRAVITEL => $_SESSION['bitAppFot']['ID']), 'PROPERTY_'.IB_PEREVODY_DATA_PEREVODA);
            $reqZP   = getList(27, array('PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA), array('PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA  => $_SESSION['bitAppFot']['ID']), 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA);
            $reqPay  = getPay($db, 'all');
            $out['status'] = 1;
        }
        elseif($_POST['BAFshow'] == 'date' && !empty($_POST['start']) && !empty($_POST['stop']))
        {
            $tmpStart = explode('.', $_POST['start']);
            $tmpStop = explode('.', $_POST['stop']);
            $start = date('d.m.Y', strtotime($tmpStart[2].'-'.$tmpStart[1].'-'.$tmpStart[0]));
            $stop  = date('d.m.Y', strtotime($tmpStop[2].'-'.$tmpStop[1].'-'.$tmpStop[0]));
            
            if($start > 0 && $stop > 0)
            {
                #$reqTask = getTask(array('RESPONSIBLE_ID' => $_SESSION['bitAppFot']['ID'], 'STATUS' => 5, '>=CLOSED_DATE' => $start, '<=CLOSED_DATE' => $stop));
                $reqTask = getTask(array('RESPONSIBLE_ID' => $_SESSION['bitAppFot']['ID'], '>='.ZADACHI_DATA_PERVOGO_ZAKRYTIYA.''  => $start, '<='.ZADACHI_DATA_PERVOGO_ZAKRYTIYA.''  => $stop));
                $reqIn   = getList(30, array('PROPERTY_'.IB_PEREVODY_DATA_PEREVODA, 'PROPERTY_'.IB_PEREVODY_SUMMA, 'PROPERTY_'.IB_PEREVODY_KOMMENTARIY), array('PROPERTY_'.IB_PEREVODY_POLUCHATEL => $_SESSION['bitAppFot']['ID'], '>=PROPERTY_'.IB_PEREVODY_DATA_PEREVODA => $start, '<=PROPERTY_'.IB_PEREVODY_DATA_PEREVODA => $stop), 'PROPERTY_'.IB_PEREVODY_DATA_PEREVODA);
                $reqOut  = getList(30, array('PROPERTY_'.IB_PEREVODY_DATA_PEREVODA, 'PROPERTY_'.IB_PEREVODY_SUMMA, 'PROPERTY_'.IB_PEREVODY_KOMMENTARIY), array('PROPERTY_'.IB_PEREVODY_OTPRAVITEL => $_SESSION['bitAppFot']['ID'], '>=PROPERTY_'.IB_PEREVODY_DATA_PEREVODA => $start, '<=PROPERTY_'.IB_PEREVODY_DATA_PEREVODA => $stop), 'PROPERTY_'.IB_PEREVODY_DATA_PEREVODA);
                $reqZP   = getList(27, array('PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA, 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA, 'PROPERTY_262'), array('PROPERTY_'.IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA  => $_SESSION['bitAppFot']['ID'], '>=PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => $start, '<=PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA => $stop), 'PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA);
                $reqPay  = getPay($db, 'date', $tmpStart[2].'-'.$tmpStart[1].'-'.$tmpStart[0], $tmpStop[2].'-'.$tmpStop[1].'-'.$tmpStop[0]);
                $out['status'] = 1;
            }
        }
            
            if(!empty($reqTask))
            {
                foreach($reqTask as $val)
                {
                    #$date = strtotime($val['closedDate']);
                    $date = strtotime($val['ufClosedateFirst']);
                    if(empty($out['minDate']) || $out['minDate'] > $date)
                        $out['minDate'] = $date;
                    
                    if(empty($out['maxDate']) || $out['maxDate'] < $date)
                        $out['maxDate'] = $date;
                    
                    if($val['ufFotResponse'] != 0 && $_SESSION['bitAppFot']['S_BALANCE'] == 1)
                    {
                        $sum = $val['ufFotResponse'];
                        $out['sumTask'] += $val['ufFotResponse'];
                    }
                    else
                    {
                        $sum = 0;
                    }
                    
                    if(!isset($out['cFot'][date('nY', $date)]))
                        $out['cFot'][date('nY', $date)] = 0;
                    
                    if($_SESSION['bitAppFot']['S_BALANCE'] == 1)
                        $out['cFot'][date('nY', $date)] += $sum;
                    
                    $out['taskList'] .= '<tr><td>'. date('d.m.Y', $date) .'</td><td>'. $sum .'</td><td><a href="/company/personal/user/'.(int)$_SESSION['bitAppFot']['ID'].'/tasks/task/view/'.(int)$val['id'].'/" target="_blank">'. htmlspecialchars($val['title']) .'</a></td></tr>';
                }
            }
            
            if(!empty($reqIn))
            {
                foreach($reqIn as $val)
                {
                    $tmpDate = explode('.', current($val['PROPERTY_'.IB_PEREVODY_DATA_PEREVODA]));
                    $date = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                    $date2 = strtotime($date);
                    
                    if(empty($out['minDate']) || $out['minDate'] > $date2)
                        $out['minDate'] = $date2;
                    
                    if(empty($out['maxDate']) || $out['maxDate'] < $date2)
                        $out['maxDate'] = $date2;
                    
                    $sum = (float)current($val['PROPERTY_'.IB_PEREVODY_SUMMA]);
                    $out['sumIn'] += $sum;
                    
                    if(!isset($out['cFot'][date('nY', $date2)]))
                        $out['cFot'][date('nY', $date2)] = 0;
                    
                    $out['cFot'][date('nY', $date2)] += $sum;
                    
                    $comment = (!empty($val['PROPERTY_'.IB_PEREVODY_KOMMENTARIY])) ? htmlspecialchars(current($val['PROPERTY_'.IB_PEREVODY_KOMMENTARIY])) : '';
                    
                    $out['inList'] .= '<tr><td>'. date('d.m.Y', $date2) .'</td><td>'. $sum .'</td><td>'. $comment .'</td></tr>';
                }
            }
            
            if(!empty($reqOut))
            {
                foreach($reqOut as $val)
                {
                    $tmpDate = explode('.', current($val['PROPERTY_'.IB_PEREVODY_DATA_PEREVODA]));
                    $date = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                    $date2 = strtotime($date);
                    
                    if(empty($out['minDate']) || $out['minDate'] > $date2)
                        $out['minDate'] = $date2;
                    
                    if(empty($out['maxDate']) || $out['maxDate'] < $date2)
                        $out['maxDate'] = $date2;
                    
                    $sum = (float)current($val['PROPERTY_'.IB_PEREVODY_SUMMA]);
                    $out['sumOut'] += $sum;
                    
                    if(!isset($out['cFot'][date('nY', $date2)]))
                        $out['cFot'][date('nY', $date2)] = 0;
                    
                    $out['cFot'][date('nY', $date2)] += $sum;
                    
                    $comment = (!empty($val['PROPERTY_'.IB_PEREVODY_KOMMENTARIY])) ? htmlspecialchars(current($val['PROPERTY_'.IB_PEREVODY_KOMMENTARIY])) : '';
                    
                    $out['outList'] .= '<tr><td>'. date('d.m.Y', $date2) .'</td><td>'. $sum .'</td><td>'. $comment .'</td></tr>';
                }
            }
            
            if(!empty($reqZP))
            {
                foreach($reqZP as $val)
                {
                    $tmpDate = explode('.', current($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA]));
                    $date = $tmpDate[2].'-'.$tmpDate[1].'-'.$tmpDate[0];
                    $date2 = strtotime($date);
                    
                    if(empty($out['minDate']) || $out['minDate'] > $date2)
                        $out['minDate'] = $date2;
                    
                    if(empty($out['maxDate']) || $out['maxDate'] < $date2)
                        $out['maxDate'] = $date2;

                    $s1 = (!empty($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA])) ? (float)current($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_SUMMA]) : 0;
                    $s2 = (!empty($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA])) ? (float)current($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_PREMIYA]) : 0;
                    $sum =  $s1 + $s2;
                    $out['sumZp'] += $sum;
                    
                    if(!isset($out['cZP'][date('nY', $date2)]))
                        $out['cZP'][date('nY', $date2)] = 0;
                    
                    $out['cZP'][date('nY', $date2)] += $sum;
                    
                    $comment = (!empty($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA])) ? (float)current($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA]) : '';
                    $mngr = (!empty($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA]) 
                        && (current($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA]) == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER 
                        || current($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA]) == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR 
                        || current($val['PROPERTY_'.IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA]) == IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA)) ? 'class="text-danger"' : '';
                    
                    if($_SESSION['bitAppFot']['S_BALANCE'] == 1)
                        $out['zpList'] .= '<tr><td>'. getMonth(date('n', $date2)) .' '.date('Y', $date2) .'</td><td '.$mngr.'>'. $sum .'</td><td>'. $comment .'</td></tr>';
                    else
                        $out['zpList'] .= '<tr><td>'. getMonth(date('n', $date2)) .' '.date('Y', $date2) .'</td><td '.$mngr.'>'. $sum .'</td></tr>';
                }
            }

            if(!empty($reqPay))
            {
                krsort($reqPay);
                $out['sumPay'] = 0;
                foreach($reqPay as $dt => $val)
                {
                    $date2 = strtotime($dt);

                    if(empty($out['minDate']) || $out['minDate'] > $date2)
                        $out['minDate'] = $date2;

                    if(empty($out['maxDate']) || $out['maxDate'] < $date2)
                        $out['maxDate'] = $date2;

                    $sum = abs($val);
                    $out['sumPay'] += $sum;

                    if(!isset($out['cPay'][date('nY', $date2)]))
                        $out['cPay'][date('nY', $date2)] = 0;

                    $out['cPay'][date('nY', $date2)] += $sum;
                    $out['payList'] .= '<tr><td><span onclick="getMorePay('. date('Ym', $date2).')" class="text-info" style="cursor:pointer">'. getMonth(date('n', $date2)) .' '.date('Y', $date2).'</span></td><td>'. $sum .'</td></tr>';
                }
            }
            
            if($out['status'] == 1)
            {
                $out['minDate'] = date('Y-m-d', $out['minDate']);
                $out['maxDate'] = date('Y-m-d', $out['maxDate']);
                echo json_encode($out);
            }
            else
            {
                unset($out);
                $out['status'] = 0;
                echo json_encode($out);
            }
            
            exit;
    }
}
else
    exit('Пользователь не найден');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Мои данные</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <style>.moreFot{display:none}</style>
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-datepicker.min.js"></script>
    <script src="js/bootstrap-datepicker.ru.min.js"></script>
    <script src="js/chart.min.js"></script>
    <style>
        .error {
        border: 1px solid red;
        }
    </style>
    <script>
        let emailInput;
        let myChart;
        user = 0;

        <?php
      //  if(!isset($_SESSION['bitAppFot']['DEPARTMENT'][IB_SECTION_OKPO]) || $_SESSION['bitAppFot']['ID'] == USER_DOLGOVA)
    //    {
        ?>
        getZpList();
        getBalance();
        <?php
   //     }
        ?>

        function setZp()
        {
            var id = $('#setZPuser').val();
            var period = $('#setZPyear').val()+'-'+$('#setZPmonth').val();
            
            if(id > 0 && period.length > 4)
            {
                $.ajax({
                    beforeSent: $('#SetZpBtn').prop('disabled', true),
                    type: "POST",
                    data: "setZp=1&id="+id+'&period='+period,
                    dataType: "JSON",
                    success: function (data) {
                        $('#ModalSetZP').modal('hide');
                        $('#setZPuser').val(0);
                        if(data.status == 1){
                            $('#'+id+'blns').html(data.balance);
                            $('#'+data.dt+id+'ZpInf').removeClass('alert-warning').addClass('alert-success').html(data.zp);
                            $('#SetZpBtn').prop('disabled', false);
                        }else{
                            alert(data.msg);
                            $('#SetZpBtn').prop('disabled', false);
                        }
                    }
                });
            }
        }

        // Проверки для телефона и email
        function formatPhone(input) {
            let phoneNumber = input.value.replace(/[^0-9]/g, '');

            if (phoneNumber.length > 10) {
                phoneNumber = phoneNumber.substring(0, 10);
            } 

            input.value = phoneNumber;
        }

        function validateEmail(input) {
            const emailRegex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            const isValid = emailRegex.test(input.value);

            if (isValid) {
                input.classList.remove("error");
            } else {
                input.classList.add("error");
            }
        }

        if(emailInput)

        emailInput.addEventListener("input", function() {
            validateEmail(this);
        });
        // Проверки для телефона и email
        
        function showMoreErr(id)
        {
            if(id > 0)
            {
                $.ajax({
                    beforeSent: $('#smeb'+id).prop('disabled', true),
                    type: "POST",
                    data: "modalErrors=1&id="+id,
                    dataType: 'JSON',
                    success: function (data) {
                        if(data.status == 1){
                            $('#modalErrorsContent').html(data.data);
                            $('#modalErrors').modal('show');
                        }
                        $('#smeb'+id).prop('disabled', false);
                    }
                });
            }
        }

        function addUser()
        {
            var uName  = $('#APPlastName').val();
            var uMail  = $('#APPmail').val();
            var uPhone = $('#APPphone').val();
            var uDep   = $('#APPdepartment').val();
            var uWorkSys   = $('#APPSys').val();

            console.log(uWorkSys);

            if(uName.length > 1 && uMail.length > 3 && uPhone.length == 10 && uDep.length > 0 && uWorkSys.length > 0)
            {
                var frm = $('#addUserForm').serialize();
                $.ajax({
                    beforeSent: $('#addUserBtn').prop('disabled', true),
                    type: "POST",
                    data: "addUser=1&"+frm,
                    success: function (data) {
                        if(data == 1){
                            $('#ModalNewUser').modal('hide');
                            $('#APPlastName, #APPfirstName, #APPsecondName, #APPphone, #APPmail, #APPBday, #APPdepartment, #APPwork, #APPSys, #uWGend').val(''); //Добавление
                            alert('Пользователь зарегистрирован');
                        }else{
                            alert(data);
                        }

                        $('#addUserBtn').prop('disabled', false);
                    }
                });
            }
            else
                alert('Проверьте введенные данные');
        }

        function setPrem()
        {
            var id = $('#setZPuser').val();
            var period = $('#setZPyear').val()+'-'+$('#setZPmonth').val();
            var sum = $('#setZPprem').val();

            if(id > 0 && period.length > 4 && sum > 0)
            {
                $.ajax({
                    beforeSent: $('#SetPremBtn').prop('disabled', true),
                    type: "POST",
                    data: "setPrem=1&id="+id+'&period='+period+'&sum='+sum,
                    dataType: "JSON",
                    success: function (data) {
                        $('#ModalSetZP').modal('hide');
                        $('#setZPuser').val(0);
                        if(data.status == 1){
                            $('#'+id+'blns').html(data.balance);
                            $('#'+id+'prem').html(data.prem);
                            $('#'+data.dt+id+'ZpInf').removeClass('alert-warning').addClass('alert-success').html(data.zp);
                            $('#SetPremBtn').prop('disabled', false);
                        }else{
                            alert(data.msg);
                            $('#SetPremBtn').prop('disabled', false);
                        }
                    }
                });
            }
            else
                alert('Не начислено!');
        }

        function setSdel()
        {
            var id = $('#setZPuser').val();
            var period = $('#setZPyear').val()+'-'+$('#setZPmonth').val();
            var sum = $('#setZPsdel').val();

            if(id > 0 && period.length > 4 && sum > 0)
            {
                $.ajax({
                    beforeSent: $('#SetSdelBtn').prop('disabled', true),
                    type: "POST",
                    data: "setSdel=1&id="+id+'&period='+period+'&sum='+sum,
                    dataType: "JSON",
                    success: function (data) {
                        $('#ModalSetZP').modal('hide');
                        $('#setZPuser').val(0);
                        if(data.status == 1){
                            $('#'+id+'blns').html(data.balance);
                            $('#'+id+'prem').html(data.prem);
                            $('#'+data.dt+id+'ZpInf').removeClass('alert-warning').addClass('alert-success').html(data.zp);
                            $('#SetSdelBtn').prop('disabled', false);
                        }else if(data.status == 2){
                            $('#'+id+'blns').html(data.balance);
                            $('#'+id+'prem').html(data.prem);
                            $('#'+data.dt+id+'ZpInf').html(data.zp);
                            $('#SetSdelBtn').prop('disabled', false);
                        }else{
                            alert(data.msg);
                            $('#SetSdelBtn').prop('disabled', false);
                        }
                    }
                });
            }
            else
                alert('Не начислено!');
        }

        $(document).ready(function(){
            var calend = $('#myAppFilterDate1, #myAppFilterDate2').datepicker({
                format: "dd.mm.yyyy",
                language: "ru"
            });
            calend.on('changeDate', function(){
                calend.datepicker('hide');
            });
            
            getInfo();
            
            $('#myAppFilter').change(function(){
                if($(this).val() == 'date'){
                    $('#myAppFilterDate1, #myAppFilterDate2').show(200);
                }else{
                    $('#myAppFilterDate1, #myAppFilterDate2').hide(200);
                    getInfo();
                }
            });
            
            $('#myAppFilterDate1, #myAppFilterDate2').change(function(){
                if($('#myAppFilterDate1').val().length > 0 && $('#myAppFilterDate2').val().length > 0)
                    getInfo();
            });
        });
        
        function getBalance()
        {
            $.ajax({
                type: "POST",
                data: "getBalance=1",
                success: function (data) {
                    $('#BAFTopBalance').html(data);
                }
            });
        }

        function getZpList()
        {
            $.ajax({
                beforeSent: $('#BAFZplist').html('<img src="ldng.gif">'),
                type: "POST",
                data: "getZpList=1",
                success: function (data) {
                    $('#BAFZplist').html(data);
                }
            });
        }
        
        function getMorePay(p)
        {
            $.ajax({
                type: "POST",
                data: "getMorePay=1&p="+p,
                success: function (data) {
                    alert(data)
                }
            });
        }
        
        function getInfo(){
            var tMonth = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
            var minDate = 0;
            var maxDate = 0;
            var ctxLeg = [];
            var ctxFot = [];
            var cFot = {};
            var ctxSal = [];
            var ctxPay = [];
            var cSal = {};
            var sumSalTotal    = 0;
            var sumTransTotal  = 0;
            var sumTrans2Total = 0;
            var sumTaskTotal   = 0;
                 
            if($('#myAppFilter').val() == 'year' || $('#myAppFilter').val() == 'all' || ($('#myAppFilter').val() == 'date' && $('#myAppFilterDate1').val().length > 0 && $('#myAppFilterDate2').val().length > 0)){
                $.ajax({
                    beforeSent: $('#myAppTask, #myAppTrans, #myAppTrans2, #myAppSal, #myAppPay').html('<tr><td colspan="3" class="text-center"><img src="ldng.gif"></td></tr>'),
                    type: "POST",
                    data: "BAFshow="+$('#myAppFilter').val()+'&start='+$('#myAppFilterDate1').val()+'&stop='+$('#myAppFilterDate2').val(),
                    dataType: "JSON",
                    success: function (data) {
                        if(data.status == 1)
                        {
                            $('#myAppTitle').html(data.user);
                            $('#myAppTaskSum').html(data.sumTask);
                            $('#myAppTransSum').html(data.sumIn);
                            $('#myAppTrans2Sum').html(data.sumOut);
                            $('#myAppSalSum').html(data.sumZp);
                            $('#myAppPaySum').html(data.sumPay);
                            $('#myAppTask').html(data.taskList);
                            $('#myAppTrans').html(data.inList);
                            $('#myAppTrans2').html(data.outList);
                            $('#myAppSal').html(data.zpList);
                            $('#myAppPay').html(data.payList);

                            var tmpMinM = new Date(data.minDate).getMonth();
                            var tmpMinY = new Date(data.minDate).getFullYear();
                            var tmpMaxM = new Date(data.maxDate).getMonth();
                            var tmpMaxY = new Date(data.maxDate).getFullYear();
                            var setMin = tMonth[tmpMinM] +' '+ tmpMinY;
                            var setMax = tMonth[tmpMaxM] +' '+ tmpMaxY;
                            
                            var tmpDiff = (tmpMaxY - tmpMinY) * 12 + (tmpMaxM - tmpMinM)+1;
                            
                            var m1 = tmpMinM;
                            var y1 = tmpMinY;
                            
                            for(i = 0; i < tmpDiff; i++)
                            {
                                if(m1 > 11){
                                    m1 = 0;
                                    y1++;
                                }
                                
                                ctxLeg.push(tMonth[m1] + ' ' + y1);
                                
                                var p1 = (m1 +1) +''+ y1;
                                
                                if(data.cFot[p1]){
                                    ctxFot.push(data.cFot[p1]);
                                }else{
                                    ctxFot.push(0);
                                }
                                
                                if(data.cZP[p1]){
                                    ctxSal.push(data.cZP[p1]);
                                }else{
                                    ctxSal.push(0);
                                }

                                if(data.cPay[p1]){
                                    ctxPay.push(data.cPay[p1]);
                                }else{
                                    ctxPay.push(0);
                                }
                                
                                m1++;
                            }
                            
                            setGraph(ctxLeg, ctxFot, ctxSal, ctxPay);
                        }
                        else
                        {
                            alert('Что-то пошло не так...');
                        }
                        
                        $('#myAppFilter').removeAttr('disabled');
                        $('#myAppFilterDate1, #myAppFilterDate2').removeAttr('disabled');
                    },
                    error: function(jqXHR, exception){
                        alert('Время ожидания истекло');
                        $('#myAppFilter').removeAttr('disabled');
                        $('#myAppFilterDate1, #myAppFilterDate2').removeAttr('disabled');
                    },
                    timeout: 40000
                });
            }
            
            $('#myAppFilter').attr('disabled', 'disabled');
            $('#myAppFilterDate1, #myAppFilterDate2').attr('disabled', 'disabled');
        }

        function showModal(id){
            $.ajax({
                beforeSent: $('#stzp'+id).prop('disabled', true),
                type: "POST",
                data: "getUser="+id,
                dataType: 'JSON',
                success: function (data) {
                    $('#setZPuser').val(id);
                    if(data.sdel == 1){
                        $('#setZPsdel').val(data.oklad);
                        $('#SetZpBtn').hide();
                        $('#sdelForm').show();
                    }else{
                        $('#setZPsdel').val(0);
                        $('#SetZpBtn').show();
                        $('#sdelForm').hide();
                    }
                    $('#labelUser').html(data.user);
                    $('#setZPmonth').html(data.month);
                    $('#setZPyear').html(data.year);
                    $('#premForm').hide();
                    $('#setZPprem').val('');
                    $('#ModalSetZP').modal('show');
                    $('#stzp'+id).prop('disabled', false);
                }
            });
        }
        
        function approvePrem(id, d, y)
        {
            if(id > 0 && d > 0)
            {
                $.ajax({
                    type: "POST",
                    data: "approvePrem=1&id="+id+'&date='+d+'&app='+y,
                    dataType: 'JSON',
                    success: function (data) {
                        if(data.status == 1){
                            $('#'+d+''+id+'ZpInf').html(data.zp);
                            $('#'+d+''+id+'prem').hide();
                        }else{
                            if(data.msg.length > 0)
                                alert(data.msg);
                        }
                    }
                });
            }
        }

        function approveSum(id, d, y)
        {
            if(id > 0 && d > 0)
            {
                if(y == 1 || y == 0 && confirm('Отменить начисление?'))
                {
                    $.ajax({
                        type: "POST",
                        data: "approveSum=1&id="+id+'&date='+d+'&app='+y,
                        dataType: 'JSON',
                        success: function (data) {
                            if(data.status == 1){
                                $('#'+d+''+id+'ZpInf').html(data.zp);
                                $('#'+d+''+id+'psum').hide();
                                if(y == 0){
                                    $('#'+d+''+id+'ZpInf').removeClass('alert-success').addClass('alert-warning');
                                }
                            }else{
                                console.log(data.status);
                                if(data.msg.length > 0)
                                    alert(data.msg);
                            }
                        }
                    });
                }
            }
        }
        
        function setGraph(ctxLeg, fot, sal, pay)
        {
            var ctx = $('#myAppChart');
            if(myChart){
                myChart.destroy();
            }
            
                myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ctxLeg,
                    datasets: [{
                        label: 'ФОТ+переводы',
                        data:fot,
                        backgroundColor: ['rgba(255, 99, 132, 0.3)'],
                        borderColor: ['rgba(255, 99, 132, 1)'],
                        borderWidth: 1
                    },{
                        label: 'Начисление',
                        data: sal,
                        backgroundColor: ['rgba(54, 162, 235, 0.3)'],
                        borderColor: ['rgba(54, 162, 235, 1)'],
                        borderWidth: 1
                    },{
                        label: 'Выплаты',
                        data: pay,
                        backgroundColor: ['rgba(125, 222, 87, 0.3)'],
                        borderColor: ['rgba(125, 222, 87, 1)'],
                        borderWidth: 1
                       }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }
    </script>
</head>
<body>
    <div style="margin: 5px; padding: 3px; width: 99%;">
        <h4 class="float-left">
            <?php echo htmlspecialchars($_SESSION['bitAppFot']['LAST_NAME'] .' '.$_SESSION['bitAppFot']['NAME'].'.'); ?></h4>
        
<?php
     if(/*!isset($_SESSION['bitAppFot']['DEPARTMENT'][IB_SECTION_OKPO]) &&*/ $_SESSION['bitAppFot']['manager'] != POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER)
     {
        if($_SESSION['bitAppFot']['S_BALANCE'] == 1)
            echo '<div style="float:right;max-width: 300px;" id="BAFTopBalance"><img src="ldng.gif"></div>';
        else
            echo '<div style="float:right;max-width: 300px;" id="BAFTopBalance0"></div>';
     }
     elseif($_SESSION['bitAppFot']['manager'] == POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER)
     {
         echo '<div style="float:right;max-width: 300px;" id="BAFTopBalance0">Вы являетесь отвественным за Cделки</div>';
     }
?>
    </div><br><br>
    <ul class="nav nav-tabs">
        <li class="nav-item"><a href="#BAFinfo" class="nav-link active" data-toggle="tab">Общая информация</a></li>
        <li class="nav-item"><a href="#BAFvacation" class="nav-link" data-toggle="tab">Отпуск</a></li>
<?php
     //if(!isset($_SESSION['bitAppFot']['DEPARTMENT'][IB_SECTION_OKPO]) || $_SESSION['bitAppFot']['ID'] == USER_KURAMOV)
    // {
 ?>
        <li class="nav-item"><a href="#BAFbalance" class="nav-link" data-toggle="tab">Баланс</a></li>
 <?php
    // }
 ?>
        <?php
        if(!empty($_SESSION['bitAppFot']['HEAD'])) //&& (!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93)
        {
           ?>
            <li class="nav-item"><a href="#BAFSHlist" class="nav-link" data-toggle="tab">Штатное расписание</a></li>
            <!-- ======= 08/07/2024 ======= -->
            <?php if($_SESSION['bitAppFot']['ID'] == 3): ?>
                <li class="nav-item"><a href="#BAFSHlist_r" class="nav-link" data-toggle="tab">Ровнов А.</a></li>
            <?php endif;?>
            <!-- ======= 08/07/2024 ======= --> 
            <?php if ($_SESSION['bitAppFot']['HEAD_SAVED'] == 0) { ?> <!-- Developed by WolfHound (06.09.24) -->
                <li class="nav-item"><a href="#BAFZplist" class="nav-link" data-toggle="tab">Начисление з/п</a></li>
            <?php } ?>
            <li class="nav-item"><button class="btn btn-sm btn-success" onclick="$('#ModalNewUser').modal('show')" style="margin-left:10px"><i class="fa fa-plus"></i> Принять сотрудника</button></li>
            <?php
        }
        ?>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="BAFinfo" style="font-size: 14px;padding:20px">
                <?php
                $myErrors = getErrors($db);
                if(!empty($myErrors['dealRow']) || !empty($myErrors['mtr']) || !empty($myErrors['avr']) || !empty($myErrors['invoice']) || !empty($myErrors['invoicep']) || !empty($myErrors['tasks']) || !empty($myErrors['dealDate']) || !empty($myErrors['schedule']) || !empty($myErrors['reb'][date('Y-m')]))
                {
                    echo '<div style="width:350px;z-index:1000;position:absolute;right:10px;margin-top:10px;margin-right:10px">';

                    if(!empty($myErrors['reb'][date('Y-m')]))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Выговор. Начисление з/п в размере 70% от оклада</div>';

                    if(!empty($myErrors['dealRow']))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Имеются незаполненные поля в сделках</div>';
                    
                    if(!empty($myErrors['mtr']))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Имеются МТР на инвертаризации</div>';
                    
                        if(!empty($myErrors['avr']))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Имеются просроченные АВР</div>';
                    
                    if(!empty($myErrors['invoice']))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Имеются счета на оплату на стадии "Документы не собраны"</div>';

                    if(!empty($myErrors['invoicep']))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Имеются счета на стадии "Просрочено"</div>';
                    
                    if(!empty($myErrors['tasks']))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Имеются просроченные задачи</div>';

                    if(!empty($myErrors['dealDate']))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Имеются просроченные сделки</div>';

                    if(!empty($myErrors['schedule']))
                        echo '<div class="text-danger"><span class="fa fa-remove"></span> Не установлен рабочий график</div>';
                    echo '<hr><button class="btn btn-sm btn-danger pull-right" data-toggle="modal" data-target="#modalMyErrors">Подробнее...</button>';
                    echo '</div>';
                }

                $info = getNormTime($db);

                //echo "<pre>".print_r($info, true)."</pre>";
                if($info['showInfo'] == 1)
                {
                    #№<strong class="text-danger">Это тестовая информация!</strong><hr>
                    echo '<div class="card-body" style="border: 1px #ccc solid;">';

                    $normD = $info['normD'];
                    $normH = $info['normH'];

                    if(($info['workH'] >= $info['currH']))
                    {
                        $class = 'class="text-success"';
                        $clInf = '';
                    }
                    else
                    {
                        $class = 'class="text-danger"';
                        $clInf = ' <span class="text-danger">(необходимо <strong>'.$info['currH'].'</strong> '.ending((float)$info['currH'], 'час', 'часа', 'часов').')</span>';
                    }

                    $users = '';
                    $users = '';
                    if($info['workH'] < $info['normH'])
                    {
                        /*
                        if(!empty($info['users']))
                        {
                            $users = '<br><br><span class="text-danger">Для начисления з/п за текущий месяц, Вам необходимо обратиться к ';
                            $cnt = count($info['users']);
                            $i = 1;
                            foreach($info['users'] as $id_u => $u)
                            {
                                $i++;
                                
                                $users .= '<span class="text-info" style="cursor: pointer" onclick="return window.top.BX.tooltip.openIM('.(int)$id_u.')">'.htmlspecialchars($u).'</span>';
                                if($i < $cnt)
                                    $users .= ', ';
                                elseif($i == $cnt)
                                    $users .= ' или ';
                            }
                            $users .= ' для корректировки графика отсутствия</span>';
                        }
                        else
                            $users = '';
                        */
                    }
                    
                    echo '<p>Текущий рабочий график: <strong>'.htmlspecialchars($info['schedule']).'</strong></p>';
                    echo 'Рабочих дней: '.(int)$info['normD'].'<br>';
                    echo 'Рабочий день: '.(float)$info['dur'].' '.ending((int)$info['dur'],  'час', 'часа', 'часов').'<br>';
                    echo 'Норма часов(месяц): '.(float)$info['normD'].' х '.(float)$info['dur'].' = '.(float)$info['normH'].'<br>';

                    if(isset($info['abs']) && $info['abs'] > 0)
                        echo 'Отсутствия: <strong>'.(int)$info['abs'].'</strong> '.ending((int)$info['abs'], 'день', 'дня', 'дней').'<br>';

                    echo 'Проработано: <strong '.$class.'>'.(float)$info['workH'].'</strong> '.ending((int)$info['workH'], 'час', 'часа', 'часов').$clInf.' '.$users.'<br></div><br>';
                }
                ?>
            <?php
                if(!empty($_SESSION['bitAppFot']['MTR']))
                {
                    echo '<div class="card card-body">'.$_SESSION['bitAppFot']['MTR'].'</div>';
                }
            ?>
        </div>
<div class="tab-pane fade" id="BAFvacation" style="font-size: 14px;padding:20px">
<?php
    $vac = getVacation($db, $_SESSION['bitAppFot']['ID']);
    ////////////// Отпуск
    echo '<p>Дата начала отсчёта: <strong>'.date('d.m.Y', strtotime($vac['start'])).'</strong> г.<br>
             Количество доступных дней отпуска: <strong>'.$vac['days'].'</strong> <strong class="text-danger">'.$vac['message'].'</strong><br>'.'Через '.'<strong>'.$vac['add'].ending((int)$vac['add'], ' день', ' дня', ' дней').'</strong> Вам начислится '. '<strong>'. $vac['daysadd'] .'</strong> дней отпуска'.'</p><table class="table table-bordered"><thead><tr style="vertical-align:top">';
    
    if(!empty($vac['info']))
    {
        foreach($vac['info'] as $vy => $vi)
        {
            echo '<th style="text-align:center;text-weight:bold">'.(int)$vy.'</th>';
        }
        echo '</tr></thead><tbody><tr style="vertical-align:top">';
        foreach($vac['info'] as $vy => $vi)
        {
            echo '<td>';
            foreach($vi as $v_id => $v_info)
            {
                $period = new DatePeriod(
                    new DateTime($v_info['ACTIVE_FROM']),
                    new DateInterval('P1D'),
                    (new DateTime($v_info['ACTIVE_TO']))->modify('+1 day')
               );
               $resultDays = [];
               foreach( $period as $date) { 
                   $resultDays[] = $date->format('Y-m-d'); 
               }
               $holDay2024 = array(
                '2024-01-01','2024-01-02','2024-01-03','2024-01-04','2024-01-05','2024-01-06','2024-01-07','2024-01-08','2024-02-23',
                '2024-03-08','2024-04-29','2024-05-01','2024-05-09','2024-05-10','2024-06-12','2024-11-04','2024-12-31'
                );
                $c = array_intersect($resultDays, $holDay2024);
                $plusDays = count($c);
               // $val_date = ($v_info['days'] > 1) ? '<strong>'.date('d.m.', strtotime($v_info['ACTIVE_FROM'])).'</strong> - <strong>'.date('d.m.', strtotime($v_info['ACTIVE_TO'])).'</strong> + ' . $plusDays.ending((int)$vac['add'], ' день', ' дня', ' дней').' за нерабочие праздничные дни. '. htmlspecialchars($v_info['NAME']).' ('.(int)$v_info['days'].'д.)' : '<strong>'.date('d.m.', strtotime($v_info['ACTIVE_FROM'])).'</strong> '.htmlspecialchars($v_info['NAME']).' (1д.)';
               //$val_date = ($v_info['days'] > 1) ? '<strong>'.date('d.m.', strtotime($v_info['ACTIVE_FROM'])).'</strong> - <strong>'.date('d.m.', strtotime($v_info['ACTIVE_TO'])).'</strong> + ' .$plusDays.ending((int)$plusDays, ' день', ' дня', ' дней').' за нерабочие праздничные дни. '. htmlspecialchars($v_info['NAME']).' ('.(int)$v_info['days'].'д.)' : '<strong>'.date('d.m.', strtotime($v_info['ACTIVE_FROM'])).'</strong> '.htmlspecialchars($v_info['NAME']).' (1д.)';
               $val_date = ($v_info['days'] > 1) ? '<strong>'.date('d.m.', strtotime($v_info['ACTIVE_FROM'])).'</strong> - <strong>'.date('d.m.', strtotime($v_info['ACTIVE_TO'])).'</strong> + ' .$plusDays.ending((int)$plusDays, ' день', ' дня', ' дней').' за нерабочие праздничные дни. '. htmlspecialchars($v_info['NAME']).' ('.(int)$v_info['days'].'д.)' : '<strong>'.date('d.m.', strtotime($v_info['ACTIVE_FROM'])).'</strong> '.htmlspecialchars($v_info['NAME']).' (1д.)';
               echo '<div>'.$val_date.'</div>';
            }
            echo '</td>';
        }
    }
    echo '</tr></tbody></table>';
    ////////////// Отпуск
?>
</div>
<?php
     //if(!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93)
     //{
        // if(!isset($_SESSION['bitAppFot']['DEPARTMENT'][32]) || $_SESSION['bitAppFot']['ID'] == 93)
      //   {
 ?>
        <div class="tab-pane fade" id="BAFbalance" style="font-size: 14px;padding:20px">
            <div class="float-right">Период: <select id="myAppFilter" disabled="disabled">
                                            <option value="year" selected="selected">Текущий год</option>
                                            <option value="all">За всё время</option>
                                            <option value="date">От ... до ...</option>
                                         </select><input type="text" id="myAppFilterDate1" style="display: none;width:110px; line-height:20px" autocomplete="off" placeholder="От" value=""><input type="text" id="myAppFilterDate2" style="display: none;width:110px; line-height:20px" autocomplete="off" placeholder="До" value="">
            </div>
            <div style="margin: 5px; padding: 5px; width: 99%; clear: both; border-bottom: 1px #ccc solid;">
                <canvas id="myAppChart" height="59"></canvas>
            </div>
            <div style="margin-left: 5px; padding: 5px; width: 29%; height: 300px; overflow: auto; float: left;">
                <strong class="text-danger">Выполненные задачи</strong><br><small>(<strong id="myAppTaskSum">0</strong> руб.)</small><br>
                <table style="font-size: 12px; width: 100%;" class="table-bordered">
                    <thead>
                        <tr><th>Дата</th><th>ФОТ</th><th>Задача</th></tr>
                    </thead>
                    <tbody id="myAppTask"><tr><td colspan="3" class="text-center"><img src="ldng.gif"></td></tr></tbody>
                </table>
            </div>
            <div style="margin-left: 1px; padding: 5px; width: 20%; height: 300px; overflow: auto; float: left;">
                <strong class="text-danger">Входящие переводы</strong><br><small>(<strong id="myAppTransSum">0</strong> руб.)</small><br>
                <table style="font-size: 12px; width: 100%;" class="table-bordered">
                    <thead>
                        <tr><th>Дата</th><th>Сумма</th><!--<th>Сотрудник</th>--><th>Комментарий</th></tr>
                    </thead>
                    <tbody id="myAppTrans"><tr><td colspan="3" class="text-center"><img src="ldng.gif"></td></tr></tbody>
                </table>
            </div>
            <div style="margin-left: 1px; padding: 5px; width: 20%; height: 300px; overflow: auto; float: left;">
                <strong class="text-danger">Исходящие переводы</strong><br><small>(<strong id="myAppTrans2Sum">0</strong> руб.)</small><br>
                <table style="font-size: 12px; width: 100%;" class="table-bordered">
                    <thead>
                        <tr><th>Дата</th><th>Сумма</th><!--<th>Сотрудник</th>--><th>Комментарий</th></tr>
                    </thead>
                    <tbody id="myAppTrans2"><tr><td colspan="3" class="text-center"><img src="ldng.gif"></td></tr></tbody>
                </table>
            </div>
            <div style="margin-left: 1px; padding: 5px; width: 15%; height: 300px; overflow: auto; float: left;">
                <strong class="text-danger">Начисления з/п</strong><br><small>(<strong id="myAppSalSum">0</strong> руб.)</small><br>
                <table style="font-size: 12px; width: 100%;" class="table-bordered">
                    <thead>
                    <?php
                    if($_SESSION['bitAppFot']['S_BALANCE'] == 1)
                        echo '<tr><th>Период</th><th>Сумма</th><th>Баланс</th></tr>';
                    else
                        echo '<tr><th>Период</th><th>Сумма</th></tr>';
                    ?>
                    </thead>
                    <tbody id="myAppSal"><tr><td colspan="3" class="text-center"><img src="ldng.gif"></td></tr></tbody>
                </table>
            </div>
            <div style="margin-left: 1px; padding: 5px; width: 15%; height: 300px; overflow: auto; float: left;">
                <strong class="text-danger">Выплаты</strong><br><small>(<strong id="myAppPaySum">0</strong> руб.)</small><br>
                <table style="font-size: 12px; width: 100%;" class="table-bordered">
                    <thead>
                    <tr><th>Период</th><th>Сумма</th></tr>
                    </thead>
                    <tbody id="myAppPay"><tr><td colspan="3" class="text-center"><img src="ldng.gif"></td></tr></tbody>
                </table>
            </div>
        </div>
        <?php
      //  }
         
        if(!empty($_SESSION['bitAppFot']['HEAD']))
        {
            
            echo '<div class="tab-pane fade" id="BAFSHlist" style="font-size: 14px;padding:20px">
                    <table class="table-hover">
                            <thead>
                                <th style="min-width:250px">Должность</th>
                                <th>Оклад</th>
                                <th style="min-width:100px; text-align:center;">Кол-во сотрудников</th>
                            </thead>
                            <tbody>';
            $d = 0;

            // unset($_SESSION['bitAppFot']['DEP_SHTAT']);
            if(!empty($_SESSION['bitAppFot']['DEP_SHTAT']))
            {
                $depLink = "/services/lists/70/element/0/2417677/?list_section_id=";
                 # =========== 08/07/2024 начало ============
                # вывод должностей управления, начало # $parentDeparts[$parentDepartsRes['ID']] = $parentDepartsRes;
                if(!empty($parentPositions)) {
                    // echo '<tr><td colspan="3" class="alert-info">Должности управления</td></tr>';
                } else {
                    // echo '<tr><td colspan="3" class="alert-info">У управления нет должностей</td></tr>';
                }

                foreach($parentPositions as $idPos => $dataPos) {
                    $oklad = number_format($dataPos['OKLAD']);
                    // echo '<tr><td>'.htmlspecialchars($dataPos['NAME']).' ('.$dataPos['PARENT_DEP_NAME'].')</td><td>'.$oklad.'</td><td style="min-width:100px; text-align:center;">'. $countPositionEmployees[$idPos].'</td></tr>';
                }
                # вывод должностей управления, конец #
                # =========== 08/07/2024 конец ============

                foreach($_SESSION['bitAppFot']['DEP_SHTAT'] as $dep => $dInfo)
                {
                    if($d != $dep)
                    {
                        echo '<tr><td colspan="3" class="alert-info">'.htmlspecialchars($_SESSION['bitAppFot']['DEP_NAME'][$dep]['dep']).'</td></tr>';
                    }
                    $d = $dep;

                    foreach($dInfo as $dInf)
                    {
                        $oklad = ($_SESSION['bitAppFot']['POS_OKL'] >= $dInf['oklad'] || $_SESSION['bitAppFot']['ID'] == 100) ? number_format($dInf['oklad'], 2, '.', ' ') : '';
                        # ===== 06/06/2024 ====
                        echo '<tr><td>'.htmlspecialchars($dInf['name']).'</td><td>'.$oklad.'</td><td style="min-width:100px; text-align:center;">'.$dInf['count'].'</td></tr>';
                        # ===== 06/06/2024 ====
                    }
                }
            }
            else
                //echo '<tr><td colspan="2" class="alert-danger">Список пуст</td></tr>';
                echo '<tr><td colspan="2" class="alert-danger">Вкладка временно отключена</td></tr>';

            echo '</tbody></table></div>';
            echo '<div class="tab-pane fade" id="BAFZplist" style="font-size: 14px;padding:20px"><img src="ldng.gif"></div>';

        ?>
             
            <div class="modal fade" id="ModalNewUser" tabindex="-1" role="dialog" aria-hidden="true">
                 <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="text-muted"><i class="fa fa-user"></i>&nbsp;&nbsp;<strong class="modal-title"> Новый сотрудник</strong></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <form action="/" id="addUserForm" method="post" onsubmit="return false">
                            <table style="width: 100%">
                                <tr><td style="width:25%">Фамилия</td><td><input type="text" id="APPlastName" name="APPlastName" autocomplete="off"></td></tr>
                                <tr><td>Имя</td><td><input type="text" id="APPfirstName" name="APPfirstName" autocomplete="off"></td></tr>
                                <tr><td>Отчество</td><td><input type="text" id="APPsecondName" name="APPsecondName" autocomplete="off"></td></tr>
                                <tr><td>Дата рождения</td><td><input type="text" id="APPBday" name="APPBday" autocomplete="off" placeholder="дд.мм.гггг"></td></tr>
                                <tr><td colspan="2"><hr></td></tr>
                                <tr><td>E-mail</td><td><input type="email" id="APPmail" name="APPmail" autocomplete="off" placeholder="example@mail.ru" oninput="validateEmail(this)"></td></tr>
                                <tr><td>Номер телефона</td><td>+7 <input type="text" id="APPphone" style="width:162px" name="APPphone" autocomplete="off" pattern="[0-9]{10}" placeholder="9999999999" oninput="formatPhone(this)"></td></tr>
                                <tr><td colspan="2"><hr></td></tr>
                                <tr><td>Должность</td><td>
                                    <select id="APPdepartment" name="APPdepartment">
                                    <?php
                                        $d = 0;
                                        if(!empty($_SESSION['bitAppFot']['DEP_SHTAT']))
                                        {
                                            echo '<option></option>';

                                            # ======= 06/06/2024  начало========
                                            # вывод должностей управления, начало #
                                            if(!empty($parentPositions)) {
                                                // echo '<optgroup label="Должности управления">';
                                            }

                                            foreach($parentPositions as $idPos => $dataPos) {
                                                // echo '<option value="'.$idPos.'|'.$dataPos['PARENT_DEP_ID'].'">'.htmlspecialchars($dataPos['NAME']).' ('.number_format($dataPos['OKLAD'], 2, '.', ' ').')</option>';
                                            }

                                            // echo '</optgroup>';
                                            # вывод должностей управления, конец #
                                            # ======= 06/06/2024 конец ========
                                            foreach($_SESSION['bitAppFot']['DEP_SHTAT'] as $dep => $dInfo)
                                            {
                                                if($d != $dep)
                                                {
                                                    echo '<optgroup label="'.htmlspecialchars($_SESSION['bitAppFot']['DEP_NAME'][$dep]['dep']).'">';
                                                }
                            
                                                foreach($dInfo as $di => $dInf)
                                                {
                                                    $oklad = ($_SESSION['bitAppFot']['POS_OKL'] >= $dInf['oklad'] || $_SESSION['bitAppFot']['ID'] == USER_SLUNIAEV) ? $dInf['oklad'] : 0;
                                                    echo '<option value="'.$di.'|'.$dep.'">'.htmlspecialchars($dInf['name']).' ('.number_format($oklad, 2, '.', ' ').')</option>';
                                                }

                                                if($d != $dep)
                                                    echo '</optgroup>';

                                                $d = $dep;
                                            }
                                        }
                                        else
                                            echo '<option>Список пуст</option>';
                                    ?>
                                    </select>
                                </td></tr>
                                <tr><td>Рабочий график</td><td><select id="APPwork" name="APPwork"><option></option>
                                    <?php
                                        $sql = $db->query("SELECT `ID`, `NAME` FROM `b_timeman_work_schedule` ORDER BY `ID` ASC");
                                        if($sql->num_rows > 0)
                                        {
                                            while($result = $sql->fetch_assoc())
                                            {
                                                echo '<option value="'.$result['ID'].'">'.$result['NAME'].'</option>';
                                            }
                                        }
                                    ?>
                                    </select>
                                </td></tr>
                                <tr><td>Система работы сотрудника</td><td><select id="APPSys" name="APPSys" required><option></option>
                                    <?php
                                        $sql = $db->query("SELECT `ID`, `VALUE` FROM `b_user_field_enum` WHERE USER_FIELD_ID=".POLZOVATELI_SYSTEM_WORK_ENUM);//Добавление всего tr этого и снизу пол
                                        if($sql->num_rows > 0)
                                        {
                                            while($result = $sql->fetch_assoc())
                                            {
                                                echo '<option value="'.$result['ID'].'">'.$result['VALUE'].'</option>';
                                            }
                                        }
                                    ?>
                                    </select>
                                </td></tr>
                                <tr><td>Пол</td><td><select id="APPGend" name="APPGend"><option value="">не заполнено</option><option value="M">Мужской</option><option value="F">Женский</option>
                                    </select>
                                </td></tr>
                            </table>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button>
                            <button type="button" class="btn btn-sm btn-success" id="addUserBtn" onclick="addUser()">Принять</button>
                        </div>
                    </div>
                 </div>
            </div>
            <div class="modal fade" id="ModalSetZP" tabindex="-1" role="dialog" aria-hidden="true">
                 <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="text-muted"><i class="fa fa-files-o"></i>&nbsp;&nbsp;<strong class="modal-title"> Начисление</strong></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <h4 id="labelUser"></h4>
                            <p>Выберите период, за который производится начисление:<select id="setZPmonth" name="setZPmonth"></select><select id="setZPyear" name="setZPyear"></select></p>
                            <input type="hidden" style="display:none" id="setZPuser" value="0">
                            <div class="hide" style="display: none;" id="sdelForm">
                                <p>Введите сумму оклада: <input type="text" id="setZPsdel" value="0" style="width: 80px;"> <button type="button" class="btn btn-sm btn-success" id="SetSdelBtn" onclick="setSdel()">Начислить</button></p>
                            </div>
                            <div class="hide" style="display: none;" id="premForm">
                                Введите сумму премии: <input type="text" id="setZPprem" value="0" style="width: 80px;"> <button type="button" class="btn btn-sm btn-success" id="SetPremBtn" onclick="setPrem()">Начислить</button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button>
                            <button type="button" class="btn btn-sm btn-warning" onclick="$('#premForm').show(200)">Премия</button>
                            <button type="button" class="btn btn-sm btn-success" id="SetZpBtn" onclick="setZp()">Оклад</button>
                        </div>
                    </div>
                 </div>
            </div>
            <div class="modal fade" id="modalErrors" tabindex="-1" role="dialog" aria-hidden="true">
                 <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="text-muted"><i class="fa fa-questions"></i>&nbsp;&nbsp;<strong class="modal-title"> Ограничения при начислении зарплаты</strong></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div id="modalErrorsContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button>
                        </div>
                    </div>
                 </div>
            </div>
             <?php
        } 
     //}
        // ======= 09/07/2024 начало =======
        function getEmploeesNameOnPosition($db, $idPos) {
            $names = '';

            $employeesList = array();
            $employeesList_Q = "SELECT 
                                `b_user`.`ID` AS 'ID',
                                `b_user`.`NAME` AS 'NAME', 
                                `b_user`.`LAST_NAME` AS 'LAST_NAME', 
                                `b_user`.`SECOND_NAME` AS 'SECOND_NAME',
                                `b_uts_user`.`UF_ID_POSITION` AS 'POSITION_ID'
                                FROM b_uts_user
                                LEFT JOIN b_user ON `b_user`.`ID` = `b_uts_user`.`VALUE_ID`
                                WHERE `b_uts_user`.`UF_ID_POSITION` = $idPos AND `b_user`.`ACTIVE` = 'Y'";
            $employeesList_res = $db->query($employeesList_Q);

            while($employeesData = $employeesList_res->fetch_assoc()) {
                $employeesList[$employeesData['ID']] = $employeesData;
            }

            foreach($employeesList as $employeeID => $employee) {
                $lastName = $employee['LAST_NAME'];
                $name = mb_str_split($employee['NAME'])[0] . '.';
                if($employeeID == array_key_last($employeesList)) {
                    $names .= $lastName . ' '. $name;
                } else {
                    $names .= $lastName . ' ' . $name . ', ';
                }
                
            }

            return $names;

        }
        // ======= 09/07/2024 конец =======

        # Штатное расписание для Ровнова А. "Костыль"
        // ======= 09/07/2024 =======
        if(!empty($_SESSION['bitAppFot']['HEAD']) && $_SESSION['bitAppFot']['ID'] == USER_ROVNOV_A)
        {
            echo '<div class="tab-pane fade" id="BAFSHlist_r" style="font-size: 14px;padding:20px">
                    <table class="table-hover">
                        <thead>
                            <th style="min-width:250px">Должность</th>
                            <th>Оклад</th>
                            <th style="min-width:60px; max-width:80px;  text-align:center;">Кол-во сотрудников</th>
                            <th style="padding: 0; min-width:120px; text-align:center;">Список сотрудников</th>
                        </thead>
                        <tbody>';
            $d = 0;
            if(!empty($_SESSION['bitAppFot']['DEP_SHTAT']))
            {
                
                # вывод должностей управления, начало # $parentDeparts[$parentDepartsRes['ID']] = $parentDepartsRes;
                if(!empty($parentPositions)) {
                    echo '<tr><td colspan="4" class="alert-info">Должности управления</td></tr>';
                } else {
                    echo '<tr><td colspan="4" class="alert-info">У управления нет должностей</td></tr>';
                }

                foreach($parentPositions as $idPos => $dataPos) {
                    $posLink = "/services/lists/70/element/0/" . $idPos . "/";
                    $oklad = number_format($dataPos['OKLAD']);

                    $db = new mysqli(DB_HOST, DB_USER_BD, DB_PASSWORD, DB_NAME);
                    $db->set_charset('utf8');

                    $employeesName = getEmploeesNameOnPosition($db, $idPos);

                    echo '<tr>
                            <td>
                                <a href='. $posLink . ' target="_blank">'.htmlspecialchars($dataPos['NAME']).' ('.$dataPos['PARENT_DEP_NAME'].')</a>
                            </td>
                            <td>'.$oklad.'</td>
                            <td style="min-width:100px; text-align:center;">'. $countPositionEmployees[$idPos].'</td>
                            <td style="padding: 5px 0; min-width:120px;">' . $employeesName . '</td>
                        </tr>';
                }
                # вывод должностей управления, конец #

                foreach($_SESSION['bitAppFot']['DEP_SHTAT'] as $dep => $dInfo)
                {
                    if($d != $dep)
                    {
                        echo '<tr><td colspan="4" class="alert-info">'.htmlspecialchars($_SESSION['bitAppFot']['DEP_NAME'][$dep]['dep']).'</td></tr>';
                    }
                    $d = $dep;

                    foreach($dInfo as $depID => $dInf)
                    {
                        $posLink = "/services/lists/70/element/0/" . $depID . "/";
                        // $oklad = ($_SESSION['bitAppFot']['POS_OKL'] >= $dInf['oklad'] || $_SESSION['bitAppFot']['ID'] == 100) ? number_format($dInf['oklad'], 2, '.', ' ') : '';
                        $oklad = $dInf['oklad'];

                        $employeesName = getEmploeesNameOnPosition($db, $depID);
                        echo '<tr>
                                <td><a href='. $posLink . ' target="_blank">'.htmlspecialchars($dInf['name']).'</a></td>
                                <td>'.$oklad.'</td>
                                <td style="min-width:100px; text-align:center;">'.$dInf['count'].'</td>
                                <td style="padding: 5px 10px; min-width:120px;">' . $employeesName . '</td>
                            </tr>';
                    }
                }
            }
            else
                echo '<tr><td colspan="2" class="alert-danger">Список пуст</td></tr>';

            echo '</tbody></table></div>';
        }
        // ======= 09/07/2024 =======
        ?>
    </div>
    <?php
    if(!empty($myErrors))
    {
    ?>
    <div class="modal fade" id="modalMyErrors" tabindex="-1" role="dialog" aria-hidden="true">
                 <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="text-muted"><i class="fa fa-questions"></i>&nbsp;&nbsp;<strong class="modal-title"> Ограничения начисления зарплаты</strong></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div id="modalMyErrorsContent">
                            <?php
                            if(!empty($myErrors['reb'][date('Y-m')]))
                            {
                                echo '<p><strong>Выговор</strong></p><br>';
                            }

                            if(!empty($myErrors['dealRow']))
                            {
                                echo '<p><strong>Сделки с незаполненными полями</strong></p>';
                                foreach($myErrors['dealRow'] as $id => $title)
                                {
                                    echo '<div><a target="_blank" href="/crm/deal/details/'.(int)$id.'/">'.htmlspecialchars($title).'</a></div>';
                                }
                                echo '<br>';
                            }

                            if(!empty($myErrors['mtr']))
                            {
                                echo '<p><strong>МТР на инвентаризации</strong></p>';
                                foreach($myErrors['mtr'] as $id => $title)
                                {
                                    echo '<div><a target="_blank" href="/crm/type/'.CRM_MTR_ID.'/details/'.(int)$id.'/">'.htmlspecialchars($title).'</a></div>';
                                }
                                echo '<br>';
                            }

                            if(!empty($myErrors['avr']))
                            {
                                echo '<p><strong>АВР на стадии "Документы не предоставлены"</strong></p>';
                                foreach($myErrors['avr'] as $id => $title)
                                {
                                    echo '<div><a target="_blank" href="/crm/type/'.CRM_AVR_ID.'/details/'.(int)$id.'/">'.htmlspecialchars($title).'</a></div>';
                                }
                                echo '<br>';
                            }
                            
                            if(!empty($myErrors['invoice']))
                            {
                                echo '<p><strong>Счета на оплату на стадии "Документы не собраны"</strong></p>';
                                foreach($myErrors['invoice'] as $id => $title)
                                {
                                    echo '<div><a target="_blank" href="/crm/type/128/details/'.(int)$id.'/">'.htmlspecialchars($title).'</a></div>';
                                }
                                echo '<br>';
                            }

                            if(!empty($myErrors['invoicep']))
                            {
                                echo '<p><strong>Счета на стадии "Просрочено"</strong></p>';
                                foreach($myErrors['invoicep'] as $id => $title)
                                {
                                    echo '<div><a target="_blank" href="/crm/type/159/details/'.(int)$id.'/">'.htmlspecialchars($title).'</a></div>';
                                }
                                echo '<br>';
                            }

                            
                            if(!empty($myErrors['avr']))
                            {
                                echo '<p><strong>Не переданные АВР</strong></p>';
                                foreach($myErrors['avr'] as $id => $title)
                                {
                                    echo '<div><a target="_blank" href="/crm/type/'.CRM_AVR_ID.'/details/'.(int)$id.'/">'.htmlspecialchars($title).'</a></div>';
                                }
                                echo '<br>';
                            }
                            

                            if(!empty($myErrors['tasks']))
                            {
                                echo '<p><strong>Просроченные задачи</strong></p>';
                                foreach($myErrors['tasks'] as $id => $title)
                                {
                                    echo '<div><a target="_blank" href="/company/personal/user/'.(int)$_SESSION['bitAppFot']['ID'].'/tasks/task/view/'.(int)$id.'/">'.htmlspecialchars($title).'</a></div>';
                                }
                                echo '<br>';
                            }

                            if(!empty($myErrors['dealDate']))
                            {
                                echo '<p><strong>Просроченные сделки</strong></p>';
                                foreach($myErrors['dealDate'] as $id => $title)
                                {
                                    echo '<div><a target="_blank" href="/crm/deal/details/'.(int)$id.'/">'.htmlspecialchars($title).'</a></div>';
                                }
                                echo '<br>';
                            }

                            if(!empty($myErrors['schedule']))
                                echo '<p><strong>Не установлен рабочий график</strong></p>';


                            ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button>
                        </div>
                    </div>
                 </div>
            </div>
    <?php
    }
    ?>
</body>
</html>