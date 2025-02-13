<div class="modal-header">
    <h5 class="modal-title">Перевод в экстранет</h5>
</div>
<div class="modal-body">
    <span>Сотрудник <b><?php echo $user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME']; ?></b> будет переведён из штата в экстранет.<br>Укажите группу экстранет:</span>
    <select id="SONET_GROUP_ID" class="user-input" name="SONET_GROUP_ID" onchange="groupSelect(this)"><?php echo $extranet; ?></select>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" data="toExtranet" data-id="<?= $user['ID']; ?>" onclick="modalSuccess(this);">Перевести</button>
    <button type="button" class="btn btn-secondary" onclick="modalCancel();" data-dismiss="modal">Отмена</button>
</div>
<info><?= json_encode(array('ELEMENT' => $user['ELEMENT'], 'ID' => $user['ID'], 'SONET_GROUP_ID' => [])); ?></info>
