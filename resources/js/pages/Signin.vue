<template>
    <div class="window-width window-height flex flex-center">
        <form @submit.prevent="send" style="min-width: 430px">
            <q-img src="/images/logo-name.svg" no-spinner/>
            <q-input outlined v-model="email" label="Электронная почта" required type="email" class="q-mt-lg"/>
            <q-input outlined v-model="password" label="Пароль" type="password" required class="q-mt-sm"></q-input>
            <q-btn class="q-mt-md full-width" color="primary" label="Войти" type="submit" :loading="loading"/>
            <div class="text-center q-mt-md">
                <q-btn to="/signup" label="Регистрация" flat rounded color="primary" no-caps/>
            </div>
        </form>
    </div>
</template>

<script>
import router from "../plugins/router";
import {mapActions} from "vuex";

export default {
    data() {
        return {
            email: null,
            password: null,

            show_hide_password_btn: false,
            hide_password: true,
            loading: false
        }
    },
    methods: {
        send() {
            this.loading = true;
            this.$store.dispatch('signin', {email: this.email, password: this.password})
                .then(() => router.push('/'))
                .catch((response) => this.$q.notify({
                    message: response.data.message,
                    icon: 'error_outline',
                }))
                .finally(() => this.loading = false)
        }
    },
    mounted() {
        this.$q.loading.hide();
    }
}
</script>

