<?php

require_once(__DIR__ . '/../crest/crest.php');
require_once (__DIR__.'/../crest/crestcurrent.php');

class User {
    public static function isAdmin() {
        return CRestCurrent::call('user.admin')['result'];
    }
    public static function getUserCurrentID() {
        return CRestCurrent::call('user.current')['result']['ID'];
    }
}
