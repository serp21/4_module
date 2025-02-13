<?php

namespace app\lib;

/**
 * Класс работы с "безопасностью"
 */
class Security {
    private const KEY = 'KgSD464a';
    
    /**
     * Кодирование строки в хеш
     *
     * @param string $code строка для хеширования
     * @return string хеш строка
     */
    public static function encrypt(string $code) {
        return openssl_encrypt($code, openssl_get_cipher_methods()[19], self::KEY);
    }
    
    /**
     * Декодирование хеша в строку
     *
     * @param string $code строка для расхеширования
     * @return string строка из хеш
     */
    public static function decrypt(string $hash) {
        return openssl_decrypt($hash, openssl_get_cipher_methods()[19], self::KEY);
    }

    /**
     * Проверка совпадения строки и хеша
     *
     * @param string $hash хеш строка
     * @param string $code строка в хеш
     * @return bool true или false
     */
    public static function check(string $hash, string $code) {
        $checkCode = self::decrypt($hash);

        if ($checkCode == $code) {
            return true;
        }

        return false;
    }
}