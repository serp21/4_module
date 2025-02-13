<form action="#" method="POST" class="add-popup" data-type="add-position" data-popup>
    <div  class="add-popup__container">
        <div class="add-popup__top">
            <h3 class="add-popup__title">Добавить должность</h3>
            <button class="add-popup__close" data-close-popup type="button">
                <img src="./assets/img/close.svg" alt="">
            </button>
        </div>

        <div class="add-popup__main">
            <ul class="add-popup__list">
                <li class="add-popup__item">
                    <span>Название</span>
                    <label class="add-popup__label-input">
                        <input name="position-name" type="text">
                    </label>
                </li>
                <li class="add-popup__item">
                    <span>Подразделение</span>
                    <label>
                        <select
                            class="js-select2 add-popup__select"
                            name="position-group"
                            id="position-group"
                            data-select="position-group">

                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </label>
                </li>
                <li class="add-popup__item">
                    <span>Оклад</span>
                    <label class="add-popup__label-input">
                        <input name="position-salary" type="number">
                    </label>
                </li>
            </ul>

            <button class="add-popup__btn" type="submit">
                Создать
            </button>
        </div>
    </div>
</form>