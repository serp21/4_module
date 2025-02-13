<?php

use App\Stafftable\User;

?>

<div class="right-popup settings-popup" data-type="settings" data-popup>
    <div class="right-popup__container">
        <div class="right-popup__top">
            <h2 class="right-popup__title">Настройки</h2>
            <button class="right-popup__close" data-close-popup>
                <img src="./assets/img/close.svg" alt="">
            </button>
        </div>
        <div class="right-popup__main">
            <ul class="settings-popup__list right-popup__list">
                <li class="settings-popup__item">
                    <a href="#add-group" class="settings-popup__item-link" data-add-group>
                        Выбрать подразделение
                    </a>
                </li>
                <li class="settings-popup__item">
                    <a href="#add-position" class="settings-popup__item-link" data-add-position>
                        Добавить должность
                    </a>
                </li>
                <li class="settings-popup__item">
                    <a href="#add-parent-position" class="settings-popup__item-link" data-add-position>
                        Добавить должность родительского подразделения
                    </a>
                </li>
                <?php if (User::getInstance()->isAdmin()) { ?>
                    <li class="settings-popup__item">
                        <a href="#add-position" class="settings-popup__item-link" data-first-settings>
                            Первичная настройка
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>

</div>