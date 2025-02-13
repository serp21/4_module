<?php

namespace app;

use app\Responce;
use app\lib\Security;
use app\lib\Sonet;
use app\lib\Department;
use LDAP\Result;

class HTML {
    private static $type = 'user';

    # Смена типа таблицы
    public static function changeType(string $type) {
        self::$type = $type;
    }

    /**
     * Распределение данных по отдаваемым HTML\
     * Распределяется на страницу целиком, подстраницу для разделения должностей и сотрудников, таблицу или модальное окно
     *
     * @param array $param Обязательно ROUTE\
     * В страницу и подстраницу: TABLE, TOTAL, SELECTED, USER (current), DEPARTMENTS, STAFF_TABLE\
     * В таблицу: TABLE, TOTAL, SELECTED, USER (current)\
     * В модальное окно: MODAL_TYPE, USER, DEPARTMENTS, POSITIONS
     * @return HTML вставка кода на страницу или ошибка в класс Responce
     */
    public static function route(array $param) {
        switch ($param['ROUTE']) {
            case 'page':
                Responce::success(OK);
                self::getPage($param);
                break;

            case 'base':
                Responce::success(PARTIAL);
                self::getBase($param);
                break;

            case 'main':
                Responce::success(PARTIAL);
                self::getMain($param);
                break;

            case 'modal':
                Responce::success(PARTIAL);
                self::getModal($param);
                exit;
                break;

            default:
                Responce::exception(NOT_ACCEPTABLE, 'unknown page');
                break;
        }
    }

    # Получение страницы
    private static function getPage(array $param) {
        $type = self::$type;
        $admin = $param['USER']->isAdmin();

        $table = self::getTable($param, $admin);
        $footer = self::getFooter($param['TOTAL'], $param['USER']->onPageGet(), $param['SELECTED']);

        $pages = $footer['PAGES'];
        $select = $footer['SELECTER'];
        $totalRow = $param['TOTAL'];

        $dep = '<option data="0" value="0"></option>';
        $pos = '<option data="0" value="0"></option>';

        foreach ($param['DEPARTMENTS'] as $elem) {
            $id = $elem['ID'];
            $name = $elem['NAME'];

            $dep .= "<option value='$id'>$name</option>";
        }
        
        foreach ($param['STAFF_TABLE'] as $elem) {
            $id = $elem['ID'];
            $name = $elem['NAME'];

            $view = $param['USER']->getReversView();
            $data = array_shift($elem[$view['Отдел']]);

            $pos .= "<option data='$data' value='$id' style='display: none;'>$name</option>";
        }

        require_once _PATH . '/include/pages/basic.php';
    }

    private static function getBase(array $param) {
        $type = self::$type;
        $admin = $param['USER']->isAdmin();

        $table = self::getTable($param, $admin);
        $footer = self::getFooter($param['TOTAL'], $param['USER']->onPageGet(), $param['SELECTED']);
        
        $pages = $footer['PAGES'];
        $select = $footer['SELECTER'];
        $totalRow = $param['TOTAL'];

        if ($type == 'user') {
            echo '<header id="header">';

            require _PATH . '/include/pages//header.php';

            echo '</header><div style="background: #fff; border-radius: 10px;"><div id="main"><main id="table">' . $table . '</main><footer>';

            require _PATH . '/include/pages//footer.php';

            echo '</footer></div></div>';
        } elseif ($type == 'list') {
            // Добавить вывод списка должностей
        }
    }

    private static function getMain(array $param) {
        $table = self::getTable($param, $param['USER']->isAdmin());
        $footer = self::getFooter($param['TOTAL'], $param['USER']->onPageGet(), $param['SELECTED']);
        
        $pages = $footer['PAGES'];
        $select = $footer['SELECTER'];

        echo '<main id="table">' . $table . '</main><footer>';

        require _PATH . '/include/pages//footer.php';

        echo '</footer>';
    }

