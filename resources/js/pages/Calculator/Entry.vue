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
            .then((response) => {
                if (response.data.data.calculation_status) {
                    axios.get('/api/reference_books')
                        .then((response) => {
                            this.$store.commit('SET_REFERENCE_BOOKS', response.data.data);
                            this.$router.replace("/calculator/pools").then(() => this.$q.loading.hide());
                        })
                } else {
                    this.$router.replace("/calculator/upload").then(() => this.$q.loading.hide());
                }
            })
            .finally(() => this.$store.commit('ENTRY_TO_CALCULATION_DONE'))
    }
}
</script>

<style scoped>

</style>
