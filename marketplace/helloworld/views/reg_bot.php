<?php 
    require_once (__DIR__.'/../functions/bots.php');

?>
<div class="content_info reg-bot">
    <div class="reg-bot__title-block">
        <h3 class="reg-bot__title">Сообщение будет отправляться от Бота, для этого введите его имя и загрузите картинку для аватарки*</h3>
        <h3 class="reg-bot__title">*Аватарку загружать не обязательно</h3>

    </div>
    <div class="reg-bot__main">
        <label class="reg-bot__label">
            Имя бота*
            <input type="text" name='botname' data-type='outBotName' class='reg-bot__input' placeholder='Введите имя бота, например: Николай Николаевич' require></input>
        </label>
        <label class="reg-bot__label reg-bot__label_file" data-type="nameBotAvatar">
            Выберите файл для аватарки бота
            <input type="file" name='botavatar' data-type='outBotAvatar' class='reg-bot__input reg-bot__input_file' accept="image/png, image/jpeg" ></input>
        </label>
    </div>
    
    
    <div class='info_bttn' data-type="saveBot">
        <button class="test-send__btn test-send__btn_reg" type='submit' data-type='saveMessage'>Зарегистрировать</button>
    </div>                          
    <span class="preloader"></span>
    <!-- print_r(Storage::getStorage("ACTIVE_BOT_ID")); -->
</div>