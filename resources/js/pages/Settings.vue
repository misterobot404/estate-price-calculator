<template>
    <div class="q-pa-lg">
        <template v-if="tables.length">
            <div class="flex items-center">
                <div class="text-bold text-h7">Настройки оценки:</div>
                <q-select dense v-model="selected_list" :options="setting_lists" option-value="id" option-label="Название_списка" style="margin-left: 18px"/>
                <q-btn @click="addSettingListModal = true" icon="post_add" class="q-ml-sm" round flat color="primary" style="font-size: 14px" size="sm">
                    <q-tooltip>Добавить список</q-tooltip>
                </q-btn>
                <q-btn @click="removeSettingListModal = true" icon="delete_sweep" class="q-ml-xs" round flat color="primary" style="font-size: 14px" size="sm">
                    <q-tooltip>Удалить текущий</q-tooltip>
                </q-btn>
            </div>
            <q-card class="q-mt-md" style="width: 500px; padding: 8px 12px">
                <q-card-section>
                    <div class="text-bold q-mb-sm">Перечень корректировок</div>
                    <div v-for="table in tables" class="q-mt-xs">
                        {{ table.Название }}
                    </div>
                </q-card-section>
            </q-card>
            <div class="flex" style="margin-top: 32px">
                <q-btn @click="save()" label="Сохранить изменения" no-caps color="primary"/>
                <q-select dense v-model="selected_tables_name" :options="['Все справочники', ...tables.map(el => el.Название)]" filled class="q-ml-md"/>
            </div>
            <q-separator class="q-mt-md" style="max-width: 1600px"/>
            <div class="q-mt-md">
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
        </template>

        <!-- Окно добавления списка справочников -->
        <q-dialog v-model="addSettingListModal">
            <q-card style="width: 500px">
                <q-card-section>
                    <div class="text-h6">Добавление списка справочников</div>
                </q-card-section>
                <q-card-section class="q-pt-none">
                    <q-input filled v-model="new_settings_list.name" label="Название"/>
                    <q-select filled v-model="new_settings_list.parent" :options="setting_lists" option-value="id" option-label="Название_списка" label="Наследовать справочник от" class="q-mt-sm"/>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Подтвердить" color="primary" v-close-popup @click="addSettingList()"/>
                </q-card-actions>
            </q-card>
        </q-dialog>

        <q-dialog v-model="removeSettingListModal">
            <q-card style="width: 500px">
                <q-card-section>
                    <div class="text-h6">Удаление списка справочников</div>
                </q-card-section>
                <q-card-section class="q-pt-none">
                    <div>Данный список нельзя будет восстановить в дальнейшем. Вы уверены?</div>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Подтвердить" color="primary" v-close-popup @click="removeSettingList()"/>
                </q-card-actions>
            </q-card>
        </q-dialog>
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
            raw_tables: [],

            isLoading: false,

            // Данные модалки
            removeSettingListModal: false,
            addSettingListModal: false,
            new_settings_list: {
                name: null,
                parent: null
            }
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
        loadConfigsByListId(setting_list_id) {
            this.isLoading = true;

            axios.get('/api/settings/' + setting_list_id)
                .then((response) => {
                    let raw_tables = response.data.data.settings
                        .sort((a, b) => {
                            return a < b ? -1 : 1;
                        }).map(el => {
                            el.Данные = JSON.parse(el.Данные);
                            return el;
                        });

                    this.tables = this.formatted_tables(raw_tables);
                    this.isLoading = false;

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
        },

        addSettingList() {
            axios.post("/api/setting_lists", {
                name: this.new_settings_list.name,
                parent_id: this.new_settings_list.parent.id,
            })
                .then((res) => {
                    this.setting_lists = res.data.data.setting_lists;

                    this.$q.notify({
                        type: 'positive',
                        message: 'Новый список справочников успешно создан'
                    })

                    this.addSettingListModal = false;
                })
        },
        removeSettingList() {
            if (this.setting_lists.length > 1) {
                axios.delete("/api/setting_lists/" + this.selected_list.id)
                    .then((res) => {
                        this.setting_lists = res.data.data.setting_lists;
                        this.selected_list = this.setting_lists[0];

                        this.$q.notify({
                            icon: 'announcement',
                            message: 'Список справочников успешно удалён'
                        })

                        this.addSettingListModal = false;
                    })
            } else {
                this.$q.notify({
                    icon: 'announcement',
                    message: 'Невозможно удалить последний справочник'
                })
            }
        },
    },
    watch: {
        selected_list(v) {
            this.loadConfigsByListId(v.id)
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
                this.selected_list = this.setting_lists[0];
            })
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

