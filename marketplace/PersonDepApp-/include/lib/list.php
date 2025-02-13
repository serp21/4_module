<?php

namespace app\lib;

use app\Responce;
use \CRest;

class Lists {

    // Работа со списками \\

    /**
     * Создание нового списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE, FIELDS->NAME
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function addList(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && isset($param['IBLOCK_CODE']) && isset($param['FIELDS']['NAME']) && strlen($param['FIELDS']['NAME']) > 0) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID'], 'IBLOCK_CODE' => $param['IBLOCK_CODE'], 'FIELDS' => $param['FIELDS']);

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            if (isset($param['MESSAGES'])) {
                $fields['MESSAGES'] = $param['MESSAGES'];
            }
            
            if (isset($param['RIGHTS'])) {
                $fields['RIGHTS'] = $param['RIGHTS'];
            }

            $list = CRest::call('lists.add', $fields);

            if (isset($list['result'])) {
                return $list;
            } else {
                Responce::exception(FAILED, $list['error'] . '<br>' . $list['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Удаление списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function deleteList(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID']))) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID']);

            if (isset($param['IBLOCK_ID'])) {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            } else {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            $list = CRest::call('lists.delete', $fields);

            if (isset($list['result'])) {
                return $list;
            } else {
                Responce::exception(FAILED, $list['error'] . '<br>' . $list['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Получение списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function getList(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID']))) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID']);

            if (isset($param['IBLOCK_ID'])) {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            } else {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            if (isset($param['IBLOCK_ORDER']) && !empty($param['IBLOCK_ORDER'])) {
                $fields['IBLOCK_ORDER'] = $param['IBLOCK_ORDER'];
            }

            $list = CRest::call('lists.get', $fields);

            if (isset($list['result'])) {
                return $list;
            } else {
                Responce::exception(FAILED, $list['error'] . '<br>' . $list['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'list name is null');
        }
    }

    /**
     * Обновление списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID, FIELDS->NAME
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function updateList(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID'])) && isset($param['FIELDS']['NAME']) && strlen($param['FIELDS']['NAME']) > 0) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID'], 'FIELDS' => $param['FIELDS']);

            if (isset($param['IBLOCK_ID'])) {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            } else {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            if (isset($param['MESSAGES'])) {
                $fields['MESSAGES'] = $param['MESSAGES'];
            }
            
            if (isset($param['RIGHTS'])) {
                $fields['RIGHTS'] = $param['RIGHTS'];
            }

            $list = CRest::call('lists.update', $fields);

            if (isset($list['result'])) {
                return $list;
            } else {
                Responce::exception(FAILED, $list['error'] . '<br>' . $list['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    // Работа с элементами \\

    /**
     * Добавление элемента списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID, ELEMENT_CODE
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function addElement(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID'])) && isset($param['ELEMENT_CODE'])) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID'], 'ELEMENT_CODE' => $param['ELEMENT_CODE']);

            if (isset($param['IBLOCK_CODE'])) {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            } else {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            }

            if (isset($param['LIST_ELEMENT_URL'])) {
                $fields['LIST_ELEMENT_URL'] = $param['LIST_ELEMENT_URL'];
            }

            if (isset($param['FIELDS'])) {
                $fields['FIELDS'] = $param['FIELDS'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            $element = CRest::call('lists.element.add', $fields);

            if (isset($element['result'])) {
                return $element;
            } else {
                Responce::exception(FAILED, $element['error'] . '<br>' . $element['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Удаление элемента списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID, ELEMENT_CODE или ELEMENT_ID
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function deleteElement(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID'])) && (isset($param['ELEMENT_CODE']) || isset($param['ELEMENT_ID']))) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID']);

            if (isset($param['IBLOCK_CODE'])) {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            } else {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            }

            if (isset($param['ELEMENT_CODE'])) {
                $fields['ELEMENT_CODE'] = $param['ELEMENT_CODE'];
            } else {
                $fields['ELEMENT_ID'] = $param['ELEMENT_ID'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }
            
            $element = CRest::call('lists.element.delete', $fields);

            if (isset($element['result'])) {
                return $element;
            } else {
                Responce::exception(FAILED, $element['error'] . '<br>' . $element['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Получение элементов списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID (CHANGE_NAME => true для подмены PROPERTY)
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function getElement(array $param = []) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID']))) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID']);

            if (isset($param['IBLOCK_CODE'])) {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            } else {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            }

            if (isset($param['ELEMENT_CODE'])) {
                $fields['ELEMENT_CODE'] = $param['ELEMENT_CODE'];
            } else {
                $fields['ELEMENT_ID'] = $param['ELEMENT_ID'];
            }

            if (isset($param['ELEMENT_ORDER'])) {
                $fields['ELEMENT_ORDER'] = $param['ELEMENT_ORDER'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            if (isset($param['FILTER'])) {
                $fields['FILTER'] = $param['FILTER'];
            }

            $item = CRest::call('lists.element.get', $fields);

            if (isset($item['result'])) {
                $elementsName = [];
                if (isset($param['CHANGE_NAME']) && $param['CHANGE_NAME'] === true) {
                    usleep(500000);

                    $itemName = self::getField($param);

                    if (isset($itemName['result'])) {
                        $elementsName = $itemName['result'];
                    }
                }

                $elements = $item['result'];
                $total = $item['total'];
                
                if ($item['total'] > 50) {
                    $query = array();
                    
                    $count = ceil($item['total'] / 50);
                    for ($i = 1; $i <= $count && $i <= 50; $i++) {
                        $fields['start'] = 50 * $i;
                        $query[] = array('method' => 'lists.element.get', 'params' => $fields);
                    }

                    usleep(500000);
                    $item = CRest::callBatch($query, true)['result'];

                    if (isset($item['result']) && empty($item['result_error'])) {
                        foreach ($item['result'] as $result) {
                            $elements = array_merge($elements, $result);
                        }
                    } else {
                        $error = array_shift($item['result_error']);
                        Responce::exception(FAILED, $error['error'] . '<br>' . $error['error_description']);
                    }
                }
                    
                if (isset($param['CHANGE_NAME']) && $param['CHANGE_NAME'] === true) {
                    for ($i = 0; $i < count($elements); $i++) {
                        foreach ($elementsName as $name) {
                            if (isset($elements[$i][$name['FIELD_ID']])) {
                                $temp = $elements[$i][$name['FIELD_ID']];

                                unset($elements[$i][$name['FIELD_ID']]);

                                $elements[$i][$name['NAME']] = $temp;
                            }
                        }
                    }
                }

                return array('result' => $elements, 'names' => $elementsName, 'total' => $total);
            } else {
                Responce::exception(FAILED, $item['error'] . '<br>' . $item['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'list id is null');
        }
    }

    /**
     * Изменение элемента списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID, ELEMENT_CODE или ELEMENT_ID, FIELDS
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function updateElement(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID'])) && (isset($param['ELEMENT_CODE']) || isset($param['ELEMENT_ID'])) && isset($param['FIELDS']) && !empty($param['FIELDS'])) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID']);

            if (isset($param['IBLOCK_CODE'])) {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            } else {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            }

            if (isset($param['ELEMENT_CODE'])) {
                $fields['ELEMENT_CODE'] = $param['ELEMENT_CODE'];
            } else {
                $fields['ELEMENT_ID'] = $param['ELEMENT_ID'];
            }

            if (isset($param['FIELDS'])) {
                $fields['FIELDS'] = $param['FIELDS'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            $element = CRest::call('lists.element.update', $fields);

            if (isset($element['result'])) {
                return $element;
            } else {
                Responce::exception(FAILED, $element['error'] . '<br>' . $element['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    // Работа с полями \\

    /**
     * Создание поля списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID, FIELDS->NAME
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function addField(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID'])) && isset($param['FIELDS']['NAME']) && strlen($param['FIELDS']['NAME']) > 0) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID'], 'FIELDS' => $param['FIELDS']);

            if (isset($param['IBLOCK_CODE'])) {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            } else {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            $field = CRest::call('lists.field.add', $fields);

            if (isset($field['result'])) {
                return $field;
            } else {
                Responce::exception(FAILED, $field['error'] . '<br>' . $field['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Удаление поля списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function deleteField(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID']))) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID']);

            if (isset($param['IBLOCK_CODE'])) {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            } else {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            if (isset($param['FIELD_ID'])) {
                $fields['FIELD_ID'] = $param['FIELD_ID'];
            }

            $field = CRest::call('lists.field.delete', $fields);

            if (isset($field['result'])) {
                return $field;
            } else {
                Responce::exception(FAILED, $field['error'] . '<br>' . $field['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Получение поля списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function getField(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID']))) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID']);

            if (isset($param['IBLOCK_CODE'])) {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            } else {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            if (isset($param['FIELD_ID'])) {
                $fields['FIELD_ID'] = $param['FIELD_ID'];
            }

            $field = CRest::call('lists.field.get', $fields);

            if (isset($field['result'])) {
                return $field;
            } else {
                Responce::exception(FAILED, $field['error'] . '<br>' . $field['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Обновление поля списка
     *
     * @param array $param параметры списка, обязательно IBLOCK_TYPE_ID, IBLOCK_CODE или IBLOCK_ID, FIELDS->NAME
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function updateField(array $param) {
        if (isset($param['IBLOCK_TYPE_ID']) && (isset($param['IBLOCK_CODE']) || isset($param['IBLOCK_ID'])) && isset($param['FIELDS']['NAME']) && strlen($param['FIELDS']['NAME']) > 0) {
            $fields = array('IBLOCK_TYPE_ID' => $param['IBLOCK_TYPE_ID'], 'FIELDS' => $param['FIELDS']);

            if (isset($param['IBLOCK_CODE'])) {
                $fields['IBLOCK_CODE'] = $param['IBLOCK_CODE'];
            } else {
                $fields['IBLOCK_ID'] = $param['IBLOCK_ID'];
            }

            if (isset($param['FIELD_ID'])) {
                $fields['FIELD_ID'] = $param['FIELD_ID'];
            }

            if (isset($param['SOCNET_GROUP_ID'])) {
                $fields['SOCNET_GROUP_ID'] = $param['SOCNET_GROUP_ID'];
            }

            $field = CRest::call('lists.field.update', $fields);

            if (isset($field['result'])) {
                return $field;
            } else {
                Responce::exception(FAILED, $field['error'] . '<br>' . $field['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

}
