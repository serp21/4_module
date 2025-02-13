<?php

namespace app;

use app\Responce;
use app\HTML;

use app\lib\Security;
use app\lib\User;
use app\lib\Lists;
use app\lib\Department;
use app\lib\CRM;
use app\lib\Option;
use app\lib\Tasks;
use Random\Engine\Secure;

class Manage {

    /**
     * Проверка наличия списка должностей EndPoint
     *
     * @return bool true или false
     */
    public static function checkEndPointList() {
        $list = Lists::getList(array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_CODE' => LIST_NAME));
        
        if (isset($list['result']) && !empty($list['result'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function authRoute() {
        global $USER;

        $userList = [];
        $onPage = $USER->onPageGet();

        $filters = self::getFilters();

        $filters['user']['count'] = $onPage;
        $filters['user']['start'] = 0;

        $users = User::get($filters['user']);
        delay();
        $deps = Department::tree($filters['dep'])['result'];
        delay();

        if ($USER->getAccess() == 'HEAD') {
            $headDep = [];

            foreach ($deps as $dep) {
                $headDep[] = $dep['ID'];
            }

            $filters['pos']['FILTER'][$USER->getReversView()['Отдел']] = $headDep;
        }

        $pos = Lists::getElement($filters['pos'])['result'];


        for ($i = 0; $i < $onPage && $i < 100; $i++) {
            $userList[] = $users['result'][$i];
        }

        HTML::route(array('ROUTE' => 'page', 'TABLE' => $userList, 'TOTAL' => $users['total'], 'USER' => $USER, 'SELECTED' => 1, 'DEPARTMENTS' => $deps, 'STAFF_TABLE' => $pos));
    }

    public static function pageRoute() {
        if (isset($_GET['PAGE']['ON_PAGE']) && isset($_GET['PAGE']['SELECTED_PAGE']) && isset($_GET['PAGE']['FILTER'])) {
            global $USER;
    
            $userList = [];
            $onPage = $USER->onPageGet();

            if ($onPage != $_GET['PAGE']['ON_PAGE']) {
                $onPage = $_GET['PAGE']['ON_PAGE'];

                if ($onPage < 5) {
                    $onPage = 5;
                } elseif ($onPage > 5 && $onPage < 10) {
                    $onPage = 10;
                } elseif ($onPage > 10 && $onPage < 20) {
                    $onPage = 20;
                } elseif ($onPage > 20 && $onPage < 50) {
                    $onPage = 50;
                } elseif ($onPage > 50 && $onPage < 100 || $onPage > 100) {
                    $onPage = 100;
                }

                Option::userSet(array('COUNT' => $onPage));
                delay();
            }

            if ($_GET['PAGE']['SELECTED_PAGE'] < 1) {
                $_GET['PAGE']['SELECTED_PAGE'] = 1;
            }

            $filters = self::getFilters();

            $filters['user']['count'] = $onPage;
            $filters['user']['start'] = ($_GET['PAGE']['SELECTED_PAGE'] - 1) * $onPage;

            foreach ($_GET['PAGE']['FILTER'] as $key => $param) {
                if (isset($filters['user']['FILTER'][$key])) {
                    if ($key == 'USER_TYPE') {
                        if ($filters['user']['ADMIN_MODE'] === true) {
                            $filters['user']['FILTER']['USER_TYPE'] = $param;
                        }
                    } else {
                        $filters['user']['FILTER'][$key] = $param;
                    }
                } else {
                    $filters['user']['FILTER'][$key] = $param;
                }
            }
    
            $users = User::get($filters['user']);

            for ($i = 0; $i < $onPage && $i < 100; $i++) {
                $userList[] = $users['result'][$i];
            }
    
            HTML::route(array('ROUTE' => 'main', 'TABLE' => $userList, 'TOTAL' => $users['total'], 'SELECTED' => $_GET['PAGE']['SELECTED_PAGE'], 'ON_PAGE' => $onPage));
        } else {
            Responce::exception(BAD_REQUEST, 'table parameters are bad');
        }
    }

    public static function userRoute() {}

    public static function listRoute() {}

    public static function modalRoute() {
        global $USER;

        if (isset($_GET['MODAL']['TYPE'])) {
            $modal = $_GET['MODAL'];

            if ($modal['TYPE'] == 'settings' || $modal['TYPE'] == 'table') {
                HTML::route(array('ROUTE' => 'modal', 'MODAL_TYPE' => $modal['TYPE']));
                exit;
            }

            $filters = self::getFilters();

            if ($modal['TYPE'] != 'newUser') {
                if (!Security::check($modal['ITEM']['ELEMENT'], $modal['ITEM']['ID'])) {
                    Responce::exception(CONFLICT, 'check is fail');
                }

                $users = User::getById($modal['ITEM']['ID'])['result'];
                delay();

                $users[0]['ELEMENT'] = Security::encrypt($users[0]['ID']);
            }

            $deps = Department::tree($filters['dep'])['result'];
            delay();
    
            if ($USER->getAccess() == 'HEAD') {
                $headDep = [];
    
                foreach ($deps as $dep) {
                    $headDep[] = $dep['ID'];
                }
    
                $filters['pos']['FILTER'][$USER->getReversView()['Отдел']] = $headDep;
            }
    
            $pos = Lists::getElement($filters['pos'])['result'];

            HTML::route(array('ROUTE' => 'modal', 'MODAL_TYPE' => $modal['TYPE'], 'USER' => $users, 'DEPARTMENTS' => $deps, 'DEPARTMENTS_OLD' => $users['UF_DEPARTMENT'], 'POSITIONS' => $pos));
        } else {
            Responce::exception(NOT_FOUND, 'modal type not found');
        }
    }

    private static function getFilters() {
        global $USER;

        $userFilter = array('FILTER' => array('ACTIVE' => true, 'USER_TYPE' => 'employee'));
        $userFilter['ADMIN_MODE'] = $USER->isAdmin();
        
        $depFilter = array('ADMIN_MODE' => true);
        $posFilter = $USER->getTable();

        $access = $USER->getAccess();

        if (isset($_GET['FILTER']['USER'])) {
            $userFilter = [];
            if (isset($_GET['FILTER']['order'])) {
                $USER->setOrder($_GET['FILTER']['order']);
            }

            if (isset($_GET['FILTER']['sort'])) {
                $USER->setOrder($_GET['FILTER']['sort']);
            }

            $userFilter['order'] = $USER->getOrder();
            $userFilter['sort'] = $USER->getSort();

            $userFilter['FILTER'] = $_GET['FILTER']['USER'];

            if ($access == 'FULL')
            {
                $userFilter['ADMIN_MODE'] = false;
                $userFilter['FILTER']['USER_TYPE'] = 'employee';
            }
            elseif ($access == 'HEAD')
            {
                $userFilter['ADMIN_MODE'] = false;
                $userFilter['FILTER']['USER_TYPE'] = 'employee';

                $depFilter['ADMIN_MODE'] = false;
                $depFilter['UF_HEAD'] = $USER->getId();
            }
        }

        return array('user' => $userFilter, 'dep' => $depFilter, 'pos' => $posFilter);
    }
}
