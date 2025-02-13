<!DOCTYPE html>
<html lang='ru'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>

        <link rel='stylesheet' href='<?= _URI;?>/static/bootstrap_4.0/css/bootstrap.css'>
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
        <div id='preloader' style='display: none;'></div>
        <div id="base">
            <header id='header'>
                <?php require __DIR__ . '/header.php'; ?>
            </header>
            <div>
                <div id="main">
                    <main id="table">
                    </main>
                    <footer>
                        <?php require __DIR__ . '/footer.php'; ?>
                    </footer>
                </div>
            </div>
        </div>
        <windows>
            <!-- Модальное окно вывода информации -->
            <div class="modal fade" id="modalBlock" tabindex="-1" aria-labelledby="modalBlockTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" id="modalBlockBody">
                    </div>
                </div>
            </div>
            
            <!-- Модальное окно вывода уведомлений -->
            <div class="modal fade" id="modalNotify" tabindex="-1" aria-labelledby="modalNotifyTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalNotifyTitle">Уведомление</h5>
                        </div>
                        <div class="modal-body" id="modalNotifyBody">
                        </div>
                        <div class="modal-footer" id="modalNotifyFooter">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Модальное окно вывода ошибок -->
            <div class="modal fade" id="modalError" tabindex="-1" aria-labelledby="modalErrorTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalErrorTitle">Ошибка</h5>
                        </div>
                        <div class="modal-body" id="modalErrorBody">
                            <span id="errorText"></span>
                            <span>Попробуйте перезагрузить страницу или обратитесь за помощью к администратору.<br>Перезагрузить страницу сейчас?</span>
                        </div>
                        <div class="modal-footer" id="modalErrorFooter">
                            <button type='button' class='btn btn-success' onclick='window.parent.location.reload();' style='margin: 2px;'>Да</button>
                            <button type='button' class='btn btn-secondary' onclick="cancelError();" style='margin: 2px;'>Нет</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Окно фильтра сотрудников -->
            <div id="modalSearchUser" tabindex="-1">
                <table style="margin: 10px;">
                    <tbody id="modalFilter">
                        <tr>
                            <td>
                                <div>
                                    <span>ФИО</span>
                                </div>
                            </td>
                            <td>
                                <input id="full_name" type="text" autocomplete="off" style="min-width: 400px;">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <span>Дата рождения</span>
                                </div>
                            </td>
                            <td>
                                <input id="personal_birthday" type="date" min="1900-01-01" max="<?= date("Y-12-31") ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <span>Пол</span>
                                </div>
                            </td>
                            <td><select id="personal_gender"><option value="null"></option><option value="M">Мужской</option><option value="F">Женский</option></select></td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <span>Телефон</span>
                                </div>
                            </td>
                            <td><input id="personal_mobile" style="width:162px" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <span>E-Mail</span>
                                </div>
                            </td>
                            <td><input id="email" autocomplete="off" placeholder="example@mail.ru" oninput="validateEmail(this)" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <span>Дата регистрации</span>
                                </div>
                            </td>
                            <td>
                                <input id="date_register" type="date" min="1900-01-01" max="<?= date("Y-12-31") ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <span>Отдел</span>
                                </div>
                            </td>
                            <td><select id="uf_department" onchange="posSelect(this)" style="width: 100%;"><?php echo $dep; ?></select></td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <span>Должность</span>
                                </div>
                            </td>
                            <td><select id="work_position" style="width:100%;"><?php echo $pos; ?>'</select></td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <span>Сотрудники</span>
                                </div>
                            </td>
                            <td>
                                <input id="active_1" class="check" type="radio" style="height: 100%;" name="active" checked><span>Только действующие</span>
                                <input id="active_2" class="check" type="radio" style="height: 100%;" name="active"><span>Только уволенные</span>
                                <input id="active_3" class="check" type="radio" style="height: 100%;" name="active"><span>Все</span>
                            </td>
                        </tr>
                        <?php
                            if ($admin === true) {
                                echo '<tr>
                                        <td></td>
                                        <td>
                                            <input id="user_type_1" class="check" type="radio" style="height: 100%;" name="type" value="employee" checked><span>Только интранет</span>
                                            <input id="user_type_2" class="check" type="radio" style="height: 100%;" name="type" value="extranet"><span>Только экстранет</span>
                                            <input id="user_type_3" class="check" type="radio" style="height: 100%;" name="type" value=""><span>Все</span>
                                        </td>
                                    </tr>';
                            }
                        ?>
                    </tbody>
                </table>
                <div class="modal-footer" style="display: block;">
                    <button type="button" class="btn btn-primary" onclick="filterSet();" style="margin: 2px;">Найти</button>
                    <button type="button" class="btn btn-secondary" onclick="filterUnSet();" style="margin: 2px;">Сбросить</button>
                </div>
            </div>
        </windows>
        <script>
            // Открыть окно действий над списком, если оно не открылось при первом нажатии
            $(document).on('click', '.editButton', (e) => {
                if (e['target']['parentElement']['children'][1]['className'].indexOf('show') == -1)
                    document.getElementById(e['target']['id']).click();
            });

            const resizeObserver = new ResizeObserver(entries =>
                window.parent[0].frameElement.style['min-height'] = (document.body.scrollHeight + 140) + 'px'
            )

            resizeObserver.observe(document.body);
        </script>
        <script src="<?= _URI; ?>/include/JS/beforeChange.js"></script>
        <script src="<?= _URI; ?>/include/JS/saveFilter.js"></script>
        <script src="<?= _URI; ?>/include/JS/ajaxFunctions.js"></script>
        <script src="<?= _URI; ?>/include/JS/modalRoute.js"></script>
        <script src="<?= _URI; ?>/include/JS/otherFunctions.js"></script>
        <div id='temp'>
            <script defer>
                window.parent[0].frameElement.parentElement.parentElement.parentElement.style.background = "transparent";
                window.parent[0].frameElement.parentElement.style.background = "transparent";
                window.parent[0].frameElement.parentElement.parentElement.style.height = "100%";
                window.parent[0].frameElement.parentElement.parentElement.style.padding = 0;
                window.parent[0].frameElement.parentElement.parentElement.style['overflow-y'] = "hidden";

                setTimeout(() => { table.innerHTML = `<?= $table; ?>`; }, 200);

                for (let ev of ['input', 'blur', 'focus']) {
                    personal_mobile.addEventListener(ev, eventCalllback);
                }

                temp.remove();
            </script>
        </div>
    </body>
</html>
