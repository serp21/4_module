<?php
require_once (__DIR__.'/../crest/crest.php');
require_once (__DIR__.'/../crest/crestcurrent.php'); 

$resultGet = CRest::call('app.option.get', []);

$tasks = array(0);

if($resultGet['result']['tasksToUpdate']) {
	$tasks = $resultGet['result']['tasksToUpdate'];
}

array_push($tasks, $_REQUEST['data']['FIELDS_AFTER']["ID"]);

$result = CRest::call('app.option.set',[
	"options"=>[
		'tasksToUpdate' => $tasks
	]
]);

$result01 = CRest::call('tasks.task.update',[
	"taskId" => $_REQUEST['data']['FIELDS_AFTER']["ID"],
	"fields" => ["ID" => $_REQUEST['data']['FIELDS_AFTER']["ID"]]
]);

file_put_contents(
	__DIR__ . '/log/' . "LOG_ADD_" . date('d-m-Y-H-i-s') . '.txt',
	var_export(array_merge($_REQUEST, $tasks), true)
);

?>