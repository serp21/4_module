<?php

// Класс для работы с хранилищем приложения
namespace App\Stafftable;

class Option
{
    public static function getAllOption()
    {
        return \CRestCurrent::call('app.option.get', [])['result'];
    }

    public static function getOption(string $storage = "")
    {
        if (empty($storage)) {
            return false;
        }

        $res = \CRestCurrent::call(
            'app.option.get',
            [
                "option" => $storage
            ]
        )['result'];

        if (empty($res)) {
            return false;
        }

        return $res;
    }

    public static function setOption($options)
    {
        if (empty($options)) {
            return false;
        }

        $res = \CRestCurrent::call(
            'app.option.set',
            [
                "options" => $options
            ]
        )['result'];

        return true;
    }
}
