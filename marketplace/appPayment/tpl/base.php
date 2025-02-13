<?php   if(!defined('_PATH')) die('Error');?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Платежи</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="pub/css/bootstrap1.min.css">
    <link rel="stylesheet" href="pub/css/all.min.css">
    <link media="screen" href="pub/css/bootstrap-datepicker.min.css" type="text/css" rel="stylesheet">
    <link media="screen" href="pub/css/style.css" type="text/css" rel="stylesheet">
    <style type="text/css">
        table thead.sticky th {
            position: -webkit-sticky;
            position: sticky;
            top: 50px;
            background: #eee;
        }
        .sticky2{
            position: -webkit-sticky;
            position: sticky;
            top: 55px;
        }
        .disabledbutton {
            pointer-events: none;
            opacity: 0.4;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md fixed-top navbar-light" style="background-color: #fdfdfa;">
    <!--<span class="navbar-brand" href="#">Платежи</span>-->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label=""><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
        <ul class="navbar-nav justify-content-end">
        <?php
            
            #   Поиск
            echo '<li class="nav-item"><button data-toggle="modal" data-target="#ModalFilter" class="btn btn-sm btn-outline-info" style="margin: 2px;"><i id="iconFilter" class="fa fa-search"></i> Поиск <span class="badge-pill badge badge-success" id="ModalFilterBtn"></span></button></li>';
            
            #   Наличка
            if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['nal'] == 1)
            {
                echo '<li class="nav-item"><button data-toggle="modal" id="btnModNal" data-target="#ModalNal" class="btn btn-sm btn-outline-info" style="margin: 2px;"><i id="iconNal" class="fa fa-money"></i> Добавить наличный платёж</button></li>';
            }
            
            #   Сейф
            if($_SESSION['bitAppPayment']['ID'] == 26 || $_SESSION['bitAppPayment']['ID'] == 3 || $_SESSION['bitAppPayment']['ID'] == 7 || $_SESSION['bitAppPayment']['ID'] == 121 || $_SESSION['bitAppPayment']['ID'] == 128)
            {
                
                if(!empty($this->safe))
                {
                    $safeSum = 0;
                    if($_SESSION['bitAppPayment']['ADMIN'] == 1)
                    {
                        foreach($this->safe as $user_id => $ar)
                        {
                            $safeSum += $ar['sum'];
                        }
                    }
                    else
                    {
                        foreach($this->safe as $user_id => $ar)
                        {
                            if($user_id == $_SESSION['bitAppPayment']['ID'])
                                $safeSum = $ar['sum'];
                        }
                    }
                    
                    if($safeSum < 0)
                    {
                        echo '<li class="nav-item"><button data-toggle="modal" data-target="#ModalSafe" class="btn btn-sm btn-outline-info" style="margin: 2px;"><i id="iconSafe" class="fa fa-money"></i> Сейф (<span class="text-danger">'.number_format($safeSum, 2, '.', ' ').'</span>)</button></li>';
                    }
                    elseif($safeSum > 0)
                    {
                        echo '<li class="nav-item"><button data-toggle="modal" data-target="#ModalSafe" class="btn btn-sm btn-outline-info" style="margin: 2px;"><i id="iconSafe" class="fa fa-money"></i> Сейф (<span class="text-success">'.number_format($safeSum, 2, '.', ' ').'</span>)</button></li>';
                    }
                }
            }
            
            #   Правила
            if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['rules'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['allrules'] == 1)
            {
                echo '<li class="nav-item"><button data-toggle="modal" data-target="#ModalRules" onclick="getRules(0)" class="btn btn-sm btn-outline-info" style="margin: 2px;" id="ruleBtn"><i class="fa fa-files-o"></i> Правила <span class="badge-pill badge badge-success" id="rulesBage"></span></button></li>';
            }
            
            #   Счета
            if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['bnal'] == 1)
            {
                echo '<li class="nav-item"><button data-toggle="modal" data-target="#ModalAccount" class="btn btn-sm btn-outline-info" style="margin: 2px;"><i id="iconAccount" class="fa fa-list"></i> Счета <span class="badge-pill badge badge-success" id="ModalAccountBtn"></span></button></li>';
            }

            #   Очередь
            if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['quenue'] == 1)
            {
                echo '<li class="nav-item"><button data-toggle="modal" data-target="#ModalQuenue" class="btn btn-sm btn-outline-info" style="margin: 2px; display: none;" id="quenueBtn"><i id="iconQuenue" class="fa fa-database"></i> Очередь <span class="badge-pill badge badge-success" id="quenueBage">0</span></button></li>';
            }

            #   Множественное разбиение
            #if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['part'] == 1)
            #{
                echo '<li class="nav-item"><button data-toggle="modal" data-target="#ModalMorePart" class="btn btn-sm btn-info" id="ModalMorePartBtn" style="right:5px;margin-top: 110px;margin-right:20px;position:fixed;display:none"><span class="badge-pill badge badge-danger" id="MorePartBage"></span> <i id="iconMorePart" class="fa fa-check-square-o"></i> Множественное разбиение (<span id="BAmpSum">0</span> руб.)</button></li>';
            #}
            #   Группировка
            echo '<li class="nav-item"><button class="btn btn-sm btn-outline-info"  id="btnGroup" style="margin: 2px;"><i id="appGroupIcon" class="fa fa-object-ungroup"></i> По дате изменения</button></li>';
            
            #   Загрузка выписки
            if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['vb'] == 1)
            {
                echo '<li class="nav-item"><input type="file" name="file" id="file" accept="text/plain" style="width:.1px;height:.1px;opacity:0;overflow:hidden;position:absolute;z-index:-1">
                      <label for="file" class="btn btn-sm btn-outline-info" style="margin: 2px;"><i id="iconUpload" class="fa fa-upload"></i> <span class="js-fileName">Загрузить выписку</span></label></li>';
            }
            
            #   Настройки
            if($_SESSION['bitAppPayment']['ADMIN'] == 1)
            {
                echo '<li class="nav-item">
                          <button data-toggle="modal" data-target="#ModalSettings" class="btn btn-sm btn-outline-info" style="margin: 2px;"><i id="iconSettings" class="fa fa-cog"></i></button>
                      </li>';
            }
            
        ?>
            <!--<li class="nav-item">
                <a class="btn btn-sm btn-outline-danger float-right" style="margin: 2px;" href="?reload"><i class="fa fa-sign-out"></i> <small><?php echo htmlspecialchars($_SESSION['bitAppPayment']['LAST_NAME'].' '.$_SESSION['bitAppPayment']['NAME']); ?></small></a>
            </li>-->
        </ul>
    </div>
