<template>
  <div class="page-container">
    <Sidebar/>
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
        <button class="create-course-btn" @click="openPopup">
          Создать курс
        </button>
      </div>
      <div class="courses-grid">
        <CourseCard
            v-for="course in filteredCourses"
            :key="course.id"
            :course="course"
        />
      </div>

      <CreateCoursePopup :isVisible="isPopupOpen" @close="closePopup"/>
    </div>
  </div>


</template>

<script>
import CourseCard from "../components/CourseCard.vue";
import Sidebar from "../components/Sidebar.vue";
import CreateCoursePopup from "../components/CreateCoursePopup.vue";

export default {
  components: {
    CourseCard,
    Sidebar,
    CreateCoursePopup
  },
  data() {
    // Пока для примера
    // 1 - курсы, на которые польз подписан, 2 - созданные курсы
    return {
      filterType: null,
      isPopupOpen: false,
      courses: [
        {
          id: 1,
          type: 1,
          name: 'Курс по Vue.js',
          author: 'Иван Иванов',
          description: 'Изучите основы Vue.js и создайте свои первые приложения.',
        },
        {
          id: 2,
          type: 1,
          name: 'Курс по React',
          author: 'Петр Петров',
          description: 'Освойте React и создавайте современные веб-приложения.',
        },
        {
          id: 3,
          type: 2,
          name: 'Курс по Angular',
          author: 'Я',
          description: 'Погрузитесь в мир Angular и создавайте мощные приложения.',
        },
        {
          id: 4,
          type: 2,
          name: 'Курс по Node.js',
          author: 'Я',
          description: 'Научитесь создавать серверные приложения на Node.js.',
        },
      ],
    };
  },
  computed: {
    filteredCourses() {
      if (this.filterType === null) {
        return this.courses;
      }
      return this.courses.filter(course => course.type === this.filterType);
    }
  },
  methods: {
    setFilter(type) {
      this.filterType = type;
    },
    openPopup() {
      this.isPopupOpen = !this.isPopupOpen;
    },
    closePopup() {
      this.isPopupOpen = false;
    },
  }
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