<?php

namespace app\lib;

use \CRest;
use app\Responce;

/**
 * Класс работы с отделами
 */
class Department {

    /**
     * Создание нового отдела
     *
     * @param array $param параметры для создания отдела NAME, SORT, PARENT и UF_HEAD
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function add(array $param) {
        if (isset($param['NAME'])) {
            $fields = array('NAME' => $param['NAME']);

            if (isset($param['SORT'])) {
                $fields['SORT'] = $param['SORT'];
            }

            if (isset($param['PARENT'])) {
                $fields['PARENT'] = $param['PARENT'];
            }

            if (isset($param['UF_HEAD'])) {
                $fields['UF_HEAD'] = $param['UF_HEAD'];
            }

            $dep = CRest::call('department.add', $fields);
            
            if (isset($dep['result'])) {
                return $dep;
            } else {
                Responce::exception(FAILED, $dep['error'] . '<br>' . $dep['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Изменение отдела
     *
     * @param array $param параметры для создания отдела NAME, SORT, PARENT и UF_HEAD
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function edit(array $param) {
        if (isset($param['ID']) && isset($param['NAME'])) {
            $fields = array('ID' => $param['ID'],'NAME' => $param['NAME']);

            if (isset($param['SORT'])) {
                $fields['SORT'] = $param['SORT'];
            }

            if (isset($param['PARENT'])) {
                $fields['PARENT'] = $param['PARENT'];
            }

            if (isset($param['UF_HEAD'])) {
                $fields['UF_HEAD'] = $param['UF_HEAD'];
            }

            $dep = CRest::call('department.update', $fields);
            
            if (isset($dep['result'])) {
                return $dep;
            } else {
                Responce::exception(FAILED, $dep['error'] . '<br>' . $dep['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Удаление отдела
     *
     * @param integer $id идентификатор удаляемого отдела
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function delete(int $id = 0) {
        if ($id > 0) {
            $dep = CRest::call('department.delete', array('ID' => $id));
            
            if (isset($dep['result'])) {
                return $dep;
            } else {
                Responce::exception(FAILED, $dep['error'] . '<br>' . $dep['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Получение списка отделов
     *
     * @param array $filter фильтр для получения отдела, если пуст, вернёт все отделы
     * @return array массив подобный CRest или ошибка в класс Responce
     */
    public static function get(array $filter = []) {
        if (!empty($filter)) {
            $dep = CRest::call('department.get', $filter);
        } else {
            $dep = CRest::call('department.get');
        }

        if (isset($dep['result'])) {
            $total = $dep['total'];
            $departments = $dep['result'];

            if ($total > 50) {
                $count = ceil($total / 50);

                $query = array();
                for ($i = 1; $i <= $count && $i <= 50; $i++) {
                    $filter['start'] = $i * 50;
                    $query[] = array('method' => 'department.get', 'params' => $filter);
                }

                usleep(500000);
                $dep = CRest::callBatch($query, true)['result'];

                if (isset($dep['result']) && empty($dep['result_error'])) {
                    foreach ($dep['result'] as $result) {
                        $departments = array_merge($departments, $result);
                    }
                } else {
                    $error = array_shift($dep['result_error']);
                    Responce::exception(FAILED, $error['error'] . '<br>' . $error['error_description']);
                }
            }
            
            return array('result' => $departments, 'total' => $total);
        } else {
            Responce::exception(FAILED, $dep['error'] . '<br>' . $dep['error_description']);
        }
    }

    /**
     * Получение дерева отделов компании и сотрудника
     *
     * @param array $param параметр поиска отделов по ADMIN_MODE, UF_DEPARTMENT или UF_HEAD
     * @return array массив подобный CRest или ошибка в класс Responce
     */
    public static function tree(array $param) {
        if (!empty($param) && (isset($param['ADMIN_MODE']) || isset($param['UF_DEPARTMENT']) || isset($param['UF_HEAD']))) {
            $dep = self::get();

            if (isset($dep['result'])) {
                $depTree = self::setTree($dep['result']);

                if (isset($param['ADMIN_MODE']) && $param['ADMIN_MODE'] === true) {
                    return array('result' => $depTree, 'total' => $dep['total']);
                } elseif (isset($param['UF_DEPARTMENT']) && !empty($param['UF_DEPARTMENT'])) {
                    $arrayDep = array();

                    foreach ($param['UF_DEPARTMENT'] as $id) {
                        if (!empty($depTree[$id])) {
                            $arrayDep[$id] = $depTree[$id];
                        }
                    }

                    return array('result' => $arrayDep, 'total' => count($arrayDep));
                } elseif (isset($param['UF_HEAD'])) {
                    $arrayDep = array();

                    foreach ($depTree as $item) {
                        if ($item['UF_HEAD'] === $param['UF_HEAD']) {
                            $arrayDep[$item['ID']] = $item;
                        }
                    }

                    return array('result' => $arrayDep, 'total' => count($arrayDep));
                } else {
                    Responce::exception(BAD_REQUEST, 'parameters is null');
                }
            } else {
                Responce::exception(FAILED, $dep['error'] . '<br>' . $dep['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    /**
     * Формирование дерева отделов
     *
     * @param array $data список всех отделов
     * @param int $parent идентификатор родительского отдела
     * @return array массив всех отделов с иерархией
     */
    private static function setTree(array &$data, int $parent = null) {
        $dep = [];
    
        foreach ($data as $item) {
            if ($item['PARENT'] == $parent || $parent == null && $item['PARENT'] != null) {
                $dep[$item['ID']] = ['ID' => $item['ID'], 'NAME' => $item['NAME'], 'SORT' => $item['SORT'], 'UF_HEAD' => $item['UF_HEAD'], 'PARENT' => $parent, 'CHILDREN' => self::setTree($data, $item['ID'])];
            }
        }

        return $dep;
    }

}
