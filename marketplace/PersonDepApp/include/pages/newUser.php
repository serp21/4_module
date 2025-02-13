<div class="modal-header">
    <h5 class="modal-title">Принятие нового сотрудника</h5>
</div>
<div class="modal-body">
    <p>Поля, обязательные к заполнению, отмечены <span style="color: red;">*</span></p>

    <p>
        <span>Фамилия<span style="color: red;">*</span></span><br>
        <input type="text" id="LAST_NAME" class="user-input" name="LAST_NAME" autocomplete="off" style="width:100%">
    </p>
    
    <p>
        <span>Имя<span style="color: red;">*</span></span><br>
        <input type="text" id="NAME" class="user-input" name="NAME" autocomplete="off" style="width:100%">
    </p>
    
    <p>
        <span>Отчество</span><br>
        <input type="text" id="SECOND_NAME" class="user-input" name="SECOND_NAME" autocomplete="off" style="width:100%">
    </p>
    
    <p>
        <span>Дата рождения</span><br>
        <input id="PERSONAL_BIRTHDAY" class="user-input" type="date">
    </p>
    
    <p>
        <span>Пол</span><br>
        <select id="PERSONAL_GENDER" class="user-input" name="PERSONAL_GENDER" style="cursor:pointer;"><option value=""></option><option value="M">Мужской</option><option value="F" style="width:100%">Женский</option></select>
    </p>
    
    <p>
        <span>E-mail<span style="color: red;">*</span></span><br>
        <input type="email" id="EMAIL" class="user-input" name="EMAIL" autocomplete="off" placeholder="example@mail.ru" oninput="validateEmail(this)" style="width:100%">
    </p>

    <p>
        <span>Номер телефона</span><br>
        <input type="tel" id="PERSONAL_MOBILE" class="user-input" style="width:162px" name="PERSONAL_MOBILE">
    </p>
    
    <p>
        <span>Отдел</span><br>
        <select id="UF_DEPARTMENT" class="user-input" name="UF_DEPARTMENT" onchange="posSelect(this)"><?php echo $dep; ?></select>
    </p>
    
    <p>
        <span>Должность</span><br>
        <select id="WORK_POSITION" name="WORK_POSITION" class="user-input"><?php echo $pos; ?></select>
    </p>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" data="newUser" onclick="modalSuccess(this);">Принять</button>
    <button type="button" class="btn btn-secondary" onclick="modalCancel();" data-dismiss="modal">Отмена</button>
</div>
