<template>
    <Loader/>
  <Toolbar
      v-if="route.name !== 'LoginPage'"
      style="border-radius: 3rem; padding: 1rem 1rem 1rem 1.5rem; margin-top: 20px">
    <template #center>
      <div class="flex items-center gap-2">
        <Button
            label="Мои курсы"
            text
            plain
            @click="goToPage('MyCoursesPage')"
        />
        <Button
            label="Курсы в подписках"
            text
            plain
            @click="goToPage('SubscribeCoursesPage')"
        />
      </div>
    </template>

    <template #end>
      <div class="flex items-center gap-2">
        <Button
            icon="pi pi-user"
            :label="userName"
            severity="secondary"
            @click="toggle"
        />
        <Menu
            id="user_menu"
            ref="menu"
            :model="items"
            :popup="true"
        />
      </div>
    </template>
  </Toolbar>
  <router-view />
</template>

<script setup>
import {computed, onMounted, ref} from 'vue';
import {useRoute} from 'vue-router';
import {Button, Toolbar} from "primevue";
import Loader from "@/components/Loader.vue";
import {loader} from "@/utils/loader.js";
import router from "@/router/index.js";
import {Menu} from "primevue";
import store from "@/store/index.js";

const route = useRoute();
const darkMode = ref(false);
const menu = ref();
const toggle = (event) => {
  menu.value.toggle(event);
};

const userName = computed(() => {
  return store.state.user?.name || 'Профиль';
});

const items = computed(() => [
  {
    label: 'Сменить тему',
    icon: darkMode.value ? 'pi pi-moon' : 'pi pi-sun',
    command: () => switchTheme()
  },
  {
    label: 'Выйти',
    icon: 'pi pi-sign-out',
    command: () => {
      store.dispatch('logout');
      router.push({name: 'LoginPage'});
    }
  }
]);

const goToPage = async (page) => {
  loader.show()
  await router.push({name: page});
  loader.hide()
}

const switchTheme = () => {
  darkMode.value = !darkMode.value;
  document.documentElement.classList.toggle('my-app-dark')
  localStorage.setItem('theme', darkMode.value ? 'dark' : 'light');
};

onMounted(async () => {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    darkMode.value = true;
    document.documentElement.classList.toggle('my-app-dark');
  }
});
</script>
