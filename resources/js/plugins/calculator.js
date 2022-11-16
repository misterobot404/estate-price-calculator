//Структура для хранения таблиц коэффициентов
class CoefficientsTable {

    tableName;
    //Название строк и столбцов
    rowcolNames;
    //таблица значений
    table;
    //Название параметра на который применяются корректировки данной таблицы
    pharamName;
    tableType; //1 => таблица процентная (значения 0.03)
               //0 => таблица цифровая (значения -5000)
    rowcolType; //типы: [значения в диапазоне => d, четко заданные значения => e, константная величина корректировки => c]
    // d => если в ячейке присудствуют символы [12-23, <120  и т.п.]
    // e =>  если задана строка или число в 1 экзкмпляре [3, "Учереждение" и т.п.]
    // c => когда столбец и строка равны 1 и не имеют условий Пример => Кофиициэнт на торг

    constructor(table, tableName, tableType, rowcolNames = null, pharamName = null,) {
        this.table = table;
        this.pharamName = pharamName;
        this.tableType = tableType;
        this.tableName = tableName;


        if (rowcolNames === null) {
            this.rowcolType = 'c';
            this.rowcolNames = rowcolNames;
        } else {
            //ищем число в строке
            let regex = /(\d*)$/g;
            let found = rowcolNames[0].match(regex)[0];
            if (found !== '' && rowcolNames[0].replace(regex, '') !== '') {
                this.rowcolType = 'd';
                let newrowcolNames = [];
                for (let i = 0; i < rowcolNames.length; i++) {

                    let moreRegex = /(>)/g;
                    if (rowcolNames[i].match(moreRegex)) {
                        newrowcolNames.push(Infinity);
                    } else {
                        newrowcolNames.push(rowcolNames[i].match(regex)[0]);
                    }
                }
                this.rowcolNames = newrowcolNames;
            } else {
                this.rowcolNames = rowcolNames;
                this.rowcolType = 'e';
            }

        }
    }


    //Поиск значений строгово равенства
    findIndexEquals = (value) => {
        for (let i = 0; i < this.rowcolNames.length; i++) {
            if (this.rowcolNames[i] == value) {
                return i;
            }
        }
    }
    //Поиск индекса для двумерного массива с числами
    findIndexRange = (value) => {

        for (let i = 0; i < this.rowcolNames.length; i++) {
            if (value < this.rowcolNames[i]) {
                return i;
            }
        }
        return this.rowcolNames.length - 1;
    }
    //Получение процентного значения по таблице
    getTableValue = (reference, analog) => {
        let analogIndex;
        let referenceIndex;

        switch (this.rowcolType) {

            case 'c':
                return this.table;

            case 'e':
                analogIndex = this.findIndexEquals(analog[this.pharamName]);
                referenceIndex = this.findIndexEquals(reference[this.pharamName]);
                return this.table[referenceIndex][analogIndex];

            case 'd':
                analogIndex = this.findIndexRange(analog[this.pharamName]);
                referenceIndex = this.findIndexRange(reference[this.pharamName]);
                return this.table[referenceIndex][analogIndex];

        }
    }
}

//коэффициент
class ItermediateValues {
    cPrice = 0; //Сумма с учетом коэфиициэнта
    cValue = 1;//Процент коэффициэнта
}

//Таблица значений примененных коэффициентами
class AppliedCoefficients {
    appliedC = [];
    cPrice;
    cSize;
    cWeight;
    cTax;

    //
    addCoefficient = () => {
        this.appliedC.push(new ItermediateValues());
    }

    //Расчитывает размер примененных корректирововк
    getCSize = () => {
        this.cSize = 0;
        this.appliedC.forEach((element) => {
            this.cSize += Math.abs(element.cValue);
        })
    }
}

class Apartment {
    source;//Источник объявления
    square;//Площадь квартиры кв.м
    floor;// Этаж
    maxFloor;//Кол-во этажей в доме
    metroDistance;//Расстояние до метро мин
    room;//Кол-во комнат
    floorType;
    material;//Материал дома
    squareKitchen;//Площадь кухни кв.м
    balcony;//Наличие балкона
    renovation;//Тип ремонта
    price;//Цена
    priceM;//Цена за кв.м
    cCalculation = new AppliedCoefficients();
}

//Проверка на наличие налога (20%)
let checkTax = (analogArr, average) => {
    //Разница не более чем в 20 процентов
    const diff = 0.2;
    for (let i = 0; i < analogArr.length; i++) {
        //Процентная разница в цене между аналогом и эталоном
        let diffTmp = ((analogArr[i].priceM - average) / average);
        //Сравниваем разницу с значением в 20%
        if (Math.abs(diffTmp) > diff) {
            alert("Стоимость аналога " + (i + 1) + " отличается от рыночной на " + (Math.round(diffTmp * 100)) + "%. Будет начислен налог!");
        }
        //Вывод разницы для 1 аналога
        analogArr[i].cCalculation.cTax = diffTmp;
    }
}

