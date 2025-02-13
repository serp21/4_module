<?php

namespace app\lib;

use app\Responce;
use \CRest;

/**
 * Класс работы с параметрами приложения
 */
class Option {

    /**
     * Установить параметр приложения
     *
     * @param array $options массив настроек приложения
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function appSet(array $options) {
        if (!empty($options)) {
            $aoptions = CRest::call('app.option.set', array('options' => $options));

            if (isset($aoptions['result'])) {
                return $aoptions;
            } else {
                Responce::exception(FAILED, $aoptions['error'] . '<br>' . $aoptions['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'fail options');
        }
    }

    /**
     * Получить параметр приложения
     *
     * @param string $options строка параметра, если пусто, вернёт всё
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function appGet(string $options = '') {
        if (strlen($options) > 0) {
            $aoptions = CRest::call('app.option.get', array('option' => $options));
        } else {
            $aoptions = CRest::call('app.option.get');
        }

        if (isset($aoptions['result'])) {
            return $aoptions;
        } else {
            Responce::exception(BAD_REQUEST, $aoptions['error'] . '<br>' . $aoptions['error_description']);
        }
    }

    /**
     * Установить параметр приложения с привязкой к пользователю
     *
     * @param array $options массив настроек пользователя
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function userSet(array $options) {
        if (!empty($options)) {
            $uoptions = CRest::call('user.option.set', array('options' => $options));

            if (isset($uoptions['result'])) {
                return $uoptions;
            } else {
                Responce::exception(FAILED, $uoptions['error'] . '<br>' . $uoptions['error_description']);
            }
        } else {
            Responce::exception(BAD_REQUEST, 'fail options');
        }
    }

    /**
     * Получить параметр приложения с привязкой к пользователю
     *
     * @param string $options строка параметра, если пусто, вернёт всё
     * @return array массив CRest или ошибка в класс Responce
     */
    public static function userGet(string $options = '') {
        if (strlen($options) > 0) {
            $uoptions = CRest::call('user.option.get', array('option' => $options));
        } else {
            $uoptions = CRest::call('user.option.get');
        }

        if (isset($uoptions['result'])) {
            return $uoptions;
        } else {
            Responce::exception(FAILED, $uoptions['error'] . '<br>' . $uoptions['error_description']);
        }
    }

}
