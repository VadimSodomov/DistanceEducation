<template>
  <Toast/>
  <div class="page-container">
    <div class="courses-page-menu-container">
      <Fieldset legend="Поиск курса">
        <p class="m-0 find-course">
          <FloatLabel variant="on">
            <InputText
                v-model="findCode"
            />
            <label for="date">Код</label>
          </FloatLabel>

          <Button
              label="Найти"
              @click="findCourse"
              :loading="loadingFind"
              :disabled="isFindCourseDisabled"
          />
        </p>
      </Fieldset>
    </div>

    <div class="cards-container">
      <div v-for="course in courses" :key="course.course.id">
        <Card class="card" @click="">
          <template #title>{{ course.course.name }}</template>
          <template #content>
            <div style="height: 160px; overflow-y: auto; ">
              <p class="m-0">
                <b>Автор:</b>
                {{ course.course.author.name }}
              </p>
              <p class="m-0">
                <b>Описание:</b>
                {{ course.course.description }}
              </p>
            </div>

          </template>
          <template #footer>
            <div class="flex gap-4 mt-1">
              <Button
                  label="Перейти"
                  @click="openCourse(course.course.id)"
              />
              <Button
                  severity="danger"
                  variant="outlined"
                  icon="pi pi-sign-out"
                  @click="unsubscribeCourse(course.course.id)"
              />
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup>
import {ref, onMounted, computed} from 'vue';
import apiClient from "@/api/index.js";
import Button from 'primevue/button';
import {Card, useToast} from "primevue";
import {loader} from '@/utils/loader';
import {getErrorMessage} from "@/utils/ErrorHelper.js";
import {Toast} from "primevue";
import {useRouter} from "vue-router";
import {Fieldset} from "primevue";
import {FloatLabel} from "primevue";
import {InputText} from "primevue";

const router = useRouter()
const toast = useToast()
const courses = ref([]);
const findCode = ref('')
const loadingFind = ref(false)

const isFindCourseDisabled = computed(() => {
  return !findCode.value
})

const openCourse = async (courseId) => {
  await router.push({
    name: 'CoursePage',
    query: {courseId}
  })
}

const findCourse = async () => {
  loadingFind.value = true
  try {
    const response = await apiClient.get(`api/course?code=${findCode.value}`);
    const findId = response.data.data.course.id
    await router.push({
      name: 'CoursePage',
      query: {courseId: findId}
    })
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при поиске курса',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loadingFind.value = false
  }
}

const unsubscribeCourse = async (courseId) => {
  loader.show();
  try {
    await apiClient.post(`api/course/unsubscribe/${courseId}`);
    await fetchCourses()
    toast.add({
      severity: 'success',
      summary: 'Вы успешно покинули курс!',
      life: 4000
    });
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при покидании курса',
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
    courses.value = response.data.data.coursesUser
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
}

.find-course {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

:deep(.p-card-title) {
  background-color: var(--p-button-primary-background);
  padding: 20px;
  border-radius: var(--p-border-radius-xl) var(--p-border-radius-xl) 0 0;
  color: var(--p-content-background);
  height: 100px;
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