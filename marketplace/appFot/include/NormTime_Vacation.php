<?php

use Bitrix\Intranet\UserAbsence;

function getVacation($db, $id)
{
    $ret = array();
    $ret['days'] = 14;
    $ret['addDaysCount'] = 0;
    $isEmp = 0;

    $days = -14;
    $date_register = '2021-01-01';
    if($id > 0)
    {
        // Дата приема на работу
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

        // Выходные дни битрикс
        $holydaysYearQuery = $db->query("SELECT VALUE FROM b_option WHERE NAME = 'year_holidays' AND MODULE_ID = 'calendar'")->fetch_assoc();
        $holydaysYear = explode(",", $holydaysYearQuery["VALUE"]);

        foreach($holydaysYear as $key => $holydayYear) {
            $holydaysYear[$key] = date("Y-m-d", strtotime($holydayYear . "." . date("Y")));
        }
        $holydaysYear = array_merge($holydaysYear, HOLYDAYS_2024); // + за 2024 год
        $ret['holydaysYear'] = $holydaysYear;

        // Отсутствия из интранет
        $absence = [];
        $ret['absName'] = array();
        $absenceList = UserAbsence::getVacationTypes();
        foreach ($absenceList as $item) {
            if (VACATION_FROM_INTRANET === true && $item['ACTIVE'] === true || VACATION_FROM_INTRANET === false && in_array($item['ID'], VACATION_LIST)) {
                $absence[] = $item['ENUM_ID'];
                $ret['absName'][] = $item['NAME'];
            }
        }

        // SQL строка для поиска отпуска
        $absenceSql = "";
        $countAbsence = count($absence);
        for ($i = 0; $i < $countAbsence; $i++) {
            $absenceSql .= $absence[$i];

            if ($i < $countAbsence - 1) {
                $absenceSql .= ", ";
            }
        }
        
        // Сокращенные рабочие дни
        $totalShortDays = [];
        $sql = $db->query("SELECT `td`.`VALUE` as `DATE`
                        FROM `b_iblock_element_property` as `td`
                        WHERE `td`.`IBLOCK_PROPERTY_ID` = ".SHORT_WORK_DAY_DATE);
        while($res = $sql->fetch_assoc()) {
            $totalShortDays[] = date("Y-m-d", strtotime($res['DATE']));
        }
        $ret['totalShortDays'] = $totalShortDays;

        // Поиск отпусков
        if ($absenceSql != "") {
            $addDaysCount = 0;
            $sql = $db->query("SELECT `e`.`ID`, `e`.`NAME`, `e`.`ACTIVE_FROM`, `e`.`ACTIVE_TO`, DATEDIFF(`e`.`ACTIVE_TO`, `e`.`ACTIVE_FROM`) +1 AS `days`, `p2`.`VALUE` AS `type`
                           FROM `b_iblock_element` AS `e`
                           LEFT JOIN `b_iblock_element_property` AS `p1` ON `p1`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p1`.`IBLOCK_PROPERTY_ID` = ".IB_GRAFIK_OTSUTSTVIY_POLZOVATEL."
                           LEFT JOIN `b_iblock_element_property` AS `p2` ON `p2`.`IBLOCK_ELEMENT_ID` = `e`.`ID` AND `p2`.`IBLOCK_PROPERTY_ID` = ".IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA."
                           WHERE `e`.`IBLOCK_ID` = ".IB_GRAFIK_OTSUTSTVIY." AND `e`.`ACTIVE_FROM` >= '".$date_register."'
                           AND `p2`.`VALUE` IN (" . $absenceSql . ") AND `p1`.`VALUE` = ".(int)$id."
                           ORDER BY `e`.`ACTIVE_FROM` ASC");
            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc())
                {
                    $days += $result['days'];
                    $plusDays = 0;

                    // Отсутствия в праздники и в сокращенные рабочие дни
                    $begin = new DateTime($result['ACTIVE_FROM']);
                    $end   = new DateTime($result['ACTIVE_TO']);
                    for($i = $begin; $i <= $end; $i->modify('+1 day')){
                        if (in_array($i->format('Y-m-d'), $holydaysYear)) {
                            $addDaysCount++;
                            $plusDays++;
                        
                            if (ABSENCE_SHORT_WORK_DAY === true && in_array($i->format('Y-m-d'), $totalShortDays)) {
                                $addDaysCount--;
                                $plusDays--;
                            }
                        }
                    }

                    $result['plusDays'] = $plusDays;
                    $ret['info'][date('Y', strtotime($result['ACTIVE_FROM']))][$result['ID']] = $result;
                }

                $ret['addDaysCount'] = $addDaysCount;
            }
        }
    }
    $date1 = new DateTime($date_register);
    $date2 = new DateTime();
    $year0 = 365;
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
        $ret['message'] = ' - Превышено количество доступных дней отпуска.';
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
        if($dateDiff > $year0 / 2){
            $dateDiff = intval($year0 - $dateDiff);
        } else{
            $dateDiff = intval($year0 / 2 - $dateDiff);
        }
    }
    $ret['start'] = $date_register;
    $ret['add'] = $dateDiff;
    $ret['daysadd'] = $colDays;
    return $ret;
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
    $return['shortWorkDays'] = ""; // Add shortWorkTime by WolfHound (27.11.2024)
    $return['shortWorkDaysCount'] = 0; // Add shortWorkTime by WolfHound (27.11.2024)

    $firstDay = date('Y-m-d', strtotime('first day of now'));
    $start    = date('Y-m-d', strtotime('first day of now'));
    //$stop     = date('d.m.Y', strtotime('last day of now'));
    $absence  = 0;
    $works    = 0;
    $norm     = 0;
    $holyday  = 0;
    $currDay  = 0;

    // Сокращенные рабочие дни
    $shortWorkTime = 0;
    $shortWorkDaysCount = 0;
    $totalShortDays = [];
    $sql = $db->query("SELECT `td`.`VALUE` as `DATE`, `tt`.`VALUE` as `TIME`
                       FROM `b_iblock_element_property` as `td`
                       INNER JOIN `b_iblock_element_property` as `tt` ON `tt`.`IBLOCK_ELEMENT_ID` = `td`.`IBLOCK_ELEMENT_ID` AND `tt`.`IBLOCK_PROPERTY_ID` = ".SHORT_WORK_DAY_TIME."
                       WHERE `td`.`IBLOCK_PROPERTY_ID` = ".SHORT_WORK_DAY_DATE);
    while($res = $sql->fetch_assoc()) {
        $shortDate = date("Y-m-d", strtotime($res['DATE']));
        $totalShortDays[$shortDate] = $res['TIME'];
    }

    // График работы сотрудника
    $sql = $db->query("SELECT `s`.`NAME`, `t`.`SCHEDULE_ID`
                       FROM `b_timeman_work_schedule_user` AS `t`
                       LEFT JOIN `b_timeman_work_schedule` AS `s` ON `s`.`ID` = `t`.`SCHEDULE_ID`
                       WHERE `t`.`STATUS` = 0 AND `t`.`USER_ID` = ".(int)$id);
    if($sql->num_rows > 0)
    {
        $result = $sql->fetch_assoc();
        $return['schedule'] = $result['NAME'];
        $return['schedule_id'] = $result['SCHEDULE_ID'];
        
        if($result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_REMOTE || $result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_FACE || $result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_BROWSER || $result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_WIFI) # for test @|| $result['SCHEDULE_ID'] == TIMEMAN_SCHEDULE_BROWSER@
        {
            $sql = $db->query("SELECT `VALUE_ID`, `".POLZOVATELI_DATA_NACHALA_RABOTY."` FROM `b_uts_user` WHERE `VALUE_ID` =".(int)$id);
            if($sql->num_rows > 0)
            {
                $result = $sql->fetch_assoc();
                $return['showInfo'] = 1;
                
                if(date('Y-m') == date('Y-m', strtotime($result[POLZOVATELI_DATA_NACHALA_RABOTY]))) {
                    // $start = $result[POLZOVATELI_DATA_NACHALA_RABOTY];
                    $return['isFirstMonth'] = true; // === 02/08/2024 ===
                    $firstDay = $result[POLZOVATELI_DATA_NACHALA_RABOTY]; // === 02/08/2024 ===
                }
                    
            }
        }
    }

    // Если установлен график уд. раб. стол или facecontrole или браузер или wi-fi и установлена дата принятия на работу
    if($return['showInfo'] == 1)
    {
        $forStop = date('d', strtotime('last day of now'));
        $dayStop = date('d');

        // Праздники из календаря
        $holydaysYearQuery = $db->query("SELECT VALUE FROM b_option WHERE NAME = 'year_holidays' AND MODULE_ID = 'calendar'")->fetch_assoc();
        $holydaysYear = explode(",", $holydaysYearQuery["VALUE"]);

        foreach($holydaysYear as $key => $holydayYear) {
            $holydaysYear[$key] = date("Y-m-d", strtotime($holydayYear . "." . date("Y")));
        }

        // До конца месяца
        for($i = 1; $i <= $forStop; $i++)
        {
            if(date('Y-m-'.$i) >= $start)
            {
                // Правильный формат даты
                $day  = date('N', strtotime(date('Y-m-'.$i)));
                $tempDate = date('Y-m-'.$i);
                if ($i < 10) {
                    $tempDate = date('Y-m-0'.$i);
                }
                
                // Если выходной или праздник не в сокращенный рабочий день, то +праздник иначе рабочий день
                if(($day == 6 || $day == 7 || in_array($tempDate, $holydaysYear)) && (ABSENCE_SHORT_WORK_DAY === false || empty($totalShortDays[$tempDate]))) {
                    $holyday++;
                } else {
                    $norm++;

                    $return['normWorkDay']++; // разраб
                    
                    // Add shortWorkTime by WolfHound (27.11.2024) \\
                    if (isset($totalShortDays[$tempDate])) {
                        $shortWorkTime += $totalShortDays[$tempDate];
                        $shortWorkDaysCount++;
                        $return['shortWorkTimeOfDays'][] = $totalShortDays[$tempDate];
                    }
                    // ===== \\

                    if($return['isFirstMonth'] && strtotime(date('Y-m-'.$i)) >= strtotime($firstDay) && !isset($totalShortDays[$tempDate])) {
                        $return['normDayFirstMonth']++; // === 02/08/2024 === // разраб
                    }

                    if($i < $dayStop && !isset($totalShortDays[$tempDate]))
                    {
                        $currDay++;
                    }
                }
            }
        }


        // Строка в "Рабочих дней (в месяце)"
        $return['shortWorkDaysCount'] = $shortWorkDaysCount;
        if ($shortWorkDaysCount > 0) {
            if ($shortWorkDaysCount == 1) {
                $return['shortWorkDays'] = ", из которых ".$shortWorkDaysCount." сокращенный";
            } else {
                $return['shortWorkDays'] = ", из которых ".$shortWorkDaysCount." сокращенных";
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
                            if($day != 6 && $day != 7 && !in_array(date('Y-m-'.$ii), $holydaysYear) || ABSENCE_SHORT_WORK_DAY === true && !empty($totalShortDays[date('Y-m-'.$ii)]))
                            {
                                if (date('Y-m-'.$ii) <= date('Y-m-d')) {
                                    $absence++;
                                    $currDay--;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $return['normD'] = $norm - $absence - $shortWorkDaysCount; // Add shortWorkTime by WolfHound (27.11.2024) ($norm - $absence)
        $return['normAllD'] = $norm;
        $return['dur']   = $duration;
        $return['abs']   = $absence;
        $return['normH'] = $return['normD'] * $duration;
        $return['normAllH'] = ($norm - $absence) * $duration;
        
        if($return['isFirstMonth']) {
            $return['currH'] = $return['normDayFirstMonth'] * $duration + $shortWorkTime;
        } else {
            $return['currH'] = $currDay * $duration + $shortWorkTime;
        }
    }

    // echo "<pre>".print_r($return, true)."</pre>";

    return $return;
}