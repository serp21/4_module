<div class="right-popup filter-popup" data-type="filter" data-popup>
    <div class="right-popup__container filter-popup__container">
        <div class="right-popup__top">
            <h2 class="right-popup__title">Фильтры</h2>
            <button class="right-popup__close" data-close-popup>
                <img src="./assets/img/close.svg" alt="">
            </button>
        </div>
        <form action="filter" method="POST" class="right-popup__main">
            <ul class="filter-popup__list right-popup__list">
                <li class="filter-popup__item">
                    <span>Подразделение</span>
                    <label>
                        <select
                            class="js-select2 filter-popup__select"
                            name="filter-group-name"
                            id="filter-group-name"
                            data-select="group">

                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </label>
                </li>
                <li class="filter-popup__item">
                    <span>Оклад</span>
                    <label>
                        <select
                            class="js-select2 filter-popup__select"
                            name="filter-salary-type"
                            id="filter-salary-type"
                            data-select="salary-type">

                            <option value="1" selected>Больше</option>
                            <option value="1">Меньше</option>
                        </select>
                    </label>

                    <label class="filter-popup__label-input">
                        <input name="filter-salary-number" class="filter-popup__input" type="number">
                    </label>
                </li>
                <li class="filter-popup__item">
                    <span>Оклад</span>
                    <label>
                        <select
                            class="js-select2 filter-popup__select"
                            name="filter-employee-type"
                            id="filter-employee-type"
                            data-select="employee-type">

                            <option value="1" selected>Больше</option>
                            <option value="1">Меньше</option>
                        </select>
                    </label>

                    <label class="filter-popup__label-input">
                        <input name="filter-employee-number" class="filter-popup__input" type="number">
                    </label>
                </li>
            </ul>

            <button class="right-popup__btn filter-popup__btn" type="submit">
                Применить
            </button>
        </form>
    </div>

</div>