<template>
    <div v-if="is_done" class="q-pa-lg">
        <div class="flex items-center">
            <q-btn flat round icon="arrow_back" size="sm" class="text-grey-8"
                   :to="'/calculator/pools/'+object.Пул+'/'+object.id"/>
            <div class="q-ml-xs">Вернуться к карте</div>
        </div>
        <div style="margin: 16px 34px">
            <div class="text-bold text-h8 q-mb-sm">Итоговый расчёт стоимости эталона</div>
            <q-card>
                <q-card-section class="q-pa-lg">
                    <div class="flex">
                        <div class="q-mr-lg">
                            <div>
                                <span class="text-bold">Рыночная стоимость:</span>
                                {{ res_calc.price_m.toLocaleString('ru') }} ₽
                            </div>
                            <div class="text-caption">За кв.м. (с НДС)</div>
                        </div>
                        <div>
                            <div>
                                <span class="text-bold">Рыночная стоимость:</span>
                                {{ res_calc.price.toLocaleString('ru') }} ₽
                            </div>
                            <div class="text-caption">(с НДС)</div>
                        </div>
                    </div>
                    <div class="q-mt-md">
                        <q-btn label="Рассчитать для всех объектов" color="primary" unelevated
                               @click="revealResults()"/>
                        <q-btn label="Выгрузить расчёт эталона .csv" no-caps class="q-ml-sm" color="primary" flat
                               @click="exportToCSV()"/>
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
                :card-style="{ padding: '18px 22px' }"
            />

            <div class="flex q-mb-sm q-mt-lg items-center">
                <div class="text-bold text-h8">Корректирующие коэффициенты аналогов</div>
                <q-select dense v-model="selected_setting_list" :options="setting_lists" option-value="id" option-label="Название_списка" filled class="q-ml-md"/>
            </div>
            <q-table
                :rows="coef_table.rows"
                :columns="coef_table.columns"
                row-key="Название"
                :rows-per-page-options="[0]"
                hide-bottom
                :card-style="{ padding: '18px 22px' }"
            >
                <template v-slot:body="props">
                    <q-tr :props="props">
                        <q-td v-for="(el, keys) in props.row" :key="keys" :props="props">
                            {{ el }}
                            <q-popup-edit v-model="props.row[keys]" v-slot="scope">
                                <q-input v-model="scope.value" dense autofocus @keyup.enter="this.changeValue(props.row.name, scope.value, keys)"></q-input>
                            </q-popup-edit>
                        </q-td>
                    </q-tr>
                </template>
            </q-table>

            <div class="text-bold text-h8 q-mb-sm q-mt-lg">Вес корректировок</div>
            <q-table
                :rows="extend_coef_table.rows"
                :columns="extend_coef_table.columns"
                :rows-per-page-options="[0]"
                hide-bottom
                :card-style="{ padding: '18px 22px' }"
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
            changes:[],

            setting_lists: [],
            // Выбранный список кор-ок
            selected_setting_list: null,
            // Все настройки
            base_settings: [],
            // Настройки (кор-ки), которые применяются в данный момент
            settings: [],
            // Итоговая таблицы с кор-ми на вывод пользователю
            coef_table: null,

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

            is_done: false,
            res_calc: null,

            analog_objects_of_pool: null,

            result_for_mass_reveal: null,
            notifications: [],

            isLoading: false,
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
                        let res_calc = this.calc(el, [this.object], this.settings, this.changes);

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

        calc(object, analogs, settings, changes) {

            return calc_func.findEtalonPrice(object, analogs, settings, changes);
        },

        loadData() {
            this.isLoading = true;

            axios.get('/api/get_operation/' + this.$route.params.operation_id)
                .then((response) => {
                    this.object = response.data.data.object;
                    this.analogs = JSON.parse(response.data.data.operation.Аналоги);

                    axios.get('/api/settings/' + this.selected_setting_list.id)
                        .then((response) => {
                            this.settings = response.data.data.settings;

                            this.base_settings = [...this.settings];

                            // Расчитываем
                            this.res_calc = this.calc(this.object, this.analogs, this.settings,this.changes);
                            this.showAlert();
                            // Обновляем итоговую таблицу с кор-ми на вывод пользователю
                            this.coef_table = this.setCoefTable();
                            this.setStartChanges();

                            if (this.is_done) {
                                this.$q.notify({
                                    icon: "check_circle_outline",
                                    iconColor: 'green',
                                    color: 'white',
                                    textColor: 'black',
                                    position: 'top',
                                    message: `Выбранный список корректирующих коэффициентов был изменён. Расчёты обновлены`,
                                })
                            }

                            this.is_done = true;
                            this.isLoading = false;
                        })
                })
        },

        disableCoof(coof_name) {
            let el_index = this.settings.findIndex(setting => setting.Название === coof_name);
            this.settings.splice(el_index, 1);

            this.res_calc = this.calc(this.object, this.analogs, this.settings,this.changes);

            this.coef_table = null;
            this.coef_table = this.setCoefTable();

            this.$q.notify({
                message: 'Цена пересчитана относительно кор-их коэффициентов',
                icon: 'announcement'
            })
        },

        enableCoof(coof_name) {
            let el = this.base_settings.find(setting => setting.Название === coof_name);

            this.settings.push(el);

            this.res_calc = this.calc(this.object, this.analogs, this.settings, this.changes);

            this.coef_table = null;
            this.coef_table = this.setCoefTable();

            this.$q.notify({
                message: 'Цена пересчитана относительно кор-их коэффициентов',
                icon: 'announcement'
            })
        },
        setStartChanges(){
            console.log(this.coef_table);
            for(let i =0; i<this.coef_table.rows.length;i++){
                let row = {table: null, change:[]};
                let keys = Object.keys(this.coef_table.rows[i]);
                keys.forEach(key=>{
                    if(key === 'name'){
                        row.table =this.coef_table.rows[i][key];
                    }
                    else{
                        let myRe = new RegExp('^[-]?[0-9]+[.][0-9]+');
                        row.change.push((parseFloat(myRe.exec(this.coef_table.rows[i][key])[0])/100).toFixed(4));
                    }
                });
                this.changes.push(row);
            }

        },
        changeValue(table, value, index){

            let reg = new RegExp('[0-9]+$');
            let ind = reg.exec(index)[0]-1;
            this.changes.forEach((el, key)=>{
                if(el.table === table){
                    this.changes[key].change[ind]=value/100;
                }

            });
            this.res_calc = this.calc(this.object,this.analogs, this.settings, this.changes);

            this.coef_table = null;
            this.coef_table = this.setCoefTable();

            this.$q.notify({
                message: 'Цена пересчитана относительно кор-их коэффициентов',
                icon: 'announcement'
            })
        },

        // Формируем результирующую для вывода пользователю таблицу с кор-ми коэф-ми
        setCoefTable() {
            let columns = [];
            let rows = [];

            // Формируем вывод на основе base_settings
            this.base_settings.forEach((base_setting, index) => {
                // Проверяем использовалась ли настройка в расчётах
                let used_setting = this.res_calc.analog_changes_table.find(setting => setting.name === base_setting.Название);
                if (used_setting) {
                    let row = {
                        name: used_setting.name,
                    };
                    this.analogs.forEach((el, index) => {
                        row['Аналог' + (index + 1)] = used_setting.values[index];
                    })
                    rows.push(row)
                } else {
                    let row = {
                        name: base_setting.Название,
                    };
                    this.analogs.forEach((el, index) => {
                        row['Аналог' + (index + 1)] = "-";
                    })
                    rows.push(row);
                }
            })

            columns.push({
                name: 'name',
                label: 'Название',
                align: 'left',
                field: row => row[0],
            })
            // Заголовки столбцов
            this.analogs.forEach((el, index) => {
                columns.push({
                    name:  'Аналог' + (index + 1),
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

        //Вывлд алерта о 20 и 33%
        showAlert() {
            //Выводим алерт если нужно
            let path = "/calculator/pools/" + (this.object.Пул) + "/" + (this.object.id);
            this.notifications = [];
            if (this.res_calc.errors.length !== 0) {
                this.res_calc.errors.forEach((error, index) => {
                    this.alert('ВНИМАНИЕ!', error.text, [
                        {
                            label: 'Вернуться к выбору аналогов', color: 'blue', to: path, handler: () => {
                            }
                        },
                        {
                            label: 'Ингнорировать', color: 'blue', handler: () => {
                            }
                        },
                    ], 0, 'warning', 'red',);

                });
            }
        },
        alert(title, text, buttons, timeout = 0, icon = 'announcement', iconColour = 'orange') {
            this.notifications.push(this.$q.notify({
                icon: icon,
                iconColor: iconColour,
                color: 'white',
                textColor: 'black',
                position: 'top',
                message: `${title}<br><b>${text}</b><div class='q-mt-sm'>Выберете дальнейшее действие<div>`,
                html: true,
                timeout: timeout,
                actions: buttons,
            }));

        }
    },
    computed: {
        extend_objects_table() {
            let columns = [];
            let rows = [];

            //Добавляем эталонный объект в начало
            let row = Object.assign({}, this.object);
            row[0] = 'Эталон';
            row.Состояние = store.getters.nameOfConditionById(this.object.Состояние).toLowerCase();
            row.Сегмент = store.getters.nameOfSegmentById(this.object.Сегмент).toLowerCase();
            row.МатериалСтен = store.getters.nameOfWallById(this.object.МатериалСтен).toLowerCase();
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
    watch: {
        selected_setting_list(v) {
            this.loadData();
        },
        isLoading(v) {
            if (v) {
                this.$q.loading.show({
                    spinner: QSpinnerFacebook,
                    spinnerSize: 120,
                    backgroundColor: 'grey-4',
                    spinnerColor: 'primary'
                })
            } else {
                this.$q.loading.hide();
            }
        }
    },
    beforeMount() {
        this.isLoading = true;

        axios.get("/api/setting_lists")
            .then((res) => {
                this.setting_lists = res.data.data.setting_lists;
                this.selected_setting_list = this.setting_lists[0];
            })
    },
    beforeRouteLeave() {
        this.notifications.forEach((notify) => {
            notify();
        });
        this.notifications = [];
    }
}
</script>

<style>
/* Костыльно */
i.q-icon.text-red {
    margin-bottom: auto !important;
    margin-top: 8px;
}
</style>
