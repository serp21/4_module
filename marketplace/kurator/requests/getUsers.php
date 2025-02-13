<?php

require_once (__DIR__.'/../crest/crest.php');
require_once (__DIR__.'/../crest/crestcurrent.php');

if (isset($_POST['search'])) {

    $result_user = CRest::call(
        'user.search',
        array("FILTER" => array("LAST_NAME" => $_POST['search'], 'ACTIVE' => 'Y' ))
    );

    echo json_encode(array_values($result_user), JSON_UNESCAPED_UNICODE);

}

?>