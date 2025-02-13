// === Получение списка полей для Списка ====

let params = new URLSearchParams(window.location.search);

$("[data-select='custom-list-select']")
  .off("change")
  .on("change", (jqEvent) => {
    jqEvent.preventDefault();

    let listId = jqEvent.target.value;

    // controller/field/list

    $.post(
      "?" + params.toString(),
      {
        controller: "list",
        method: "getFields",
        data: {
          listId: listId,
        },
      },
      function (response) {
        let fields = JSON.parse(response);

        console.log(fields);

        let options = `<option value selected disabled>Выберите поле</option>`;

        for (let fieldKey in fields) {
          let fieldData = fields[fieldKey];
          let fieldId = fieldData.FIELD_ID;
          let fieldName = fieldData.NAME;

          let option = `
            <option value=${fieldId}>${fieldName}</option>
          `;

          options += option;
        }

        const selectFieldsArr = document.querySelectorAll(
          "[data-field-select]"
        );

        selectFieldsArr.forEach((fieldSelect) => {
          fieldSelect.innerHTML = options;
        });
      }
    ).fail(function (error) {
      console.log(error.responseText);
    });
  });

$("[data-type='first-settings']")
  .off("submit")
  .on("submit", (jqEvent) => {
    jqEvent.preventDefault();

    let formData = $(jqEvent.target).serializeArray();
    let data = {
      "custom-list-check": "off",
      "custom-list-select": null,
      "custom-list-name": null,
      "custom-list-group": null,
      "custom-list-salary": null,
    };

    formData.forEach((element) => {
      data[element.name] = element.value;
    });

    $.post(
      "?" + params.toString(),
      {
        controller: "list",
        method: "setIndexList",
        data: {
          listId: data["custom-list-select"],
          customList: data["custom-list-check"],
          fieldsInfo: {
            NAME_FIELD_ID: data["custom-list-name"],
            GROUP_FIELD_ID: data["custom-list-group"],
            SALARY_FIELD_ID: data["custom-list-salary"],
          },
        },
      },
      function (response) {
        $(jqEvent.target).removeClass('open');
        let form = document.querySelector("[data-type='first-settings']");

        form.reset();
        $("[data-type='first-settings']").find("select").select2('val', 0);
      }
    ).fail(function (error) {
      alert(error.responseText);
    });
  });

$("[data-checkbox='is-custom-list']")
  .off("change")
  .on("change", (jqEvent) => {
    //
    if (!$(jqEvent.target).is(":checked")) {
      let form = document.querySelector("[data-type='first-settings']");
      
      $("[data-type='first-settings']").find("select").select2('val', 0);
    }

    
  });


  $("[data-row-btn='edit']")
  .off("click")
  .on("click", (jqEvent) => {
    //

    let target = jqEvent.currentTarget;

    // console.log(target.classList);

    while(!target.classList.contains('table__row')) {
      console.log(target);
      target = target.parentElement;
    }

    let staffId = target.getAttribute('data-eid');

    console.log(staffId);

    // Положить идентификатор в локал сторадж
    // Открыть окно редактирования должности
    

    
  });
