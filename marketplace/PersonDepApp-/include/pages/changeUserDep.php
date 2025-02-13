<div class="modal-header">
    <h5 class="modal-title">Смена отдела</h5>
</div>
<div class="modal-body">
    <span>Сотрудник <b><?php echo $user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME']; ?></b> будет переведён из отдела</span>
    <select id="UF_DEPARTMENT_OLD" class="user-input" name="UF_DEPARTMENT_OLD"><?php echo $depOld; ?></select>
    <span>в отдел</span>
    <select id="UF_DEPARTMENT" class="user-input" name="UF_DEPARTMENT" onchange="posSelect(this)"><?php echo $dep; ?></select>
    <span>с новой должностью</span>
    <select id="WORK_POSITION" class="user-input" name="WORK_POSITION"><?php echo $pos; ?></select>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" data="changeUserDep" data-id="<?= $user['ID']; ?>" onclick="modalSuccess(this);">Перевести</button>
    <button type="button" class="btn btn-secondary" onclick="modalCancel();" data-dismiss="modal">Отмена</button>
</div>
<info><?= json_encode(array('ELEMENT' => $user['ELEMENT'], 'ID' => $user['ID'], 'UF_DEPARTMENT_OLD' => [], 'UF_DEPARTMENT' => [], 'WORK_POSITION' => $user['WORK_POSITION'])); ?></info>
