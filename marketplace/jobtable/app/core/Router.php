<?php

namespace App\Core;

// use App\Controllers\MainController;

class Router
{
    public static function route()
    {

        $controllerName = $_POST['controller'];
        $methodName = $_POST['method'];
        $data = $_POST['data'];



        if (!isset($controllerName)) {

            require_once(__DIR__ . "/../controllers/MainController.php");

            $controller = new \MainController;

            $controller->index();
        } else if (isset($controllerName) && empty($controllerName)) {
            http_response_code(404);
            die();
        } else if (!isset($methodName) || empty($methodName)) {

            if (!is_string($controllerName) || !is_string($methodName)) {
                http_response_code(422);
                die();
            }

            $controllerName = mb_strtoupper(mb_substr($_POST['controller'], 0, 1)) . mb_substr($_POST['controller'], 1, mb_strlen($_POST['controller']))  . "Controller";

            if (file_exists(__DIR__ . "/../controllers/" . $controllerName . ".php")) {
                require_once(__DIR__ . "/../controllers/" . $controllerName . ".php");

                $controller = new $controllerName;
                $controller->index();
            } else {
                http_response_code(404);
            }

            die();
        }

        if (!is_string($controllerName) || !is_string($methodName)) {
            http_response_code(422);
            die();
        }

        $controllerName = mb_strtoupper(mb_substr($_POST['controller'], 0, 1)) . mb_substr($_POST['controller'], 1, mb_strlen($_POST['controller']))  . "Controller";

        if (file_exists(__DIR__ . "/../controllers/" . $controllerName . ".php")) {
            require_once(__DIR__ . "/../controllers/" . $controllerName . ".php");

            $controller = new $controllerName;
            $result = $controller->$methodName($data);

        } else {
            http_response_code(404);
        }
    }
}
