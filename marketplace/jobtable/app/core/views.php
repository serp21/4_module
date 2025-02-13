<?php

class View
{
    public function view($name, $data = array())
    {
        include_once(_PATH . "/app/views/" . $name . ".php");
        extract($data);
    }
}
