<div class="modal-header">
    <h5 class="modal-title">Исключение из отдела</h5>
</div>
<div class="modal-body">
    <span>Сотрудник <b><?php echo $user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME']; ?></b> будет исключён из отдела</span>
    <select id="UF_DEPARTMENT" class="user-input" name="UF_DEPARTMENT"><?php echo $dep; ?></select>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" data="deleteUserDep" data-id="<?= $user['ID']; ?>" onclick="modalSuccess(this);">Исключить</button>
    <button type="button" class="btn btn-secondary" onclick="modalCancel();" data-dismiss="modal">Отмена</button>
</div>
<info><?= json_encode(array('ELEMENT' => $user['ELEMENT'], 'ID' => $user['ID'], 'UF_DEPARTMENT' => [])); ?></info>
