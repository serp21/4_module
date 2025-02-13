<?php 

require_once (__DIR__.'/bots.php');

$input = json_decode(file_get_contents('php://input'));

if(!empty($input->ACTIVE_BOT_ID)) {
    $idBot = htmlspecialchars($input->ACTIVE_BOT_ID);
}

if(isset($idBot)) {
  
    if(Bots::setActiveBot($idBot)) {
        echo json_encode("Активный бот успешно сохранено");
    }
    
} else {
    $out = array("error" => "Активный бот не сохранен. Пустой идентификатор");
    echo json_encode($out);
}