<?php

namespace App\Stafftable;

use App\Stafftable\Lists;

class Staff
{


    public static function getList($filter = [], $select = ['ID'], $order =[])
    {
        $order = [
            Lists::getInstance()->getGroupId() => 'ASC'
        ];

        $res = \CRest::call(
            "lists.element.get",
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => Lists::getInstance()->getListId(),
                'ELEMENT_ORDER' => $order,
                'FILTER' => $filter,
                'SELECT' => $select,
            ]
        );

        return $res['result'];
    }
}