    private static function getModal(array $param) {
        global $USER;

        switch ($param['MODAL_TYPE']) {
            case 'table':
                $columns = $USER->getColumns();

                require _PATH . '/include/pages/tableColumns.php';
                break;
            
            case 'settings':
                // Указать настройки приложения
                require _PATH . '/include/pages/settings.php';
                break;
            
            case 'newUser':
                $modalParam = self::modalDepPos(departments : $param['DEPARTMENTS'], positions : $param['POSITIONS']);

                $dep = $modalParam['NO_DEPARTMENT'];
                $pos = $modalParam['NO_POSITIONS'];

                require _PATH . '/include/pages/newUser.php';
                break;

            case 'editUser':
                $user = $param['USER'][0];
                require _PATH . '/include/pages/editUser.php';
                break;
            
            case 'changeUserPos':
                $user = $param['USER'][0];
                $pos = [];

                $property = $USER->getReversView()['Отдел'];
                foreach ($param['POSITIONS'] as $item) {
                    $depId = array_shift($item[$property]);

                    if (in_array($depId, $user['UF_DEPARTMENT']) && $item['NAME'] != $user['WORK_POSITION']) {
                        $pos[] = $item;
                    }
                }

                require _PATH . '/include/pages/changeUserPos.php';
                break;

            case 'addUserDep':
                $user = $param['USER'][0];
                
                $modalParam = self::modalDepPos($user['UF_DEPARTMENT'], $param['DEPARTMENTS'], $param['POSITIONS']);

                $dep = $modalParam['NO_DEPARTMENT'];

                require _PATH . '/include/pages/addUserDep.php';
                break;

            case 'changeUserDep':
                $user = $param['USER'][0];
                
                $modalParam = self::modalDepPos($user['UF_DEPARTMENT'], $param['DEPARTMENTS'], $param['POSITIONS']);

                $depOld = $modalParam['UF_DEPATMENT'];
                $dep = $modalParam['NO_DEPARTMENT'];
                $pos = $modalParam['NO_POSITIONS'];

                require _PATH . '/include/pages/changeUserDep.php';
                break;

            case 'deleteUserDep':
                $user = $param['USER'][0];
                
                $modalParam = self::modalDepPos($user['UF_DEPARTMENT'], $param['DEPARTMENTS'], $param['POSITIONS']);

                $dep = $modalParam['UF_DEPATMENT'];

                require _PATH . '/include/pages/deleteUserDep.php';
                break;

            case 'deleteUser':
                $user = $param['USER'][0];

                require _PATH . '/include/pages/deleteUser.php';
                break;

            case 'toIntranet':
                $user = $param['USER'][0];

                $modalParam = self::modalDepPos($user['UF_DEPARTMENT'], $param['DEPARTMENTS'], $param['POSITIONS']);

                $dep = $modalParam['DEPARTMENTS'];
                $pos = $modalParam['POSITIONS'];

                require _PATH . '/include/pages/toIntranet.php';
                break;

            case 'toExtraner':
                $user = $param['USER'][0];
                $sonet = Sonet::getExtranet()['result'];
                $extranet = '';

                foreach ($sonet as $group) {
                    $id = $group['ID'];
                    $name = $group['NAME'];
        
                    $extranet .= "<option value='$id'>$name</option>";
                }

                require _PATH . '/include/pages/toExtraner.php';
                break;

            default:
                Responce::exception(NOT_ACCEPTABLE, 'unknown modal');
                break;
        }
    }

