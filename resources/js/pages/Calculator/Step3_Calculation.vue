<template>
    <div v-if="is_done" class="q-pa-lg">
        <div class="flex items-center q-mb-md">
            <q-btn flat round icon="arrow_back" size="sm" class="text-grey-8" :to="'/calculator/pools/'+object.Пул+'/'+object.id"/>
            <div class="q-ml-xs">Изменить аналоги</div>
        </div>

        <q-card>
            <q-card-section class="q-pa-lg">
                <div class="text-h8 q-mb-md">Итоговый расчёт эталона</div>
                <q-separator class="q-my-md"/>
                <div class="flex">
                    <div class="q-mr-lg">
                        <div class="text-bold">Рыночная стоимость: {{ res_calc.price_m }}₽</div>
                        <div class="text-caption">За кв.м. (с НДС)</div>
                    </div>
                    <div>
                        <div class="text-bold">Рыночная стоимость: {{ res_calc.price }}₽</div>
                        <div class="text-caption">(с НДС)</div>
                    </div>
                </div>
                <div class="q-mt-md">
                    <q-btn label="Рассчитать для всех объектов" color="primary" unelevated @click="revealResults()"/>
                </div>
            </q-card-section>
        </q-card>

        <div class="text-bold text-h8 q-mb-sm q-mt-lg">Объекты сравнения</div>
        <q-table
            :rows="analogs"
            :columns="columns_comparation_analogs"
            row-key="name"
            :rows-per-page-options="[]"
        >
            <template v-slot:top-right>
                <q-btn
                    color="primary"
                    icon-right="archive"
                    label="Выгрузка"
                    no-caps
                    flat
                    @click="exportTable(analogs, columns_comparation_analogs)"
                />
            </template>
        </q-table>

        <div class="text-bold text-h8 q-mb-sm q-mt-lg">Корректирующие коэффициенты аналогов</div>
        <q-table
            :rows="coef_table.rows"
            :columns="coef_table.columns"
            row-key="Название"
            :rows-per-page-options="[10]"
        >
            <template v-slot:top-right>
                <q-btn
                    color="primary"
                    icon-right="archive"
                    label="Выгрузка"
                    no-caps
                    flat
                    @click="exportTable(coef_table.rows, coef_table.columns)"
                />
            </template>
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
            :rows-per-page-options="[]"
        />
    </div>
</template>

<script>
import axios from "axios";
import {QSpinnerFacebook, exportFile, useQuasar} from "quasar";
import * as calc_func from '../../plugins/calculator'

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
                    format: val => `${val.toLowerCase()}`,
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
                    field: row => row.МатериалСтен,
                },
                {
                    name: 'НаличиеБалконаЛоджии',
                    required: true,
                    label: 'Наличие балкона/лоджии',
                    format: val => `${val === 1 ? "да" : "нет"}`,
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
                    format: val => `${val.toLowerCase()}`,
                    field: row => row.Состояние,
                },
                {
                    name: 'Стоимость',
                    required: true,
                    label: 'Стоимость (р.)',
                    align: 'left',
                    field: row => row.Стоимость,
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

        wrapCsvValue(val, formatFn, row) {
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
        },

        exportTable(rows, columns) {
            // naive encoding to csv format
            const content = [columns.map(col => this.wrapCsvValue(col.label))].concat(
                rows.map(row => columns.map(col => this.wrapCsvValue(
                    typeof col.field === 'function'
                        ? col.field(row)
                        : row[col.field === void 0 ? col.name : col.field],
                    col.format,
                    row
                )).join(','))
            ).join('\r\n')

            const status = exportFile(
                'table-export.csv',
                "ufeff" + content,
                'text/csv'
            )

            if (status !== true) {
                $q.notify({
                    message: 'Browser denied file download...',
                    color: 'negative',
                    icon: 'warning'
                })
            }
        },


        calc(object, analogs, settings) {
            return calc_func.findEtalonPrice(object, analogs, settings.map(el => JSON.parse(el.Данные)));
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