//Поиск ср значения цены выборки
let getAverageValue = (analogArr) => {
    let summ = -1;
    for (let i = 0; i < analogArr.length; i++) {
        summ += analogArr[i].priceM;
    }
    return summ / analogArr.length;
}
// Поиск Суммы для дисперсии выборки
let findDispersionSum = (analogArr, average) => {
    let sum = -1;
    for (let i = 0; i < analogArr.length; i++) {
        sum = Math.pow(analogArr[i].priceM - average, 2)
    }
    return sum;
}

//Проверка достаточности и достоверности подобранных аналогов
let checkReliability = (analogArr, average) => {
    //Разница не более чем в 33%
    const diff = 0.33;
    //Среднеквадратичное отклонение
    let standardDeviation = Math.sqrt((findDispersionSum(analogArr, average) / analogArr.length - 1));
    //Поиск коэффициэнта вариации
    let res = standardDeviation / average;
    console.log("Достоверность и достаточность подобранных аналогов состовляет: " + res * 100);
    if (res > diff) {
        alert("Подобраны не достоверные аналоги!");
        return false;
    }
    return true;
}

//Оценка аналога по коэффициэнтам
let findAnalogCoefficients = (reference, analog, cTables) => {

    //нач цена
    analog.cCalculation.cPrice = analog.priceM;

    //Расчет для процентных таблиц
    for (let i = 0; i < cTables.length; i++) {
        //Записываем коэффициэнт в таблицу
        //Добавляем коэффициэнт в таблицу
        analog.cCalculation.addCoefficient();
        //Если таблица процентная

        analog.cCalculation.appliedC[i].cValue = cTables[i].tableType ? cTables[i].getTableValue(reference, analog) : cTables[i].getTableValue(reference, analog) / analog.cCalculation.cPrice;

        analog.cCalculation.cPrice += analog.cCalculation.cPrice * analog.cCalculation.appliedC[i].cValue;
        //Записываем цену в таблицу для вывода
        analog.cCalculation.appliedC[i].cPrice = analog.cCalculation.cPrice;
    }
    //Расчет примененных корректирововк
    analog.cCalculation.getCSize();

}

//Поиск цены объекта оценки
let findPrice = (analogArr) => {
    let summ = 0;
    for (let i = 0; i < analogArr.length; i++) {
        summ += analogArr[i].cCalculation.cWeight * analogArr[i].cCalculation.cPrice;
    }
    return summ;
}
//Приведение характеристики этажа к табличным значениям
let getFloorType = (apartment) => {
    apartment.floorType = apartment.floor === 1 ? 'Первый этаж' : apartment.floor === apartment.maxFloor ? 'Последний этаж' : 'Средний этаж';
}
//поиск разницы между макисимальтным и минимальным значениями цены аналогов
let findMinMaxPriceDiff = (analogArr) => {
    //Поиск максимума
    let max = analogArr[0].cCalculation.cPrice;
    let min = analogArr[0].cCalculation.cPrice;
    for (let i = 0; i < analogArr.length; i++) {
        if (analogArr[i].cCalculation.cPrice > max)
            max = analogArr[i].cCalculation.cPrice;
        if (analogArr[i].cCalculation.cPrice < min)
            min = analogArr[i].cCalculation.cPrice;
    }

    return (max / min - 1) * 100;
}

//осуществляет расчет весовой характеристики объектов аналогов
let findWeight = (analogArr) =>{

    //По формуле из их примера
    //Ищем делитель
    let del = 0;
    for (let i = 0; i < analogArr.length; i++) {
        if(analogArr[i].cCalculation.cSize!=0)
            del += 1 / analogArr[i].cCalculation.cSize;
        else del+=1;
    }
    if(del === 0)
        del = 1;
    //Выполняем поочередное деление размера примененных корректирововк на рассчитаный выше делитель
    for (let i = 0; i < analogArr.length; i++) {
        if(analogArr[i].cCalculation.cSize!=0)
            analogArr[i].cCalculation.cWeight = (1 / analogArr[i].cCalculation.cSize) / del;
        else analogArr[i].cCalculation.cWeight =1/del;
    }
}

function parseRenovation(renovation) {
    switch (renovation) {
        case "Без отделки":
        case "Требуется ремонт":
            return "Без отделки";
        case "Дизайнерский ремонт":
        case "Муниципальный ремонт":
        case "Косметический ремонт":
            return "Эконом";
        case "Евро ремонт":
            return "Улучшеный";
        default:
            return "Без отделки";
    }

}

