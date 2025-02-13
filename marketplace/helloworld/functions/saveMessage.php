<?php 

require_once (__DIR__.'/storage.php');

$input = json_decode(file_get_contents('php://input'));

if(!empty($input->MESSAGE)) {
    $message = htmlspecialchars($input->MESSAGE);
}

if(isset($message)) {
  
    if(Storage::setMessage($message)) {
        echo json_encode("Сообщение успешно сохранено");
    }
    
} else {
    $out = array("error" => "Сообщение не сохранено. Пустое сообщение");
    echo json_encode($out);
}

