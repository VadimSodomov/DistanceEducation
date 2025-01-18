<template>
  <div class="all-courses">
    <h1>Все курсы</h1>
    <div class="courses-grid">
      <CourseCard
          v-for="course in courses"
          :key="course.id"
          :course="course"
      />
    </div>
  </div>
</template>

<script>
import CourseCard from "../components/CourseCard.vue";

export default {
  components: {
    CourseCard
  },
  data() {
    // Пока для примера
    return {
      courses: [
        {
          id: 1,
          name: 'Курс по Vue.js',
          author: 'Иван Иванов',
          description: 'Изучите основы Vue.js и создайте свои первые приложения.',
        },
        {
          id: 2,
          name: 'Курс по React',
          author: 'Петр Петров',
          description: 'Освойте React и создавайте современные веб-приложения.',
        },
        {
          id: 3,
          name: 'Курс по Angular',
          author: 'Сергей Сергеев',
          description: 'Погрузитесь в мир Angular и создавайте мощные приложения.',
        },
        {
          id: 4,
          name: 'Курс по Node.js',
          author: 'Алексей Алексеев',
          description: 'Научитесь создавать серверные приложения на Node.js.',
        },
      ],
    };
  }
};
</script>

<style scoped>
.all-courses {
  height: 100vh;
  padding: 20px;
  margin: 15px;
  background: white;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

h1{
  margin: 10px 0 5px 10px;
  color: #2e2d2d;
}

.courses-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}
</style>