<template>
    <div v-if="is_done" class="q-pa-lg">
        <div class="flex items-center">
            <q-btn flat round icon="arrow_back" size="sm" class="text-grey-8" :to="'/calculator/pools/'+object.Пул+'/'+object.id"/>
            <div class="q-ml-xs">Вернуться к карте</div>
        </div>

        <div style="margin: 16px 34px">
            <div class="text-bold text-h8 q-mb-sm">Итоговый расчёт стоимости эталона</div>
            <q-card>
                <q-card-section class="q-pa-lg">
                    <div class="flex">
                        <div class="q-mr-lg">
                            <div>
                                <span class="text-bold">Рыночная стоимость:</span> {{ res_calc.price_m.toLocaleString('ru') }} ₽
                            </div>
                            <div class="text-caption">За кв.м. (с НДС)</div>
                        </div>
                        <div>
                            <div>
                                <span class="text-bold">Рыночная стоимость:</span> {{ res_calc.price.toLocaleString('ru') }} ₽
                            </div>
                            <div class="text-caption">(с НДС)</div>
                        </div>
                    </div>
                    <div class="q-mt-md">
                        <q-btn label="Рассчитать для всех объектов" color="primary" unelevated @click="revealResults()"/>
                        <q-btn label="Выгрузить расчёт эталона .csv" no-caps class="q-ml-sm" color="primary" flat @click="exportToCSV()"/>
                    </div>
                </q-card-section>
            </q-card>

            <div class="text-bold text-h8 q-mb-sm q-mt-lg">Объекты сравнения</div>
            <q-table
                :rows="extend_objects_table.rows"
                :columns="extend_objects_table.columns"
                row-key="name"
                wrap-cells
                :rows-per-page-options="[0]"
                hide-bottom
                :card-style="{ padding: '14px 20px' }"
            />

            <div class="text-bold text-h8 q-mb-sm q-mt-lg">Корректирующие коэффициенты аналогов</div>
            <q-table
                :rows="coef_table.rows"
                :columns="coef_table.columns"
                row-key="Название"
                :rows-per-page-options="[0]"
                hide-bottom
                :card-style="{ padding: '14px 20px' }"
            >
                <template v-slot:body-cell-delete="props">
                    <q-td :props="props">
                        <div>
                            <q-btn flat round color="primary" icon="remove_circle_outline" @click="disableCoof(props.row[0])"/>
                        </div>
                    </q-td>
                </template>
            </q-table>

            <div class="text-bold text-h8 q-mb-sm q-mt-lg">Вес корректировок</div>
            <q-table
                :rows="extend_coef_table.rows"
                :columns="extend_coef_table.columns"
                :rows-per-page-options="[0]"
                hide-bottom
                :card-style="{ padding: '14px 20px' }"
            />
        </div>
    </div>
</template>

<script>
import axios from "axios";
import {QSpinnerFacebook, exportFile, useQuasar} from "quasar";
import * as calc_func from '../../plugins/calculator'
import store from "../../plugins/store";

