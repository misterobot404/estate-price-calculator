<template>
    <div v-if="!isLoading" class="q-pa-lg">
        <div class="flex items-center">
            <q-btn flat round icon="arrow_back" size="sm" class="text-grey-8" :to="'/history/'+operation_meta.Пул"/>
            <div class="q-ml-xs">Вернуться к отчёту по пулу</div>
        </div>
        <div style="margin: 16px 34px">
            <div class="text-bold text-h8 q-mb-sm">Итоговый расчёт стоимости</div>
            <q-card>
                <q-card-section class="q-pa-lg">
                    <div class="text-bold text-h8">Квартира:&nbsp;&nbsp;{{ object.Местоположение }}</div>
                    <div class="flex q-mt-sm">
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
                        <q-btn outline color="primary" icon="file_download" label="Выгрузить расчёт в .csv" @click="exportToCSV()"/>
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


            <div class="text-bold text-h8 q-mb-sm q-mt-lg">Корректирующие коэффициенты аналогов</div>
            <q-table
                :rows="coef_table.rows"
                :columns="coef_table.columns"
                row-key="Название"
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

export default {
    data() {
        return {
            isLoading: true,

            operation_meta: null,

            object: null,
            analogs: null,
            res_calc: null,
            settings: null,

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
            ]
        }
    },
    methods: {
        capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
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
    },
    computed: {
        extend_objects_table() {
            let columns = [];
            let rows = [];

            //Добавляем эталонный объект в начало
            let row = Object.assign({}, this.object);
            row[0] = 'Объект оценки';
            row.КоличествоКомнат = this.$store.getters.nameOfNumberRoomsById(this.object.КоличествоКомнат).toLowerCase();
            row.Состояние = this.$store.getters.nameOfConditionById(this.object.Состояние).toLowerCase();
            row.Сегмент = this.$store.getters.nameOfSegmentById(this.object.Сегмент).toLowerCase();
            row.МатериалСтен = this.$store.getters.nameOfWallById(this.object.МатериалСтен).toLowerCase();
            row.Стоимость = this.res_calc.price;
            row.Стоимость_м = this.res_calc.price_m;
            rows.push(row);



            // Формируем строки
            this.analogs.forEach((meta_table, index) => {
                // Если объект это аналог, то преобразовываем типы
                if (meta_table.id === this.operation_meta.Эталон) {
                    meta_table.КоличествоКомнат = this.$store.getters.nameOfNumberRoomsById(meta_table.КоличествоКомнат).toLowerCase();
                    meta_table.Состояние = this.$store.getters.nameOfConditionById(meta_table.Состояние).toLowerCase();
                    meta_table.Сегмент = this.$store.getters.nameOfSegmentById(meta_table.Сегмент).toLowerCase();
                    meta_table.МатериалСтен = this.$store.getters.nameOfWallById(meta_table.МатериалСтен).toLowerCase();
                }

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

        coef_table() {
            let columns = [];
            let rows = [];

            // Формируем вывод на основе base_settings
            this.settings.forEach((setting, index) => {
                // Проверяем использовалась ли настройка в расчётах
                let used_setting = this.res_calc.analog_changes_table.find(el => el.name === setting.Название);
                if (used_setting) {
                    let row = {
                        0: used_setting.name,
                    };
                    this.analogs.forEach((el, index) => {
                        row[index + 1] = used_setting.values[index];
                    })
                    rows.push(row)
                }
            })

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
        this.$q.loading.show({
            spinner: QSpinnerFacebook,
            spinnerSize: 120,
            backgroundColor: 'grey-4',
            spinnerColor: 'primary'
        })

        axios.get('/api/reference_books')
            .then((response_books) => {
                this.$store.commit('SET_REFERENCE_BOOKS', response_books.data.data);

                axios.get("/api/history/operation/" + this.$route.params.operation_id)
                    .then((res) => {
                        this.operation_meta = res.data.data.operation_meta;
                        this.analogs = JSON.parse(res.data.data.operation_obj.analogs);
                        this.res_calc = JSON.parse(res.data.data.operation_obj.res_calc);
                        this.object = res.data.data.object;
                        this.settings = JSON.parse(this.operation_meta.Коэффициенты);

                        this.$q.loading.hide();
                        this.isLoading = false;
                    })
            })
    },
}
</script>