    private static function modalDepPos(array $userDepartments = [], array $departments = [], array $positions = []) {
        global $USER;

        $depOld = '';
        $dep = '';
        $depAll = '';

        $posOld = "<option data='0' value='0'></option>";
        $pos = "<option data='0' value='0'></option>";
        $posAll = "<option data='0' value='0'></option>";

        $posDep = [];

        foreach ($departments as $item) {
            $id = $item['ID'];
            $name = $item['NAME'];

            $depAll .= "<option value='$id'>$name</option>";

            if (!in_array($item['ID'], $userDepartments)) {
                $dep .= "<option value='$id'>$name</option>";

                $posDep[] = $item['ID'];
            } else {
                $depOld .= "<option value='$id'>$name</option>";
            }
        }

        $property = $USER->getReversView()['Отдел'];
        foreach ($positions as $item) {
            $depId = array_shift($item[$property]);

            $id = $item['ID'];
            $name = $item['NAME'];
            
            $posAll .= "<option data='$depId' value='$id' style='display: none;'>$name</option>";

            if (in_array($depId, $posDep)) {
                $pos .= "<option data='$depId' value='$id' style='display: none;'>$name</option>";
            } else {
                $posOld .= "<option data='$depId' value='$id' style='display: none;'>$name</option>";
            }
        }

        return array('DEPARTMENTS' => $depAll, 'POSITIONS' => $posAll, 'UF_DEPATMENT' => $depOld, 'UF_POSITIONS' => $posOld, 'NO_DEPARTMENT' => $dep, 'NO_POSITIONS' => $pos);
    }

