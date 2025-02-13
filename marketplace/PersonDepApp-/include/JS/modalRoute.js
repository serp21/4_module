var modalWindow = new bootstrap.Modal(document.getElementById('modalBlock'), { keyboard: false });
var modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirm'), { keyboard: false });
var modalNotify = new bootstrap.Modal(document.getElementById('modalNotify'), { keyboard: false });
var modalError = new bootstrap.Modal(document.getElementById('modalError'), { keyboard: false });

const POSITION = 'UF_USR_ENDPOINT_WORKPOSITION_ID';
const filter = new Filter();

function openFilter() {
    document.getElementById("modalSearchUser").style.display = "block";
}

// Закрытие окна фильтров поиска при нажатии вне области окна и строки поиска
window.addEventListener('click', function(e){
    if (!modalSearchUser.contains(e.target) && !inputName.contains(e.target)){
        modalSearchUser.style.display = "none";
        filter.get();
    }
});

// Загрузка новой страницы
function pageSelect(page) {
    filter.set(page);
}

// Загрузка при смене количества на странице
function inPageSelect() {
    filter.set();
}

function filterSet() {
    filter.set();
}

function filterUnSet() {
    filter.unset();
}

function openModal(e) {
    type = e.attributes.data.value;
    context = 'div#modalBlockBody';

    param = { 'MODAL' : { 'TYPE' : type } };

    if (type != 'table' && type != 'settings' && type != 'newUser' && type != 'newElem') {
        param['MODAL']['ITEM']['ELEMENT'] = e.parentElement.attributes.data.value;
        param['MODAL']['ITEM']['ID'] = parseInt(e.parentElement.parentElement.children[0].id);
    }
    
    sendAjax('GET', param, context);
}

function modalSuccess(e, confirm = false) {
    let param = {};
    const type = e.attributes.data.value;
    if (typeof e.attributes['data-id'] != 'undefined') {
        param['ID'] = e.attributes['data-id'].value;
    }

    switch(type) {
        case 'table':
            checkbox = $( "input[type^='checkbox']" );
            for (let elem of checkbox) {
                param[elem.id] = elem.checked;
            }

            if (Object.keys(query).length > 0) {
                sendAjax('POST', { 'MODAL' : { 'TYPE' : 'table', 'ITEM' : query } });
            } else {
                console.log(param, query);
            }
            break;

        case 'settings':
            break;

        case 'newUser':
            input = $( ".user-input" );
            for (let data of input) {
                if (data.id == 'WORK_POSITION') {
                    param['WORK_POSITION'] = data.options[data.selectedIndex].innerText;;
                    param['UF_USR_ENDPOINT_WORKPOSITION_ID'] = data.value;
                } else {
                    param[data.id] = data.value;
                }
            }

            if (Object.keys(query).length > 0) {
                sendAjax('POST', { 'MODAL' : { 'TYPE' : 'user', 'METHOD' : type, 'ITEM' : param } });
            } else {
                console.log(param, query);
            }
            break;

        case 'newElem':
            input = $( ".elem-input" );
            for (let data of input) {
                param[data.id] = data.value;
            }

            if (Object.keys(query).length > 0) {
                sendAjax('POST', { 'MODAL' : { 'TYPE' : 'list', 'METHOD' : type, 'ITEM' : param } });
            } else {
                console.log(param, query);
            }
            break;
            
        default:
            tempType = type.toLowerCase();
            if (tempType.includes('user')) {
                query = item.get();

                if (Object.keys(query).length > 0) {
                    sendAjax('POST', { 'MODAL' : { 'TYPE' : 'user', 'METHOD' : type, 'ITEM' : query } });
                } else {
                    console.log(query);
                }
            } else if (tempType.includes('elem')) {
                query = item.get();

                if (Object.keys(query).length > 0) {
                    sendAjax('POST', { 'MODAL' : { 'TYPE' : 'list', 'METHOD' : type, 'ITEM' : query } });
                } else {
                    console.log(query);
                }
            } else {
                modalWindow.hide();
                setTimeout(() => {
                    modalTitle.innerHTML = '';
                    modalBody.innerHTML = '';
                    modalFooter.innerHTML = '';
                }, 500);
            }
            break;
    }
}

function modalCancel() {
    modalWindow.hide();
    setTimeout(() => {
        modalBlockBody.innerHTML = '';
    }, 500);
}

function cancelError() {
    modalError.hide();
    setTimeout(() => {
        errorText.innerHTML = '';
    }, 500);
}
