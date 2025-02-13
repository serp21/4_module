<?php

namespace app\session;

use app\lib\Option;
use app\lib\User;

use app\Responce;

/**
 * Класс настроек пользователя, наследуемый от класса настроек приложения
 */
class UserSettings extends AppSettings {
    protected array $head;
    protected bool $admin;
    protected int $id;
    protected string $uidBX;

    protected int $time = 0;
    protected const DURATION = 3600;
    private int $count = REQUEST_COUNT;

    protected array $table;

    public function __construct() {
        parent::__construct();
        
        $userCurrent = User::current()['result'];
        delay();
        $userOptions = Option::userGet()['result'];

        $this->head = $userCurrent['UF_HEAD'];
        $this->admin = $userCurrent['ADMIN'];
        $this->id = $userCurrent['ID'];

        $this->table['COLUMNS'] = $userOptions['COLUMNS'];
        $this->table['ON_PAGE'] = isset($userOptions['COUNT']) ? $userOptions['COUNT'] : 10;
        $this->table['sort'] = isset($userOptions['sort']) ? $userOptions['sort'] : 'LAST_NAME';
        $this->table['order'] = isset($userOptions['order']) ? $userOptions['order'] : 'ASC';

        $time = time();

        if ($time > $this->time) {
            if ($this->count < REQUEST_COUNT) {
                $this->count += $time - $this->time;

                if ($this->count > REQUEST_COUNT) {
                    $this->count = REQUEST_COUNT;
                }
            }

            $this->time = $time;
        } else {
            if ($this->count == 0) {
                $this->time = $time + 60;
                $this->count = REQUEST_COUNT;

                Responce::error(TOO_MANY, 'Превышено количество запросов. Доступ к приложению заблокирован на 1 минуту.<br><br>');
            } else {
                $this->count--;
            }
        }

        if (isset($_COOKIE['BITRIX_SM_UIDD'])) {
            $this->uidBX = $_COOKIE['BITRIX_SM_UIDD'];
        } else {
            Responce::error(UNAUTHORIZED, 'bitrix auth fail');
        }
    }

    /**
     * Проверка количества запросов к серверу
     *
     * @return void ничего или ошибка в класс Responce
     */
    public function checkRequest() {
        $time = time();

        if ($time > $this->time) {
            if ($this->count < REQUEST_COUNT) {
                $this->count += $time - $this->time;

                if ($this->count > REQUEST_COUNT) {
                    $this->count = REQUEST_COUNT;
                }
            }

            $this->time = $time;
        } else {
            if ($this->count == 0) {
                $this->time = $time + 60;
                $this->count = REQUEST_COUNT;

                Responce::error(TOO_MANY, 'Превышено количество запросов. Доступ к приложению заблокирован на 1 минуту.<br><br>');
            } else {
                $this->count--;
            }
        }
    }

    /**
     * Проверка продолжительности бездействия сессии
     *
     * @return boolean true, если время с последнего запроса превышено, иначе false
     */
    public function isReload() {
        $duration = time() - $this->time;

        if ($duration > self::DURATION) {
            return true;
        }

        return false;
    }

    /**
     * Получить тип доступа сотрудника
     *
     * @return string значение доступа
     */
    public function getAccess() {
        if ($this->admin === true) {
            return 'ADMIN';
        } elseif (in_array($this->id, $this->userAccess)) {
            return 'FULL';
        } elseif ($this->headAccess === true && !empty($this->head)) {
            return 'HEAD';
        }
        
        return 'USER';
    }

    /**
     * Получить информацию о текущем пользователе
     *
     * @return array со значениями ID, BITRIX_SM_UIDD, ADMIN и UF_HEAD
     */
    public function current() {
        return array('ID' => $this->id, 'BITRIX_SM_UIDD' => $this->uidBX, 'ADMIN' => $this->admin, 'UF_HEAD' => $this->head);
    }

