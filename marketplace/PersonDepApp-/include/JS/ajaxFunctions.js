var sended = true;
var exceptionsCount = 0;
var data;
const item = new Item();
const repit = 0;

// Отображение экрана загрузки
function preloader(bool) {
    if (bool == true) {
        sended = false; // Запрет отправки ajax
        document.getElementById("preloader").style.display = "block";
    } else {
        sended = true; // Разрешение отправки ajax
        document.getElementById("preloader").style.display = "none";
    }
}

// Отправка запроса ajax на сервер
function sendAjax(methodName, parameters, element = null, timeoutSet = 20000) {
    if (sended == true) {
        let blockName = Object.keys(parameters);

        if (blockName.length > 0 && (methodName == 'POST' || methodName == 'GET')) {
            $.ajax({
                method: methodName, 
                data: parameters,
                timeout: timeoutSet,
                beforeSend: preloader(true)
            }).done(function(result) {
                console.log(result);
                try {
                    if (typeof result == 'string') {
                        if (result.includes('<info>') && result.includes('</info>')) {
                            info = result.split('<info>');
                            result = info[0];
                            info[1] = info[1].replace('</info>', '');

                            data = $.parseJSON(info[1]);
                        }

                        if (methodName == 'GET') {
                            if (Object.keys( $( element ) ).length > 0) {
                                $( element )[0].innerHTML = result;

                                if (typeof PERSONAL_MOBILE != 'undefined') {
                                    for (let ev of ['input', 'blur', 'focus']) {
                                        PERSONAL_MOBILE.addEventListener(ev, eventCalllback);
                                    }
                                }

                                if (data != null && Object.keys(data).length > 0) {
                                    if (data[0] != null && typeof data[0]['ID'] != 'undefined') {
                                        item.set(data[0]);
                                    } else if (data != null && typeof data['ID'] != 'undefined') {
                                        item.set(data);
                                    }
                                    
                                    data = null;
                                }

                                modalWindow.show();
                            } else {
                                errorText.innerHTML = result;
                                modalError.show();
                            }
                        } else if (methodName == 'POST') {}
                    } else {
                        console.log(result);
                        errorText.innerHTML = $.parseJSON(result)['error'];
                        modalError.show();
                    }
                } catch (error) {
                    console.log(error);
                } finally {
                    preloader(false);
                }
            }).fail(function(error) {
                exceptionsCount++;

                if (exceptionsCount < repit) {
                    sended = true;
                    setTimeout(() => {
                        sendAjax(methodName, parameters, timeoutSet);
                    }, 1000);
                } else {
                    preloader(false);

                    if (typeof error == 'object' && typeof error.responseText != 'undefined') {
                        errorText.innerHTML = error.responseText;
                        modalError.show();
                    } else if (typeof error == 'string' && error.includes('error:')) {
                        errorText.innerHTML = $.parseJSON(error)['error'];
                        modalError.show();
                    } else {
                        errorText.innerHTML = error;
                        modalError.show();
                    }

                    exceptionsCount = 0;
                }
            })
        } else if (exceptionSwitch == true) {
            preloader(false);
            errorText.innerHTML = 'Ошибка клиента.<br><br>Передача неверных параметров на сервер или недопустимый запрос.<br><br>';
            modalError.show();
        }
    } else {
        setTimeout(() => {
            sendAjax(methodName, parameters, timeoutSet);
        }, 200);
    }
}
