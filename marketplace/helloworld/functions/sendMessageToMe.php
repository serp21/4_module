<?php 


require_once(__DIR__ . '/../crest/crest.php');
require_once (__DIR__.'/../crest/crestcurrent.php');
require_once (__DIR__.'/message.php');
require_once (__DIR__.'/storage.php');
require_once (__DIR__.'/bots.php');
require_once (__DIR__.'/user.php');

$input = json_decode(file_get_contents('php://input'));

if(!empty($input->USER_ID)) {
    $userID = htmlspecialchars($input->USER_ID);
    $botID = Bots::getActiveBot()['ID'];
    $message = Storage::getStorage("MESSAGE");
}


// $result = CRest::call(
// 	'event.test',
// 	[
// 		'any' => 'data'
// 	]
// );

// $event = CRest::call("event.unbind", array(
//     "event" => "ONUSERADD",
//     "handler" => 'http://dev.vestrus.ru/marketplace/helloworld/install/install.php/. ./handlers/useradd.php'
// ));
if(isset($userID)) {
    $response = Message::addMessage($botID, $userID, $message);
    echo json_encode($response);
}