</nav>

<?php
    if(!empty($this->safe))
    {
        
        if($_SESSION['bitAppPayment']['ID'] == 26 || $_SESSION['bitAppPayment']['ID'] == 3 || $_SESSION['bitAppPayment']['ID'] == 7 || $_SESSION['bitAppPayment']['ID'] == 121 || $_SESSION['bitAppPayment']['ID'] == 128)
        {
?>
        <div class="modal fade" id="ModalSafe" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="text-muted"><i class="fa fa-files-o"></i>&nbsp;&nbsp;<strong class="modal-title"> Сейф</strong></div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a href="#BASafe" class="nav-link active" data-toggle="tab">Сейфы</a></li>
                            <li class="nav-item"><a href="#BASafeHis" class="nav-link" data-toggle="tab">История</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="BASafe">
                                <div style="height:400px;overflow: auto;">
                                    <table style="width: 100%;" class="table-sm table-hover">
                                    <?php
                                        foreach($this->safe as $user_id => $ar)
                                        {
                                            $sumSafe = ($ar['sum'] < 0) ? '<span class="text-danger">'.number_format($ar['sum'], 2, '.', ' ').'</span>' : '<span class="text-success">'.number_format($ar['sum'], 2, '.', ' ').'</span>';
                                            echo '<tr><td>'.htmlspecialchars($ar['user']).'</td><td style="text-align:right">'.$sumSafe.'</td></tr>';
                                        }
                                    ?>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="BASafeHis">
                                <div style="height:400px;overflow: auto;">
                                    <table style="width: 100%; font-size: 12px;" class="table-sm table-hover">
                                        <thead>
                                            <tr><th>ID</th><th>Дата<br><input id="safeCalend" style="width: 80px;"></th><th style="width:100px">Сумма</th><th>Комментарий</th><th>Кассир<br><select id="safeUser"><option></option>
                                            <?php
                                                foreach($this->safe as $user_id => $ar)
                                                {
                                                    echo '<option value="'.(int)$user_id.'">'.htmlspecialchars($ar['user']).'</option>';
                                                }
                                            ?>
                                            </select></th></tr>
                                        </thead>
                                        <tbody id="safeContent">
                                            <?php echo $this->safeHis;?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
<?php
        }
        else
        {
            /*
?>
            <div class="modal fade" id="ModalSafe" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="text-muted"><i class="fa fa-files-o"></i>&nbsp;&nbsp;<strong class="modal-title"> Сейф</strong></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                                <div class="tab-pane fade" id="BASafeHis">
                                    <div style="height:400px;overflow: auto;">
                                        <table style="width: 100%; font-size: 12px;" class="table-sm table-hover">
                                            <thead>
                                                <tr><th>ID</th><th>Дата<br><input id="safeCalend"></th><th style="width:100px">Сумма</th><th>Комментарий</th><th>Кассир</th></tr>
                                            </thead>
                                            <tbody id="safeContent">
                                                <?php echo $this->safeHis;?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>

<?php
            */
        }
    }
