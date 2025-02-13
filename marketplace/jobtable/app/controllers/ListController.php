<?php

use App\Stafftable\Lists;

class ListController extends Controller
{

    public function index()
    {
        echo "1234";
    }

    public function getFields($data = array())
    {

        if (empty($data)) {
            http_response_code(422);
            echo "Отсутствуют необходимые параметры. Повторите запрос, либо обратитесь в техподдержку";
            return;
        }

        $listId = $data['listId'];

        $fields = \CRest::call(
            "lists.field.get",
            [
                "IBLOCK_TYPE_ID" => 'lists',
                "IBLOCK_ID" => $listId
            ]
        )['result'];

        echo json_encode($fields);
    }

    public function setIndexList($data = array())
    {
        $errorCount = 0;
        $errorMessage = '';

        $listId = $data['listId'];
        $customList = $data['customList'];
        $fieldsInfo = $data['fieldsInfo'];

        if ($customList == "off") {
            $res = Lists::createNewList();

            if(!$res) {
                http_response_code(422);
                echo 'Ошибка добавления списка, возможно, список уже добавлен? Если список не появился, обратитесь в техническую поддержку!';
                return;
            }
            
            print_r($res);
            echo 'Новый список успешно добавлен!';
            return;
        }

        $customNameFieldId = $fieldsInfo['NAME_FIELD_ID'];
        $customGroupFieldId = $fieldsInfo['GROUP_FIELD_ID'];
        $customSalaryFieldId = $fieldsInfo['SALARY_FIELD_ID'];

        foreach ($fieldsInfo as $key => $fieldId) {
            if (empty($fieldId)) {
                $errorMessage = "Ошибка. Укажите все поля списка для корректной работы";
                break;
            }

            $res = CRest::call(
                "lists.field.get",
                [
                    "IBLOCK_TYPE_ID" => 'lists',
                    "IBLOCK_ID" => $listId,
                    "FIELD_ID" => $fieldId
                ]
            )['result'];

            if (count($res) > 1) {
                $errorMessage = "Поле $key не существует!";
            }

            if ($fieldId == $customNameFieldId && $res[array_key_first($res)]['TYPE'] != 'NAME') {
                $errorMessage =  "Некорректный тип для поля \"Название\"";
                break;
            } else if ($fieldId == $customGroupFieldId && $res[array_key_first($res)]['TYPE'] != 'N') {
                $errorMessage =  "Некорректный тип для поля \"Подразделение\"";
                break;
            } else if ($fieldId == $customSalaryFieldId && $res[array_key_first($res)]['TYPE'] != 'N') {
                $errorMessage =  "Некорректный тип для поля \"Оклад\"";
                break;
            }    
        }

        if (!empty($errorMessage)) {
            http_response_code(422);
            echo $errorMessage;
            return;
        }

        $res = Lists::setIndexList($listId, $fieldsInfo);

        if($res) {
            echo "Список успешно зарегистрирован!";
            return;
        } else {
            http_response_code(500);
            echo "Произошла непредвиденная ошибка!";
            return;
        }
    }
}
