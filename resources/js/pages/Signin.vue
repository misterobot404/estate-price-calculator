<template>
  <div class="window-width window-height flex flex-center">
    <form @submit.prevent="signin" style="min-width: 430px">
      <q-img src="/images/logo-name.svg"/>
      <q-input outlined v-model="login" label="Логин" required class="q-mt-lg"/>
      <q-input outlined v-model="password" label="Пароль" type="password" required class="q-mt-sm"></q-input>
      <q-btn class="q-mt-md full-width" color="primary" label="Войти" type="submit" :loading="loading"/>
      <div class="text-center q-mt-md">
        <q-btn to="/signup" label="Регистрация" flat rounded color="primary" no-caps/>
      </div>
    </form>
  </div>
</template>

<script>
import router from "../router";

export default {
  data() {
    return {
      login: null,
      password: null,

      show_hide_password_btn: false,
      hide_password: true,
      loading: false
    }
  },
  methods: {
    signin() {
      this.loading = true;
      setTimeout(() => {
        router.push('/calculator');
        this.loading = false
      }, 1600)
    }
  }
}
</script>

<style scoped>
</style>

