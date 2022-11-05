<template>

</template>

<script>
import {QSpinnerFacebook} from "quasar";
import axios from "axios";

export default {
    beforeMount() {
        //  Определяем на какую страницу пользователю необоримо перейти при первичной загрузке приложения
        this.$q.loading.show({
            spinner: QSpinnerFacebook,
            spinnerSize: 120,
            backgroundColor: 'grey-5',
            spinnerColor: 'primary'
        })

        axios.get('/api/calculation_status')
            .then((response_status) => {
                axios.get('/api/reference_books')
                    .then((response_books) => {
                        this.$store.commit('SET_REFERENCE_BOOKS', response_books.data.data);
                        if (response_status.data.data.calculation_status) {
                            this.$router.replace("/calculator/pools").then(() => this.$q.loading.hide());
                        } else {
                            this.$router.replace("/calculator/upload").then(() => this.$q.loading.hide());
                        }
                    })
            })
            .finally(() => this.$store.commit('ENTRY_TO_CALCULATION_DONE'))
    }
}
</script>

<style scoped>

</style>