export default {
    data() {
        return {
            object: null,
            analogs: [],
            settings: [],
            columns_comparation_analogs: [
                {
                    name: 'Местоположение',
                    required: true,
                    label: 'Местоположение',
                    align: 'left',
                    field: row => row.Местоположение,
                },
                {
                    name: 'ПлощадьКвартиры',
                    required: true,
                    label: 'Площадь квартиры',
                    align: 'left',
                    field: row => row.ПлощадьКвартиры,
                },
                {
                    name: 'ПлощадьКухни',
                    required: true,
                    label: 'Площадь кухни',
                    align: 'left',
                    field: row => row.ПлощадьКухни,
                },
                {
                    name: 'КоличествоКомнат',
                    required: true,
                    label: 'Количество комнат',
                    align: 'left',
                    field: row => row.КоличествоКомнат,
                },
                {
                    name: 'Сегмент',
                    required: true,
                    label: 'Сегмент',
                    align: 'left',
                    format: val => `${this.capitalizeFirstLetter(val)}`,
                    field: row => row.Сегмент,
                },
                {
                    name: 'ЭтажностьДома',
                    required: true,
                    label: 'Этажность дома',
                    align: 'left',
                    field: row => row.ЭтажностьДома,
                },
                {
                    name: 'ЭтажРасположения',
                    required: true,
                    label: 'Этаж расположения',
                    align: 'left',
                    field: row => row.ЭтажРасположения,
                },
                {
                    name: 'МатериалСтен',
                    required: true,
                    label: 'Материал стен',
                    align: 'left',
                    format: val => `${this.capitalizeFirstLetter(val)}`,
                    field: row => row.МатериалСтен,
                },
                {
                    name: 'НаличиеБалконаЛоджии',
                    required: true,
                    label: 'Наличие балкона/лоджии',
                    format: val => `${val === 1 ? "Да" : "Нет"}`,
                    align: 'left',
                    field: row => row.НаличиеБалконаЛоджии,
                },
                {
                    name: 'МетроМин',
                    required: true,
                    label: 'Метро (мин. ходьбы)',
                    align: 'left',
                    field: row => row.МетроМин,
                },
                {
                    name: 'Состояние',
                    required: true,
                    label: 'Состояние',
                    align: 'left',
                    format: val => `${this.capitalizeFirstLetter(val)}`,
                    field: row => row.Состояние,
                },
                {
                    name: 'Стоимость',
                    required: true,
                    label: 'Стоимость (с НДС)',
                    align: 'left',
                    format: val => `${Number(val).toLocaleString('ru')} ₽`,
                    field: row => row.Стоимость,
                    style: 'width: 140px',
                },
                {
                    name: 'Стоимость_м',
                    required: true,
                    label: 'Стоимость за кв. м. (с НДС)',
                    align: 'left',
                    format: val => `${Number(val).toLocaleString('ru')} ₽`,
                    field: row => row.Стоимость_м,
                },
            ],
            coef_table: null,

            is_done: false,
            res_calc: null,

            analog_objects_of_pool: null,
            result_for_mass_reveal: null
        }
    },
    methods: {
        capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },

        revealResults() {
            this.$q.loading.show({
                spinner: QSpinnerFacebook,
                spinnerSize: 120,
                backgroundColor: 'grey-4',
                spinnerColor: 'primary'
            })

            axios.get('/api/pools/' + this.object.Пул)
                .then((response) => {
                    // Получаю все объекты в пуле, кроме эталонного
                    this.objects_of_pool = response.data.data.objects.filter(el => el.id !== this.object.id);

                    // Общая информация для таблицы Операции
                    let operation_meta = {
                        // Пул ID
                        pool_id: this.object.Пул,
                        // Эталон
                        object_id: this.object.id,
                        // Коэффициенты - Объект Settings
                        coof: this.settings,
                    };

                    // Информация для таблицы Операция_ОцениваемаяНедвижимость
                    // Для каждого объекта оценки расчитываю цену
                    // Заносим эталон
                    let operations = [
                        {
                            // Оцениваемый объект. Тут Операция_ОцениваемаяНедвижимость.ОцениваемыйОбъект = Операции.Эталон
                            object_id: this.object.id,
                            // Стоимость
                            price_m: this.res_calc.price_m
                        }
                    ];

                    this.object.Стоимость = this.res_calc.price;

                    // Проходимся по всему массиву объектов в пуле и расчитываем цену
                    this.objects_of_pool.forEach(el => {
                        // Объект - элементы в пуле
                        let res_calc = this.calc(el, [this.object], this.settings);

                        operations.push(
                            {
                                // Оцениваемый объект. Тут Операция_ОцениваемаяНедвижимость.ОцениваемыйОбъект = Операции.Эталон
                                object_id: el.id,
                                // Стоимость
                                price_m: res_calc.price_m
                            }
                        )
                    })

                    axios.post('/api/save_operations', {
                        operation_meta: operation_meta,
                        operations: operations
                    })
                        .then((response) => {
                            this.$router.push('/calculator/pools/' + this.object.Пул)
                        })

                    // Необходимо сохранить объект в базу
                    this.$q.loading.hide();
                })
        },

        exportToCSV() {
            let wrapCsvValue = function (val, formatFn, row) {
                let formatted = formatFn !== void 0
                    ? formatFn(val, row)
                    : val

                formatted = formatted === void 0 || formatted === null
                    ? ''
                    : String(formatted)

                formatted = formatted.split('"').join('""')
                /**
                 * Excel accepts \n and \r in strings, but some other CSV parsers do not
                 * Uncomment the next two lines to escape new lines
                 */
                // .split('\n').join('\\n')
                // .split('\r').join('\\r')

                return `"${formatted}"`
            }

            let [rows1, columns1] = [this.extend_objects_table.rows, this.extend_objects_table.columns]
            const content1 = [columns1.map(col => wrapCsvValue(col.label))].concat(
                rows1.map(row => columns1.map(col => wrapCsvValue(
                    typeof col.field === 'function'
                        ? col.field(row)
                        : row[col.field === void 0 ? col.name : col.field],
                    col.format,
                    row
                )).join(','))
            ).join('\r\n')

            let [rows2, columns2] = [this.coef_table.rows, this.coef_table.columns]
            const content2 = [columns2.map(col => wrapCsvValue(col.label))].concat(
                rows2.map(row => columns2.map(col => wrapCsvValue(
                    typeof col.field === 'function'
                        ? col.field(row)
                        : row[col.field === void 0 ? col.name : col.field],
                    col.format,
                    row
                )).join(','))
            ).join('\r\n')

            let [rows3, columns3] = [this.extend_coef_table.rows, this.extend_coef_table.columns]
            const content3 = [columns3.map(col => wrapCsvValue(col.label))].concat(
                rows3.map(row => columns3.map(col => wrapCsvValue(
                    typeof col.field === 'function'
                        ? col.field(row)
                        : row[col.field === void 0 ? col.name : col.field],
                    col.format,
                    row
                )).join(','))
            ).join('\r\n')

            const status = exportFile('Выгрузка_по_расчёту.csv', '\n' + "ufeff" + content1 + '\n\n\n\n' + content2 + '\n\n\n\n' + content3, 'text/csv')
        },

        calc(object, analogs, settings) {
            let result = calc_func.findEtalonPrice(object, analogs, settings);
            let path = "/calculator/pools/"+(object.Пул)+"/"+(object.id);
            let notifications = [];
            if(result.errors.length != 0) {
                result.errors.forEach(error => {
                   notifications.push(this.$q.notify({
                        message: error.text,
                        icon: 'announcement',
                        position: "top",
                        color: 'primary',
                        timeout: 0,
                        actions: [
                            {label: 'Вернуться к выбору аналогов', color: 'white', to: path, handler: () =>{notifications.forEach(notify =>{
                                notify();
                                })}},
                            {label: 'Ингнорировать', color: 'white', handler: () => {notifications.forEach(notify =>{
                                    notify();})}},
                        ]
                    }));
                })
            }
            return result;
        },

        loadData() {
            this.$q.loading.show({
                spinner: QSpinnerFacebook,
                spinnerSize: 120,
                backgroundColor: 'grey-4',
                spinnerColor: 'primary'
            })

            axios.get('/api/get_operation/' + this.$route.params.operation_id)
                .then((response) => {
                    this.object = response.data.data.object;
                    this.analogs = JSON.parse(response.data.data.operation.Аналоги);
                    this.settings = response.data.data.settings;

                    this.res_calc = this.calc(this.object, this.analogs, this.settings);
                    this.coef_table = this.setCoefTable();

                    this.is_done = true;
                    this.$q.loading.hide();
                })
        },

        disableCoof(coof_name) {
            let el = this.settings.findIndex(setting => setting.Название === coof_name);
            this.settings.splice(el, 1);

            this.res_calc = this.calc(this.object, this.analogs, this.settings);

            this.coef_table = null;
            this.coef_table = this.setCoefTable();

            this.$q.notify({
                message: 'Цена пересчитана относительно кор-их коэффициентов',
                icon: 'announcement'
            })
        },

        setCoefTable() {
            let columns = [];
            let rows = [];

            // Формируем строки
            this.res_calc.analog_changes_table.forEach((analog_changes) => {
                let row = {
                    0: analog_changes.name,
                };
                this.analogs.forEach((el, index) => {
                    row[index + 1] = analog_changes.values[index];
                })
                rows.push(row)
            });

            columns.push({
                name: 'Название',
                label: 'Название',
                align: 'left',
                field: row => row[0],
            })
            // Заголовки столбцов
            this.analogs.forEach((el, index) => {
                columns.push({
                    name: 'Аналог' + (index + 1),
                    label: 'Аналог ' + (index + 1),
                    align: 'left',
                    field: row => row[index + 1],
                })
            })
            columns.push({
                name: 'delete',
                label: 'Отключить',
                align: 'center'
            })

            return {
                columns: columns,
                rows: rows
            }
        }
    },
    computed: {
        extend_objects_table() {
            let columns = [];
            let rows = [];

            //Добавляем эталонный объект в начало
            let row = this.object;
            row[0] = 'Эталон';
            row.Состояние = store.getters.nameOfConditionById(row.Состояние).toLowerCase();
            row.Сегмент = store.getters.nameOfSegmentById(row.Сегмент).toLowerCase();
            row.МатериалСтен = store.getters.nameOfWallById(row.МатериалСтен).toLowerCase();
            row.Стоимость = this.res_calc.price;
            row.Стоимость_м = this.res_calc.price_m;
            rows.push(row);

            // Формируем строки
            this.analogs.forEach((meta_table, index) => {
                let row = meta_table;
                row.Стоимость_м = Math.floor(row.Стоимость / row.ПлощадьКвартиры);
                row[0] = 'Аналог ' + (index + 1);
                rows.push(row)
            });

            columns.push({
                name: 'ЭлементСравнения',
                label: 'Элемент сравнения',
                align: 'left',
                field: row => row[0],
            })

            // Заголовки столбцов
            this.columns_comparation_analogs.forEach((el) => {
                columns.push(el);
            })

            return {
                columns: columns,
                rows: rows
            }
        },
        extend_coef_table() {
            let columns = [];
            let rows = [];

            // Формируем строки
            this.res_calc.coef_meta_table.forEach((meta_table) => {
                let row = {
                    0: meta_table.name,
                };
                this.analogs.forEach((el, index) => {
                    row[index + 1] = meta_table.values[index];
                })
                rows.push(row)
            });

            columns.push({
                name: 'Параметр',
                label: 'Параметр',
                align: 'left',
                field: row => row[0],
            })
            // Заголовки столбцов
            this.analogs.forEach((el, index) => {
                columns.push({
                    name: 'Аналог' + (index + 1),
                    label: 'Аналог ' + (index + 1),
                    align: 'left',
                    field: row => row[index + 1],
                })
            })

            return {
                columns: columns,
                rows: rows
            }
        },
    },
    beforeMount() {
        this.loadData();
    }
}
</script>
