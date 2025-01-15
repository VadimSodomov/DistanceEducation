<template>
  <div class="container">
    <div class="sidebar">
      <div class="sidebar-header">
        <h3>Название/лого</h3>
      </div>
      <ul class="sidebar-menu">
        <li>
          <Button @click="ToMainPage"
                  :fullWidth="true"
                  textAlign="left">Главная</Button>
        </li>
        <li>
          <CoursesSection
              title="Курсы, слушателем которых я являюсь"
              :courses="studentCourses"
          />
        </li>
        <li>
          <CoursesSection
              title="Курсы, которые я преподаю"
              :courses="teacherCourses"
          />
        </li>
      </ul>
      <div class="sidebar-footer">
        <span>{{ username }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import CoursesSection from './CoursesSection.vue';
import Button from './Button.vue';

export default {
  components: {
    CoursesSection,
    Button
  },
  data() {
    return {
      username: 'Имя пользователя',
      studentCourses: [
        { id: 1, name: 'Курс 1', link: '/course/1' },
        { id: 2, name: 'Курс 2', link: '/course/2' },
      ],
      teacherCourses: [
        { id: 3, name: 'Курс A', link: '/course/A' },
        { id: 4, name: 'Курс B', link: '/course/B' },
      ],
    };
  },
  methods: {
    ToMainPage() {
      alert('Кнопка нажата!');
    },
  }
};
</script>

<style scoped>
.container {
  display: flex;
  height: 100vh; /* Высота контейнера равна высоте экрана */
}

.sidebar {
  position: relative;
  width: 20%;
  background-color: whitesmoke;
  color: #2e2d2d;
  padding: 20px;
  margin: 15px;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.sidebar-header h3 {
  margin: 0;
  padding-bottom: 20px;
  border-bottom: 1px solid #34495e;
  font-size: 16px;
}

.sidebar-menu {
  list-style: none;
  padding: 0;
}

.sidebar-menu li {
  margin: 15px 0;
}

.sidebar-footer {
  position: absolute;
  bottom: 20px;
  left: 20px;
  right: 20px;
  text-align: center;
  padding-top: 10px;
  border-top: 1px solid #34495e;
}
</style>