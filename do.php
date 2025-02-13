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
        700,
        //???? / total kakoy-to
    );



 ?>


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

























