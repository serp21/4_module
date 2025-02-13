<div class="modal-header">
    <h5 class="modal-title">Увольнение</h5>
</div>
<div class="modal-body">
    <span>Сотрудник <b><?php echo $user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME'] . '</b> в должности <b>' . $user['WORK_POSITION']; ?></b> будет уволен.</span>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" data="deleteUser" data-id="<?= $user['ID']; ?>" onclick="modalSuccess(this);">Уволить</button>
    <button type="button" class="btn btn-secondary" onclick="modalCancel();" data-dismiss="modal">Отмена</button>
</div>
<info><?= json_encode(array('ELEMENT' => $user['ELEMENT'], 'ID' => $user['ID'])); ?></info>
