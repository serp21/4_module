<?php

namespace app;

class Responce {

    /**
     * Формирование списка аргументов функций трассировки
     *
     * @param array $args передаваемые аргумены функции
     * @param integer $line начальный отступ, по умолчанию 0
     * @return string строка аргументов
     */
    private static function arguments(array $args, int $line = 0)
    {
        $argument = '';
    
        $end = array_key_last($args);
        foreach ($args as $key => $arg) {
            $argument .= str_repeat("&emsp;", $line) . $key . ' => ';
    
            if (is_array($arg)) {
                $argument .= "array(";

                $temp = self::arguments($arg, $line + 1);

                $argument .= $temp != '' ? "<br>" . $temp . "<br>" . str_repeat("&emsp;", $line) . ')' : ')';

                $argument .= $end == $key ? '' : ",<br>";
            } else {
                $argument .= $arg;
                
                $argument .= $end == $key ? '' : ",<br>";
            }
        }
    
        return $argument;
    }

    /**
     * Отображение окна ошибок с отправкой ошибки 5хх
     *
     * @param integer $code код состояния HTTP
     * @param string $text выводимый текст
     * @return exception ошибки http_response_code 5хх
     */
    public static function error(int $code = 500, string $error = 'bad parameters')
    {
        $debug = $error;
        http_response_code($code);

        if (isset($_REQUEST['AUTH_ID'])) {
            require _PATH . '/include/pages/error.php';
        } else {
            $error .= '<br><br>';

            if (DEBUG_TRACE) {
                $backtrace = debug_backtrace();
    
                foreach ($backtrace as $trace) {
                    $error .= $trace['line'] . ' : ' . $trace['file'] . ' - ' . $trace['function'] . '<br>';

                    $error .= ARG_TRACE === true ? self::arguments($trace['args']) . '<br>' : '';

                    $error .= '<br>';
                }
            }

            echo $error;
        }

        if (ERROR_LOG) {
            $backtrace = debug_backtrace();
    
            foreach ($backtrace as $trace) {
                $debug .= $trace['line'] . ' : ' . $trace['file'] . ' - ' . $trace['function'] . '<br>';

                $debug .= ARG_TRACE === true ? self::arguments($trace['args']) . '<br>' : '';

                $debug .= '<br>';
            }

            $debug = str_replace('<br>', "\n", $debug);
            $debug = str_replace('&emsp;', "\t", $debug);

            $path = _PATH . '/error_log/' . $_REQUEST['DOMAIN'];

			if (!file_exists($path))
			{
				@mkdir($path, 0775, true);
			}

            file_put_contents($path . '/' . date('Y-m-d'), date('H:i:s') . "\n" . $debug . "\n", FILE_APPEND);
        }

        exit;
    }

    /**
     * Отображение окна уведомлений с отправкой ошибки 4хх
     *
     * @param integer $code код состояния HTTP
     * @param string $text выводимый текст
     * @return exception ошибки http_response_code 4хх
     */
    public static function exception(int $code = 418, string $error = 'bad parameters')
    {
        global $USER;
        http_response_code($code);

        if (isset($_REQUEST['AUTH_ID'])) {
            require _PATH . '/include/pages/error.php';
        } else {
            if ($USER->isException() || $USER->isAdmin()) {
                $error .= '<br><br>';
    
                if (DEBUG_TRACE) {
                    $backtrace = debug_backtrace();
        
                    foreach ($backtrace as $trace) {
                        $error .= $trace['line'] . ' : ' . $trace['file'] . ' - ' . $trace['function'] . '<br>';
    
                        $error .= ARG_TRACE === true ? self::arguments($trace['args']) . '<br>' : '';
    
                        $error .= '<br>';
                    }
                }
    
                echo $error;
    
                echo $error;
            }
        }

        if (ERROR_LOG) {
            $backtrace = debug_backtrace();
    
            foreach ($backtrace as $trace) {
                $debug .= $trace['line'] . ' : ' . $trace['file'] . ' - ' . $trace['function'] . '<br>';

                $debug .= ARG_TRACE === true ? self::arguments($trace['args']) . '<br>' : '';

                $debug .= '<br>';
            }

            $debug = str_replace('<br>', "\n", $debug);
            $debug = str_replace('&emsp;', "\t", $debug);

            $path = _PATH . '/error_log/' . $_REQUEST['DOMAIN'];

			if (!file_exists($path))
			{
				@mkdir($path, 0775, true);
			}

            file_put_contents($path . '/' . date('Y-m-d'), date('H:i:s') . "\n" . $debug . "\n", FILE_APPEND);
        }

        exit;
    }

    /**
     * Отображение окна уведомлений со статусом 2хх
     *
     * @param integer $code код состояния HTTP
     * @param string $text выводимый текст
     * @return void http_response_code 2хх
     */
    public static function notify(int $code, string $text = '')
    {
        http_response_code($code);
        echo json_encode(array('notify' => $text));
        exit;
    }

    /**
     * Отправка со статусом 2хх
     *
     * @param integer $code код состояния HTTP
     * @return void http_response_code 2хх
     */
    public static function success(int $code)
    {
        http_response_code($code);
    }

}
