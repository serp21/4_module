<?php
require_once (__DIR__.'/../crest/crest.php');
require_once (__DIR__.'/../crest/crestcurrent.php'); 


$taskInfo = CRest::call('tasks.task.get', [
	'taskId' => $_REQUEST['data']['FIELDS_BEFORE']["ID"]
]);

$taskToAdd = false;

$resultGet = CRest::call('app.option.get', []);

if(is_countable($resultGet['result']['tasksToUpdate']) && count($resultGet['result']['tasksToUpdate']) > 0) {

	if (in_array($_REQUEST['data']['FIELDS_BEFORE']["ID"], $resultGet['result']['tasksToUpdate'])) {

		$taskToAdd = true;
		
		$resultGet['result']['tasksToUpdate'] = array_diff($resultGet['result']['tasksToUpdate'], [$_REQUEST['data']['FIELDS_BEFORE']["ID"]]);

		CRest::call('app.option.set',[
			"options"=>[
				'tasksToUpdate' => $resultGet['result']['tasksToUpdate'] 
			]
		]);
	}
}

$observers = array();

foreach ($resultGet['result']['people_settings'] as $res) {
    if($res['sup'] == $taskInfo['result'][ 'task']['responsibleId']) {
		if($res['upd'] === 'true' || $taskToAdd == true) {
			array_push($observers, $res['cur']);
		}
	}
}

$resultuUpdate = array();

if(is_countable($observers) && count($observers) > 0) {

	if (!empty(array_diff($observers, $taskInfo['result']['task']['auditors']))) {
		$resultuUpdate = CRest::call('tasks.task.update', [
			'taskId' => $_REQUEST['data']['FIELDS_BEFORE']["ID"],
			'fields' => array("AUDITORS" => array_merge($observers, $taskInfo['result'][ 'task']['auditors']))
		]);
	}
}

file_put_contents(
	__DIR__ . '/log/' . "LOG_UPD_" . date('d-m-Y-H-i-s') . '.txt',
	var_export(array_merge($_REQUEST, $taskInfo, $resultGet, $resultuUpdate), true)
);


?>