<template>
  <div class="main-page">
    <Sidebar />
    <div class="content">
      <h1>тут типа страницы</h1>
    </div>
  </div>
</template>

<script>
import Sidebar from '../components/Sidebar.vue'; // Импортируем Sidebar

export default {
  components: {
    Sidebar,
  },
};
</script>

<style scoped>
.main-page {
  display: flex;
  height: 100vh;
}

.content {
  flex: 1;
}
</style>