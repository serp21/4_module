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

























