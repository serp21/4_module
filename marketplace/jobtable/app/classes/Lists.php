<?php

namespace App\Stafftable;

use App\Stafftable\Option;

class Lists
{

    protected $listId;
    protected $nameId; 
    protected $groupId; 
    protected $salaryId; 

    private static $obj;

    protected function __construct() {
        $this->listId = Option::getOption('ENDPOINT_STAFFTABLE_LIST_ID');
        $this->nameId = Option::getOption('ENDPOINT_STAFFTABLE_NAME_FIELD_ID');
        $this->groupId = Option::getOption('ENDPOINT_STAFFTABLE_GROUP_FIELD_ID');
        $this->salaryId = Option::getOption('ENDPOINT_STAFFTABLE_SALARY_FIELD_ID');
    }

    public static function getInstance() {
        if(empty(self::$obj)) {
            self::$obj = new self();
        }

        return self::$obj;
    }

    public static function getData() {}

    public function getListId() {
        return $this->listId;
    }

    public function getNameId() {
        return $this->nameId;
    }
    public function getGroupId() {
        return $this->groupId;
    }
    public function getSalaryId() {
        return $this->salaryId;
    }

    public function getFieldsId() {
        return $fields = [
            'NAME' => $this->getNameId(),
            'GROUP' => $this->getGroupId(),
            'SALARY' => $this->getSalaryId()
        ];
    }

    public static function setIndexList($listId, $fields = array())
    {
        $res = Option::setOption([
            'ENDPOINT_STAFFTABLE_LIST_ID' => $listId,
            'ENDPOINT_STAFFTABLE_NAME_FIELD_ID' => $fields['NAME_FIELD_ID'],
            'ENDPOINT_STAFFTABLE_GROUP_FIELD_ID' => $fields['GROUP_FIELD_ID'],
            'ENDPOINT_STAFFTABLE_SALARY_FIELD_ID' => $fields['SALARY_FIELD_ID'],
        ]);

        return $res;
    }

    public static function getIndexList()
    {
        $listId = Option::getOption('ENDPOINT_STAFFTABLE_LIST_ID');

        $responseListGet = \CRestCurrent::call(
            'lists.get',
            [
                "IBLOCK_TYPE_ID" => "lists",
                "IBLOCK_ID" => $listId
            ]
        )['result'];

        if(empty($responseListGet)) {
            return false;
        }

        return $responseListGet;
    }

    public static function createNewList($code = '')
    {
        $responseListAdd = \CRestCurrent::call(
            'lists.add',
            [
                "IBLOCK_TYPE_ID" => "lists",
                "IBLOCK_CODE" => "ENDPOINT_STAFFTABLE",
                "FIELDS" => [
                    "NAME" => "Штатное расписание - Endpoint",
                    "DESCRIPTION" => "Список для хранения информации о Штатном расписании компании при использовании приложение Штатное расписание от компании Endpoint"
                ]
            ]
        );

        $responseGroupFieldAdd = \CRestCurrent::call(
            'lists.field.add',
            [
                "IBLOCK_TYPE_ID" => "lists",
                "IBLOCK_CODE" => "ENDPOINT_STAFFTABLE",
                "FIELDS" => [
                    "NAME" => 'Подразделение',
                    "IS_REQUIRED" => 'Y',
                    "TYPE" => 'N',
                    "CODE" => 'GROUP',
                ]
            ]
        );

        $responseSalaryFieldAdd = \CRestCurrent::call(
            'lists.field.add',
            [
                "IBLOCK_TYPE_ID" => "lists",
                "IBLOCK_CODE" => "ENDPOINT_STAFFTABLE",
                "FIELDS" => [
                    "NAME" => 'Оклад',
                    "IS_REQUIRED" => 'Y',
                    "TYPE" => 'N',
                    "CODE" => 'SALARY',
                ]
            ]
        );

        if (
            isset($responseListAdd['error'])
            || isset($responseGroupFieldAdd['error'])
            || isset($responseSalaryFieldAdd['error'])
        ) {
            return false;
        }

        $fields = [
            'NAME_FIELD_ID' => 'NAME',
            'GROUP_FIELD_ID' => $responseGroupFieldAdd['result'],
            'SALARY_FIELD_ID' => $responseSalaryFieldAdd['result'],
        ];

        $res = self::setIndexList($responseListAdd['result'], $fields);

        if(!$res) {
            return false;
        }

        return true;
    }
}
