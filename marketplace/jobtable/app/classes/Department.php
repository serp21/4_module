<?php

namespace App\Stafftable;

class Department
{


    public static function getById($id = '')
    {

        if (empty($id) || !is_numeric($id)) {
            return false;
        }

        $res = \CRest::call(
            "department.get",
            [
                'ID' => $id,
            ]
        );

        return $res['result'][0];
    }
}
