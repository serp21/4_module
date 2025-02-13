<?php

/*
Класс пользователя. Создается при открытии пользователяем приложения. 

Необходим для проверки данных у пользователя и для получения информации 
без необходимости повторного rest запроса
*/

// require_once('./crest/crest.php');
// require_once('./crest/crestcurrent.php');

namespace App\Stafftable;

class User
{
    protected $id;
    protected $name;

    protected $isAdmin = false;

    protected $departments = array();
    protected $departmentsHead = array();

    protected static $user;

    protected function __construct()
    {
        $userArr = \CRestCurrent::call("user.current")['result'];

        if (!is_array($userArr) && empty($userArr)) {
            return false;
        }

        $this->id = $userArr['ID'];
        $this->name = $userArr['NAME'];
        $this->isAdmin = \CRestCurrent::call("user.admin")['result'] == 1 ? true : false;
        $this->departments = $userArr['UF_DEPARTMENT'];

        $this->departmentsHead = $this->getHeadDepartments();
    }

    public static function getInstance()
    {
        if (empty(self::$user)) {
            if (empty($_SESSION['ENDPOINT_USER_OBJ'])) {
                $_SESSION['ENDPOINT_USER_OBJ'] = new self();

                self::$user = new self();
            } else {

                if (
                    empty($_SESSION['ENDPOINT_SM_UIDD'])
                    || $_SESSION['ENDPOINT_SM_UIDD'] != $_COOKIE['BITRIX_SM_UIDD']
                ) {
                    $_SESSION['ENDPOINT_USER_OBJ'] = new self();
                    $_SESSION['ENDPOINT_SM_UIDD'] = $_COOKIE['BITRIX_SM_UIDD'];
                }

                if(empty($_SESSION['ENDPOINT_USER_OBJ']->getId())) {
                    $_SESSION['ENDPOINT_USER_OBJ'] = new self();
                }

                self::$user = $_SESSION['ENDPOINT_USER_OBJ'];
            }
        }

        return self::$user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function getHeadDepartments()
    {
        $departmentsHead = [];

        foreach ($this->departments as $department) {

            $departmentInfo = \CRest::call("department.get", ['ID' => $department])["result"][0];

            if ($this->id == $departmentInfo['UF_HEAD']) {
                $departmentsHead[] =  $departmentInfo['ID'];
            }
        }

        return $departmentsHead;
    }

    public function isHead()
    {
        if (!empty($this->departmentsHead)) {
            return true;
        }

        return false;
    }
}
