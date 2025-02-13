<?php

namespace app\lib;

use app\Responce;
use \CRest;
use \CRestCurrent;

/**
 * Класс работы с сотрудниками
 */
class User {

    /**
     * Получить данные о текущем пользователе
     *
     * @return array массив с result или ошибка в класс Responce
     */
    public static function current() {
        $info = CRestCurrent::call('profile');

        if (isset($info['result']) && !empty($info['result'])) {
            $userCurrent['ID'] = $info['result']['ID'];
            $userCurrent['ADMIN'] = $info['result']['ADMIN'];

            delay();
            $userCurrent['UF_HEAD'] = Department::tree(array('UF_HEAD' => $userCurrent['ID']))['result'];

            return array('result' => $userCurrent);
        } else {
            Responce::exception(FAILED, $info['error'] . '<br>' . $info['error_information']);
        }
    }

    /**
     * Получить данные о доступных пользователях до 100 записей
     *
     * @param array $filter параметры фильтрации
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function get(array $filter) {
        if (isset($filter['count']) && $filter['count'] > 50) {
            $query = [ array('method' => 'user.get', 'params' => []), array('method' => 'user.get', 'params' => []) ];

            if (isset($filter['sort']) && strlen($filter['sort']) > 0) {
                $query[0]['params']['sort'] = $filter['sort'];
                $query[1]['params']['sort'] = $filter['sort'];
            } else {
                $query[0]['params']['sort'] = 'LAST_NAME';
                $query[1]['params']['sort'] = 'LAST_NAME';
            }
            
            if (isset($filter['order']) && ($filter['order'] == 'ASC' || $filter['order'] == 'DESC')) {
                $query[0]['params']['order'] = $filter['order'];
                $query[1]['params']['order'] = $filter['order'];
            } else {
                $query[0]['params']['order'] = 'ASC';
                $query[1]['params']['order'] = 'ASC';
            }
            
            if (isset($filter['FILTER'])) {
                $query[0]['params']['FILTER'] = $filter['FILTER'];
                $query[1]['params']['FILTER'] = $filter['FILTER'];
            }
            
            if (isset($filter['ADMIN_MODE']) && $filter['ADMIN_MODE'] === true) {
                $query[0]['params']['ADMIN_MODE'] = 'true';
                $query[1]['params']['ADMIN_MODE'] = 'true';
            }
            
            if (isset($filter['start'])) {
                $query[0]['params']['start'] = (int)$filter['start'];
                $query[1]['params']['start'] = (int)$filter['start'] + 50;
            } else {
                $query[0]['params']['start'] = 0;
                $query[1]['params']['start'] = 50;
            }

            $users = CRest::callBatch($query, true)['result'];

            if (isset($users['result']) && empty($users['result_error'])) {
                return array( 'result' => array_merge($users['result'][0], $users['result'][1]), 'total' => $users['result_total'][0] );
            } else {
                $error = array_shift($users['result_error']);
                Responce::exception(FAILED, $error['error'] . '<br>' . $error['error_description']);
            }
        } else {
            $queryFilter = [];

            if (isset($filter['sort'])) {
                $queryFilter['sort'] = $filter['sort'];
            } else {
                $queryFilter['sort'] = 'LAST_NAME';
            }
            
            if (isset($filter['order'])) {
                $queryFilter['order'] = $filter['order'];
            } else {
                $queryFilter['order'] = 'ASC';
            }
            
            if (isset($filter['FILTER'])) {
                $queryFilter['FILTER'] = $filter['FILTER'];
            }
            
            if (isset($filter['FILTER']['ACTIVE']) && ($filter['FILTER']['ACTIVE'] == 'true' || $filter['FILTER']['ACTIVE'] == 'false')) {
                $queryFilter['FILTER']['ACTIVE'] = $filter['FILTER']['ACTIVE'] == 'true' ? true : false;
            }
            
            if (isset($filter['ADMIN_MODE']) && $filter['ADMIN_MODE'] === true) {
                $queryFilter['ADMIN_MODE'] = 'true';
            }
            
            if (isset($filter['start'])) {
                $queryFilter['start'] = (int)$filter['start'];
            } else {
                $queryFilter['start'] = 0;
            }

            $users = CRest::call('user.get', $queryFilter);
            
            if (isset($users['result'])) {
                return $users;
            } else {
                Responce::exception(FAILED, $users['error'] . '<br>' . $users['error_description']);
            }
        }
    }

    /**
     * Получить данные сотрудника по идентификатору
     *
     * @param integer $id идентификатор сотудника
     * @param boolean $adminMode режим администратора
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function getById(int $id = 0, bool $adminMode = false) {
        if ($id > 0) {
            $filter = array ('ID' => $id);
            if ($adminMode) {
                $filter['ADMIN_MODE'] = 'true';
            }

            $users = CRest::call('user.get', array('FILTER' => $filter));
            
            if (isset($users['result'])) {
                return $users;
            } else {
                Responce::exception(FAILED, $users['error'] . '<br>' . $users['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'fail variables');
        }
    }

    /**
     * Добавить нового сотрудника в компанию
     *
     * @param array $param данные сотрудника, обязательно EMAIL
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function add(array $param) {
        if (isset($param['EMAIL']) && $param['EMAIL'] != '') {
            $user = CRest::call('user.add', $param);
            
            if (isset($user['result'])) {
                return $user;
            } else {
                Responce::exception(FAILED, $user['error'] . '<br>' . $user['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'e-mail is null');
        }
    }

    /**
     * Изменение данных сотрудника по ID
     *
     * @param array $param данные сотрудника, обязательно ID
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function edit(array $param) {
        if (isset($param['ID']) && $param['ID'] > 0) {
            $user = CRest::call('user.update', $param);
            
            if (isset($user['result'])) {
                return $user;
            } else {
                Responce::exception(FAILED, $user['error'] . '<br>' . $user['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'e-mail is null');
        }
    }

    /**
     * Поиск сотрудника
     *
     * @param array $filter параметры для поиска, обязательно не empty
     * @return array массив подобный CRest или ошибка в класс Responce
     */
    public static function search(array $filter) {
        if (!empty($filter)) {
            $queryFilter = [];

            if (isset($filter['sort'])) {
                $queryFilter['sort'] = $filter['sort'];
            } else {
                $queryFilter['sort'] = 'LAST_NAME';
            }
            
            if (isset($filter['order'])) {
                $queryFilter['order'] = $filter['order'];
            } else {
                $queryFilter['order'] = 'ASC';
            }
            
            if (isset($filter['FILTER'])) {
                if ($filter['FILTER']['ACTIVE'] == 'true' || $filter['FILTER']['ACTIVE'] == 'false') {
                    $queryFilter['FILTER']['ACTIVE'] = $filter['FILTER']['ACTIVE'] == 'true' ? true : false;
                }

                if (isset($filter['FILTER']['USER_TYPE'])) {
                    $queryFilter['FILTER']['USER_TYPE'] = $filter['FILTER']['USER_TYPE'];
                } else {
                    $queryFilter['FILTER']['USER_TYPE'] = 'employee';
                }

                if (isset($filter['FILTER']['UF_DEPARTMENT'])) {
                    $queryFilter['FILTER']['UF_DEPARTMENT'] = $filter['FILTER']['UF_DEPARTMENT'];
                }

                if (isset($filter['FILTER']['WORK_POSITION'])) {
                    $queryFilter['FILTER']['WORK_POSITION'] = $filter['FILTER']['WORK_POSITION'];
                }

                if (isset($filter['FILTER']['PERSONAL_BIRTHDAY'])) {
                    $queryFilter['FILTER']['PERSONAL_BIRTHDAY'] = $filter['FILTER']['PERSONAL_BIRTHDAY'];
                }

                if (isset($filter['FILTER']['PERSONAL_GENDER'])) {
                    $queryFilter['FILTER']['PERSONAL_GENDER'] = $filter['FILTER']['PERSONAL_GENDER'];
                }
            } else {
                $queryFilter['FILTER']['USER_TYPE'] = 'employee';
                $queryFilter['FILTER']['ACTIVE'] = true;
            }
            
            if (isset($filter['ADMIN_MODE']) && $filter['ADMIN_MODE'] === true) {
                $queryFilter['ADMIN_MODE'] = 'true';
            }
            
            $response = CRest::call('user.get', $queryFilter);

            if (isset($response['result'])) {
                $users = $response['result'];

                if ($response['total'] > 50) {
                    $count = ceil($response['total'] / 50);

                    $query = [];
                    for ($i = 1; $i <= $count && $i <= 50; $i++) {
                        $queryFilter['start'] = 50 * $i;
                        $query[] = array('method' => 'user.get', 'params' => $queryFilter);
                    }

                    delay();
                    $response = CRest::callBatch($query, true)['result'];

                    if (isset($response['result']) && empty($response['result_error'])) {
                        foreach ($response['result'] as $list) {
                            $users = array_merge($users, $list);
                        }
                    } else {
                        $error = array_shift($response['result_error']);
                        Responce::exception(FAILED, $error['error'] . '<br>' . $error['error_description']);
                    }
                }

                $result = [];

                if (!isset($filter['start'])) {
                    $filter['start'] = 0;
                }

                $total = 0;
                $temp = 0;
                foreach ($users as $user) {
                    if (isset($filter['FILTER']['EMAIL']) || isset($filter['FILTER']['DATE_REGISTER']) || isset($filter['FILTER']['PERSONAL_MOBILE']) || isset($filter['FILTER']['WORK_POSITION']) || isset($filter['FILTER']['FULL_NAME']) || isset($filter['FILTER']['FULL'])) {
                        if (isset($filter['FILTER']['EMAIL']) && str_contains(mb_strtolower($user['EMAIL']), mb_strtolower($filter['FILTER']['EMAIL']))) {
                            $total++;
                        } elseif (isset($filter['FILTER']['WORK_POSITION']) && str_contains($user['WORK_POSITION'], $filter['FILTER']['WORK_POSITION'])) {
                            $total++;
                        } elseif (isset($filter['FILTER']['DATE_REGISTER']) && str_contains($user['DATE_REGISTER'], $filter['FILTER']['DATE_REGISTER'])) {
                            $total++;
                        } elseif (isset($filter['FILTER']['FULL_NAME']) && str_contains(mb_strtolower($user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME']), mb_strtolower($filter['FILTER']['FULL_NAME']))) {
                            $total++;
                        } elseif (isset($filter['FILTER']['FULL'])) {
                            $full = $user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME'] . ' ' . $user['EMAIL'] . ' ' . $user['PERSONAL_PHONE'] . ' ' . $user['PERSONAL_MOBILE'] . ' ' . $user['WORK_POSITION'];

                            if (str_contains(mb_strtolower($full), mb_strtolower($filter['FILTER']['FULL']))) {
                                $total++;
                            }
                        } elseif (isset($filter['FILTER']['PERSONAL_MOBILE'])) {
                            $phone = $user['PERSONAL_PHONE'];
                            $modile = $user['PERSONAL_MOBILE'];
                            $strPhone1 = '+';
                            $strPhone2 = '+';
                            $strPhone3 = '+';

                            for ($i = 0; $i < strlen($phone); $i++) {
                                if (is_numeric($phone[$i])) {
                                    if (!str_starts_with($phone, '+') && strlen($strPhone1) == 1) {
                                        $num = $phone[$i] - 1;
                                        $strPhone1 .= $num;
                                    } else {
                                        $strPhone1 .= $phone[$i];
                                    }
                                }
                            }
                            
                            for ($i = 0; $i < strlen($modile); $i++) {
                                if (is_numeric($modile[$i])) {
                                    if (!str_starts_with($modile, '+') && strlen($strPhone2) == 1) {
                                        $num = $modile[$i] - 1;
                                        $strPhone2 .= $num;
                                    } else {
                                        $strPhone2 .= $modile[$i];
                                    }
                                }
                            }

                            $filterPhone = $filter['FILTER']['PERSONAL_MOBILE'];
                            for ($i = 0; $i < strlen($filterPhone); $i++) {
                                if (is_numeric($filterPhone[$i])) {
                                    if (!str_starts_with($filterPhone, '+') && strlen($strPhone3) == 1) {
                                        $num = $filterPhone[$i] - 1;
                                        $strPhone3 .= $num;
                                    } else {
                                        $strPhone3 .= $filterPhone[$i];
                                    }
                                }
                            }
                            
                            if (str_contains($strPhone1.'  '.$strPhone2, $strPhone3)) {
                                $total++;
                            }
                        }
                    } else {
                        $total++;
                    }

                    if ($total != $temp && $total > (int)$filter['start']) {
                        $result[] = $user;
                        $temp = $total;
                    }
                }

                return array('result' => $result, 'total' => $total);
            } else {
                Responce::exception(FAILED, $response['error'] . '<br>' . $response['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST);
        }
    }

    public static function userFieldSL() {
        $check = CRest::call('user.userfield.list', array('filter' => array('FIELD_NAME' => USER_POSITION, 'USER_TYPE_ID' => 'employee')));

        if (isset($check['result'])) {
            if (empty($check['result'])) {
                delay();

                $add = CRest::call('user.userfield.add', array('fields' => array('FIELD_NAME' => USER_POSITION, 'USER_TYPE_ID' => 'employee', 'LABEL' => 'Штатное расписание', 'SHOW_FILTER' => 'Y')));

                if (isset($add['result'])) {
                    return $add;
                } else {
                    Responce::exception(FAILED, $add['error'] . '<br>' . $add['error_description']);
                }
            } else {
                return $check;
            }
        } else {
            Responce::exception(FAILED, $check['error'] . '<br>' . $check['error_description']);
        }
    }

}
