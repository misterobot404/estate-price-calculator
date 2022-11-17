<template>
    <div style="padding: 24px 30px">
        <div class="flex items-center">
            <div class="text-h7 text-bold">Список отчётов за:</div>
            <q-select v-model="selected_year" :options="[2020, 2021, 2022]" dense class="q-ml-md"/>
        </div>

        <div class="flex q-mt-lg">
            <q-card class="navigation" style="width: 160px; max-height: fit-content;">
                <q-card-section>
                    <q-list class="text-primary">
                        <q-item v-for="el in months" clickable :active="selected_month === el" @click="selected_month = el" active-class="my-menu-link" style="border-radius: 8px !important">
                            <q-item-section class="text-center" :class="selected_month === el ? 'text-bold' : ''">{{ el }}</q-item-section>
                        </q-item>
                    </q-list>
                </q-card-section>
            </q-card>
            <!-- report card -->
            <div class="q-px-xl col-grow">
                <div v-if="history_by_selected.length">
                    <div v-for="(el, index) in history_by_selected" class="flex cursor-pointer" @click="$router.push('/history/' + el.id)">
                        <q-icon name="o_label" style="font-size: 22px; padding-top: 2px"></q-icon>
                        <div class="col-grow q-ml-lg">
                            <div class="text-h7 text-bold q-mb-xs">Номер расчёта # {{ el.id }}</div>
                            <div>Дата расчёта: 17.11.2022</div>
                            <div>Количество комнат: {{ $store.getters.nameOfNumberRoomsById(el.КоличествоКомнат) }}</div>
                            <div>Количество рассчитанных объектов недвижимости: {{ el.КоличествоОбъектов }}</div>
                            <q-btn outline color="primary" icon="file_download" label="Выгрузка в Excel" :href="'/history/excel/'+ el.id" class="q-mt-sm" @click.stop/>
                            <q-separator class="q-my-lg"/>
                        </div>
                    </div>
                </div>
                <div v-else-if="!loading" style="height: 600px" class="flex-center column items-center justify-center">
                    <q-img src="/images/no-data.svg" style="width: 300px;"/>
                    <div class="q-mt-md">Вы ещё не рассчитали ни один пул недвижимости</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import axios from "axios";
import {QSpinnerFacebook} from "quasar";

export default {
    name: "History",
    data() {
        return {
            history: [],
            loading: true,

            selected_year: 2022,
            selected_month: "Ноябрь",
            months: ["Декабрь", "Ноябрь", "Октябрь", "Сентябрь", "Август", "Июль", "Июнь", "Май", "Апрель", "Март", "Февраль", "Январь"].reverse(),
        }
    },
    methods: {},
    computed: {
        history_by_selected() {
            if (this.selected_year === 2022 && this.selected_month === "Ноябрь") {
                return this.history;
            } else return [];
        }
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

                axios.get('/api/history')
                    .then((response) => {
                        this.history = response.data.data.history.reverse();

                        this.loading = false;
                        this.$q.loading.hide();
                    })
            })
    }
}

</script>

<style lang="sass">
.navigation
    background: #FFFFFF
    border-radius: 6px !important


.my-menu-link
    color: #1976d2
    background: #E6F6FF
    border-radius: 6px !important

.report-card
    max-height: fit-content

</style>

