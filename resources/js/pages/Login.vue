<template>
    <div class="window-width window-height flex flex-center">
        <div class="auth-block" style="min-width: 420px">
            <q-img src="/images/logo-name.svg"/>
            <q-input outlined v-model="login" label="Логин" class="q-mt-lg"/>
            <q-input outlined v-model="password" label="Пароль" class="q-mt-md"/>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            login: null,
            password: null
        }
    },
    methods: {
        auth() {

        }
    }
}
</script>

<style scoped>
</style>