    private static function getTable(array $data, bool $admin = false) {
        global $USER;

        $header = '';
        $table = '';
        $user = $USER->getId();
        $columns = $USER->getColumns();
        $src = _URI;

        if (self::$type == 'user') {
            if (!isset($columns['department']) || $columns['department'] === true) {
                $nameDeps = [];

                $deps = Department::get()['result'];
                foreach ($deps as $dep) {
                    $nameDeps[$dep['ID']] = $dep['NAME'];
                }
            }

            $header .= '<th id="th_ts" data="check"><span id="tablesetting" class="tableSettings" data="table" onclick="openModal(this);"></span></th>';
            $header .= !isset($columns['photo']) || $columns['photo'] === true ? '<th id="th_pt" data="photo"><span>Фото</span></th>' : '';
            $header .= '<th id="th_fn" data="full_name"><span>ФИО</span></th>';
            $header .= !isset($columns['birthday']) || $columns['birthday'] === true ? '<th id="th_bd" data="birthday"><span>Дата рождения</span></th>' : '';
            $header .= !isset($columns['gender']) || $columns['gender'] === true ? '<th id="th_gen" data="gender"><span>Пол</span></th>' : '';
            $header .= !isset($columns['phone']) || $columns['phone'] === true ? '<th id="th_ph" data="phone"><span>Телефон</span></th>' : '';
            $header .= !isset($columns['mail']) || $columns['mail'] === true ? '<th id="th_em" data="email"><span>E-Mail</span></th>' : '';
            $header .= !isset($columns['regdate']) || $columns['regdate'] === true ? '<th id="th_reg" data="register"><span>Дата регистрации</span></th>' : '';
            $header .= !isset($columns['position']) || $columns['position'] === true ? '<th id="th_pos" data="position"><span>Должность</span></th>' : '';
            $header .= !isset($columns['department']) || $columns['department'] === true ? '<th id="th_dep" data="department"><span>Отдел</span></th>' : '';

            foreach ($data['TABLE'] as $info) {
                $id = $info['ID'];
                $table .= '<tr>';
                
                $edit = $admin === true ? "<a class='dropdown-item open' data='editUser' onclick='openModal(this);' style='cursor: pointer;'>Изменить данные</a>" : '';
                $hash = Security::encrypt($id);

                $dep = $info['USER_TYPE'] == 'extranet' ?
                            "<a class='dropdown-item open' data='toIntranet' onclick='openModal(this);' style='cursor: pointer;'>Перевести в интранет</a>
                            <a class='dropdown-item open' data='deleteUser' onclick='openModal(this);' style='cursor: pointer;'>Уволить</a>"
                        :
                            "<a class='dropdown-item open' data='changeUserPos' onclick='openModal(this);' style='cursor: pointer;'>Сменить должность</a>
                            <a class='dropdown-item open' data='addUserDep' onclick='openModal(this);' style='cursor: pointer;'>Добавить в отдел</a>
                            <a class='dropdown-item open' data='changeUserDep' onclick='openModal(this);' style='cursor: pointer;'>Сменить отдел</a>
                            <a class='dropdown-item open' data='deleteUserDep' onclick='openModal(this);' style='cursor: pointer;'>Исключить из отдела</a>
                            <a class='dropdown-item open' data='toExtraner' onclick='openModal(this);' style='cursor: pointer;'>Перевести в экстранет</a>
                            <a class='dropdown-item open' data='deleteUser' onclick='openModal(this);' style='cursor: pointer;'>Уволить</a>";

                $table .= $user != $id ?
                            "<td align='center' class='dropright'>
                                <a type='button' id='$id' class='editButton' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'></a>
                                <div class='dropdown-menu' aria-labelledby='$id' data='$hash'>
                                    <div style='font-size: 10px; width: 20px; height: 20px; background: #ffffff; border-left: 2px solid #dedede; border-bottom: 1px solid #dedede; rotate: 45deg; position: absolute; top: 8px; left: -11px; z-index: -1;'></div>
                                    $edit
                                    $dep
                                </div>
                            </td>" : '<td></td>';
                
                if (!isset($columns['photo']) || $columns['photo'] === true) {
                    $photo = '';
    
                    if ($info['PERSONAL_PHOTO'] != null) {
                        $photo .= "<img id='photo_$id' src='" . $info['PERSONAL_PHOTO'] . "' onload=\"this.parentElement.children[1].remove(); this.style.display=''\" style='display: none; width: 100%; border-radius: 50%;'>";
                        $photo .= "<img src='$src/static/img/user.png' style='width: 100%; border-radius: 50%;'>";
                    } else {
                        $photo .= "<img src='$src/static/img/user.png' style='width: 100%; border-radius: 50%;'>";
                    }
        
                    if ($info['USER_TYPE'] == 'extranet') {
                        $photo .= "<img src='$src/static/img/extranetuser.svg' style='position: absolute; bottom: -2px; right: -2px; width: 40%;'>";
                    }
    
                    if ($info['ACTIVE'] === false) {
                        $photo .= "<img src='$src/static/img/fired.png' style='position: absolute; top: 0; left: 0; width: 100%; height: 100%;'>";
                    }

                    $table .=   '<td align="center">
                                    <div class="userImg" style="position: relative;">' .
                                        $photo .
                                    '</div>
                                </td>';
                }

                $table .= '<td style="padding-left: 10px">' . $info['LAST_NAME'] . ' ' . $info['NAME'] . ' ' . $info['SECOND_NAME'] . '</td>';
                
                if (!isset($columns['birthday']) || $columns['birthday'] === true) {
                    $birthday = strlen($info['PERSONAL_BIRTHDAY']) > 0 ? date("d.m.Y", strtotime($info['PERSONAL_BIRTHDAY'])) : '';
                    $table .= '<td align="center">' . $birthday . '</td>';
                }

                if (!isset($columns['gender']) || $columns['gender'] === true) {
                    if ($info['PERSONAL_GENDER'] == 'F') {
                        $gender = 'Жен';
                    } elseif ($info['PERSONAL_GENDER'] == 'M') {
                        $gender = 'Муж';
                    } else {
                        $gender = '';
                    }
                    
                    $table .= '<td align="center">' . $gender . '</td>';
                }

                $table .= !isset($columns['phone']) || $columns['phone'] === true ? '<td align="center">' . $info['PERSONAL_MOBILE'] . '</td>' : '';

                $regday = strlen($info['DATE_REGISTER']) > 0 ? date("d.m.Y", strtotime($info['DATE_REGISTER'])) : '';
                $table .= !isset($columns['regdate']) || $columns['regdate'] === true ? '<td align="center">' . $regday . '</td>' : '';
                
                $table .= !isset($columns['mail']) || $columns['mail'] === true ? '<td style="padding-left: 10px">' . $info['EMAIL'] . '</td>' : '';
                
                $table .= !isset($columns['position']) || $columns['position'] === true ? '<td style="padding-left: 10px">' . $info['WORK_POSITION'] . '</td>' : '';

                if (!isset($columns['department']) || $columns['department'] === true) {
                    $depList = '';

                    $end = array_key_last($info['UF_DEPARTMENT']);
                    foreach ($info['UF_DEPARTMENT'] as $key => $udep) {
                        if (isset($nameDeps[$udep])) {
                            $depList .= $nameDeps[$udep];
                        } else {
                            $depList .= $udep;
                        }

                        $depList .= $end == $key ? '' : ", ";
                    }

                    $table .= '<td style="padding-left: 10px">' . $depList . '</td>';
                }
                
                $table .= '</tr>';
            }
        } elseif (self::$type == 'list') {
            foreach ($data as $info) {}
        } else {
            Responce::exception(NOT_ACCEPTABLE, 'unknown page');
        }

        return '<table><thead><tr style="height: 40px;">' . $header . '</tr></thead><tbody>' . $table . '</tbody></table>';
    }

