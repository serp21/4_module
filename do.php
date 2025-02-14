<?

function getList(
    $list = 0, 
    $select = array(), 
    $filter = array(), 
    $order = 'ID', 
    $start = 0, 
    $content = array()
    )

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


$qIn   = getList(
    30, 
    array(
        'PROPERTY_'.IB_PEREVODY_DATA_PEREVODA, 
        'PROPERTY_'.IB_PEREVODY_SUMMA, 
        'PROPERTY_'.IB_PEREVODY_KOMMENTARIY),
     array(
        'PROPERTY_'.IB_PEREVODY_POLUCHATEL => $_SESSION['bitAppFot']['ID'], 
        '>=PROPERTY_'.IB_PEREVODY_DATA_PEREVODA => $start, 
        '<=PROPERTY_'.IB_PEREVODY_DATA_PEREVODA => $stop), 
        'PROPERTY_'.IB_PEREVODY_DATA_PEREVODA
    );








    то есть че надо будет

    нарисовать табличку

    взять данные из иб фот

    и форычнуть их как в тестовом задании?

    и вывести под табличку



    CModule::IncludeModule("im");
    CModule::IncludeModule("iblock");
    CModule::IncludeModule("crm");
    
    $deal_id = "{{ID}}";
    $assigned = "{{Кем изменен}}";
    $assigned_id = explode("_", $assigned)[1];
    
    $iblockId = 172;
    
    $arFilter = array(
        "IBLOCK_ID" => $iblockId,
        "ACTIVE" => "Y",
        "PROPERTY_DEAL" => $deal_id
    );
    
    $arSelect = array(
        "ID",
        "NAME",
        //"PROPERTY_DESC",
        "PROPERTY_PRICE"
    //	, "PROPERTY_COUNT"
    //	, "PROPERTY_DEAL"
    );
    
    $res = CIBlockElement::GetList(
        [],
        $arFilter,
        false,
        false,
        $arSelect
    );
    
    $resOut = array();
    $totalPrice = 0;
    
    $dealName = "{{Название}}";
    
    $pList = "Сделка: " . $dealName . "\n";
    $pList .= "Список товаров:\n";
    
    while ($Element = $res->Fetch()) {
        $itemName = $Element['NAME'];
    //    $itemDesc = $Element['PROPERTY_DESC_VALUE'];
        $itemPrice = $Element['PROPERTY_PRICE_VALUE'];
    //    $itemCount = $Element['PROPERTY_COUNT_VALUE'];
    //	$itemDeal = $Element['PROPERTY_DEAL_VALUE'];
    
        $totalPrice += $itemPrice;
    
        //$resOut[] = "Наименование: $itemName, Описание: $itemDesc, Цена: $itemPrice, Количество: $itemCount";
        $pList .= $itemName . "\n";
    }
    
    //$pList = "Сделка № " . $deal_id . "\n";
    
    //$pList .= !empty($resOut) ? implode("\n", $resOut) : "Нет товаров";
    
    $pList .= "Общая стоимость: " . $totalPrice;
    
    echo "<pre>"; // Чтобы вывод был более читаемым
    print_r('12321');
    print_r($pList);
    echo "</pre>";
    
    $arMessageFields = array(
        "NOTIFY_TYPE" => IM_NOTIFY_FROM,
        "FROM_USER_ID" => $assigned_id,
        "TO_USER_ID" => $assigned_id,
        "NOTIFY_MESSAGE" => $pList,
        "NOTIFY_MODULE" => "bizproc",
        "NOTIFY_EVENT" => "activity"
    );
    
    CIMNotify::Add($arMessageFields);


/////////////////////////////////////////////////////////////////////////////


    $iblockId = 149;
    
    $arFilter = array(
        "IBLOCK_ID" => $iblockId,
        "ACTIVE" => "Y",
        "PROPERTY_DEAL" => $deal_id
    );
    
    $arSelect = array(
        //"ID",
        "TIMESTAMP_X",
        702, //worker
        "NAME",
        701, //task
        700
    );


    $totalBalance;


    
    function getList(
            $list = 0, 
            $select = array(), 
            $filter = array(), 
            $order = 'ID', 
            $start = 0, 
            $content = array()
        )


        $htmlTable .= '<td><a href="' . BITRIX_DOMAIN . 'company/personal/user/' . $idUser . '/">'.$idUser.'</a></td>';

<a href=""></a>


$testTable = getList(
    149
);

