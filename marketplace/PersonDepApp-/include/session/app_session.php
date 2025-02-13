<?php

namespace app\session;

use app\Responce;
use app\lib\Option;

/**
 * Класс настроек приложения
 */
class AppSettings {
    protected bool $endpoint;
    protected string $tableCode;
    protected int $tableId;

    protected bool $headAccess;
    protected bool $showExceptions;
    protected int $reload;

    protected array $userAccess;
    protected array $beforeDismissal;
    protected array $forvardView;
    protected array $reversView;

    public function __construct() {
        $appOptions = Option::appGet()['result'];

        if (isset($appOptions['ENDPOINT_TABLE'])) {
            $this->endpoint = $appOptions['ENDPOINT_TABLE'];
        } else {
            Responce::error(NOT_FOUND, 'Не удалось получить данные списка должностей. Перезагрузите страницу или обратитесь за помощью к администратору.');
        }

        if (isset($appOptions['TABLE_CODE'])) {
            $this->tableCode = $appOptions['TABLE_CODE'];
        } else {
            $this->tableCode = '';
            if (isset($appOptions['TABLE_ID'])) {
                $this->tableId = $appOptions['TABLE_ID'];
            } else {
                Responce::error(NOT_FOUND, 'Не удалось получить настройки списка должностей. Перезагрузите страницу или обратитесь за помощью к администратору.');
            }
        }

        if (isset($appOptions['ACCESS_HEAD'])) {
            $this->headAccess = $appOptions['ACCESS_HEAD'];
        } else {
            $this->headAccess = false;
        }

        if (isset($appOptions['SHOW_EXCEPTIONS'])) {
            $this->showExceptions = $appOptions['SHOW_EXCEPTIONS'];
        } else {
            $this->showExceptions = false;
        }

        if (isset($appOptions['RELOAD'])) {
            $this->reload = $appOptions['RELOAD'];
        } else {
            $this->reload = 0;
        }

        if (isset($appOptions['ACCESS_USERS'])) {
            $this->userAccess = $appOptions['ACCESS_USERS'];
        } else {
            $this->userAccess = [];
        }

        if (isset($appOptions['BEFORE_DISMISSAL'])) {
            $this->beforeDismissal = $appOptions['BEFORE_DISMISSAL'];
        } else {
            Responce::error(NOT_FOUND, 'Не удалось получить настройки проверки предварительного увольнения сотрудников. Перезагрузите страницу или обратитесь за помощью к администратору.');
        }

        if (isset($appOptions['FORVARD_VIEW'])) {
            $this->forvardView = $appOptions['FORVARD_VIEW'];
        } else {
            Responce::error(NOT_FOUND, 'Не удалось получить настройки прямого просмотра списка должностей. Перезагрузите страницу или обратитесь за помощью к администратору.');
        }

        if (isset($appOptions['REVERS_VIEW'])) {
            $this->reversView = $appOptions['REVERS_VIEW'];
        } else {
            Responce::error(NOT_FOUND, 'Не удалось получить настройки обратного просмотра списка должностей. Перезагрузите страницу или обратитесь за помощью к администратору.');
        }
    }

    /**
     * Установить настройки приложения
     *
     * @param array $param массив настроек
     * @return array массив CRest или ошибка в класс Responce
     */
    public function set(array $param = []) {
        if (!empty($param)) {
            $option = Option::appSet($param);

            if (isset($option['result'])) {
                delay(400000);
                $this->__construct();

                return $option;
            } else {
                Responce::exception(FAILED, $option['error'] . '<br>' . $option['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'Невозможно установить настройки приложения с пустыми значениями.');
        }
    }

    /**
     * Получить значение списка штатного расписания
     *
     * @return array часть готового запроса для класса Lists
     */
    public function getTable() {
        if ($this->endpoint === true && $this->tableCode != '') {
            return array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_CODE' => $this->tableCode);
        } else {
            return array('IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => $this->tableId);
        }
    }

    /**
     * Проверить доступ для руководителей отделов
     *
     * @return bool true или false
     */
    public function isHeadAccess() {
        return $this->headAccess;
    }

    /**
     * Проверить отображение исключений пользователям без прав администратора
     *
     * @return bool true или false
     */
    public function isException() {
        return $this->showExceptions;
    }

    /**
     * Получить количество повторов для запроса
     *
     * @return int количество повторов
     */
    public function getReload() {
        return $this->reload;
    }

    /**
     * Получить список элементов для проверки перед увольнением сотрудника
     *
     * @return array список элементов CRM, сделок, задач и т.д.
     */
    public function getDismissal() {
        return $this->beforeDismissal;
    }

    /**
     * Получить зону прямого просмотра (PROPERTY => НАЗВАНИЕ)
     *
     * @return array список прямого просмотра
     */
    public function getForvardView() {
        return $this->forvardView;
    }

    /**
     * Получить зону обратного просмотра (НАЗВАНИЕ => PROPERTY)
     *
     * @return array список обратного просмотра
     */
    public function getReversView() {
        return $this->reversView;
    }
}
