
<!-- 
 

При добавлении/выборе подразделения необходимо подгружать доступные пользоваетлю 
в данный момент подразделения, в которых он руководитель и те, которые еще не добавлены им.
После того как он нажал кнопку добавить, если:
    ДА
    у подразделения есть родительское подразделение, 
    то уточнить нужно ли выводить должности родительского подразделения и если да, то проверить, 
    если родительское у родителя и повторить вопрос, после вывести
    ====
    НЕТ
    то проверить есть ли должности в штатном расписании у подразделения, которое выбрали 
        и если есть то вывести их,
        если нет, то предложить добавить, после добавления вывести список должностей для подразделения

    


-->

<form action="#" method="POST" class="add-popup" data-type="add-group" data-popup>
    <div class="add-popup__container">
        <div class="add-popup__top">
            <h3 class="add-popup__title">Добавить подразделение</h3>
            <button class="add-popup__close" data-close-popup type="button">
                <img src="./assets/img/close.svg" alt="">
            </button>
        </div>

        <div class="add-popup__main">
            <ul class="add-popup__list">
                <li class="add-popup__item">
                    <span>Подразделение</span>
                    <label>
                        <select
                            class="js-select2 add-popup__select"
                            name="group-parent"
                            id="group-parent"
                            data-select="group-parent">

                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </label>
                </li>
            </ul>

            <button class="add-popup__btn" data-add="group">
                Добавить
            </button>
        </div>
    </div>
</form>