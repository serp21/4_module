<?php 
require_once (__DIR__.'/../crest/crest.php');
require_once (__DIR__.'/storage.php');


class Bots {
    public static function installBot($code = "", $handler = "" , $properties = array()) {

        if(empty($properties) || empty($code) || empty($handler)){
            return false;
        }

        $idBot = CRest::call('imbot.register', array(
            "CODE" => $code,
            "EVENT_HANDLER" => $handler,
            "PROPERTIES" => $properties
        ));

        return $idBot;
    }
    public static function updateBot($idBot, $code = "", $name = "") {

        if(empty($name) || empty($code) || empty($idBot)){
            return false;
        }

        $update = CRest::call("imbot.update", array(
            "BOT_ID" => $idBot,
            "FIELDS" => array(
                "CODE" => $code,
                "PROPERTIES" => array(
                    'NAME' => $name
                )
            )
        ));

        return true;
    }
    public static function getBotsInfo() {
        return CRest::call('imbot.bot.list')['result'];
    }
    public static function createSelect($botsInfo = array()) {

        $selectOut = "<select name='from' class='from_body' data-type='outActiveBot'>";

        foreach($botsInfo as $botInfo) {
            $botID = $botInfo['ID'];
            $botName = $botInfo['NAME'];
            $selectOut .= "<option value='$botID'>$botName</option>";
        }

        $selectOut .= "</select>";
            
       return $selectOut;
    }
    public static function getActiveBot() {
        $activeBotID = Storage::getStorage("ACTIVE_BOT_ID");
        $activeBot = array();

        if(empty($activeBotID)) {
            return false;
        }

        $bots = static::getBotsInfo();
        foreach($bots as $botID => $bot) {
            if($botID == $activeBotID) {
                $activeBot = $bot;
                break;
            }
        }
        return $activeBot;
    }
    public static function setActiveBot($idBot) {
        if(empty($idBot)) {
            return false;
        }
    
        return CRest::call('app.option.set', [
            "options" => [
                "ACTIVE_BOT_ID" => $idBot
            ]
        ]);
    }
}


