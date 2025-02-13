<?php

define('_URI', 'https://dev.vestrus.ru/marketplace/PersonDepApp');
define('_PAGES', 'https://dev.vestrus.ru/marketplace/PersonDepApp/static/pages/');

define('DEBUG_TRACE', true);
define('ARG_TRACE', true);
define('ERROR_LOG', false);

define('REQUEST_COUNT', 3);

define('USER_POSITION', 'UF_USR_ENDPOINT_WORKPOSITION_ID'); // Пользовательское поле привязки сотрудника к должности

define('LIST_NAME', 'ENDPOINT_STAFFTABLE'); // Код списка должностей ЭндПоинт

// Коды ответов сервера \\
define('OK', 200); # хорошо
define('CREATED', 201); # создано
define('PARTIAL', 206); # частичное содержимое
define('BAD_REQUEST', 400); # некорректный запрос
define('UNAUTHORIZED', 401); # не авторизован
define('FORBIDDEN', 403); # запрещено
define('NOT_FOUND', 404); # не найдено
define('NOT_ALLOOWED', 405); # метод не поддерживается
define('NOT_ACCEPTABLE', 406); # неприемлемо
define('CONFLICT', 409); # конфликт
define('GONE', 410); # удалён
define('UNSUPPORTED', 415); # неподдерживаемый тип данных
define('FAILED', 417); # ожидание не оправдалось
define('LOCKED', 423); # заблокировано
define('TOO_MANY', 429); # слишком много запросов
define('ERROR', 500); # внутренняя ошибка сервера
define('UNAVAILABLE', 503); # сервис недоступен
define('UNKNOWN', 520); # неизвестная ошибка
define('SERVER_IS_DOWN', 521); # веб-сервер не работает
define('UNREACHABLE', 523); # источник недоступен
define('TIMEOUT', 524); # время ожидания истекло
