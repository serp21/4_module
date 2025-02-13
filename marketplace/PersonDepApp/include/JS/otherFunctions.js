var eventCalllback = function(e) {
  var el = e.target,
    clearVal = el.dataset.phoneClear,
    pattern = el.dataset.phonePattern,
    matrix_def = "+7 (___) ___-__-__",
    matrix = pattern ? pattern : matrix_def,
    i = 0,
    def = matrix.replace(/\D/g, ""),
    val = e.target.value.replace(/\D/g, "");
  if (clearVal !== 'false' && e.type === 'blur') {
    if (val.length < matrix.match(/([\_\d])/g).length) {
      e.target.value = '';
      return;
    }
  }
  if (def.length >= val.length) val = def;
  e.target.value = matrix.replace(/./g, function(a) {
    return /[_\d]/.test(a) && i < val.length ? val.charAt(i++) : i >= val.length ? "" : a
  });
}

function posSelect(e) {
  let depId = e.value;

  if (e.id == 'uf_department') {
    work_position.value = 0;

    for (let option of work_position.options) {
      if (option.attributes.data.value == depId) {
        option.style.display = '';
      } else {
        option.style.display = 'none';
      }
    }
  } else if (e.id == 'UF_DEPARTMENT') {
    WORK_POSITION.value = 0;

    for (let option of WORK_POSITION.options) {
      if (option.attributes.data.value == depId) {
        option.style.display = '';
      } else {
        option.style.display = 'none';
      }
    }
  }
}