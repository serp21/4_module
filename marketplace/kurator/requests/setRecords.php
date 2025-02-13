<?php

require_once (__DIR__.'/../crest/crest.php');
require_once (__DIR__.'/../crest/crestcurrent.php');


$result = CRest::call('app.option.set',[
	"options"=>[
		'people_settings' => $_POST["people"],
	]
]);

echo print_r($result);

?>