?>


<?php
    if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['rules'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['allrules'] == 1)
    {
?>
        <div class="modal fade" id="ModalRules" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="text-muted"><i class="fa fa-files-o"></i>&nbsp;&nbsp;<strong class="modal-title"> Правила авторазбиения</strong></div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" id="MRbody">
                        <button class="btn btn-sm btn-outline-info" onclick="$('#BAruleList').toggle(200)">Новое правило</button><br><br>
                        <div id="BAruleList" class="card card-body bg-light" style="display: none;">
                            <form action="" method="post" onsubmit="return false" id="BRform">
                            <small class="text-primary">Реквизиты платежа</small>
                            <table>
                                <tr>
                                    <td>Дата платежа</td>
                                    <td>от <input id="r_date1" autocomplete="off" name="BRdateStart" style="width: 90px;" type="text"> до <input name="BRdateStop" autocomplete="off" style="width: 90px;" id="r_date2" type="text"></td>
                                </tr>
                                <tr>
                                    <td>Счёт компании <strong class="text-danger">*</strong></td>
                                    <td><select name="BRcompany" id="BRcompany"></select></td>
                                </tr>
                                <tr>
                                    <td>Контрагент <strong class="text-warning">*</strong></td>
                                    <td><input type="text" autocomplete="off" name="BRcontr" id="BRcontr"></td>
                                </tr>
                                <tr>
                                    <td>Сумма <strong class="text-warning">*</strong></td>
                                    <td><input type="text" autocomplete="off" name="BRsum" id="BRsum"></td>
                                </tr>
                                <tr>
                                    <td>Назначение платежа <strong class="text-warning">*</strong></td>
                                    <td><textarea name="BRnazn" id="BRnazn" rows="2" cols="90"></textarea></td>
                                </tr>
                            </table>
                            <hr><small class="text-primary">Разбить на </small>
                            <table style="width: 100%;">
                                <tr>
                                    <td><label><input type="radio" name="BRPart" value="1" checked="checked"> задачу</label></td>
                                    <td><select name="BRtask" id="BRtask"></select></td>
                                </tr>
                            <?php
                                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1)
                                {
                            ?>
                                <tr>
                                    <td><label><input type="radio" name="BRPart" value="2"> зарплату</label></td>
                                    <td><select name="BRzp" id="BRzp" disabled="disabled"></select></td>
                                </tr>
                            <?php
                                }
                            ?>
                            </table>
                            <button class="btn btn-sm btn-success pull-right" id="addRuleBtn" onclick="addRule()">Сохранить</button>
                            <button class="btn btn-sm btn-secondary pull-right" onclick="$('#BAruleList').hide(200)" style="margin-right:5px">Закрыть</button>
                            </form>
                        </div>
                        <table class="table" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th style="width:100px">Дата</th>
                                    <th>Контрагент</th>
                                    <th>Сумма</th>
                                    <th>Назначение платежа</th>
                                    <th>Разбиение</th>
                                    <th>Найдено</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tblListRule">
                                <tr><td colspan="6"><div class="text-center"><img src="pub/img/loading.gif">Загрузка...</div></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer" id="MRfooter">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="ModalRulesID" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="text-muted"><i class="fa fa-files-o"></i>&nbsp;&nbsp;<strong class="modal-title"> Правила авторазбиения</strong> <small id="MRIDUser"></small></div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" style="height:400px;overflow-x: auto;">
                        <label class="badge badge-danger">Перед запуском правила убедитесь, что в списке отсутствуют "лишние" платежи!</label>
                        <table class="table table-sm table-hover" style="font-size: 12px;">
                            <thead>
                                <tr><th></th><th>Дата</th><th>Компания</th><th>Контрагент</th><th>Сумма</th><th>Назначение платежа</th></tr>
                            </thead>
                            <tbody id="ModalRuleIDBody">
                            
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer" id="MRIDfooter">
                        <span id="MRIDTask" style="float: left;"></span>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="$('#ModalRules').modal('show')" data-dismiss="modal">Закрыть</button>
                        <span id="MRIDsaveBtn"></span>
                    </div>
                </div>
            </div>
        </div>
