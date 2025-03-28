<template>
  <div class="page-container">
    <Sidebar :studentCourses="coursesUser" :teacherCourses="coursesAuthored"/>
    <div class="page">
      <h1 class="page-title">Курсы</h1>
      <div class="courses-page-menu-container">
        <div class="filter-buttons">
          <button
              @click="setFilter(null)"
              :class="['filter-btn', { 'active': filterType === null}]"
          >
            Все курсы
          </button>
          <button
              @click="setFilter(1)"
              :class="['filter-btn', { 'active': filterType === 1}]"
          >
            Курсы в подписках
          </button>
          <button
              @click="setFilter(2)"
              :class="['filter-btn', { 'active': filterType === 2}]"
          >
            Созданные курсы
          </button>
        </div>
        <div class="filter-buttons">
          <button class="create-course-btn" @click="openPopupFind">
            Найти курс
          </button>
          <button class="create-course-btn" @click="openPopup">
            Создать курс
          </button>
        </div>

      </div>
      <div class="courses-grid">
        <CourseCard
            v-for="course in filteredCourses"
            :key="course.id"
            :course="course"
            :course-url="generateCourseUrl(course.id)"
        />
      </div>

      <CreateCoursePopup :isVisible="isPopupOpen" @close="closePopup"/>
      <FindToCoursePopup :isVisible="isPopupOpenFind" @closeFind="closePopupFind"/>
    </div>
  </div>


</template>

<script>
import CourseCard from "@/components/CourseCard.vue";
import Sidebar from "@/components/Sidebar.vue";
import CreateCoursePopup from "@/components/CreateCoursePopup.vue";
import FindToCoursePopup from "@/components/FindToCoursePopup.vue";
import apiClient from "@/api/index.js";

export default {
  components: {
    CourseCard,
    Sidebar,
    CreateCoursePopup,
    FindToCoursePopup
  },
  data() {
    return {
      filterType: null,
      isPopupOpen: false,
      isPopupOpenFind: false,
      courses: [],
      coursesUser: [],
      coursesAuthored: [],
    };
  },
  computed: {
    filteredCourses() {
      if (this.filterType === null) {
        return this.courses;
      } else if (this.filterType === 1) {
        return this.coursesUser;
      } else if (this.filterType === 2) {
        return this.coursesAuthored;
      }
      return [];
    }
  },
  methods: {
    async fetchCourses() {
      try {
        const response = await apiClient.get('api/course/all');
        // Извлекаем курсы из CourseUser
        const coursesUser = response.data.data.coursesUser.map(courseUser => courseUser.course);

        // Курсы, созданные пользователем
        const coursesAuthored = response.data.data.coursesAuthored;

        // Объединяем оба списка курсов
        this.coursesUser = coursesUser;
        this.coursesAuthored = coursesAuthored;

        this.coursesUser = coursesUser.map(course => {
          return {
            ...course,
            isAuthored: false,
          };
        });

        this.coursesAuthored = coursesAuthored.map(course => {
          return {
            ...course,
            isAuthored: true,
          };
        });

        this.courses = [...this.coursesUser, ...this.coursesAuthored];

        console.log('Данные с бэкэнда:', response.data);
        console.log('Курсы из CourseUser:', coursesUser);
        console.log('Курсы, созданные пользователем:', coursesAuthored);
      } catch (error) {
        alert(getErrorMessage(error));
      }
    },
    //TODO: Вынести в какой-нибудь хелпер, повторяется в CoursesSection.vue
    generateCourseUrl(courseId) {
      return `/course/${courseId}`; // Генерация URL на основе ID курса
    },
    setFilter(type) {
      this.filterType = type;
    },
    openPopup() {
      this.isPopupOpen = !this.isPopupOpen;
    },
    closePopup() {
      this.isPopupOpen = false;
    },

    openPopupFind() {
      this.isPopupOpenFind = !this.isPopupOpenFind;
    },
    closePopupFind() {
      this.isPopupOpenFind = false;
    },
  },
  async created() {
    await this.fetchCourses();
  },
};
</script>

<style scoped>
.courses-page-menu-container {
  display: flex;
  margin: 10px;
  justify-content: space-between;
}

.filter-buttons {
  display: flex;
  gap: 10px;

  .filter-btn {
    cursor: pointer;
    padding: 10px 15px;
    border-radius: 10px;
    background-color: white;
    border: 1px solid #6D7CF2;
    color: black;

    &:hover {
      background-color: #ECECEC;
    }

    &.active {
      color: white;
      background-color: #6D7CF2;

      &:hover {
        background-color: #8C99FF;
      }
    }
  }
}

.create-course-btn {
  padding: 5px 10px;
  border-radius: 10px;
  border: 1px solid #6D7CF2;
  color: white;
  background-color: #6D7CF2;
  cursor: pointer;

  &:hover {
    background-color: #8C99FF;
  }
}

.courses-grid {
  display: flex;
  flex-wrap: wrap;
}
</style>