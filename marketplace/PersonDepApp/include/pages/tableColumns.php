<div class="modal-header">
    <h5 class="modal-title">Таблица</h5>
</div>
<div class="modal-body">
    <table>
        <tbody>
            <tr>
                <td style="vertical-align: top; width: 60%;">
                    <div>
                        <div style="display: -webkit-box;"><input type="checkbox" id="checkPhoto" name="check" class="check" <?php echo !isset($columns['photo']) || $columns['photo'] === true ? 'checked' : ''; ?>><h6 style="padding-left: 5px;">Фото</h6></div>
                        <div style="display: -webkit-box;"><input type="checkbox" id="checkBDay" name="check" class="check" <?php echo !isset($columns['birthday']) || $columns['birthday'] === true ? 'checked' : ''; ?>><h6 style="padding-left: 5px;">Дата рождения</h6></div>
                        <div style="display: -webkit-box;"><input type="checkbox" id="checkGen" name="check" class="check" <?php echo !isset($columns['gender']) || $columns['gender'] === true ? 'checked' : ''; ?>><h6 style="padding-left: 5px;">Пол</h6></div>
                        <div style="display: -webkit-box;"><input type="checkbox" id="checkPhone" name="check" class="check" <?php echo !isset($columns['phone']) || $columns['phone'] === true ? 'checked' : ''; ?>><h6 style="padding-left: 5px;">Телефон</h6></div>
                    </div>
                </td>
                <td style="vertical-align: top; width: 60%;">
                    <div>
                        <div style="display: -webkit-box;"><input type="checkbox" id="checkMail" name="check" class="check" <?php echo !isset($columns['mail']) || $columns['mail'] === true ? 'checked' : ''; ?>><h6 style="padding-left: 5px;">E-Mail</h6></div>
                        <div style="display: -webkit-box;"><input type="checkbox" id="checkReg" name="check" class="check" <?php echo !isset($columns['regdate']) || $columns['regdate'] === true ? 'checked' : ''; ?>><h6 style="padding-left: 5px;">Дата регистрации</h6></div>
                        <div style="display: -webkit-box;"><input type="checkbox" id="checkDep" name="check" class="check" <?php echo !isset($columns['position']) || $columns['position'] === true ? 'checked' : ''; ?>><h6 style="padding-left: 5px;">Отдел</h6></div>
                        <div style="display: -webkit-box;"><input type="checkbox" id="checkPos" name="check" class="check" <?php echo !isset($columns['department']) || $columns['department'] === true ? 'checked' : ''; ?>><h6 style="padding-left: 5px;">Должность</h6></div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" data="tableColumns" data-id="<?= $user['ID']; ?>" onclick="modalSuccess(this);">Сохранить</button>
    <button type="button" class="btn btn-secondary" onclick="modalCancel();" data-dismiss="modal">Отмена</button>
</div>