<?php
    }

    if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['bnal'] == 1)
    {
?>
    <div class="modal fade" id="ModalAccount" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-muted"><i class="fa fa-list"></i> <strong class="modal-title">Счета компании</strong></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" style="display: hone;" id="accTableID" value="">
                    <input type="checkbox" style="display:none" id="saveSumAcc" <?php if(isset($_SESSION['bitAppPayment']['sumAcc']) && $_SESSION['bitAppPayment']['sumAcc'] == 1) echo 'checked="checked"'; ?>>
                    <button id="btnSumAcc" class="btn btn-sm btn-outline-info"><span id="wordSumAcc"><?php echo (isset($_SESSION['bitAppPayment']['sumAcc']) && $_SESSION['bitAppPayment']['sumAcc'] == 1) ? 'Скрыть' : 'Учитывать'; ?></span> нулевой и отрицательный остаток</button>
                    <table id="accTable1" class="eHover table-bordered table-sm table-hover" style="width: 100%;">
                        <thead>
                            <tr class="text-center">
                                <th>Компания</th>
                                <th colspan="2">Счёт</th>
                                <th>Баланс</th>
                            </tr>
                        </thead>
                        <tbody id="accTableBody">
                            <tr><td colspan="4" class="text-center"><img src="pub/img/loading.gif">Загрузка...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button>
                </div>
            </div>
        </div>
    </div>
<?php
    }
?>
    
    <div class="modal fade" id="ModalFilter" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" id="ModalFilterForm" onsubmit="return false">
                    <div class="modal-header">
                        <div class="text-muted"><i class="fa fa-list"></i> <strong class="modal-title">Поиск платежей</strong></div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <table id="accTable" class="table-bordered table-sm" style="width: 100%;">
                            <tbody>
                                <tr><td>Платежи за </td><td colspan="4"><select id="f_month" name="f_month"><option></option></select> &nbsp; <select id="f_year" name="f_year"><option></option></select></td></tr>
                                <tr><td>Дата платежа</td><td colspan="4"><input type="text" id="f_date1" name="f_date1" autocomplete="off" value=""></td></tr>
                                <tr><td>Дата изменения</td><td colspan="4"><input type="text" id="f_date2" name="f_date2" autocomplete="off" value=""></td></tr>
                                <!--<tr><td>Оператор(кассир)</td><td colspan="4"><select id="f_oper" name="f_oper"><option></option></select></td></tr>-->
                                <tr><td>Ответственный за платёж</td><td colspan="4"><input type="text" id="f_resp" name="f_resp" autocomplete="off" value=""></td></tr>
                                <tr><td id="tabCompTitle1">Компания</td><td colspan="4"><select id="f_company" name="f_company"></select></td></tr>
                                <tr><td id="tabKontrTitle1">Контрагент</td><td colspan="4"><input type="text" id="f_contragent" name="f_contragent" autocomplete="off" value=""> <!-- &nbsp; <select id="f_contragent2" name="f_contragent2"><option></option></select>--></td></tr>
                                <tr><td>Сделка</td><td colspan="4"><select id="f_deal" name="f_deal"></select></td></tr>
                                <tr><td>ID счёта</td><td colspan="4"><input type="text" id="f_invoice" name="f_invoice" autocomplete="off" value=""></td></tr>
                                <tr><td>Задача</td><td colspan="4"><select id="f_task" name="f_task"></select></td></tr>
                                <tr><td>Сумма</td><td colspan="4"><input type="text" id="f_sum" name="f_sum" autocomplete="off" value=""></td></tr>
                                <tr><td>Назначение платежа</td><td colspan="4"><input type="text" id="f_comment" name="f_comment" autocomplete="off" value="" style="width:100%"></td></tr>
                                <?php

                                    if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1)
                                    {
                                        echo '<tr><td>Начисление ЗП</td><td colspan="4"><select id="f_salary" name="f_salary">'.$_SESSION['bitAppPayment']['arSalary'].'</select></td></tr>';
                                    }
                                ?>
                                <tr>
                                    <td><label><input type="radio" name="f_part_r" id="f_part_r1" checked="checked" value="283"> Безналичные платежи</label></td>
                                    <td><label><input type="checkbox" name="f_part[]" id="f_bnal1" checked="checked" value="1"> Разбитые</label></td>
                                    <td><label><input type="checkbox" name="f_part[]" id="f_bnal2" checked="checked" value="2"> Неразбитые</label></td>
                                    <td><label><input type="checkbox" name="f_part[]" id="f_bnal3" checked="checked" value="3"> Приход</label></td>
                                    <td><label><input type="checkbox" name="f_part[]" id="f_bnal4" checked="checked" value="4"> Расход</label></td></tr>
                                <tr><td><label><input type="radio" name="f_part_r" id="f_part_r2" value="274"> Наличные платежи</label></td>
                                    <td><label><input type="checkbox" name="f_part[]" id="f_nal1" value="5"> Разбитые</label></td>
                                    <td><label><input type="checkbox" name="f_part[]" id="f_nal2" value="6"> Неразбитые</label></td>
                                    <td><label><input type="checkbox" name="f_part[]" id="f_nal3" value="7"> Приход</label></td>
                                    <td><label><input type="checkbox" name="f_part[]" id="f_nal4" value="8"> Расход</label></td></tr>
                            </tbody>
                        </table>
                        <input type="hidden" id="appPage" style="display: none;" value="0">
                        <input type="hidden" id="appGroup" style="display: none;" value="0">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i> Закрыть</button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="resetFilter()" style="margin-right:20px"><i id="iconReset" class="fa fa-refresh"></i> Очистить фильтры</button>
                        <button type="button" id="ModalFilterFind" class="btn btn-sm btn-success hide"><i class="fa fa-search"></i> Найти</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ModalAlert" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-muted"><i class="fa fa-bell"></i>&nbsp;&nbsp;<strong class="modal-title" id="MAlabel"> </strong></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="MAbody"></div>
                <div class="modal-footer" id="MAfooter">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
