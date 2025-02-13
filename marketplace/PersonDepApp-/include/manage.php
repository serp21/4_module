<?php

namespace app;

use app\Responce;
use app\HTML;

use app\lib\Security;
use app\lib\User;
use app\lib\Lists;
use app\lib\Department;
use app\lib\CRM;
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
        $filters['user']['start'] = 1;

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

    public static function pageRoute() {}

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
