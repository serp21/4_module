<?php

namespace app\lib;

use app\Responce;
use \CRest;

/**
 * Класс работы с задачами сотрудника
 */
class Tasks {

    /**
     * Получить список задач по ответственному и статусу
     *
     * @param array $param параметры поиска задач сотрудника по RESPONSIBLE_ID и REAL_STATUS
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function getTasks(array $param = [])
    {
        if (isset($param['RESPONSIBLE_ID']) && isset($param['REAL_STATUS'])) {
            $filter = array();
            $filter['RESPONSIBLE_ID'] = $param['RESPONSIBLE_ID'];
            $filter['REAL_STATUS'] = $param['REAL_STATUS'];

            $tasks = CRest::call('tasks.task.list', $filter);
            if (isset($tasks['result'])) {
                $uTasks = $tasks['result'];
                $total = $tasks['total'];

                if ($total > 50) {
                    $query = [];
                    $count = ceil($total / 50);

                    for ($i = 1; $i <= $count && $i <= 50; $i++) {
                        $filter['start'] = $i * 50;
                        $query[] = array('method' => 'tasks.task.list', 'params' => $filter);
                    }

                    usleep(500000);
                    $tasks = CRest::callBatch($query, true)['result'];

                    if (isset($tasks['result']) && empty($tasks['result_error'])) {
                        foreach ($tasks['result'] as $result) {
                            $uTasks = array_merge($uTasks, $result);
                        }
                    } else {
                        $error = array_shift($tasks['result_error']);
                        Responce::exception(FAILED, $error['error'] . '<br>' . $error['error_description']);
                    }
                }

                return array('result' => $uTasks, 'total' => $total);
            } else {
                Responce::exception(FAILED, $tasks['error'] . '<br>' . $tasks['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'parameters is null');
        }
    }

}