<?php
    if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['part'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1)
    {
?>
    <div class="modal fade" id="ModalMorePart" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-muted"><i class="fa fa-check-square-o"></i>&nbsp;&nbsp;<strong class="modal-title">Множественное разбиение платежей</strong></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="MMPbody">
                    <?php 
                        if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp3'] == 1)
                        {
                            echo '<label><input type="radio" name="PFMorePart2" value="1" checked="checked"> Задачи</label> &nbsp;&nbsp;&nbsp; <label><input type="radio" name="PFMorePart2" value="2"> Зарплата</label>';
                        }
                    ?>
                    <input type="text" class="span12" style="width: 100%;" name="PFsearchTask2" id="PFsearchTask2" placeholder="Поиск..."><br><br>
                    <div id="PFpartList2" style="cursor: pointer;white-space:nowrap; height: 160px;width:100%;overflow:auto"></div><br>
                    <textarea id="PRMoreComment" name="PRMoreComment" placeholder="Комментарий" rows="3" style="width: 100%;"></textarea>
                    <input type="hidden" id="PFpartTask2" name="PFpartTask2" value="0">
                </div>
                <div class="modal-footer" id="MMPfooter">
                    <div style="position:absolute;left:10px">Ответственный за платёж: <select type="text" id="respPay" disabled="disabled"></select> <button id="btnSaveResp" class="btn btn-sm btn-secondary" disabled="disabled">Сменить</button></div>
                    <?php 
                    if($_SESSION['bitAppPayment']['ID'] == 26)
                    {
                    ?>
                    <button type="button" class="btn btn-sm btn-danger" style="margin-right:20px;" id="BitAppDelMorePay"><i class="fa fa-trash"></i> Удалить</button>
                    <?php
                    }
                    ?>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-sm btn-success" id="BitAppSaveMorePay">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
        
    <div class="modal fade" id="PartForm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-muted"><i id="iconPart" class="fa fa-clone"></i>&nbsp;&nbsp;<strong class="modal-title" id="PFlabel"> Разбиение платежей</strong></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="PFbody">
                    <div id="FPlayer" style="opacity:0.4;background:#000;width:96.5%;height:95%;position:absolute;text-align: center;padding-top:15%"><img src="pub/img/loading.gif"></div>
                    <form action="" id="ModalPartForm" onsubmit="return false">
                                <table style="width: 100%;">
                                    <tr>
                                        <td id="pay_type" style="width:170px">Платёж: </td>
                                        <td><select id="PFcontainer" name="PFcontainer"></select> &nbsp;
                                        <?php
                                            if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['partdel'] == 1)
                                            {
                                                echo '<button id="PFRP" class="btn btn-sm" style="color: red" onclick="removePay()"><i class="fa fa-trash"></i></button></td>';
                                            }
                                        ?>
                                    </tr>
                                    <tr>
                                        <td>Поиск:</td>
                                        <td><input type="text" class="span12" style="width: 100%;" name="PFsearchTask" id="PFsearchTask"></td>
                                    </tr>
                                    <tr valign="top">
                                        <td>
                                            <label><input type="radio" name="selTypeList" value="1" onclick="$('#PFzpListTab, #PFrentListTab, #PFinvListTab, #PFcomadListTab').removeClass('active');$('#PFtaskListTab').addClass('active');" checked="checked">&nbsp; Задачи:</label><br>
                                            &nbsp;&nbsp;<label><input type="checkbox" id="shCloseTask">&nbsp;<small>Показать закрытые</small></label><br>
                                            <label><input type="radio" name="selTypeList" value="4" onclick="$('#PFtaskListTab, #PFrentListTab, #PFzpListTab, #PFinvListTab').removeClass('active');$('#PFcomadListTab').addClass('active');">&nbsp; Командировки:</label><br>
                                            <label><input type="radio" name="selTypeList" value="3" onclick="$('#PFtaskListTab, #PFrentListTab, #PFzpListTab, #PFcomadListTab').removeClass('active');$('#PFinvListTab').addClass('active');">&nbsp; Счета:</label><br>
                                            <label><input type="radio" name="selTypeList" value="5" onclick="$('#PFtaskListTab, #PFinvListTab, #PFzpListTab, #PFcomadListTab').removeClass('active');$('#PFrentListTab').addClass('active');">&nbsp; Аренда ТС:</label><br>
                                            <?php
                                                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp3'] == 1)
                                                {
                                            ?>
                                                    <label><input type="radio" name="selTypeList" value="2" onclick="$('#PFtaskListTab, #PFrentListTab, #PFinvListTab, #PFcomadListTab').removeClass('active');$('#PFzpListTab').addClass('active');">&nbsp; Зарплата:</label>
                                            <?php
                                                }
                                            ?>

                                        </td>
                                        <td>
                                            <div class="tab-content">
                                                <div class="tab-pane active container" id="PFtaskListTab">
                                                    <table style="width:100%">
                                                        <tr>
                                                            <td style="background: #fff;">
                                                                <div id="PFpartList" style="cursor: pointer;white-space:nowrap; height: 160px;width:100%;overflow:auto"></div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="tab-pane container" id="PFcomadListTab">
                                                    <table style="width:100%">
                                                        <tr>
                                                            <td style="background: #fff;">
                                                                <div id="PFcomandList" style="cursor: pointer;white-space:nowrap; height: 160px;width:690px;overflow:auto"></div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="tab-pane container" id="PFinvListTab">
                                                    <table style="width:100%">
                                                        <tr>
                                                            <td style="background: #fff;"><input type="hidden" id="PFpartInv" name="PFpartInv" value="0">
                                                                <div id="PFinvList" style="cursor: pointer;white-space:nowrap; height: 160px;width:100%;overflow:auto">Счета не найдены</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="tab-pane container" id="PFrentListTab">
                                                    <table style="width:100%">
                                                        <tr>
                                                            <td style="background: #fff;"><input type="hidden" id="PFpartRent" name="PFpartRent" value="0">
                                                                <div id="PFrentList" style="cursor: pointer;white-space:nowrap; height: 160px;width:100%;overflow:auto">Информация об аренде не найдена</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            <?php
                                                if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp1'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp3'] == 1)
                                                {
                                            ?>
                                                <div class="tab-pane container" id="PFzpListTab">
                                                    <table style="width:100%">
                                                        <tr>
                                                            <td style="background: #fff;"><input type="hidden" id="PFpartZP" name="PFpartZP" value="0">
                                                                <div id="PFcurrDebt"></div>
                                                                <div id="PFpartListZP" style="cursor: pointer;white-space:nowrap; height: 160px;width:100%;overflow:auto"></div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            <?php
                                                }
                                            ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr><td>Сумма: </td><td><input type="text" style="width: 100%;" id="PFpartSum" name="PFpartSum"></td></tr>
                                    <tr><td>Комментарий: </td><td><textarea rows="2" style="width: 100%;" id="PFpartComm" name="PFpartComm"></textarea></td></tr>
                                    <tr><td>Файл: </td><td><input type="file" id="PFpayFile"></td></tr>
                                    <tr id="PFciRow" style="display:none"><td colspan="2"><br><label><input id="PFci" name="PFci" type="checkbox" value="1"> Создать счёт на оплату <small>(только при списании на задачу)</small></label></td></tr>
                                </table>
                                <input type="hidden" class="span12" name="PFpayTypeList" id="PFpayTypeList" value="283">
                                <input type="hidden" class="span12" name="PFpayId" id="PFpayId" value="0">
                                <input type="hidden" id="PFpartTask" name="PFpartTask" value="0">
                                <input type="hidden" id="PFpartTask3" name="PFpartTask3" value="0">
                                <input type="hidden" id="savePartTest" name="savePart" value="1">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-sm btn-success" id="BitAppSavePay">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
