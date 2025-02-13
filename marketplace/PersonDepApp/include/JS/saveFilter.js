class Filter {
    #inputName;
    #FULL_NAME;
    #PERSONAL_BIRTHDAY;
    #PERSONAL_GENDER;
    #PERSONAL_MOBILE;
    #EMAIL;
    #DATE_REGISTER;
    #WORK_POSITION;
    #UF_DEPARTMENT;
    #ACTIVE;
    #USER_TYPE;
    #send;

    constructor() {
        this.#inputName = '';
        this.#FULL_NAME = '';
        this.#PERSONAL_BIRTHDAY = '';
        this.#PERSONAL_GENDER = '';
        this.#PERSONAL_MOBILE = '';
        this.#EMAIL = '';
        this.#DATE_REGISTER = '';
        this.#WORK_POSITION = '';
        this.#UF_DEPARTMENT = 0;
        this.#ACTIVE = 0;
        this.#USER_TYPE = 0;
        this.#send = false;
    }

    set(selected = 1) {
        let param = {};
    
        if (inputName.value != '') {
            param['FULL'] = inputName.value;
            this.#inputName = inputName.value;

            this.#send = true;
        }

        if (full_name.value != '') {
            param['FULL_NAME'] = full_name.value;
            this.#FULL_NAME = full_name.value;

            this.#send = true;
        }

        if (personal_birthday.value != '') {
            param['PERSONAL_BIRTHDAY'] = personal_birthday.value;
            this.#PERSONAL_BIRTHDAY = personal_birthday.value;

            this.#send = true;
        }
    
        if (personal_gender.value == 'M' || personal_gender.value == 'F') {
            param['PERSONAL_GENDER'] = personal_gender.value;
            this.#PERSONAL_GENDER = personal_gender.value;

            this.#send = true;
        }

        if (personal_mobile.value != '') {
            param['PERSONAL_MOBILE'] = personal_mobile.value;
            this.#PERSONAL_MOBILE = personal_mobile.value;

            this.#send = true;
        }

        if (email.value != '') {
            param['EMAIL'] = email.value;
            this.#EMAIL = email.value;

            this.#send = true;
        }

        if (date_register.value != '') {
            param['DATE_REGISTER'] = date_register.value;
            this.#DATE_REGISTER = date_register.value;

            this.#send = true;
        }

        if (work_position.value > 0) {
            param['WORK_POSITION'] = work_position.value;
            this.#WORK_POSITION = work_position.value;

            this.#send = true;
        }

        if (uf_department.value > 0) {
            param['UF_DEPARTMENT'] = parseInt(uf_department.value);
            this.#UF_DEPARTMENT = parseInt(uf_department.value);

            this.#send = true;
        }

        let active = this.#ACTIVE;
        if (active_1.checked == true) {
            param['ACTIVE'] = true;
            this.#ACTIVE = 1;
        } else if (active_2.checked == true) {
            param['ACTIVE'] = false;
            this.#ACTIVE = 2;
        } else {
            this.#ACTIVE = 3;
        }

        if (active != this.#ACTIVE) {
            this.#send = true;
        }

        let type = this.#USER_TYPE;
        if (user_type_1.checked == true) {
            param['USER_TYPE'] = 'employee';
            this.#USER_TYPE = 1;
        } else if (user_type_2.checked == true) {
            param['USER_TYPE'] = 'extranet';
            this.#USER_TYPE = 2;
        } else {
            this.#USER_TYPE = 3;
        }

        if (type != this.#USER_TYPE) {
            this.#send = true;
        }

        modalSearchUser.style.display = "none";

        if (this.#send == true) {
            this.#send = false;
            let onPage = parseInt(selectUserListCount.value);
            sendAjax('GET', { 'PAGE' : { 'SELECTED_PAGE' : selected, 'ON_PAGE' : onPage, 'FILTER' : param } }, '#main');
        }
    }

    unset() {
        this.clear();
        this.#send = true;
        this.set();
    }

    get() {
        if (this.#inputName != '') {
            inputName.value = this.#inputName;
        }
    
        if (this.#FULL_NAME != '') {
            full_name.value = this.#FULL_NAME;
        }
    
        if (this.#PERSONAL_BIRTHDAY != '') {
            personal_birthday.value = this.#PERSONAL_BIRTHDAY;
        }
    
        if (this.#PERSONAL_GENDER != '') {
            personal_gender.value = this.#PERSONAL_GENDER;
        }
    
        if (this.#PERSONAL_MOBILE != '') {
            personal_mobile.value = this.#PERSONAL_MOBILE;
        }
    
        if (this.#EMAIL != '') {
            email.value = this.#EMAIL;
        }
    
        if (this.#DATE_REGISTER != '') {
            date_register.value = this.#DATE_REGISTER;
        }
    
        if (this.#WORK_POSITION > 0) {
            work_position.value = this.#WORK_POSITION;
        }
    
        if (this.#UF_DEPARTMENT > 0) {
            uf_department.value = this.#UF_DEPARTMENT;
        }

        switch (this.#ACTIVE) {
            case 1:
                active_1.checked == true;    
                break;
                
            case 2:
                active_2.checked == true;    
                break;
                
            case 3:
                active_3.checked == true;    
                break;
        }

        switch (this.#USER_TYPE) {
            case 1:
                user_type_1.checked == true;    
                break;
                
            case 2:
                user_type_2.checked == true;    
                break;
                
            case 3:
                user_type_3.checked == true;    
                break;
        }
    }

    clear() {
        this.#inputName = '';
        this.#FULL_NAME = '';
        this.#PERSONAL_BIRTHDAY = '';
        this.#PERSONAL_GENDER = '';
        this.#PERSONAL_MOBILE = '';
        this.#EMAIL = '';
        this.#DATE_REGISTER = '';
        this.#WORK_POSITION = '';
        this.#UF_DEPARTMENT = 0;
        this.#ACTIVE = 1;
        this.#USER_TYPE = 1;

        inputName.value = this.#inputName;
        full_name.value = this.#FULL_NAME;
        personal_birthday.value = this.#PERSONAL_BIRTHDAY;
        personal_gender.value = this.#PERSONAL_GENDER;
        personal_mobile.value = this.#PERSONAL_MOBILE;
        email.value = this.#EMAIL;
        date_register.value = this.#DATE_REGISTER;
        work_position.value = this.#WORK_POSITION;
        uf_department.value = this.#UF_DEPARTMENT;
        active_1.checked = true;    
        user_type_1.checked = true;
    }
}