    /**
     * Получить идентификатор текущего пользователя
     *
     * @return int id пользователя
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Проверить сессию текущего пользователя
     *
     * @return bool true, если сессия не изменилась, и false, если пустая или изменилась
     */
    public function isSession() {
        if (!isset($_COOKIE['BITRIX_SM_UIDD']) || empty($_COOKIE['BITRIX_SM_UIDD']) || $_COOKIE['BITRIX_SM_UIDD'] !== $this->uidBX) {
            return false;
        }

        return true;
    }

    /**
     * Получить идентификатор Bitrix текущего пользователя
     *
     * @return string id пользователя
     */
    public function getUID() {
        return $this->uidBX;
    }

    /**
     * Получить информацию об администраторе
     *
     * @return boolean значение true или false
     */
    public function isAdmin() {
        return $this->admin;
    }

    /**
     * Получить список всех по иерархии отделов пользователя, в которых он является руководителем
     *
     * @return array массив идентификаторов отделов
     */
    public function getHead() {
        return $this->head;
    }

    /**
     * Получить список столбцов таблицы
     *
     * @return array массив столбцов таблицы пользователя
     */
    public function getColumns() {
        $responce = array();

        foreach ($this->table['COLUMNS'] as $name => $column) {
            if ($column === true || $column === 'true') {
                $responce[$name] = true;
            } else {
                $responce[$name] = false;
            }
        }

        return $responce;
    }

    /**
     * Задать значение отображаемых столбцов таблиц
     *
     * @param array $columns массив названий столбцов
     * @return array массив CRest или ошибка в класс Responce
     */
    public function setColumns(array $columns) {
        $option = Option::userSet(array('COLUMNS' => $columns));

        if (isset($option['result'])) {
            $this->table['COLUMNS'] = $columns;

            return $option;
        } else {
            Responce::exception(FAILED, $option['error'] . '<br>' . $option['error_description']);
        }
    }

    /**
     * Получить количество строк в таблице на странице
     *
     * @return int число строк таблицы пользователя
     */
    public function onPageGet() {
        return $this->table['ON_PAGE'];
    }

    /**
     * Задать значение отображаемых строк таблиц на странице
     *
     * @param int $onPage число строк таблицы пользователя
     * @return array массив CRest или ошибка в класс Responce
     */
    public function onPageSet(int $onPage) {
        $option = Option::userSet(array('ON_PAGE' => $onPage));

        if (isset($option['result'])) {
            $this->table['ON_PAGE'] = $onPage;
            
            return $option;
        } else {
            Responce::exception(FAILED, $option['error'] . '<br>' . $option['error_description']);
        }
    }

    /**
     * Получить значение сортировки
     *
     * @return string название поля сортировки сотрудников
     */
    public function getSort() {
        return $this->table['sort'];
    }

    /**
     * Задать значение сортировки
     *
     * @param string название поля сортировки сотрудников
     * @return array массив CRest или ошибка в класс Responce
     */
    public function setSort(string $sort) {
        $option = Option::userSet(array('sort' => $sort));

        if (isset($option['result'])) {
            $this->table['sort'] = $sort;
            
            return $option;
        } else {
            Responce::exception(FAILED, $option['error'] . '<br>' . $option['error_description']);
        }
    }

    /**
     * Получить направление сортировки
     *
     * @return string ASC или DESC
     */
    public function getOrder() {
        return $this->table['order'];
    }

    /**
     * Задать направление сортировки
     *
     * @param string ASC или DESC
     * @return array массив CRest или ошибка в класс Responce
     */
    public function setOrder(string $order) {
        if ($order == 'DESC' && $order == 'ASC') {
            $order = 'ASC';
        }

        $option = Option::userSet(array('order' => $order));

        if (isset($option['result'])) {
            $this->table['order'] = $order;
            
            return $option;
        } else {
            Responce::exception(FAILED, $option['error'] . '<br>' . $option['error_description']);
        }
    }
}
