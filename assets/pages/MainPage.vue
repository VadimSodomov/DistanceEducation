<template>
  <div class="main-page">
    <Sidebar />
    <div class="content">
      <AllCoursesPage/>
    </div>
  </div>
</template>

<script>
import Sidebar from '../components/Sidebar.vue';
import AllCoursesPage from "./AllCoursesPage.vue";

export default {
  components: {
    Sidebar,
    AllCoursesPage
  },
};
</script>

<style scoped>
.main-page {
  display: flex;
  height: 100vh;
  width: 100%;
}

.content {
  flex: 1;
  width: 80%;
  min-width: 300px;
}
</style>