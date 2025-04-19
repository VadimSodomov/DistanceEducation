<template>
  <Toast/>
  <div class="auth-page">
    <Card style="width: 300px;">
      <template #title>
        <div class="main-title">
          <div :class="[{ 'active': isEntry }]" @click="isEntry = !isEntry">
            Вход
          </div>
          <div :class="[{ 'active': !isEntry }]" @click="isEntry = !isEntry">
            Регистрация
          </div>
        </div>
      </template>
      <template #content>
        <p class="m-0 content">
          <InputText
              v-if="!isEntry"
              id="name"
              v-model="authData.name"
              placeholder="Введите имя"
              required
          />

          <InputText
              v-model="authData.email"
              required
              type="email"
              id="email"
              placeholder="Введите email"
          />

          <InputText
              v-model="authData.password"
              type="password"
              id="password"
              placeholder="Введите пароль"
              required
          />
        </p>
      </template>
      <template #footer>
        <Button
            @click="handleLogin"
            class="w-full"
            style="margin-top: 10px"
            :disabled="disabledBtn"
            :loading="isLoading"
            :label="isEntry ? 'Войти' : 'Создать аккаунт'"
        />
      </template>
    </Card>
  </div>
</template>

<script setup>
import {Card, useToast} from "primevue";
import {computed, ref} from "vue";
import apiClient from "@/api/index.js";
import {InputText} from "primevue";
import {Button} from "primevue";
import {getErrorMessage} from "@/utils/ErrorHelper.js";
import {useRouter} from "vue-router";
import {Toast} from "primevue";
import store from "@/store/index.js";

const fetchCurrentUser = () => store.dispatch('fetchCurrentUser');

const toast = useToast()
const router = useRouter()
const isEntry = ref(true)
const isLoading = ref(false)

const authData = ref({
  name: '',
  email: '',
  password: ''
})

const disabledBtn = computed(() => {
  if (isEntry.value)
    return !(authData.value.email && authData.value.password && authData.value.password.length >= 5)
  else
    return !(authData.value.name && authData.value.email && authData.value.password && authData.value.password.length >= 5)
})

const handleLogin = async () => {
  isLoading.value = true;
  try {
    const endpoint = isEntry.value ? '/api/login' : '/api/register';
    const response = await apiClient.post(endpoint, {
      email: authData.value.email,
      password: authData.value.password,
      ...(!isEntry.value && {name: authData.value.name})
    });

    localStorage.setItem('jwt-token', response.data.token);
    await store.dispatch('fetchCurrentUser');
    await router.push({name: 'MyCoursesPage'});
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: getErrorMessage(error),
      life: 4000
    });
  } finally {
    isLoading.value = false;
  }
}

</script>

<style scoped>
.auth-page {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.main-title {
  display: flex;
  justify-content: space-around;
  font-size: 20px;
  margin-bottom: 20px;
  font-weight: 600;
  color: #333;
  cursor: pointer;

  .active {
    color: var(--p-button-primary-background);
    position: relative;

    &:after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -2px;
      width: 100%;
      height: 2px;
      background-color: var(--p-button-primary-background);
    }
  }
}

.content {
  display: flex;
  gap: 20px;
  flex-direction: column;
}

:deep(.p-card-body) {
  height: 320px;
  justify-content: space-between;
}
</style>