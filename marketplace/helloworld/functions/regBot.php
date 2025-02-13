<?php 
require_once (__DIR__.'/bots.php');
require_once (__DIR__.'/../config/config.php');

$input = json_decode(file_get_contents('php://input'));

if(!empty($input->NAME)) {
    $botName = htmlspecialchars($input->NAME);
    if(!empty($input->AVATAR)) {
        $botAvatarB64 = explode(",", $input->AVATAR)[1];
    } else {
        $botAvatarB64 = $DEFAULT_IMG_BASE64;
    }
}


if(isset($botName)) {
    $botProperties = array(
        "NAME" => $botName
    );
    if(isset($botAvatarB64)) {
        $botProperties['PERSONAL_PHOTO'] = $botAvatarB64;
    }
    $idBot = Bots::installBot("helloworldbot", $DOMAIN . $MARKET_PATH . 'helloworld/handlers/bot.php', $botProperties)['result'];
    Bots::setActiveBot($idBot);
	Bots::updateBot($idBot, 'helloworldbot', $botName);

    echo json_encode($idBot); 
}


