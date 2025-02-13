<?php

$addButton = $type == 'user' ? '<button type="button" class="btn btn-success" style="margin-bottom: 4px" id="newUser" data="newUser" onclick="openModal(this)">Принять на работу</button>' : '<button type="button" class="btn btn-success" style="margin-bottom: 4px" id="newItem" data="newElem" onclick="openModal(this)">Создать</button>';
$settingButton = $admin === true ? '<button id="settings" class="btn btn-secondary-bitrix" style="border: rgba(220, 220, 220, 0.7) solid 2px; background: rgba(220, 220, 220, 0.2) url(' . _URI . '/static/img/ui-setting-white.svg) no-repeat center; background-size: 30px;" data="settings" onclick="openModal(this);"></button>' : '';

?>

<div class='nav-pills form-inline' id='menuToggle' style='justify-content: space-between'>
    <div>
        <?php echo $addButton; ?>
        <div class='input-group'>
            <input id='inputName' type='text' class='form-control usersTxt' onclick='openFilter();' placeholder='Фильтр + поиск' aria-describedby='button-addon2'>
            <div class='input-group-append'>
                <button class='btn btn-primary' type='button' onclick='filterSet();'></button>
            </div>
        </div>
    </div>
    <div>
        <?php echo $settingButton; ?>
        <button id='update' class='btn btn-secondary-bitrix' onclick='pageSelect(1);' title='Обновить таблицу' style='border: rgba(220, 220, 220, 0.7) solid 2px; background: rgba(220, 220, 220, 0.2) url(<?= _URI; ?>/static/img/update-bitrix.png) no-repeat center; background-size: 22px;'></button>
    </div>
</div>
