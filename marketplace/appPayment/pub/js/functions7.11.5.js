load = 0;

$(document).ready(function(){
    loadInfo();
    
    $('#f_sum').change(function(){$('#f_sum').val($('#f_sum').val().replace(',', '.'));});
    $('#shCloseTask').click(function(){
         if($('#shCloseTask').is(':checked'))
         {
             $('.BitClose').removeClass('BitClose').addClass('BitOpen').show();
         }
         else
         {
             $('.BitOpen').removeClass('BitOpen').addClass('BitClose').hide();
         }
    });
    $('#ModalFilterFind').click(function(){
        getInfo();
    });
    
$('#btnSumAcc').on('click', function(){
    ($('#wordSumAcc').html() == 'Учитывать') ? $('#wordSumAcc').html('Скрыть') : $('#wordSumAcc').html('Учитывать');
    
    if($('#saveSumAcc').is(':checked')){
        $('#saveSumAcc').prop('checked', false);
    }else{
        $('#saveSumAcc').prop('checked', true);
    }

    var accSum = ($('#saveSumAcc').is(':checked')) ? 1 : 0;
    $.ajax({
        beforeSent: $('#accTableBody').html('<tr><td colspan="4" class="text-center"><img src="pub/img/loading.gif">Загрузка...</td></tr>'),
        data: 'saveSumAcc='+accSum,
        type: 'POST',
        success: function (data) {
            $('#accTableBody').html(data);
        }
    });
});

    $('[name=BRPart]').click(function(){
        if($(this).val() == 1)
        {
            $('#BRtask').removeAttr('disabled');
            $('#BRzp').attr('disabled', 'disabled');
        }
        else
        {
            $('#BRzp').removeAttr('disabled');
            $('#BRtask').attr('disabled', 'disabled');
        }
    });
    
    $('[name="nalPayType"]').on('click', function(){
        $(this).each(function(i, x){
            if($(x).is(':checked')){
                if($(x).val() == 'task'){
                    $('[name="nalPayTask"], [name="searchNalTask"], .objList').prop('disabled', false);
                    $('#nalPayTaskList').removeClass('disabledbutton');
                    $('#nalPayComandList').hide();
                    $('#nalPayTaskList').show();
                    $('[name="nalPayInvoice"], [name="nalPayZP"], [name="nalPayUser"]').prop('disabled', true);
                }else{
                    if($(x).val() == 'invoice'){
                        $('#nalPayTaskList').addClass('disabledbutton');
                        $('[name="nalPayInvoice"]').prop('disabled', false);
                        $('[name="nalPayTask"], [name="searchNalTask"], .objList, [name="nalPayZP"], [name="nalPayUser"]').prop('disabled', true);
                    }else{
                        if($(x).val() == 'zp'){
                            $('#nalPayTaskList').addClass('disabledbutton');
                            $('[name="nalPayZP"]').prop('disabled', false);
                            $('[name="nalPayTask"], [name="searchNalTask"], .objList, [name="nalPayInvoice"], [name="nalPayUser"]').prop('disabled', true);
                        }else{
                            if($(x).val() == 'comand')
                            {
                                $('#nalPayTaskList').hide();
                                $('#nalPayComandList').show();
                                $('#nalPayComandList').removeClass('disabledbutton');
                                $('[name="nalPayTask"], [name="nalPayInvoice"], [name="nalPayZP"], [name="nalPayUser"]').prop('disabled', true);
                            }else{
                                $('#nalPayTaskList').addClass('disabledbutton');
                                $('[name="nalPayUser"]').prop('disabled', false);
                                $('[name="nalPayTask"], [name="searchNalTask"], .objList, [name="nalPayInvoice"], [name="nalPayZP"]').prop('disabled', true);
                            }
                        }
                    }
                }
            }
        });
    });
    
    $('#btnGroup').click(function(){
        var g = $('#appGroup').val();
        if(g == 1)
        {
            $('#btnGroup').removeClass('active');
            $('#appGroupIcon').removeClass('fa-object-group').addClass('fa-object-ungroup');
            $('#appGroup').val(0);
        }
        else
        {
            $('#btnGroup').addClass('active');
            $('#appGroupIcon').removeClass('fa-object-ungroup').addClass('fa-object-group');
            $('#appGroup').val(1);
        }
        getInfo();
    });
    
    $('#file').change(function(){
        $('#MAlabel').html('Загрузка выписки');
        
        var file = this.files[0];
        
        if(file.type != 'text/plain' || file.name.split('.').pop() != 'txt'){
            $('#MAbody').html('<div class="alert alert-danger"><i class="fa fa-times"></i> Можно загружать только текстовые файлы (*.txt)</div>');
            $('#ModalAlert').modal('show');
            return;
        }

        if(file.size > 26214400){
            $('#MAbody').html('<div class="alert alert-danger"><i class="fa fa-times"></i> Слишком большой размер файла</div>');
            $('#ModalAlert').modal('show');
            return;
        }
        
        var fd = new FormData;
        fd.append('txt', $('#file').prop('files')[0]);
        
        $.ajax({
            beforeSent: $('#iconUpload').removeClass('fa-upload').addClass('fa-spin fa-spinner'),
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (data) {
                $('#iconUpload').removeClass('fa-spin fa-spinner').addClass('fa-upload'),
                alert(data);
                return;
            }
        });
    });
    
    var calend = $('#f_date1, #f_date2, #r_date1, #r_date2, #safeCalend').datepicker({
        format: "dd.mm.yyyy",
        language: "ru"
    });
    calend.on('changeDate', function(){
        calend.datepicker('hide');
    });
    
    $('[name=selTypeList]').click(function(){
        $('#PFpartTask').val(0);
        $('.objList').removeClass('alert-success');
    });
    
    $('#searchNalTask').on('input', function () {
        $('#nalPayTask').val(0);
        $('.objList').removeClass('alert-success');
        var letter = $(this).val().toLowerCase();
        $('#nalPayTaskList, #nalPayComandList').find('div').each(function () {
            var option = $(this).html().toLowerCase();
            if (option.includes(letter)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#PFsearchTask').on('input', function () {
        $('#PFpartTask').val(0);
        $('.objList').removeClass('alert-success');

        var letter = $(this).val().toLowerCase();
        $('#PFpartList').find('div').each(function () {
            var option = $(this).html().toLowerCase();
            if (option.includes(letter)) {
                if(($(this).hasClass('BitClose') && $('#shCloseTask').is(':checked')) || !$(this).hasClass('BitClose')){
                    $(this).show();
                }
            } else {
                $(this).hide();
            }
        });
        
        $('#PFpartListZP, #PFinvList, #PFrentList, #PFcomandList').find('div').each(function () {
            var option = $(this).html().toLowerCase();
            if (option.includes(letter)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#PFsearchTask2').keyup(function () {
        $('#PFpartTask2').val(0);
        $('.objList').removeClass('alert-success');

        var letter = $(this).val().toLowerCase();
        $('#PFpartList2').find('div').each(function () {
            var option = $(this).html().toLowerCase();
            if (option.includes(letter)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#safeCalend, #safeUser').change(function(){
        $.ajax({
            beforeSent: $('#safeCalend, #safeUser').attr('disabled', 'disabled'),
            data: 'getSafeHistory=1&date=' + $('#safeCalend').val()+'&user='+$('#safeUser').val(),
            type: 'POST',
            success: function (data) {
                $('#safeCalend, #safeUser').removeAttr('disabled');
                $('#safeContent').html(data);
            },
            timeout: 20000
        });
    });
    
    $('#PFsearchUsr').keyup(function () {
        var letter = $(this).val().toLowerCase();
        $('#MSAA').find('div').each(function () {
            var option = $(this).html().toLowerCase();
            if (option.includes(letter)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#PFsearchUsr2').keyup(function () {
        var letter = $(this).val().toLowerCase();
        $('#MSAU2').find('div').each(function () {
            var option = $(this).html().toLowerCase();
            if (option.includes(letter)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#ModalMorePartBtn').on('click', function(){
        $('#respPay, #btnSaveResp').prop('disabled', true);
        $('#btnSaveResp').removeClass('btn-success').removeClass('btn-danger').addClass('btn-secondary');
        var payments = [];
        $('[name=MorePart]').each(function(){
            if($(this).is(':checked'))
                payments.push($(this).val());
        });
        $.ajax({
            data: 'getRespPay=' + payments,
            type: 'POST',
            success: function (data) {
                $('#respPay').html(data);
                $('#respPay, #btnSaveResp').prop('disabled', false);
                $('#btnSaveResp').removeClass('btn-secondary').addClass('btn-success');
            },
            timeout: 7000
        });
    });

    $('#btnSaveResp').on('click', function(){
        if($('#respPay').val() > 0) {
            var payments = [];
            $('[name=MorePart]').each(function(){
                if($(this).is(':checked'))
                    payments.push($(this).val());
            });
            $.ajax({
                beforeSent: $('#btnSaveResp').prop('disabled', true),
                data: 'saveRespPay=' + payments + '&resp=' + $('#respPay').val(),
                type: 'POST',
                dataType: 'JSON',
                success: function (data) {
                    if(data.status == 1)
                        $('#btnSaveResp').prop('disabled', false);
                    else
                        $('#btnSaveResp').prop('disabled', false).removeClass('btn-success').addClass('btn-danger');
                },
                timeout: 7000
            });
        }
    });
    
    $('#BitAppSaveMorePay').click(function(){
        if(typeof $('#BitAppSaveMorePay').attr('disabled') === "undefined")
        {
            var part = [];
            var type = 283;
            var task = $('#PFpartTask2').val();
            var zp = 1;
            $('[name=PFMorePart2]').each(function(){
                if($(this).is(':checked')){
                    zp = $(this).val();
                }
            });
            
            $('[name=MorePart]').each(function(){
                if($(this).is(':checked'))
                    part.push($(this).val());
                    type = $('#type'+$(this).val()).val();
            });
            
            if(part.length > 0 && task > 0)
            {
                $.ajax({
                    beforeSent: $('#BitAppSaveMorePay').attr('disabled', 'disabled').html('<i class="fa fa-spin fa-spinner"></i> Сохранить'),
                    data: 'saveMorePart=1&task='+task+'&payments='+part+'&type='+$('#PFpayTypeList').val()+'&zp=' + zp + '&comment='+$('#PRMoreComment').val(),
                    type: 'POST',
                    success: function (data) {
                        alert(data);
                        $.each(part, function(idx, val){
                            $('#chk'+val).html('<i class="fa fa-spinner fa-spin"></i>');
                            $('#sm'+val).removeAttr('onclick').removeAttr('style');
                        });
                        //getQuenue();
                        $('#BitAppSaveMorePay').removeAttr('disabled').html('Сохранить');
                        $('#ModalMorePart').modal('hide');
                        showMorePart();
                    },
                    error: function(jqXHR, exception){
                        alert('Ошибка при сохранении');
                        $('#BitAppSaveMorePay').removeAttr('disabled').html('Сохранить');
                    }
                });
            }
        }
    });
    
    $('#bitAppSelAll').change(function(){
        if($(this).is(':checked'))
        {
            $('[name=MorePart]').each(function(){
                $(this).prop('checked', true);
            });
        }
        else
        {
            $('[name=MorePart]').each(function(){
                $(this).prop('checked', false);
            });
        }
        
        showMorePart();
    });
    
    $('[name=PFMorePart2]').change(function(){
        $('#PFpartList2').html('');
        $('#PFpartTask2').val(0);
        
        $.ajax({
            data: 'getListMoreTask=' + $(this).val(),
            type: 'POST',
            success: function (data) {
                $('#PFpartList2').html(data);
            },
            timeout: 7000
        });
    });
    
    $('#PFcontainer').change(function(){
        $.ajax({
            data: 'getSum=' + this.value,
            type: 'POST',
            success: function (data) {
                $('#PFpartSum').val(data);
                $('#PFpayId').val($('#PFcontainer').val());
            },
            error: function(){
                alert('Ошибка выбора платежа');
            },
            timeout: 7000
        });
        $('#PFpayId').val(this.value);
    });
    
    $('#BitAppSavePay').on('click', function(){
        if(typeof $('#BitAppSavePay').attr('disabled') === "undefined")
        {
            var fd = new FormData(document.getElementById('ModalPartForm'));
                fd.append('FILE', $('#PFpayFile').prop('files')[0]);
                
            $.ajax({
                beforeSent: $('#BitAppSavePay').attr('disabled', 'disabled').html('<i class="fa fa-spin fa-spinner"></i> Сохранить'),
                data: fd,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'JSON',
                success: function (data) {
                    if(data.status == 1){
                        //getQuenue();
                        $('[name=nalPayZP]').html(data.zp1);
                        $('#PFpartListZP').html(data.zp2);
                        getInfo();
                    }else{
                        if(data.status == 2){
                            //getQuenue();
                            var idStr = '#str'+ $('#PFpayId').val();
                            $(idStr).removeClass('alert-danger');
                            $('#strI'+$('#PFpayId').val()).html(data.inv);
                            $('#strT'+$('#PFpayId').val()).html(data.task);
                        }else{
                            alert('Что-то пошло не так');
                        }
                    }
                    
                    $('#BitAppSavePay').removeAttr('disabled').html('Сохранить');
                    $('#PartForm').modal('hide');
                },
                error: function(jqXHR, exception){
                    alert('Ошибка при сохранении');
                    $('#BitAppSavePay').removeAttr('disabled').html('Сохранить');
                }
            });
        }
    });
    
    $('#BitAppDelMorePay').click(function(){
        var part = [];
        var type = 283;
        $('[name=MorePart]').each(function(){
            if($(this).is(':checked')){
                part.push($(this).val());
                type = $('#type'+$(this).val()).val();
            }
        });
        
        if(part.length > 0){
            if(confirm('Удалить разбиения?'))
            {
                $('#BitAppSaveMorePay').attr('disabled', 'disabled');
                $.ajax({
                    beforeSent: $('#BitAppDelMorePay').attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Удалить'),
                    data: 'delMorePart=1&ids='+part+'&type=' + type,
                    type: 'POST',
                    success: function (data) {
                        $('#BitAppDelMorePay').removeAttr('disabled').html('<i class="fa fa-trash"></i> Удалить');
                        $('#ModalMorePart').modal('hide');
                        getInfo();
                    },
                    error: function(jqXHR, exception){
                        alert('Ошибка при сохранении');
                        $('#BitAppDelMorePay').removeAttr('disabled').html('<i class="fa fa-trash"></i> Удалить');
                    }
                });
                $('#BitAppSaveMorePay').removeAttr('disabled');
            }
        }
    });
    
    $('#setAccBNal').change(function(){
        if($(this).is(':checked'))
        {
            $('#ModalAccAccForm').show();
        }
        else
        {
            $('#ModalAccAccForm').hide();
        }
    });
    
    $('[name=f_part_r]').change(function(){
        if($(this).val() == 283){
            $('#f_nal1, #f_nal2, #f_nal3, #f_nal4').prop('checked', false);
            $('#f_bnal1, #f_bnal2, #f_bnal3, #f_bnal4').prop('checked', true);
        }else{
            $('#f_nal1, #f_nal2, #f_nal3, #f_nal4').prop('checked', true);
            $('#f_bnal1, #f_bnal2, #f_bnal3, #f_bnal4').prop('checked', false);
        }
    });
});

function saveSettings()
{
    var lst = $('#setTaskFot').val();
    if(lst.length > 0){
        $.ajax({
            beforeSent: $('#saveSettingsBtn').prop('disabled', true),
            data: 'setTaskFot='+lst,
            type: 'POST',
            success: function (data) {
                if(data == 1)
                    $('#saveSettingsBtn').hide();
                else
                    $('#saveSettingsBtn').prop('disabled', true).removeClass('btn-success').addClass('btn-danger');
            },
            error: function(jqXHR, exception){},
            timeout: 5000
        });
    }
}

function createTransList()
{
    $.ajax({
        beforeSent: $('#createTransListBtn').prop('disabled', true),
        data: 'createTransList=1',
        type: 'POST',
        dataType: 'JSON',
        success: function (data) {
            if(data.status == 1)
            {
                $('#spTransList').html(data.lst);
                $('#createTransListBtn').hide();
            }
            else
                $('#createTransListBtn').prop('disabled', false).removeClass('btn-success').addClass('btn-danger');
        },
        error: function(jqXHR, exception){},
        timeout: 5000
    });
}
function setAllAcc(obj)
{
    if($(obj).is(':checked'))
    {
        $('.accList').each(function(){
            $(this).prop('checked', true);
        });
    }
    else
    {
        $('.accList').each(function(){
            $(this).prop('checked', false);
        });
    }
}

function getAccUpd()
{
    $.ajax({
        data: 'getAccUpd=1',
        type: 'POST',
        success: function (data) {
            $('#BAgetAcc').html(data);
        },
        error: function(jqXHR, exception){},
        timeout: 5000
    });
}

function selAllAccUsr()
{
    var cnt = 0;
    $('.accessUser').each(function(){if($(this).is(':checked')){cnt++;}});
    if(cnt > 0){
        $('#setAccessUsrBtn').show();
    }else{
        $('#setAccessUsrBtn').hide();
    }
}
function setAccessUsr()
{
    $('#ModalSettings').modal('hide');
    var fLoadTable = $('#checkLoadTable').val();
    var selAccUsr = [];
    $('.accessUser:checked').each(function(i, obj) {
        selAccUsr.push($(obj).val());
    });
    
    $('#ModalAccUsrTitle').html('<img src="pub/img/loading3.gif">');
    if(selAccUsr.length == 1){
        $.ajax({
            beforeSent: $('#setAccessUsrBtn').attr('disabled', 'disabled'),
            data: 'getAccessAcc=1&usr='+selAccUsr,
            type: 'POST',
            dataType: 'JSON',
            success: function (data) {
                $('#ModalAccAccForm').html(data.acc);
                (data.nal == 1) ? $('[name="nal"]').prop('checked', true) : $('[name="nal"]').prop('checked', false);
                (data.bnal == 1) ? $('[name="bnal"]').prop('checked', true) : $('[name="bnal"]').prop('checked', false);
                (data.bnal_sum == 1) ? $('[name="bnal_sum"]').prop('checked', true) : $('[name="bnal_sum"]').prop('checked', false);
                (data.vb == 1) ? $('[name="vb"]').prop('checked', true) : $('[name="vb"]').prop('checked', false);
                (data.rules == 1) ? $('[name="rules"]').prop('checked', true) : $('[name="rules"]').prop('checked', false);
                (data.allrules == 1) ? $('[name="allrules"]').prop('checked', true) : $('[name="allrules"]').prop('checked', false);
                (data.dellrules == 1) ? $('[name="dellrules"]').prop('checked', true) : $('[name="dellrules"]').prop('checked', false);
                (data.part == 1) ? $('[name="part"]').prop('checked', true) : $('[name="part"]').prop('checked', false);
                (data.zp == 1) ? $('[name="zp"]').prop('checked', true) : $('[name="zp"]').prop('checked', false);
                (data.zp1 == 1) ? $('[name="zp1"]').prop('checked', true) : $('[name="zp1"]').prop('checked', false);
                (data.zp2 == 1) ? $('[name="zp2"]').prop('checked', true) : $('[name="zp2"]').prop('checked', false);
                (data.zp3 == 1) ? $('[name="zp3"]').prop('checked', true) : $('[name="zp3"]').prop('checked', false);
                (data.partdel == 1) ? $('[name="partdel"]').prop('checked', true) : $('[name="partdel"]').prop('checked', false);
                (data.quenue == 1) ? $('[name="quenue"]').prop('checked', true) : $('[name="quenue"]').prop('checked', false);                
                $('#ModalAccAccForm').show();
                $('#setAccessUsrBtn').removeAttr('disabled');
                $('#checkLoadTable').val('1');
                $('#ModalAccUsrTitle').html('Управление доступом');
            },
            error: function(jqXHR, exception){
                alert('Время ожидания истекло');
                $('#setAccessUsrBtn').removeAttr('disabled');
                $('#ModalAccUsrTitle').html('Управление доступом');
            },
            timeout: 10000
        });
    }
    else
    {
        if(fLoadTable == 0){
            $.ajax({
                beforeSent: $('#setAccessUsrBtn').attr('disabled', 'disabled'),
                data: 'getAccessAcc=1',
                type: 'POST',
                success: function (data) {
                    $('#ModalAccAccForm').html(data);
                    if($('#setAccBNal').is(':checked'))
                        $('#ModalAccAccForm').show();
                    
                    $('#setAccessUsrBtn').removeAttr('disabled');
                    $('#checkLoadTable').val('1');
                    $('#ModalAccUsrTitle').html('Управление доступом');
                },
                error: function(jqXHR, exception){
                    alert('Время ожидания истекло');
                    $('#setAccessUsrBtn').removeAttr('disabled');
                    $('#ModalAccUsrTitle').html('Управление доступом');
                },
                timeout: 10000
            });
        }
        $('#ModalAccUsrTitle').html('Управление доступом');
    }
    
    
    
    $('#ModalAccUsr').modal('show');
}

function saveAcc()
{
    var usr = [];
    $('.accessUser').each(function(){
        if($(this).is(':checked'))
            usr.push($(this).val());
    });
    
    if(usr.length > 0)
    {
        var acc    = $('#ModalAccUsrForm').serialize();
        var accAcc = [];
        $('.accList').each(function(){
            if($(this).is(':checked')){
                accAcc.push($(this).val());
            }
        });
        
        $.ajax({
            beforeSent:$('#saveAccBtn').attr('disabled', 'disabled').html('<i class="fa fa-spin fa-spinner"></i> Сохранить'),
            data: 'setAccessUsr=1&'+acc+"&account="+accAcc+'&user='+usr,
            type: 'POST',
            success: function (data) {
                if(data != 1)
                    alert('Что-то пошло не так');
                
                $('.accessUser').prop('checked', false);
                $('#setAccessUsrBtn').hide();
                $('#saveAccBtn').removeAttr('disabled').html('Сохранить');
                $('#ModalSettings').modal('show');
                $('#ModalAccUsr').modal('hide');
            },
            error: function(jqXHR, exception){
                alert('Время ожидания истекло');
                $('#saveAccBtn').removeAttr('disabled').html('Сохранить');
                $('#ModalSettings').modal('show');
                $('#ModalAccUsr').modal('hide');
            },
            timeout: 10000
        });
    }
}

function showMorePart()
{
    var part = 0;
    var sum  = 0;
    $('[name=MorePart]').each(function(){
        if($(this).is(':checked'))
        {
            part++;
            $('#str'+$(this).val()).addClass('alert-success');
            sum += parseFloat($('#smtr'+$(this).val()).val());
        }
        else
        {
            $('#str'+$(this).val()).removeClass('alert-success');
        }
    });
    
    if(part > 0)
    {
        sum = sum.toFixed(2);
        $('#BAmpSum').html(sum);
        $('#ModalMorePartBtn').show();
        $('#MorePartBage').html(part);
    }
    else
    {
        $('#BAmpSum').html(0);
        $('#ModalMorePartBtn').hide();
        $('#MorePartBage').html('');
    }
}

function setAcc(id)
{
    $('#accTableBody tr').removeClass('alert-info');
    $('#'+id+'str').addClass('alert-info');
    $('#accTableID').val(id);
    
     if($('#f_part_r2').is(':checked'))
    {
        $('#f_part_r2').prop('checked', false);
        $('#f_part_r1').prop('checked', true);
    }
    
    getInfo();
}

function addRule()
{
    var form = $('#BRform').serialize();
    $.ajax({
        beforeSent: $('#addRuleBtn').attr('disabled', 'disabled').html('<i class="fa fa-spanner fa-spin"></i> Сохранить'),
        data: 'addRule=1&'+form,
        type: 'POST',
        success: function (data) {
            $('#BRcompany, #BRtask, #BRzp').val(0);
            $('#BRcontr, #BRsum, #BRnazn').val('');
            $('#BAruleList').hide(200);
            getRules(data);
            $('#addRuleBtn').removeAttr('disabled').html('Сохранить');
        },
        error: function(jqXHR, exception){
            alert('Ошибка сохранения');
            $('#addRuleBtn').removeAttr('disabled').html('Сохранить');
        },
        timeout: 15000
    });
}

function getRules(blnk)
{
    $.ajax({
        data: 'getRules=1',
        type: 'POST',
        dataType: 'JSON',
        success: function (data) {
            if(data.status == 1){
                $('#tblListRule').html(data.content);
                if(blnk > 0){
                    $('#BRrule'+blnk).css("background-color","#28a745");
                    $('#BRrule'+blnk).animate({ backgroundColor: "#FFFFFF"}, 1500);
                }
            }else{
                $('#tblListRule').html('<tr><td colspan="7"><div class="alert alert-danger text-center">Список правил пуст</div></td></tr>');
            }
        },
        error: function(jqXHR, exception){
            alert('Время ожидания истекло');
        },
        timeout: 5000
    });
}

function runAutoPart(id, u){
    $.ajax({
        data: 'runRule='+id+'&u='+u,
        type: 'POST',
        success: function (data) {
            if(data == 1){
                $('#btnR'+id+u).attr('disabled', 'disabled').html('<span class="fa fa-spin fa-spinner"></span>');
            }
        },
        error: function(jqXHR, exception){
            alert('Время ожидания истекло');
            $('#btnR'+u+id).removeAttr('disabled');
        },
        timeout: 5000
    });
}

function showRule(id, u){
    $.ajax({
        beforeSent: $('#btnR'+u+id).attr('disabled', 'disabled'),
        data: 'showRule='+id+'&u='+u,
        type: 'POST',
        dataType: 'JSON',
        success: function (data) {
            $('#btnR'+u+id).removeAttr('disabled');
            $('#MRIDUser').html(data.user);
            $('#MRIDTask').html(data.task);
            $('#ModalRuleIDBody').html(data.table);
            $('#MRIDsaveBtn').html(data.btn);
            $('#ModalRules').modal('hide');
            $('#ModalRulesID').modal('show');
        },
        error: function(jqXHR, exception){
            alert('Время ожидания истекло');
            $('#btnR'+u+id).removeAttr('disabled');
        },
        timeout: 15000
    });
}

function delAutoPart(id, u)
{
    if(id >= 0 && confirm('Удалить правило?')){
        $.ajax({
            beforeSent: $('#BRbtnDelRule'+u+id).html('<i class="fa fa-spinner fa-spin"></i>').attr('disabled', 'disabled'),
            data: 'delRule='+id+'&u='+u,
            type: 'POST',
            success: function (data) {
                if(data == 1){
                    $('#BRrule'+u+id).hide();
                }
            },
            error: function(jqXHR, exception){
                $('#BRbtnDelRule'+u+id).html('<i class="fa-trash"></i>').removeAttr('disabled');
                alert('Время ожидания истекло');
            },
            timeout: 15000
    });
    }
}

function loadInfo()
{
    $.ajax({
        beforeSent: $('#contentTable').html('<tr id="strLoading"><td colspan="12" style="text-align: center;"><img src="pub/img/loading.gif"> <strong>Загрузка фильтров...</strong></strong></td></tr>'),
        data: 'loadInfo=1',
        type: 'POST',
        dataType: 'JSON',
        success: function (data) {
            $('#accTableBody').html(data.account);
            $('#f_year').html(data.year);
            $('#f_month').html(data.month);
            $('#f_task').html(data.task);
            $('#f_deal').html(data.deal);
            $('#f_company').html(data.company);
            $('#BRcompany').html(data.acc);
            $('#PFpartList').html(data.taskList);
            $('#PFrentList').html(data.rentList);
            $('#PFcomandList').html(data.comandList);
            $('#PFpartList2').html(data.taskList2);
            $('#BRtask').html(data.taskList3);
            $('#BRzp').html(data.zp);
            $('[name=nalPayZP]').html(data.zp);
            $('[name=nalPayUser]').html(data.employee);
            $('#nalPayTaskList').html(data.taskList4);
            $('#nalPayComandList').html(data.comandList4);
            $('[name=nalPayInvoice]').html(data.invoice);
            $('#ModalFilterFind').show();
            getInfo();
        },
        error: function(jqXHR, exception){
            alert('Ошибка загрузки фильтров');
        }
    });
}

function saveNalPay()
{
    var type = $('[name=nalPayType]:checked').val();
    var sum  = $('[name=nalPaySum]').val();
    var comment = $('[name=nalPayComment]').val();
    if(type == 'task'){
        var task = $('[name=nalPayTask]').val();
    }else{
        if(type == 'zp'){
            var task = $('[name=nalPayZP]').val();
        }else{
            if(type == 'user'){
                var task = $('[name=nalPayUser]').val();
            }else{
                if(type == 'invoice'){
                    var task = $('[name=nalPayInvoice]').val();
                }else{
                    if(type == 'comand'){
                        var task = $('[name=nalPayComand]').val();
                    }
                }
            }
        }
    }
    
    if(task > 0 && sum != '' && sum != 0){
        var typesum = $('[name=nalTypeSum]:checked').val();
        $.ajax({
            beforeSent: $('#btnSaveNalPay').prop('disabled', true),
            data: 'saveNalPay=1&type='+type+'&sum='+sum+'&task='+task+'&comment='+comment+'&typeSum='+typesum,
            type: 'POST',
            dataType: 'JSON',
            success: function (data) {
                if(data.status == 1)
                {
                    $('[name=nalPaySum]').val('');
                    $('[name=nalPayTask]').val('');
                    $('[name=nalPayComand]').val('');
                    $('[name=nalPayZP]').val('');
                    $('[name=nalPayInvoice]').val('');
                    $('[name=nalPayComment]').val('');
                    $('#btnSaveNalPay').prop('disabled', false);
                    $('#ModalNal').modal('hide');
                    $('#btnSaveNalPay').removeClass('btn-danger').addClass('btn-success');
                    $('[name=nalPayZP]').html(data.zp1);
                    $('#PFpartListZP').html(data.zp2);
                }
                else
                {
                    $('#btnSaveNalPay').prop('disabled', false);
                    $('#btnSaveNalPay').removeClass('btn-success').addClass('btn-danger');
                    alert(data.msg);
                }
            },
            error: function(){$('#btnSaveNalPay').prop('disabled', false);$('#btnSaveNalPay').removeClass('btn-success').addClass('btn-danger');},
            timeout: 30000
        });
    }
}

function getInfo()
{
    if(load == 0)
    {
        load = 1;
        $('#ModalMorePartBtn').hide();
        $('#ModalMoreBage').html('');
        
        var iconFilter = 1;
        if($('#f_year').val() > 0 || $('#f_month').val() > 0)
            iconFilter++;
        if($('#f_date1').val().length > 0)
            iconFilter++;
        if($('#f_date2').val().length > 0)
            iconFilter++;
        if($('#f_contragent').val().length > 0 || $('#f_contragent2').val() > 0)
            iconFilter++;
        if($('#f_oper').val() > 0)
            iconFilter++;
        if($('#f_invoice').val().length > 0)
            iconFilter++;
        if($('#f_sum').val().length > 0)
            iconFilter++;
        if($('#f_company').val() > 0)
            iconFilter++;
        if($('#f_deal').val() > 0)
            iconFilter++;
        if($('#f_task').val() > 0)
            iconFilter++;
        if($('#f_comment').val().length > 0)
            iconFilter++;
        /*
        $('[name=f_part]').each(function(idx, obj){
            if($(this).is(':checked')){
                iconFilter++;
            }
        });*/
        
        if($('#accTableID').val() > 0)
            $('#ModalAccountBtn').html(1);
        else
            $('#ModalAccountBtn').html('');
        
        $('#ModalFilterBtn').html(iconFilter);
        
        if($('#f_part_r1').is(':checked'))
            $('#PFpayTypeList').val(283);
        else
            $('#PFpayTypeList').val(274);
        
        $('#appPage').val(0);
        var findPay = $('#ModalFilterForm').serialize();
        var invoice = '&invoice='+$('#accTableID').val();
        var page  = '&page='+$('#appPage').val();
        var grp  = '&group='+$('#appGroup').val();
        var param = findPay + invoice + page + grp;
        tLoad = 1;
        $('#ModalAccount, #ModalFilter').modal('hide');
        
        $.ajax({
            beforeSent: $('#contentTable').html('<tr id="strLoading"><td colspan="12" style="text-align: center;"><img src="pub/img/loading.gif"> <strong>Загрузка платежей...</strong></strong></td></tr>'),
            data: 'getInfo=1&' + param,
            type: 'POST',
            success: function (data) {
                load = 0;
                $('#strLoading').hide();
                $('#contentTable').html(data);
                funcPop();
            },
            error: function(){
                $('#contentTable').html('<tr id="strLoading"><td colspan="12" style="text-align: center;"><div class="alert alert-danger">Данные не получены</div></td></tr>');
                load = 0;
            },
            timeout: 50000
        });
    }
}

function funcPop()
{
    $.fn.popover.Constructor.Default.whiteList.table = [];
    $.fn.popover.Constructor.Default.whiteList.th = [];
    $.fn.popover.Constructor.Default.whiteList.tr = [];
    $.fn.popover.Constructor.Default.whiteList.td = [];
    $.fn.popover.Constructor.Default.whiteList.div = [];
    $.fn.popover.Constructor.Default.whiteList.tbody = [];
    $.fn.popover.Constructor.Default.whiteList.thead = [];
    
    $('.fa-question-circle-o').popover({
        trigger: 'outside-click',
        html: true,
        content: function () {
            return $('#'+this.id+'over').html();
        }
    });
}

function showPartForm(id)
{
    $('#PFpartTask').val(0);
    $('#PFpartTask2').val(0);
    $('#PFpartTask3').val(0);
    $('#PFpayFile').val('');
    $('.objList').removeClass('alert-success');
    $('#FPlayer').show();
    $('#PartForm').modal('show');
    
    if(id > 0)
    {
        var type = $('#type'+id).val();
        $('#PFci').prop('checked', false);
        $('#PFciRow').css('display', 'none');
        $.ajax({
            data: 'getPay=1&type='+ type +'&id=' + id,
            type: 'POST',
            dataType: 'JSON',
            success: function (data) {
                if(type == 274){
                    $('#PFpartSum').prop('readonly', true);
                }else{
                    $('#PFpartSum').prop('readonly', false);
                }

                $('#PFlabel').html(data.title);
                $('#PFcontainer').html(data.part);
                $('#PFpartListZP').html(data.zp);
                $('#PFcurrDebt').html(data.cd);
                $('#PFinvList').html(data.inv);
                $('#PFrentList').html(data.rent);
                $('#PFpartSum').val(data.sum);
                $('#PFpartComm').val(data.com);
                $('#PFpayId').val($('#PFcontainer').val());
                $('#FPlayer').hide();
            },
            error: function(jqXHR, textStatus, errorThrown){
                alert('Время ожидания истекло');
            },
            timeout: 20000
        });
    }
}

function removePay()
{
    var pay  = $('#PFcontainer').val();
    var type = $('#type'+pay).val();
    
    if(pay > 0)
    {
        $.ajax({
            beforeSent: $('#PFRP').attr('disabled', 'disabled'),
            data: 'removePay=' + pay + '&type=' + type,
            type: 'POST',
            success: function (data) {
                $('#MAlabel').html('Удалить платёж?');
                $('#MAbody').html(data);
                $('#MAfooter').html('<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Закрыть</button> <button id="BitAppRemBtn" class="btn btn-sm btn-danger" onclick="removePart('+ pay +')"><i class="fa fa-trash"></i> Удалить</button>');
                $('#PartForm').modal('hide');
                $('#PFRP').removeAttr('disabled');
                $('#ModalAlert').modal('show');
            },
            error: function(){
                $('#ModalAlert').modal('hide');
                $('#PartForm').modal('show');
                alert('Ошибка получения данных');
            },
            timeout: 15000
        });
    }
}

function removePart(id)
{
    if(id > 0)
    {
        $.ajax({
            beforeSent: $('#BitAppRemBtn').attr('disabled', 'disabled'),
            data: 'removePart=' + id+'&PFpayTypeList='+$('#PFpayTypeList').val(),
            type: 'POST',
            success: function (data) {
                $('#MAlabel').html('');
                $('#MAbody').html('');
                $('#BitAppRemBtn').remove();
                $('#ModalAlert').modal('hide');
                if(data == 1)
                {
                    //getQuenue();
                    getInfo();
                }
                else
                    alert('Не удалось удалить платёж');
            },
            error: function(){
                alert('Ошибка получения данных');
            },
            timeout: 15000
        });
    }
}

function getQuenue()
{
    $.ajax({
        data: 'getQuenue=1',
        type: 'POST',
        dataType: 'JSON',
        success: function (data) {
            if(data.count > 0)
            {
                $('#MQtable').html(data.data);
                $('#quenueBage').html(data.count);
                $('#quenueBtn').show();
            }
            else
            {
                $('#quenueBtn').hide();
                $('#quenueBage').html(0);
                $('#MQtable').html('');
            }
            
            if(data.rules > 0)
                $('#rulesBage').html(data.rules);
            else
                $('#rulesBage').html('');
        },
        error: function(){},
            timeout: 10000
    });
}
setInterval(getQuenue, 50000)

function resetFilter()
{
    $('#f_year, #f_month, #f_date1, #f_date2, #f_contragent, #f_contragent2, #f_invoice, #f_sum, #f_company, #f_deal, #f_task, #f_comment, #accTableID').val('');
    $('#appGroup').val(0);
    $('#f_bnal1, #f_bnal2, #f_bnal3, #f_bnal4').prop('checked', true);
    $('#f_nal1, #f_nal2, #f_nal3, #f_nal4').prop('checked', false);
    $('#f_part_r1').prop('checked', true);
    $('#f_part_r2').prop('checked', false);
    $('#f_company').removeAttr('disabled');
    $('#f_contragent').show().val('').removeAttr('disabled');
    $('#accTableBody tr').removeClass('alert-info');
    $('#btnGroup').removeClass('active');
    $('#appGroupIcon').removeClass('fa-object-group').addClass('fa-object-ungroup');
    $('#appGroup').val(0);
    $('#f_oper, #f_card').val('');
}