<?php
    }
    
    
    #   Наличка
    if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['nal'] == 1)
    {
        echo '<li class="nav-item"><button data-toggle="modal" data-target="#ModalNal" class="btn btn-sm btn-outline-info" style="margin: 2px;"><i id="iconNal" class="fa fa-money"></i> Добавить наличный платёж <span class="badge-pill badge badge-success" id="ModalNalBtn"></span></button></li>';
?>
        <div class="modal fade" id="ModalNal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-muted"><i class="fa fa-money"></i>&nbsp;&nbsp;<strong class="modal-title"> Добавление наличного платежа</strong></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="MNbody">
                    <table style="width: 100%;">
                        <tr><td style="width:200px"><label><input type="radio" name="nalPayType" checked="checked" value="user"> Выдать сотруднику</label></td>
                            <td><select name="nalPayUser"></select></td>
                        </tr>
                        <tr><td style="width:200px;vertical-align: top;">
                                <label><input type="radio" name="nalPayType" value="task"> Платёж на задачу</label><br>
                                <label><input type="radio" name="nalPayType" value="comand"> Командировка</label>
                            </td>
                            <td><input type="text" name="searchNalTask" id="searchNalTask" value="" disabled="disabled" autocomplete="off" placeholder="Поиск..." style="width: 100%;">
                                <div id="nalPayTaskList" style="width: 100%;height: 125px;overflow: auto;cursor: pointer;white-space:nowrap;" class="disabledbutton"></div>
                                <div id="nalPayComandList" style="width: 100%;height: 125px;overflow: auto;cursor: pointer; display:none" class="disabledbutton"></div>
                                <input type="hidden" name="nalPayTask" style="display: none;" id="nalPayTask" value="0">
                                <input type="hidden" name="nalPayComand" style="display: none;" id="nalPayComand" value="0">
                            </td>
                        </tr>
                        <?php
                             if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['zp2'] == 1)
                             {
                                 echo '<tr><td style="width:200px"><label><input type="radio" name="nalPayType" value="zp"> Платёж на зарплату</label></td>
                                           <td><select name="nalPayZP" disabled="disabled"><option></option></select></td>
                                       </tr>';
                             }
                         ?>
                        <tr><td style="width:200px">Сумма</td>
                            <td><input type="text" name="nalPaySum">
                                <label><input type="radio" id="nalTypeSum1" name="nalTypeSum" value="p">Приход</label>
                                <label><input type="radio" id="nalTypeSum2" name="nalTypeSum" value="r" checked="checked">Расход</label></td>
                        </tr>
                        <tr><td style="width:200px">Комментарий</td>
                            <td><textarea rows="2" style="width:100%" name="nalPayComment"></textarea></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer" id="MNfooter">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="button" id="btnSaveNalPay" onclick="saveNalPay()" class="btn btn-sm btn-success hide"> Сохранить</button>
                </div>
            </div>
        </div>
    </div>
