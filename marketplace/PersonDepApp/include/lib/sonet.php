<?php

namespace app\lib;

use app\Responce;
use \CRest;

class Sonet {

    /**
     * Получть группы экстранет
     *
     * @return array список экстранет групп или ошибка в класс Responce
     */
    public static function getExtranet() {
        $groups = CRest::call('sonet_group.get', array('IS_ADMIN' => 'Y'));

        if (isset($groups['result'])) {
            $extranet = [];

            foreach ($groups['result'] as $group) {
                if ($group['ACTIVE'] == 'Y' && $group['IS_EXTRANET'] == 'Y') {
                    $extranet[] = $group;
                }
            }

            return array('result' => $extranet, 'total' => count($extranet));
        } else {
            Responce::exception(FAILED, $groups['error'] . '<br>' . $groups['error_description']);
        }
    }

}