    private static function getFooter(int $totalRow, int $rowOnPage, int $pageSelected) {
        $pageCount = ceil($totalRow / $rowOnPage);

        $pages = '';
        if ($pageCount > 7) {
            $pages .= $pageSelected == 1 ? "<a class='page selected'>1</a>" : "<a class='page' onclick='pageSelect(1);'>1</a>";
        
            if ($pageSelected < 5) {
                for ($i = 2; $i <= 5; $i++) {
                    $pages .= $pageSelected == $i ? "<a class='page selected'>$i</a>" : "<a class='page' onclick='pageSelect($i);'>$i</a>";
                }
        
                $temp = round($pageCount / 2) + 2;
                $pages .= "<a class='page' onclick='pageSelect($temp);'>...</a>";
            } elseif ($pageSelected > $pageCount - 4) {
                $temp = round($pageCount / 2) - 2;
                $pages .= "<a class='page' onclick='pageSelect($temp);'>...</a>";
        
                for ($i = $pageCount - 4; $i < $pageCount; $i++) {
                    $pages .= $pageSelected == $i ? "<a class='page selected'>$i</a>" : "<a class='page' onclick='pageSelect($i);'>$i</a>";
                }
            } else {
                $temp = round($pageSelected / 2) - 1;
                $pages .= "<a class='page' onclick='pageSelect($temp);'>...</a>";
                
                for ($i = $pageSelected - 2; $i <= $pageSelected + 2; $i++) {
                    $pages .= $pageSelected == $i ? "<a class='page selected'>$i</a>" : "<a class='page' onclick='pageSelect($i);'>$i</a>";
                }
        
                $temp = round(($pageCount - $pageSelected) / 2) + $pageSelected + 1;
                $pages .= "<a class='page' onclick='pageSelect($temp);'>...</a>";
            }
        
            $pages .= $pageSelected == $pageCount ? "<a class='page selected'>$pageCount</a>" : "<a class='page' onclick='pageSelect($pageCount);'>$pageCount</a>";
        } else {
            for ($i = 1; $i <= $pageCount; $i++) {
                $pages .= $pageSelected == $i ? "<a class='page selected'>$i</a>" : "<a class='page' onclick='pageSelect($i);'>$i</a>";
            }
        }
        
        $select = '';
        $selectPages = [5, 10, 20, 50, 100];
        foreach ($selectPages as $page) {
            $select .= $page == $rowOnPage ? "<option value='$page' selected='selected'>$page</option>" : "<option value='$page'>$page</option>";
        }

        return array('PAGES' => $pages, 'SELECTER' => $select);
    }

}
