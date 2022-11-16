<template>
    <div class="q-pa-lg">
        <div class="text-bold text-h7">Настройки оценки:</div>
        <div class="flex q-mt-md">
            <q-select dense v-model="selected_list" :options="years" filled class="q-mr-md"/>
            <q-select dense v-model="selected_tables_name" :options="['Все справочники', ...tables.map(el => el.Название)]" filled/>
            <q-btn @click="save()" label="Сохранить" class="q-ml-md" no-caps/>
            <q-btn @click="save()" label="Добавить категорию" flat class="q-ml-md" no-caps/>
        </div>
        <div class="q-mt-lg">
            <div v-for="(table, table_index) in selected_tables" class="q-mt-lg" style="max-width: 1600px">
                <div class="q-mb-sm text-bold">{{ table.Название }}</div>
                <q-table
                    :rows="table.Данные.rows"
                    :columns="table.Данные.columns"
                    :class="table.Данные.rowcolNames === null ? 'color-first-row' : 'color-first-row color-first-col'"
                    :rows-per-page-options="[0]"
                    hide-bottom
                >
                    <template v-slot:body="props">
                        <q-tr :props="props">
                            <q-td v-for="(el, col_index) in props.row" :key="col_index" :props="props">
                                <div>{{ el }}</div>
                                <q-popup-edit buttons v-model="props.row[col_index]" v-slot="scope">
                                    <q-input
                                        type="number"
                                        v-model.number="scope.value"
                                        autofocus
                                        counter
                                        @keyup.enter.stop
                                    />
                                </q-popup-edit>
                            </q-td>
                        </q-tr>
                    </template>
                </q-table>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import {QSpinnerFacebook} from "quasar";

export default {
    data() {
        return {
            selected_list: null,
            selected_tables_name: "Все справочники",
            setting_lists: null,
            // Справочники
            tables: [],
            raw_tables: []
        }
    },
    computed: {
        selected_tables() {
            if (this.selected_tables_name === "Все справочники") {
                return this.tables;
            } else {
                return [this.tables.find(el => el.Название === this.selected_tables_name)];
            }
        }
    },
    methods: {
        loadConfigsByListId(year) {
            this.$q.loading.show({
                spinner: QSpinnerFacebook,
                spinnerSize: 120,
                backgroundColor: 'grey-4',
                spinnerColor: 'primary'
            })

            this.selected_list = year;

            axios.get('/api/settings/' + this.selected_list)
                .then((response) => {
                    let raw_tables = response.data.data.settings
                        .sort((a, b) => {
                            return a < b ? -1 : 1;
                        }).map(el => {
                            el.Данные = JSON.parse(el.Данные);
                            return el;
                        });

                    this.raw_tables = JSON.parse(JSON.stringify(raw_tables));

                    this.tables = this.formatted_tables(raw_tables);
                    this.$q.loading.hide();

                    this.$q.notify({message: "Для редактирования полей вы можете кликнуть по ним мышкой", icon: 'announcement'})
                })
        },
        formatted_tables(tables) {
            let res_selected_tables_formatted = [];

            tables.forEach((el) => {
                el.Данные.columns = [];
                el.Данные.rows = [];

                if (el.Данные.rowcolNames === null) {
                    el.Данные.columns.push({
                        name: "0",
                        label: 'Проценты',
                        align: 'left',
                        field: String(0),
                        headerStyle: 'width: 500px',
                    });
                    el.Данные.rows.push({0: el.Данные.table.toString()});
                } else {
                    // Добавляем первым столбцом название
                    el.Данные.rowcolNames.unshift('Название критерия сравнения (по вертикали - объект, по горизонтали - аналог)');
                    el.Данные.columns = el.Данные.rowcolNames.map((el, index) => {
                        return {
                            name: index,
                            label: (el === "1" || el === "0") ? (el === "1" ? "Да" : "Нет") : el,
                            align: 'left',
                            field: index.toString(),
                            headerStyle: 'width: 700px',
                        }
                    });

                    el.Данные.table.forEach((table_el, index) => {
                        // Добавляем в элемент название
                        table_el.unshift(el.Данные.columns[index + 1].label);
                        // Добавляем элемент в массив строк
                        el.Данные.rows.push(Object.assign({}, table_el));
                    })
                }
                res_selected_tables_formatted.push(el);
            })

            return res_selected_tables_formatted;
        },
        save() {
            this.$q.loading.show({
                spinner: QSpinnerFacebook,
                spinnerSize: 120,
                backgroundColor: 'grey-4',
                spinnerColor: 'primary'
            })

            let temp_tables = JSON.parse(JSON.stringify(this.tables));
            temp_tables.forEach((el, index_table) => {
                el.Данные.rows.forEach((row, index_row) => {
                    if (el.Данные.rowcolNames !== null) {
                        let temp = Object.values(el.Данные.rows[index_row]);
                        temp.shift();
                        el.Данные.rows[index_row] = temp;
                    } else {
                        el.Данные.rows = Number(el.Данные.rows[index_row][0]);
                    }
                });
            })

            this.raw_tables.forEach((el, index) => {
                this.raw_tables[index].Данные.table = temp_tables[index].Данные.rows;
            })


            axios.post('/api/settings', {
                settings: JSON.stringify(this.raw_tables)
            })
                .then((response) => {
                    this.$q.loading.hide();
                    this.$q.notify({message: "Справочники сохранены", icon: 'announcement'})
                })
        }
    },
    watch: {
        selected_list(v) {
            this.loadConfigsByListId(v)
        }
    },
    beforeMount() {

        this.loadConfigsByListId(2022);
    }
}
</script>

<style lang="scss">
.color-first-col {
    td:first-child {
        background-color: #eceff1 !important;
    }
}

.color-first-row {
    thead tr:first-child th {
        /* bg color is important for th; just specify one */
        background-color: #eceff1 !important;
    }
}

</style>

