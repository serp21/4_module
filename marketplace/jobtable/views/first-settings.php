<?php

use App\Stafftable\Lists;

$openClass = '';

$allLists = \CRest::call("lists.get", [
    "IBLOCK_TYPE_ID" => "lists"
])['result'];

if(!Lists::getIndexList()) {
    $openClass = 'open';
}

?>


<form action="#" method="POST" class="add-popup <?= $openClass ?> first-settings" data-type="first-settings" data-popup>
    <div class="add-popup__container first-settings__container">
        <div class="add-popup__top">
            <h3 class="add-popup__title">Первичная настройка</h3>
            <button class="add-popup__close" data-close-popup type="button">
                <img src="./assets/img/close.svg" alt="">
            </button>
        </div>

        <div class="add-popup__main first-settings__main">
            <div class="first-settings__lists ">
                <ul class="add-popup__list">
                    <li class="add-popup__item add-popup__item-checkbox">
                        <span>Использую свой список</span>
                        <label class="add-popup__label-checkbox">
                            <input name="custom-list-check" type="checkbox" data-checkbox-hidden data-checkbox="is-custom-list">
                        </label>
                    </li>
                    <li class="add-popup__item add-popup__item-checkbox">
                        <span>Создать новый список</span>
                        <label class="add-popup__label-checkbox">
                            <input name="custom-list-add" type="checkbox">
                        </label>
                    </li>
                    <li class="add-popup__item first-settings_hidden" data-element-hidden>
                        <span>Выберите список</span>
                        <label>
                            <select
                                class="js-select2 add-popup__select"
                                name="custom-list-select"
                                id="custom-list-select"
                                data-select="custom-list-select">
                                <option value="" selected disabled>Выберите список</option>
                                <?php foreach ($allLists as $list) { ?>
                                    <option value="<?= $list['ID'] ?>"><?= $list['NAME'] ?></option>
                                <?php } ?>
                            </select>
                        </label>
                    </li>

                </ul>

                <ul class="add-popup__list first-settings_hidden" data-element-hidden>
                    <h3 class="add-popup__title">Поля</h3>
                    <li class="add-popup__item add-popup__item_hidden">
                        <span>Название должности</span>
                        <label>
                            <select
                                class="js-select2 add-popup__select"
                                name="custom-list-name"
                                id="custom-list-name"
                                data-select="custom-list-name"
                                data-field-select>
                                <option value="" selected disabled>Выберите поле для Названия должности</option>
                            </select>
                        </label>
                    </li>
                    <li class="add-popup__item add-popup__item_hidden">
                        <span>Подразделение</span>
                        <label>
                            <select
                                class="js-select2 add-popup__select"
                                name="custom-list-group"
                                id="custom-list-group"
                                data-select="custom-list-group"
                                data-field-select>
                                <option value="" selected disabled>Выберите поле для Подразделения</option>
                            </select>
                        </label>
                    </li>
                    <li class="add-popup__item add-popup__item_hidden">
                        <span>Оклад</span>
                        <label>
                            <select
                                class="js-select2 add-popup__select"
                                name="custom-list-salary"
                                id="custom-list-salary"
                                data-select="custom-list-salary"
                                data-field-select>
                                <option value="" selected disabled>Выберите поле для Оклада</option>
                            </select>
                        </label>
                    </li>

                </ul>
            </div>


            <button class="add-popup__btn" type="submit">
                Сохранить
            </button>
        </div>
    </div>
</form>