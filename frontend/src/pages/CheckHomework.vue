<template>
  <Toast/>
  <div class="page-container">
    <div class="check-header">
      <div class="check-header__left">
        <h1>Урок: {{ lessonName }}</h1>
        <h2>Курс: {{ courseData.name }}</h2>
      </div>
      <div class="check-header__right">
        <p class="deadline-label">Дедлайн</p>
        <p class="deadline-date">{{ formatDeadline(lessonDeadline) }}</p>
      </div>
    </div>
    <ProgressBar :value="checkHomeworkProgress" style="min-height: 20px; margin-top: 10px"/>
    <h2 v-if="homeworksNotCheckedList.length">Непроверенные</h2>
    <ul class="homeworks-list">
      <li v-for="homework in homeworksNotCheckedList" :key="homework.id">
        <HomeworkCardForCheck
            :homework="homework"
            @rate="saveMark"
            @unrated="deleteMark"/>
      </li>
    </ul>
    <h2 v-if="homeworksCheckedList.length">Проверенные</h2>
    <ul class="homeworks-list">
      <li v-for="homework in homeworksCheckedList" :key="homework.id">
        <HomeworkCardForCheck
            :homework="homework"
            :is-checked="true"
            @rate="saveMark"
            @unrated="deleteMark"/>
      </li>
    </ul>
  </div>
</template>

<script setup>
import {Toast, useToast} from "primevue";
import ProgressBar from 'primevue/progressbar';
import Card from 'primevue/card';
import {loader} from '@/utils/loader';
import {computed, defineProps, onMounted, ref} from "vue";
import {useRoute} from "vue-router";
import apiClient from "@/api/index.js";
import {getErrorMessage} from "@/utils/ErrorHelper.js";
import HomeworkCardForCheck from "@/components/HomeworkCardForCheck.vue";

const toast = useToast()
const route = useRoute()

const courseId = computed(() => Number(route.query.courseId));
const lessonId = computed(() => Number(route.query.lessonId));

const checkHomeworkProgress = computed(() =>
    100 * homeworksCheckedList.value.length/(homeworksCheckedList.value.length + homeworksNotCheckedList.value.length)
);

const courseData = ref({
  id: null,
  name: '',
  description: '',
  code: '',
  lessons: [],
  author: {
    id: null,
    name: ""
  }
})

const homeworksCheckedList = ref([])
const homeworksNotCheckedList = ref([])

const currentLesson = computed(() =>
    courseData.value.lessons.find(lesson => lesson.id === lessonId.value)
);

const lessonName = computed(() => currentLesson.value?.name ?? '');
const lessonDeadline = computed(() => currentLesson.value?.hwDeadline ?? '');

const formatDeadline = (isoString) => {
  if (!isoString) return '';

  const date = new Date(isoString);
  return new Intl.DateTimeFormat('ru-RU', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'UTC'
  }).format(date);
};

const fetchCourseData = async () => {
  loader.show()
  try {
    const response = await apiClient.get(`/api/course?id=${courseId.value}`);
    courseData.value = response.data.data.course;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при получении курса',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide()
  }
}

const fetchCourseHomeworks = async () => {
  loader.show()
  try {
    const response = await apiClient.get(`/api/lesson-user/all-passed/${lessonId.value}`);
    homeworksCheckedList.value = response.data.checked;
    homeworksNotCheckedList.value = response.data.notChecked;
    console.log(homeworksCheckedList.value)
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при получении списка работ',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide()
  }
}

const saveMark = async ({ mark, homework_id, onComplete }) => {
  loader.show()
  try {
    await apiClient.post(`/api/lesson-user/rate/${homework_id}`, {
      score: mark
    });
    await fetchCourseHomeworks();
    onComplete?.(true);
    toast.add({
      severity: 'success',
      summary: 'Оценка успешно поставлена!',
      life: 4000
    });
  } catch (error) {
    onComplete?.(false);
    toast.add({
      severity: 'error',
      summary: 'Ошибка при выставлении оценки!',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide()
  }
}

const deleteMark = async ({ homework_id, onComplete }) => {
  loader.show()
  try {
    await apiClient.post(`/api/lesson-user/unrated/${homework_id}`);
    await fetchCourseHomeworks();
    onComplete?.(true);
    toast.add({
      severity: 'success',
      summary: 'Оценка успешно удалена!',
      life: 4000
    });
  } catch (error) {
    onComplete?.(false);
    toast.add({
      severity: 'error',
      summary: 'Ошибка при удалении оценки!',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide()
  }
}

onMounted(async () => {
  await fetchCourseData();
  await fetchCourseHomeworks();
});
</script>

<style scoped>
.check-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.check-header__left h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.check-header__left h2 {
  margin: 4px 0 0;
  font-size: 18px;
  font-weight: 500;
  color: #555;
}

.check-header__right {
  text-align: right;
}

.deadline-label {
  margin: 0;
  font-size: 14px;
  color: #999;
}

.deadline-date {
  margin: 2px 0 0;
  font-size: 16px;
  font-weight: 600;
  color: #e53935;
}

.homeworks-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
  list-style: none;
  padding: 0;
  margin: 0;
}
</style>