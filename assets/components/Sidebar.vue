<template>
  <div class="container">
    <div class="sidebar">
      <div class="sidebar-header">
        <h3>Наш сайтик)</h3>
      </div>
      <ul class="sidebar-menu">
        <li>
          <Button @click="ToMainPage"
                  :fullWidth="true"
                  textAlign="left">Все курсы</Button>
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
  mounted() {
    tippy(this.$refs.tooltip, {
      content: 'Выйти',
    });
  },
  methods: {
    ToMainPage() {
      alert('Кнопка нажата!');
    },
    async logout() {
      try {
        // Отправляем POST-запрос на /logout
        const response = await fetch('/logout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
        });

        if (response.ok) {
          window.location.href = '/login';
        } else {
          alert('Ошибка при выходе из системы');
        }
      } catch (error) {
        console.error('Ошибка:', error);
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