<template>
  <div class="container">
    <div class="sidebar">
      <div class="sidebar-header">
        <h2>Наш сайтик)</h2>
      </div>
      <ul class="sidebar-menu">
        <li>
          <Button @click="ToMainPage"
                  :fullWidth="true"
                  textAlign="left">Все курсы
          </Button>
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
        <i class="fas fa-sign-out-alt" ref="tooltip" @click="logout"></i>
      </div>
    </div>
  </div>
</template>

<script>
import CoursesSection from './CoursesSection.vue';
import Button from './Button.vue';

import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import axios from "axios";
import {getErrorMessage} from "../utils/ErrorHelper";

export default {
  components: {
    CoursesSection,
    Button
  },
  props: {
    studentCourses: {
      type: Array,
      default: () => [],
    },
    teacherCourses: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      username: ''
    };
  },
  mounted() {
    tippy(this.$refs.tooltip, {
      content: 'Выйти',
    });
  },
  async created() {
    try {
        const response = await axios.get(`api/user`);
        this.username = response.data.data.name
      } catch (error) {
        alert(getErrorMessage(error));
      }
  },

  methods: {
    ToMainPage() {
      window.location.href = '/';
    },
    async logout() {
      try {
        window.location.href = '/logout';
      } catch (error) {
        alert('Ошибка при выходе из системы');
      }
    },

  }
};
</script>

<style scoped>
.container {
  flex: 0;
  display: flex;
  height: 100vh; /* Высота контейнера равна высоте экрана */
  width: 20%;
  min-width: 250px;
}

.sidebar {
  position: relative;
  background-color: white;
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
  display: flex; /* Добавляем flex для выравнивания иконки и текста */
  align-items: center; /* Выравниваем элементы по центру вертикально */
  justify-content: center; /* Выравниваем элементы по центру горизонтально */
  gap: 15px; /* Расстояние между иконкой и текстом */
}

.sidebar-footer i {
  font-size: 16px; /* Размер иконки */
  color: #2e2d2d; /* Цвет иконки */
  cursor: pointer; /* Курсор в виде указателя */
}

.sidebar-footer i:hover {
  opacity: 0.8; /* Эффект при наведении */
}
</style>