//Функция для поиска стоимости
let findEtalonPrice = (reference, analogArr, tables) => {
    let Reference = new Apartment();
    Reference.source = "-";
    Reference.square = Number(reference.ПлощадьКвартиры);
    Reference.floor = Number(reference.ЭтажРасположения);
    Reference.maxFloor = Number(reference.ЭтажностьДома);
    Reference.metroDistance = Number(reference.МетроМин);
    Reference.room = Number(reference.КоличествоКомнат);
    Reference.material = reference.МатериалСтен;
    Reference.squareKitchen = Number(reference.ПлощадьКухни);
    Reference.balcony = reference.НаличиеБалконаЛоджии == 1;
    Reference.renovation = parseRenovation(reference.Состояние);
    Reference.price = 0;
    Reference.priceM = 0;


    let class_analog_arr = [];
    // Преобразуем входные объекты в классы
    analogArr.forEach((el) => {
        let Analog = new Apartment();
        Analog.source = "Авито";
        Analog.square = Number(el.ПлощадьКвартиры);
        Analog.floor = Number(el.ЭтажРасположения);
        Analog.maxFloor = Number(el.ЭтажностьДома);
        Analog.metroDistance = Number(el.МетроМин);
        Analog.room = Number(el.КоличествоКомнат);
        Analog.material = el.МатериалСтен;
        Analog.squareKitchen = Number(el.ПлощадьКухни);
        Analog.balcony = el.НаличиеБалконаЛоджии == 1;
        Analog.renovation = parseRenovation(el.Состояние);
        Analog.price = Number(el.Стоимость);
        Analog.priceM = Analog.price / Analog.square;

        class_analog_arr.push(Analog)
    })

    //Преобразование таблиц из json
    let cNames = tables.map(el => el.Название);
    let cTables = tables.map(el => JSON.parse(el.Данные));
    for(let i=0;i<cTables.length; i++){
        cTables[i] = new CoefficientsTable(cTables[i].table,cNames[i], cTables[i].isPercent, cTables[i].rowcolNames,cTables[i].pharamName);
    }

    //Приведение к типу этажа используемому при оценке
    for (let i = 0; i < class_analog_arr.length; i++) {
        getFloorType(class_analog_arr[i]);
    }
    getFloorType(Reference);

    //Расчет корректировок для аналогов
    for (let i = 0; i < class_analog_arr.length; i++) {
        findAnalogCoefficients(Reference, class_analog_arr[i], cTables);
    }

    //Расчет веса аналогов
    findWeight(class_analog_arr);

    //Расчет рыночной стоимости 1 кв.м
    Reference.priceM = findPrice(class_analog_arr);
    //Расчет Рыночной стоимости
    Reference.price = Reference.priceM * Reference.square;

    //среднее значение цены выборки
    const average = getAverageValue(class_analog_arr);

    ///ИЗ ДОП ЛИТЕРАТУРЫ
    //Проверка на наличие налога (20%)
    checkTax(class_analog_arr, average);

    // Результат функции. Тело
    let res = {
        analog_changes_table: [],
        coef_meta_table: [],
        coef_diff: null,
        price_m: null,
        price: null,
        price_diff: null,
    };

    // analog_changes_table
    for (let i = 0; i < cTables.length; i++) {
        res.analog_changes_table.push({
            name: cTables[i].tableName,
            values: class_analog_arr.map(el => (el.cCalculation.appliedC[i].cValue * 100).toFixed(2) + '% (' + Math.floor(el.cCalculation.appliedC[i].cPrice).toLocaleString('ru') + ' ₽)')
        })
    }
    // coef_meta_table
    res.coef_meta_table.push(
        {
            name: 'Размер применённых корректировок',
            values: class_analog_arr.map(el => (el.cCalculation.cSize * 100).toFixed(2))
        },
        {
            name: 'Вес аналогов',
            values: class_analog_arr.map(el => el.cCalculation.cWeight.toFixed(2))
        },
    )
    // coef_diff
    res.coef_diff = findMinMaxPriceDiff(class_analog_arr).toFixed(2);
    // price_m
    res.price_m = Math.floor(Reference.priceM);
    // price
    res.price = Math.floor(Reference.price);
    // price_diff
    res.price_diff = class_analog_arr.map(el => (el.cCalculation.cTax * 100).toFixed(2))

    //TODO тут еще 1 проверка не потеряйте!!!!
    ///ИЗ ДОП ЛИТЕРАТУРЫ
    //Проверка подобранных аналогов на достоверность (33%)
    checkReliability(class_analog_arr, average);

    return res;
}

export {findEtalonPrice}
