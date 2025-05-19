<template>
  <Toast/>
  <div class="page-container">
    <div class="course-header">
      <h1>Курс: {{ courseData.name }}</h1>
      <div class="course-actions">
        <Button
            label="Скопировать код"
            v-if="isAuthor"
            @click="copyCode"
        />
        <Button
            v-if="isAuthor"
            label="Удалить курс"
            severity="danger"
            @click="deleteCourse"
        />
        <Button
            v-if="!isAuthor && isConnected"
            label="Покинуть курс"
            severity="danger"
            @click="leaveCourse"
        />
        <Button
            v-if="!isAuthor && !isConnected"
            label="Присоединиться"
            @click="subscribeCourse"
        />
      </div>
    </div>
    <ProgressBar v-if="!isAuthor"
                 :value="courseProgress"
                 style="min-height: 20px"/>
    <div>
      <div class="course-description">
        <h2>Описание курса</h2>
        <p>{{ courseData.description }}</p>
      </div>

      <div class="lessons-section">
        <div class="lessons-header">
          <h2>Уроки</h2>
          <Button
              icon="pi pi-plus"
              v-if="isAuthor"
              rounded
              size="small"
              @click="showCreateLessonPopup = true"
          />
        </div>
        <ul class="lessons-list">
          <li v-for="lesson in courseData.lessons" :key="lesson.id">
            <Lesson
                v-if="isAuthor"
                :lesson="lesson"
                :is-completed="isCompleted(lesson.id)"
                @update="fetchCourseData"
                @delete="deleteLesson"
            />
            <LessonForStudent
                v-else
                :lesson="lesson"
                :is-completed="isCompleted(lesson.id)"
                @update="fetchCourseData"
            />
          </li>
        </ul>
      </div>
    </div>
    <CreateLessonPopup
        v-model:visible="showCreateLessonPopup"
        :course-id="courseId"
        @updateData="fetchCourseData"
    />
  </div>
</template>

<script setup>
import {getErrorMessage} from '@/utils/ErrorHelper';
import {Button} from "primevue";
import apiClient from "@/api/index.js";
import {useRoute, useRouter} from "vue-router";
import {computed, onMounted, ref} from "vue";
import {loader} from '@/utils/loader';
import {Toast, useToast} from "primevue";
import CreateLessonPopup from "@/components/CreateLessonPopup.vue";
import Lesson from "@/components/Lesson.vue";
import ProgressBar from 'primevue/progressbar';
import api from "@/api/index.js";
import LessonForStudent from "@/components/LessonForStudent.vue";

const toast = useToast()
const route = useRoute()
const router = useRouter()
const courseId = route.query.courseId ? parseInt(route.query.courseId) : null

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

const lessonsDone = ref([]);

const courseProgress = computed(() => {
  const lessonsCount = courseData.value.lessons.length || 0;
  const lessonsDoneCount = lessonsDone.value.length;

  return lessonsCount > 0 ? Math.round((lessonsDoneCount / lessonsCount) * 100) : 0;
});

const isAuthor = ref(false)
const isConnected = ref(false)
const showCreateLessonPopup = ref(false)

const leaveCourse = async () => {
  try {
    await apiClient.post(`api/course/unsubscribe/${courseId}`);
    await fetchCourseData();
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при покидании курса',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  }
}

const subscribeCourse = async () => {
  try {
    await apiClient.post(`api/course/subscribe/${courseId}`);
    await fetchCourseData();
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка подписки на курс',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  }
}


const deleteCourse = async () => {
  loader.show();
  try {
    await apiClient.post(`api/course/delete/${courseId}`);
    await router.push({
      name: 'MyCoursesPage'
    })
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

const copyCode = () => {
  navigator.clipboard.writeText(courseData.value.code)
      .then(() => {
        toast.add({
          severity: 'success',
          summary: 'Код скопирован!',
          life: 4000
        });
      })
      .catch(() => {
        toast.add({
          severity: 'error',
          summary: 'Не удалось скопировать код.',
          life: 4000
        });
      });
}

const fetchCourseData = async () => {
  loader.show()
  try {
    const response = await apiClient.get(`/api/course?id=${courseId}`);
    courseData.value = response.data.data.course;
    isAuthor.value = response.data.data.isAuthor;
    isConnected.value = response.data.data.isConnected;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при получении курсов',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide()
  }
}

const deleteLesson = async (lessonId) => {
  loader.show();
  try {
    await apiClient.post(`/api/lesson/delete/${lessonId}`);
    await fetchCourseData()
    toast.add({
      severity: 'success',
      summary: 'Урок успешно удален!',
      life: 4000
    });
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при удалении урока',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide();
  }
}

const fetchLessonsDone = async () => {
  const lessonsDoneResponse = await api.get(`/api/lesson-user/my-passed/${courseId}`);
  lessonsDone.value = lessonsDoneResponse.data;
}

const isCompleted = ((lessonId) => {
  return lessonsDone.value.some(item => item.lesson.id === lessonId);
})


onMounted(async () => {
  if (courseId) {
    await fetchCourseData();
    await fetchLessonsDone();
  } else {
    alert('ID курса не указан');
  }
})
</script>

<style scoped>

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.course-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.lessons-section {
  margin-bottom: 20px;
}

.lessons-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.lessons-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
  list-style: none;
  padding: 0;
  margin: 0;
}
</style>