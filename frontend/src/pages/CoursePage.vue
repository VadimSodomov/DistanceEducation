<template>
  <div class="page-container">
    <Sidebar :studentCourses="coursesUser" :teacherCourses="coursesAuthored"/>
    <div class="page">
      <div class="course-header">
        <h1 class="page-title">{{ course.name }}</h1>
        <div class="course-actions">
          <div v-if="isAuthor" class="course-code-container">
            <div class="course-code" @click="copyCode">Скопировать код</div>
          </div>
          <Button
              v-if="isAuthor"
              @click="deleteCourse"
              class="delete-course"
          >
            Удалить курс
          </Button>
          <Button
              v-if="!isAuthor && isConnected"
              @click="leaveCourse"
              class="delete-course"
          >
            Покинуть курс
          </Button>
          <Button
              v-if="!isAuthor && !isConnected"
              @click="subscribeCourse"
              class="subs-course"
          >
            Присоединиться
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
import {getErrorMessage} from '@/utils/ErrorHelper';
import Sidebar from "@/components/Sidebar.vue";
import Button from "@/components/Button.vue";
import LessonItem from "@/components/LessonItem.vue";
import apiClient from "@/api/index.js";

export default {
  components: {Sidebar, Button, LessonItem},
  data() {
    return {
      course: {
        id: null,
        name: '',
        description: '',
        code: '',
        lessons: [],
      },
      isAuthor: false,
      isConnected: false,
      coursesUser: [],
      coursesAuthored: [],
    };
  },
  async created() {
    await this.fetchCourses();

    const courseId = this.$route.params.id;
    if (courseId) {
      await this.fetchCourseData(courseId); // Загружаем данные курса
    } else {
      alert('ID курса не указан');
    }
  },
  methods: {
    copyCode() {
      navigator.clipboard.writeText(this.courseCode)
          .then(() => {
            alert('Код скопирован!');
          })
          .catch(() => {
            alert('Не удалось скопировать код.');
          });
    },

    async fetchCourses() {
      try {
        const response = await apiClient.get('api/course/all');
        // Извлекаем курсы из CourseUser
        const coursesUser = response.data.data.coursesUser.map(courseUser => courseUser.course);

        // Курсы, созданные пользователем
        const coursesAuthored = response.data.data.coursesAuthored;

        this.coursesUser = coursesUser;
        this.coursesAuthored = coursesAuthored;
      } catch (error) {
        alert('Ошибка при загрузке курсов');
      }
    },
    async fetchCourseData(courseId) { // Добавляем параметр courseId
      try {
        const response = await apiClient.get(`/api/course?id=${courseId}`);
        this.course = response.data.data.course; // Обновляем данные курса
        this.isAuthor = response.data.data.isAuthor;
        this.isConnected = response.data.data.isConnected;

      } catch (error) {
        alert(getErrorMessage(error));
      }
    },
    async deleteCourse() {
      try {
        await apiClient.post(`api/course/delete/${this.course.id}`);
        this.$router.push({name: 'AllCoursesPage'});
      } catch (error) {
        alert(getErrorMessage(error));
      }
    },
    async leaveCourse() {
      try {
        await apiClient.post(`api/course/unsubscribe/${this.course.id}`);
        this.isConnected = false;
        this.$router.push({name: 'AllCoursesPage'});
      } catch (error) {
        console.log('error', error)
        alert(getErrorMessage(error));
      }
    },

    async subscribeCourse() {
      try {
        await apiClient.post(`api/course/subscribe/${this.course.id}`);
        this.isConnected = true;
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

  .delete-course{
    background-color: #ed7777;

    &:hover{
      background-color: #f1b0b0;
    }
  }

  .subs-course{
    background-color: #6fc371;

    &:hover{
      background-color: #b1ddb1;
    }
  }
}

.course-code-container {
  display: flex;
  align-items: center;
  gap: 5px;
  background-color: #babfea;
  padding: 10px;
  border-radius: 14px;
  opacity: 0.7;
  cursor: pointer;

  &:hover {
    background-color: #cfd2e3;
  }
}

.course-code {
  font-weight: bold;
  color: #2e2d2d;
  cursor: pointer;
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