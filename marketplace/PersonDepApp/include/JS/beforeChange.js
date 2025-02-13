class Item {
    #element;

    constructor() {
        this.#element = {};
    }

    set(param) {
        this.#element = {};

        for (const key of Object.keys(param)) {
            let i = '#'+key;
            let input = $(i)[0];

            if (key == 'ELEMENT' || key == 'ID') {
                this.#element[key] = param[key];
            }

            if (typeof input != 'undefined') {
                this.#element[key] = param[key];

                if (input.value == '') {
                    if (key == 'PERSONAL_BIRTHDAY') {
                        input.value = param[key].split('T')[0];
                        this.#element[key] = param[key].split('T')[0];
                    } else if (key == 'PERSONAL_MOBILE') {
                        let phone = param[key];
                        phone = phone.replaceAll(' ', '');

                        if (phone.length > 0) {
                            const digits = phone.replace(/\D/g, '');
                            if (phone[0] == '+') {
                                input.value = `+${digits.slice(0, 1)} (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7, 9)}-${digits.slice(9, 11)}`;
                            } else {
                                let first = parseInt(digits.slice(0, 1)) - 1;
                                input.value = `+${first} (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7, 9)}-${digits.slice(9, 11)}`;
                            }
                        }
                    } else {
                        input.value = param[key];
                    }
                }
            }
        }
    }

    get() {
        let ajax = {};

        if (typeof this.#element['ID'] == 'undefined' || typeof this.#element['ELEMENT'] == 'undefined') {
            alert("ошибка");
        } else {
            for (const key of Object.keys(this.#element)) {
                if (key == 'ELEMENT' || key == 'ID') {
                    ajax[key] = this.#element[key];
                } else {
                    const inputUser = $( '#' + key + '.user-input' )[0];
                    const inputElem = $( '#' + key + '.elem-input' )[0];
                    let input = '';

                    if (typeof inputUser != 'undefined') {
                        input = inputUser.value;
                    } else if (typeof inputElem != 'undefined') {
                        input = inputElem.value;
                    } else {
                        continue;
                    }

                    if (this.#element[key] != input) {
                        ajax[key] = input;
                    }
                }
            }
        }

        if (Object.keys(ajax).length > 2) {
            this.#element = {};    
            return ajax;
        } else {
            return {};
        }
    }
}