<?php
    }
    
    if($_SESSION['bitAppPayment']['ADMIN'] == 1 || $_SESSION['bitAppPayment']['ACCESS']['quenue'] == 1)
    {
?>
    <div class="modal fade" id="ModalQuenue" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-muted"><i class="fa fa-database"></i>&nbsp;&nbsp;<strong class="modal-title"> Платежи в очереди</strong></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="MQbody">
                    <table class="table table-hover table-sm" style="width: 100%;font-size: 12px;">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Сумма</th>
                                <th>Назначение платежа</th>
                                <th>Операция</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody id="MQtable"></tbody>
                    </table>
                </div>
                <div class="modal-footer" id="MQfooter">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
<?php
    }
?>

<?php
    #   Настройки
    if($_SESSION['bitAppPayment']['ADMIN'] == 1)
    {
?>
    <div class="modal fade" id="ModalAccUsr" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-muted"><i class="fa fa-cog"></i>&nbsp;&nbsp;<strong class="modal-title" id="ModalAccUsrTitle"> Управление доступом</strong></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <table style="width: 100%;">
                        <tr>
                            <td>
                                <form action="" method="post" id="ModalAccUsrForm" onsubmit="return false">
                                <?php
                                    if(!empty($this->rules))
                                    {
                                        foreach($this->rules as $rule => $r_name)
                                        {
                                            if($rule == 'bnal')
                                                echo '<label><input type="checkbox" id="setAccBNal" name="'.htmlspecialchars($rule).'"> '.($r_name).'</label><br>';
                                            else
                                                echo '<label><input type="checkbox" name="'.htmlspecialchars($rule).'"> '.($r_name).'</label><br>';
                                        }
                                    }
                                ?>
                                </form>
                            </td>
                            <td>
                                <div style="height: 390px;overflow-x: auto;float:right">
                                    <input type="hidden" id="checkLoadTable" value="0">
                                    <form action="" method="post" id="ModalAccAccForm" style="display: none" onsubmit="return false">
                                    <i class="fa fa-spin fa-spinner"></i> Загрузка...
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer" id="MSfooter">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-sm btn-success" id="saveAccBtn" onclick="saveAcc()">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ModalSettings" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-muted"><i class="fa fa-cog"></i>&nbsp;&nbsp;<strong class="modal-title"> Настройки доступа</strong></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="MSbody">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a href="#BAsetAcc" class="nav-link active" data-toggle="tab">Управление доступом</a></li>
                        <li class="nav-item"><a href="#BAgetAcc" class="nav-link" onclick="getAccUpd()" data-toggle="tab">Предоставленный доступ</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="BAsetAcc">
                            <div class="sticky2" style="margin-top: 5px;">
                                <input type="text" class="span12" style="width: 60%;" name="PFsearchUsr" id="PFsearchUsr" placeholder="Поиск сотрудника">
                                <button data-toggle="modal" class="btn btn-sm btn-info float-right" style="display: none;" id="setAccessUsrBtn" onclick="setAccessUsr()"><i class="fa fa-check-square-o"></i> Управление доступом</button>
                            </div>
                            <div id="MSAA">
                            <?php
                                if(!empty($this->userAccess))
                                {
                                    foreach($this->userAccess as $id => $user)
                                    {
                                        echo '<div class="usrList"><label><input type="checkbox"  class="accessUser" onchange="selAllAccUsr()" value="'.$id.'"> <span id="usrName'.$id.'">'.htmlspecialchars($user).'</span></label></div>';
                                    }
                                }
                            ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="BAgetAcc" style="padding-top: 5px;">
                            <?php
                                if(!empty($this->GetUserAccess))
                                {
                                    foreach($this->GetUserAccess as $user => $rules)
                                    {
                                        if(!empty($rules))
                                        {
                                            echo '<div><strong>'.htmlspecialchars($user).'</strong><br>';
                                            foreach($rules as $r => $r_name)
                                            {
                                                echo '&nbsp;&nbsp;&nbsp;<small>'.($r_name).'</small><br>';
                                            }
                                            echo '<hr></div>';
                                        }
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="MSfooter0">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
<?php
    }
?>
    <div style="height: 40px;">&nbsp;</div>
    <table class="table table-sm table-hover table-bordered" style="font-size: 14px;margin: 10px;width:99%">
        <thead class="sticky">
            <tr class="text-center">
                <th><input type="checkbox" id="bitAppSelAll"></th>
                <th>Дата платежа</th>
                <th id="tabCompTitle2" style="width: 150px;">Компания</th>
                <th id="tabKontrTitle2">Контрагент</th>
                <th style="min-width: 120px;">Сумма платежа</th>
                <th>Сделка</th>
                <th>Счёт</th>
                <th>Задача</th>
                <th style="min-width: 120px;">Сумма</th>
                <th>Ответственный за платёж</th>
                <th>Дата изменения</th>
                <th>Оператор</th>
            </tr>
        </thead>
        <tbody id="contentTable">
            <tr id="strLoading"><td colspan="12" style="text-align: center;"><img src="pub/img/loading.gif"> <strong>Загрузка...</strong></td></tr>
        </tbody>
    </table>
    <div id="scrollThis"></div>
    <script src="pub/js/jquery.js"></script>
    <script src="pub/js/jquery-ui.js"></script>
    <script src="pub/js/popper.min.js"></script>
    <script src="pub/js/bootstrap.min.js"></script>
    <script src="pub/js/bootstrap-datepicker.min.js"></script>
    <script src="pub/js/bootstrap-datepicker.ru.min.js"></script>
    <script src="pub/js/functions7.11.5.js"></script>
    <script>
        $('#ModalPartForm').on('click', function(){
            if($('[name=selTypeList]:checked').val() == 1){
                $('#PFciRow').css('display', 'table-row');
            }else{
                $('#PFci').prop('checked', false);
                $('#PFciRow').css('display', 'none');
            }
        });
    </script>
</body>
</html>