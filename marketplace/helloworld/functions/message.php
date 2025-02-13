<?php 

require_once (__DIR__.'/../crest/crest.php');

class Message {
    public static function addMessage($idBot = "", $idUser = "", $message = "") {

        if(empty($idBot) || empty($idUser) || empty($message)) {
            return false;
        }

        $idBot = intval($idBot);
        $idUser = intval($idUser);

        return CRest::call('imbot.message.add', array(
            'BOT_ID' => $idBot,
            'DIALOG_ID' => $idUser,
            'MESSAGE' => $message
        ));

    }
}