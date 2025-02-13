<?php

namespace app\lib;

use app\Responce;
use \CRest;

class CRM {

    /**
     * Получить список пользовательских CRM
     *
     * @return array массив пользовательских CRM или ошибка в класс Responce
     */
    public static function getType()
    {
        $types = CRest::call('crm.type.list');
        if (isset($types['result'])) {
            $userCRM = $types['result'];
            $total = $types['total'];
                
            if ($types['total'] > 50) {
                $query = array();
                
                $count = ceil($types['total'] / 50);
                for ($i = 1; $i <= $count && $i <= 50; $i++) {
                    $fields['start'] = 50 * $i;
                    $query[] = array('method' => 'crm.type.list', 'params' => $fields);
                }

                usleep(500000);
                $types = CRest::callBatch($query, true)['result'];

                if (isset($types['result']) && empty($types['result_error'])) {
                    foreach ($types['result'] as $result) {
                        $userCRM = array_merge($userCRM, $result);
                    }
                } else {
                    $error = array_shift($types['result_error']);
                    Responce::exception(FAILED, $error['error'] . '<br>' . $error['error_description']);
                }
            }

            return array('result' => $userCRM, 'total' => $total);
        } else {
            Responce::exception(FAILED, $types['error'] . '<br>' . $types['error_description']);
        }
    }

    /**
     * Получить список элементов справочника
     *
     * @return array массив элементов справочника или ошибка в класс Responce
     */
    public static function getStatus() {
        $status = CRest::call('crm.status.list');

        if (isset($status['result'])) {
            return $status;
        } else {
            Responce::exception(FAILED, $status['error'] . '<br>' . $status['error_description']);
        }
    }

    # Получить пользовательские элементы CRM по сотруднику в стадии "не завершены"
    public static function getUserCRM() {}

    # Получить сделки по сотруднику в стадии "не завершены"
    public static function getDeal() {}

    # Получить список лидов по сотруднику в стадии "не завершены"
    public static function getLead() {}

    /**
     * Получить список компаний сотрудника
     *
     * @param integer $id идентификатор сотрудника
     * @return array массив компаний сотрудника или ошибка в класс Responce
     */
    public static function getCompany(int $id = 0) {
        if ($id > 0) {
            $filter = array('ASSIGNED_BY_ID' => $id);

            $company = CRest::call('crm.company.list', array('filter' => $filter));

            if (isset($company['result'])) {
                $ucompany = $company['result'];
                $total = $company['total'];

                if ($total > 50) {
                    $query = array();
                    
                    $count = ceil($total / 50);
                    for ($i = 1; $i <= $count && $i <= 50; $i++) {
                        $filter['start'] = 50 * $i;
                        $query[] = array('method' => 'crm.company.list', 'params' => $filter);
                    }
    
                    usleep(500000);
                    $company = CRest::callBatch($query, true)['result'];
    
                    if (isset($company['result']) && empty($company['result_error'])) {
                        foreach ($company['result'] as $result) {
                            $ucompany = array_merge($ucompany, $result);
                        }
                    } else {
                        $error = array_shift($company['result_error']);
                        Responce::exception(FAILED, $error['error'] . '<br>' . $error['error_description']);
                    }
                }

                return array('result' => $ucompany, 'total' => $total);
            } else {
                Responce::exception(FAILED, $company['error'] . '<br>' . $company['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'id is null');
        }
    }

}
