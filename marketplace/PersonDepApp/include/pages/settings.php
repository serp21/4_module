<div class="modal-header">
    <h5 class="modal-title">Настройки приложения</h5>
</div>
<div class="modal-body">
    <table>
        <tbody>
            <tr>
                <td>
                    <span style="color: #535c69; cursor: default;">Сохранять отображаемые поля таблицы сотрудников: </span>
                </td>
                <td>
                    <input type="checkbox" id="checkSaveTableColumns" name="check" class="check" style="margin-left: 5px;" checked>
                </td>
            </tr>
            <tr>
                <td>
                    <span style="color: #535c69; cursor: default;">Предоставить доступ руководителям отделов: </span>
                </td>
                <td>
                    <input type="checkbox" id="checkAccessHeadUser" name="check" class="check" style="margin-left: 5px;" checked>
                </td>
            </tr>
            <tr>
                <td>
                    <span style="color: #535c69; cursor: default;">Отображать сотрудникам уведомления об ошибках: </span>
                </td>
                <td>
                    <input type="checkbox" id="checkExceptionWindow" name="check" class="check" style="margin-left: 5px;">
                </td>
            </tr>
        </tbody>
    </table>
    <table>
        <tbody>
            <tr>
                <td>
                    <span style="cursor: default;">Отправлять повторный запрос на сервер в случае неудачи:</span>
                </td>
                <td style="width: 48%;">
                    <select id="repitAjax" name="select" style="height: 30px; width: 100%; cursor:pointer; border: 1px #ccc solid; border-radius: 5px; color: #2067b0;">
                        <option value="0" selected="selected">Не отправлять</option>
                        <option value="1">1 раз</option>
                        <option value="2">2 раза</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <h7 style="cursor: help;">Доступ сотрудникам: </h7>
                </td>
                <td>
                    <div style="border: 1px #ccc solid; border-radius: 5px; height: 30px;">
                        <span style="padding-left: 5px;">
                            <span id="selectUserSettings" class="userSettings" style="color: #2067b0; cursor: pointer;">выбрать</span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <span style="cursor: default;">Должности и отделы компании: </span>
                </td>
                <td><span id="changeDepPosCompany" class="userSettings" style="color: #2067b0; cursor: pointer;">изменить</span></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" data="settings" data-id="<?= $user['ID']; ?>" onclick="modalSuccess(this);">Сохранить</button>
    <button type="button" class="btn btn-secondary" onclick="modalCancel();" data-dismiss="modal">Отмена</button>
</div>
