<?php 

// Класс для работы с хранилищем приложения

require_once (__DIR__.'/../crest/crest.php');


class Storage {
    public static function getAllStorage() {
        return CRest::call('app.option.get', [])['result'];
    }
    
    public static function getStorage($storage = "") {
        if(empty($storage)) {
            return false;
        }

        return CRest::call('app.option.get', [
            "option" => $storage
        ])['result'];
    }

    public static function setMessage($message = "") {
        if(empty($message)) {
            return false;
        }
    
        return CRest::call('app.option.set', [
            "options" => [
                "MESSAGE" => $message
            ]
        ]);
    }
}