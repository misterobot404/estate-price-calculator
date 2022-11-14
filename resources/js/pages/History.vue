<template>
    <div class="q-px-lg q-py-lg">
        <div v-if="history.length">
            <div class="text-h7 q-mb-sm">История расчётов по пулам:</div>
            <q-card v-for="el in history" class="q-mb-md" style="border-radius: .5rem">
                <q-card-section class="q-pa-lg">
                    <div class="text-bold">Номер расчёта # {{ el.id }}</div>
                    <div>Количество комнат для квартир пула - {{ el.КоличествоКомнат }}</div>
                    <div>Количество рассчитанных объектов недвижимости- {{ el.КоличествоОбъектов }}</div>
                    <div class="flex q-mt-md">
                        <q-btn color="primary" label="Страница расчёта" class="q-mr-md"/>
                        <q-btn color="primary" label="Выгрузка в Excel" :href="'/history/excel/'+ el.id"/>
                    </div>
                </q-card-section>
            </q-card>
        </div>
        <div v-else-if="!loading" style="height: 600px" class="flex-center column items-center justify-center">
            <q-img src="/images/no-data.svg" style="width: 300px;"/>
            <div class="q-mt-md">Вы ещё не рассчитали ни один пул недвижимости</div>
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
        }
    },
    methods: {
        getExcel(el_id) {
            axios.get(+el_id).then()
        }
    },
    beforeMount() {
        this.$q.loading.show({
            spinner: QSpinnerFacebook,
            spinnerSize: 120,
            backgroundColor: 'grey-4',
            spinnerColor: 'primary'
        })

        axios.get('/api/history')
            .then((response) => {
                this.history = response.data.data.history.reverse();

                this.loading = false;
                this.$q.loading.hide();
            })
    }
}

</script>

<style lang="sass">
.navigation
    margin: 0 1% 0 1%
    background: #FFFFFF
    border-radius: 8px


.my-menu-link
    color: #038DD2
    background: #E6F6FF
    border-radius: 8px

.report-card
    max-height: fit-content

</style>

