<!DOCTYPE html>
<html lang="ru">
<head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>

        <link rel='stylesheet' href='<?= _URI;?>/static/bootstrap_4.0/css/bootstrap.css' >
        <link rel='stylesheet' href='<?= _URI;?>/static/css/style.css' >
        <link rel='stylesheet' href='<?= _URI;?>/static/css/toolTip.css' >
        <link rel='stylesheet' href='<?= _URI;?>/static/bootstrap_4.0/js/jquery-confirm.min.css'>

        <link rel='stylesheet' href='<?= _URI;?>/static/css/body/body.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/body/table.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/header/searchLine.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/modal/modalSearchUser.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/modal/window.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/bootstrap.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/buttons.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/input.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/tree.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/user.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/style.css'>
        <link rel='stylesheet' href='<?= _URI;?>/static/css/toolTip.css'>

        <script src='<?= _URI;?>/static/bootstrap_4.0/js/jquery-3.7.1.js'></script>
        <script src='<?= _URI;?>/static/bootstrap_4.0/js/popper.min.js'></script>
        <script src='<?= _URI;?>/static/bootstrap_4.0/js/bootstrap.bundle.js'></script>
        <script src='<?= _URI;?>/static/bootstrap_4.0/js/bootstrap.js'></script>
        <script src='<?= _URI;?>/static/bootstrap_4.0/js/jquery-confirm.min.js'></script>

        <title>Мои сотрудники</title>
</head>
<body>
    <!-- Модальное окно вывода ошибок -->
    <div class="modal fade" id="modalError" tabindex="-1" aria-labelledby="modalErrorTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalErrorTitle">Ошибка</h5>
                </div>
                <div class="modal-body" id="modalErrorBody">
                    <span id="error"><?php print_r($error); ?></span>
                    <span>Попробуйте перезагрузить страницу или обратитесь за помощью к администратору.<br>Перезагрузить страницу сейчас?</span>
                </div>
                <div class="modal-footer" id="modalErrorFooter">
                    <button type='button' class='btn btn-success' onclick='window.parent.location.reload();'>Да</button>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss="modal">Нет</button>
                </div>
            </div>
        </div>
    </div>

    <script defer>
        window.parent[0].frameElement.parentElement.parentElement.parentElement.style.background = "transparent";
        window.parent[0].frameElement.parentElement.style.background = "transparent";
        window.parent[0].frameElement.parentElement.parentElement.style.height = "100%";
        window.parent[0].frameElement.parentElement.parentElement.style.padding = 0;
        window.parent[0].frameElement.parentElement.parentElement.style['overflow-y'] = "hidden";

        const resizeObserver = new ResizeObserver(entries =>
            window.parent[0].frameElement.style.height = document.body.scrollHeight + 'px'
        )

        resizeObserver.observe(document.body);

        modal = new bootstrap.Modal(document.getElementById('modalError'), { keyboard: false }).show();
    </script>
</body>
</html>
