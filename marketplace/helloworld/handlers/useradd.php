<?php

// Событие ONUSERADD - срабатывает когда пользователь "принимает" приглашение на сообщение в почте. 


file_put_contents(
	__DIR__ . '/log/' . "REQ_" . date('d-m-Y-H-i-s') . '.txt',
	var_export($_REQUEST, true)
);

require_once (__DIR__.'/../functions/message.php');
require_once (__DIR__.'/../functions/storage.php');
require_once (__DIR__.'/../functions/bots.php');

$userID = $_REQUEST['data']['ID']; // получаем идентификатор зарегистрирвоанного пользователя
$botID = Bots::getActiveBot()['ID']; // получаем идентификатор активного бота для приложения
$message = Storage::getStorage('MESSAGE'); // получаем из хранилища приложения активное сообщение

if(!empty($botID) && !empty($userID) && !empty($message)) {
	$response = Message::addMessage($botID, $userID, $message); // отправляем сообщение пользователю

	file_put_contents(
		__DIR__ . '/log/' . "MES_" . date('d-m-Y-H-i-s') . '.txt',
		var_export($response, true)
	);
}