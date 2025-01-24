<template>
  <div class="page-container">
    <Sidebar/>
    <div class="page">
        <div class="course-header">
          <h1 class="page-title">{{ course.name }}</h1>
          <div class="course-actions">
            <div v-if="isAuthor" class="course-code-container">
              <span class="course-code-label">Код курса:</span>
              <div class="course-code">{{ course.code }}</div>
            </div>
            <Button
                v-if="isAuthor"
                @click="deleteCourse"
            >
            Удалить курс
            </Button>
            <Button
                v-else
                @click="leaveCourse"
            >
            Покинуть курс
            </Button>
          </div>
        </div>
      <div class="course-page">
        <!-- Описание курса -->
        <div class="course-description">
          <h2>Описание курса</h2>
          <p>{{ course.description }}</p>
        </div>

        <!-- Раздел с уроками -->
        <div class="lessons-section">
          <div class="lessons-header">
            <h2>Уроки</h2>
            <Button
                v-if="isAuthor"
                class="add-lesson-btn"
                @click="addLesson"
            >
              +
            </Button>
          </div>
          <ul class="lessons-list">
            <li v-for="lesson in course.lessons" :key="lesson.id">
              <LessonItem
                  :lesson="lesson"
                  @edit="handleEditLesson"
              />
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { getErrorMessage } from '../utils/ErrorHelper';
import Sidebar from "../components/Sidebar.vue";
import Button from "../components/Button.vue";
import LessonItem from "../components/LessonItem.vue";

export default {
  components: {Sidebar, Button, LessonItem},
  data() {
    return {
      course: {
        id: null,
        name: 'Курс по Vue.js',
        description: 'Тут будет описание курса',
        code: '111111',
        lessons: [
          { id: 1, name: 'Введение в Vue.js' },
          { id: 2, name: 'Компоненты и пропсы' },
          { id: 3, name: 'Состояние и методы' },],
        isAuthor: true,
      },
    };
  },
  computed: {
    isAuthor() {
      return this.course.isAuthor;
    },
  },
  async created() {
    await this.fetchCourseData();
  },
  methods: {
    async fetchCourseData() {
      try {
        const response = await axios.get(`/course?id=${this.$route.params.id}`);
        this.course = response.data.data.course;
      } catch (error) {
        alert(getErrorMessage(error));
      }
    },
    async deleteCourse() {
      try {
        await axios.post(`/course/delete/${this.course.id}`);
        this.$router.push('/courses');
      } catch (error) {
        alert(getErrorMessage(error));
      }
    },
    async leaveCourse() {
      try {
        await axios.post(`/course/unsubscribe/${this.course.id}`);
        this.$router.push('/courses');
      } catch (error) {
        alert(getErrorMessage(error));
      }
    },
    addLesson() {
    },
  },
};
</script>

<style scoped>
.course-page {
  padding: 10px;
}

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

.course-code-container {
  display: flex;
  align-items: center;
  gap: 5px;
  background-color: #babfea;
  padding: 10px;
  border-radius: 14px;
  opacity: 0.7;
}

.course-code-label {
  font-size: 14px;
  color: #2e2d2d
}

.course-code {
  font-weight: bold;
  color: #2e2d2d
}
.course-description {
}

.lessons-section {
  margin-bottom: 20px;
}

.lessons-header {
  display: flex;
  align-items: center;
}

.add-lesson-btn {
  padding: 5px 10px;
  border: none;
  border-radius: 50%;
  background-color: #6d7cf2;
  color: white;
  cursor: pointer;
  font-size: 16px;
  margin-left: 15px;
}

.lessons-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.lesson-item {
  margin-bottom: 10px;
}

.lesson-link {
  text-decoration: none;
  color: #6d7cf2;
}

.lesson-link:hover {
  text-decoration: underline;
}
</style>