foreach ($testTable as $row) {
    print_r($row);
    echo "<br>";
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


?>






    <li class="nav-item"><a href="#BAFhistory" class="nav-link" data-toggle="tab">История изменения баланса</a></li>

    <div class="tab-pane fade" id="BAFhistory" style="font-size: 14px;padding:20px">

    <div class="mytarget" style="display: none;">

        <?php    
        $historyTable   = getList(
            30,
            array(
                "TIMESTAMP_X",
                702, //worker
                "NAME",
                701, //task
                700
            ),
            array(),
            'TIMESTAMP_X', // Изменено на 'TIMESTAMP_X'
            0
        );


        $htmlTable = '<table border="1">';
        $htmlTable .= '<tr>';
        $htmlTable .= '<th>Дата последнего изменения</th>';
        $htmlTable .= '<th>Идентификатор работника (worker)</th>';
        $htmlTable .= '<th>Название</th>';
        $htmlTable .= '<th>Идентификатор задачи (task)</th>';
        $htmlTable .= '<th>Идентификатор дополнительного поля</th>';
        $htmlTable .= '</tr>';

        foreach ($historyTable as $row) {
            $htmlTable .= '<tr>';
            $htmlTable .= '<td>'.$row['TIMESTAMP_X'].'</td>';
            $htmlTable .= '<td>'.$row['702'].'</td>';
            $htmlTable .= '<th>'.$row['NAME'].'</th>';
            $htmlTable .= '<td>'.$row['701'].'</td>';
            $htmlTable .= '<td>'.$row['700'].'</td>';

            $totalBalance = + 701;

            $htmlTable .= '<td>'.$row[$totalBalance].'</td>';
            $htmlTable .=('</tr>');


        }

        $htmlTable .= '</table>';

        echo $htmlTable;
        ?>

    </div>

 


<table>
    <thead>
      <tr>
        <th>Дата и время</th>
        <th>Сотрудник</th>
        <th>Событие</th>
        <th>Задача</th>
        <th>ФОТ события</th>
        <th>Текущий баланс</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>2023-10-27 10:00:00</td>
        <td>Иванов И.И.</td>
        <td>Начисление зарплаты</td>
        <td>Выплата зарплаты за сентябрь</td>
        <td>50000</td>
        <td>100000</td>
      </tr>
      <tr>
        <td>2023-10-27 11:00:00</td>
        <td>Петров П.П.</td>
        <td>Оплата счета</td>
        <td>Оплата счета за интернет</td>
        <td>-5000</td>
        <td>95000</td>
      </tr>
      <!-- Здесь можно добавить другие строки данных -->
    </tbody>
  </table>







  <div class="mytarget" style="display: none;">

  <?php    
      $historyTable = getList(
          149,
          array(
              "CHANGED",
              "PROPERTY_702", //worker
              "NAME",
              "PROPERTY_701", //task
              "PROPERTY_700"
          ),
          array(),
          'TIME',
          0
      );

      $totalBalance = 0;

      $htmlTable = '<table border="1">';
      $htmlTable .= '<tr>';
      $htmlTable .= '<th>Дата и время</th>';
      $htmlTable .= '<th>Сотрудник</th>';
      $htmlTable .= '<th>Событие</th>';
      $htmlTable .= '<th>Задача</th>';
      $htmlTable .= '<th>ФОТ события</th>';
      $htmlTable .= '<th>Текущий баланс</th>';
      $htmlTable .= '</tr>';

      foreach ($historyTable as $row) {
          $htmlTable .= '<tr>';
              $htmlTable .= '<td>' . htmlspecialchars($row['CHANGED'], ENT_QUOTES, 'UTF-8') . '</td>';

              $idUser = array_shift($row['PROPERTY_702']);

              if ($idUser) {

                  $idUserSafe = htmlspecialchars($idUser, ENT_QUOTES, 'UTF-8');
                  $rsUser = CUser::GetByID($idUser);
                  $arUser = $rsUser->Fetch();

                  if ($arUser) {

                      $userProfileUrl = BITRIX_DOMAIN . "company/personal/user/" . $idUserSafe . "/";
                      $userName = htmlspecialchars($arUser["NAME"] . " " . $arUser["LAST_NAME"], ENT_QUOTES, 'UTF-8');

                      $htmlTable .= '<td><a href="' . $userProfileUrl . '">' . $userName . '</a></td>';

                  } else {
                      $htmlTable .= '<td>Пользователь не найден (ID: ' . $idUserSafe . ')</td>';
                  }
              } else {
                  $htmlTable .= '<td>ID пользователя не указан</td>';
              }

              $htmlTable .= '<td>'.$row['NAME'].'</td>';

              $htmlTable .= '<td>'.array_shift($row['PROPERTY_701']).'</td>';
              
              $fotValue = array_shift($row['PROPERTY_700']);

              $htmlTable .= '<td>'.$fotValue.'</td>'; // Выводим ФОТ событие

              $totalBalance += $fotValue;

              $htmlTable .= '<td>'.$totalBalance.'</td>'; // Выводим текущий баланс
          $htmlTable .= '</tr>';
      }

      $htmlTable .= '</table>';

      echo $htmlTable;

      $testTable = getList(
          149
      );

      foreach ($testTable as $row) {
          print_r($row);
          echo "<br>";
      }
      ?>

  </div>




  echo $_SESSION['bitAppFot']['user'];



  для даты селект из бд


  $dateSelect = "SELECT DATE_ACTIVE_FROM FROM b_iblock_element WHERE IBLOCK_ID = 149 AND PROPERTY_702 = " . (int)$userIdToFilter;

  $rDateSelect = $connection->query($dateSelect);









            
            $historyTable = getList(
                149,
                array(
                    "DATE_ACTIVE_FROM",
                    "PROPERTY_702", //worker
                    "NAME",
                    "PROPERTY_701", //task
                    "PROPERTY_700"
                ),
                array(
                    "PROPERTY_702" => $userIdToFilter
                )
            );

            $totalBalance = 0;

            $htmlTable = '<table border="1">';
            $htmlTable .= '<tr>';
            $htmlTable .= '<th>Дата и время</th>';
            $htmlTable .= '<th>Сотрудник</th>';
            $htmlTable .= '<th>Событие</th>';
            $htmlTable .= '<th>Задача</th>';
            $htmlTable .= '<th>ФОТ события</th>';
            $htmlTable .= '<th>Текущий баланс</th>';
            $htmlTable .= '</tr>';

            foreach ($historyTable as $row) {
                $htmlTable .= '<tr>';
                    //$htmlTable .= '<td>' . htmlspecialchars($row['CHANGED'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $dateSelect = "SELECT DATE_ACTIVE_FROM FROM b_iblock_element WHERE IBLOCK_ID = 149 AND PROPERTY_702 = " . (int)$userIdToFilter;
  
                    $rDateSelect = $connection->query($dateSelect);

                    $htmlTable .= '<td>'.$row['DATE_ACTIVE_FROM'].'</td>';

                    $idUser = array_shift($row['PROPERTY_702']);

                    if ($idUser) {

                        $idUserSafe = htmlspecialchars($idUser, ENT_QUOTES, 'UTF-8');
                        $rsUser = CUser::GetByID($idUser);
                        $arUser = $rsUser->Fetch();

                        if ($arUser) {

                            $userProfileUrl = BITRIX_DOMAIN . "company/personal/user/" . $idUserSafe . "/";
                            $userName = htmlspecialchars($arUser["NAME"] . " " . $arUser["LAST_NAME"], ENT_QUOTES, 'UTF-8');

                            $htmlTable .= '<td><a href="' . $userProfileUrl . '">' . $userName . '</a></td>';

                        } else {
                            $htmlTable .= '<td>Пользователь не найден (ID: ' . $idUserSafe . ')</td>';
                        }
                    } else {
                        $htmlTable .= '<td>ID пользователя не указан</td>';
                    }

                    $htmlTable .= '<td>'.$row['NAME'].'</td>';

                    $htmlTable .= '<td>'.array_shift($row['PROPERTY_701']).'</td>';
                    
                    $fotValue = array_shift($row['PROPERTY_700']);

                    $htmlTable .= '<td>'.$fotValue.'</td>'; // Выводим ФОТ событие

                    $totalBalance += $fotValue;

                    $htmlTable .= '<td>'.$totalBalance.'</td>'; // Выводим текущий баланс
                $htmlTable .= '</tr>';
            }

            $htmlTable .= '</table>';

            echo $htmlTable;

            //echo $_SESSION['bitAppFot']['ID'];
            
            $testTable = getList(
                149
            );

            foreach ($testTable as $row) {
                print_r($row);
                echo "<br>";
            }
            



            $userIdToFilter = $_SESSION['bitAppFot']['ID'];

            $sqlQuery = "
            SELECT 
                DATE_ACTIVE_FROM,
                PROPERTY_702, 
                NAME, 
                PROPERTY_701, 
                PROPERTY_700 
            FROM 
                b_iblock_element 
            WHERE 
                IBLOCK_ID = 149 
                AND PROPERTY_702 = " . (int)$userIdToFilter;

            // Выполняем SQL-запрос
            $result = $connection->query($sqlQuery);

            // Обрабатываем результат
            $htmlTable = '';
            while ($row = $result->fetch()) {
                $htmlTable .= '<tr>';

                // Получаем дату и время из текущей строки результата запроса
                $dateTime = $row['DATE_ACTIVE_FROM'];

                // Форматируем дату и время
                if ($dateTime) {
                    // Форматируем дату в нужный формат
                    $formattedDateTime = FormatDate("d.m.Y H:i:s", MakeTimeStamp($dateTime));
                    $htmlTable .= '<td>' . htmlspecialchars($formattedDateTime, ENT_QUOTES, 'UTF-8') . '</td>';
                } else {
                    $htmlTable .= '<td>Дата не указана</td>';
                }

                // Остальные поля
                $htmlTable .= '<td>' . htmlspecialchars($row['PROPERTY_702'], ENT_QUOTES, 'UTF-8') . '</td>';
                $htmlTable .= '<td>' . htmlspecialchars($row['NAME'], ENT_QUOTES, 'UTF-8') . '</td>';
                $htmlTable .= '<td>' . htmlspecialchars($row['PROPERTY_701'], ENT_QUOTES, 'UTF-8') . '</td>';
                $htmlTable .= '<td>' . htmlspecialchars($row['PROPERTY_700'], ENT_QUOTES, 'UTF-8') . '</td>';

                $htmlTable .= '</tr>';
            }

            echo $htmlTable;






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


