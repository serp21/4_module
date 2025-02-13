<?php

require_once (__DIR__.'/../crest/crest.php');
require_once (__DIR__.'/../crest/crestcurrent.php');


$result = CRest::call('app.option.get', [
	'option' => 'people_settings'
]);

echo json_encode($result);

?>