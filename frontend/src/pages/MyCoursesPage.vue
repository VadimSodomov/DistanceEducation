<template>
  <Toast/>
  <div class="page-container">
    <div class="courses-page-menu-container">
      <Button
          label="Создать курс"
          @click="showCreateCoursePopup = true"
      />
    </div>
    <div class="cards-container">
      <div v-for="course in courses" :key="course.id">
        <Card class="card" @click="openCourse(course.id)">
          <template #title>{{ course.name }}</template>
          <template #content>
            <p class="m-0">
              {{ course.description }}
            </p>
          </template>
          <template #footer>
            <div class="flex gap-4 mt-1">
              <Button
                  severity="danger"
                  variant="outlined"
                  :icon="PrimeIcons.PLUS"
                  @click="deleteCourse(course.id)"
              />
            </div>
          </template>
        </Card>
      </div>
    </div>
    <CreateCoursePopup
        v-model:visible="showCreateCoursePopup"
        @updateData="fetchCourses"
    />
  </div>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import CreateCoursePopup from "@/components/CreateCoursePopup.vue";
import apiClient from "@/api/index.js";
import Button from 'primevue/button';
import {Card, useToast} from "primevue";
import {loader} from '@/utils/loader';
import {getErrorMessage} from "@/utils/ErrorHelper.js";
import {Toast} from "primevue";
import {useRouter} from "vue-router";
import {PrimeIcons} from '@primevue/core/api';

const router = useRouter()
const toast = useToast()
const courses = ref([]);
const showCreateCoursePopup = ref(false)

const openCourse = async (courseId) => {
  await router.push({
    name: 'CoursePage',
    query: {courseId}
  })
}

const deleteCourse = async (courseId) => {
  loader.show();
  try {
    await apiClient.post(`api/course/delete/${courseId}`);
    await fetchCourses()
    toast.add({
      severity: 'success',
      summary: 'Курс успешно удален!',
      life: 4000
    });
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при удалении курса',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide();
  }
}

const fetchCourses = async () => {
  loader.show();
  try {
    const response = await apiClient.get('api/course/all');
    courses.value = response.data.data.coursesAuthored

  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка получения курсов',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide();
  }
};

onMounted(async () => {
  await fetchCourses();
});
</script>

<style scoped>
.courses-page-menu-container {
  display: flex;
  margin: 10px;
  justify-content: space-between;
}

.cards-container {
  margin-top: 20px;
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  overflow: auto;
  max-height: 70vh;
  scrollbar-width: thin;
}

.card {
  margin: 10px;
  width: 210px;
  display: flex;
  cursor: pointer;
}

:deep(.p-card-title) {
  background-color: var(--p-button-primary-background);
  padding: 20px;
  border-radius: var(--p-border-radius-xl) var(--p-border-radius-xl) 0 0;
  color: var(--p-content-background);
  height: 100px;

  &:hover {
    background-color: var(--p-primary-hover-color);
  }
}

:deep(.p-card-body) {
  padding: 0;
}

:deep(.p-card-content) {
  padding: 0 20px 20px 20px;
}

:deep(.p-card-footer) {
  padding: 0 20px 20px 20px;
}
</style>