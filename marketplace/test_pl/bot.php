<?php

require_once(__DIR__.'/crest.php');


$g_db = new mysqli('37.140.192.240', 'u2710137_default', 'Ap8MMpbG6Awxv9G0', 'u2710137_default');


function GetAppOption($optName)
{
	return CRest::call('app.option.get', ['option' => $optName])['result'];
}


$g_bot_id = (int)GetAppOption('bot_id');




// receive event "new message for bot"
if ($_REQUEST['event'] == 'ONIMBOTMESSAGEADD')
{
	$user_id = $_REQUEST['data']['PARAMS']['FROM_USER_ID'];
	$command = $_REQUEST['data']['PARAMS']['MESSAGE'];
	$domain = $_REQUEST['auth']['domain'];



	ProcessCommand($domain, $user_id, $command);
}





function ProcessCommand($domain, $user_id, $command)
{
	global $g_bot_id, $g_db;

        $result = CRest::call('imbot.message.add', array(
            //'BOT_ID' => $idBot,
            'BOT_ID' => $g_bot_id,
            //'DIALOG_ID' => $idUser,
            'DIALOG_ID' => $user_id,
            //'MESSAGE' => print_r($_REQUEST, true)
            //'MESSAGE' => $domain
            'MESSAGE' => (int)$g_db
            //'MESSAGE' => 'hi'
        ));



}




function WriteToLogFile($fn, $str)
{
	$fp = fopen($fn, 'a');
	if ($fp)
	{
		$dt = date('Y-m-d H:i:s');
		fwrite($fp, "{$dt} | {$str}\r\n");
		fclose($fp);
	}
}


//WriteToLogFile('/home/bitrix/www/marketplace/test_pl/log.txt', print_r($_REQUEST, true));



//$idUser = $_REQUEST['data']['PARAMS']['DIALOG_ID'];


	//$isAdmin = CRest::call('user.admin');





//WriteToLogFile('/home/bitrix/www/marketplace/test_pl/log.txt', print_r($result, true));


/*
	$result = CRestCurrent::call('imbot.message.add',
		array(
			'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			'MESSAGE'   => 'hi',
		)
	);
*/





?>