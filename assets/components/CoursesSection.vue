<template>
  <div class="courses-section">
    <div class="section-header" @click="toggleSection">
      <span>{{ title }}</span>
      <span class="arrow">{{ isExpanded ? '▲' : '▼' }}</span>
    </div>
    <ul v-if="isExpanded" class="courses-list">
      <li v-for="course in courses" :key="course.id">
        <a :href="generateCourseUrl(course.id)">{{ course.name }}</a>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      required: true,
    },
    courses: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      isExpanded: false,
    };
  },
  methods: {
    toggleSection() {
      this.isExpanded = !this.isExpanded;
    },
    generateCourseUrl(courseId) {
      return `/course?id=${courseId}`; // Генерация URL на основе ID курса
    },
  },
};
</script>

<style scoped>
.courses-section {
  margin-bottom: 15px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background-color: #6D7CF2;
  color: white;
  border-radius: 14px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.section-header:hover {
  background-color: #8C99FF;
}

.arrow {
  font-size: 12px;
}

.courses-list {
  list-style: none;
  padding: 0;
  margin-top: 10px;
}

.courses-list li {
  margin: 5px 0;
}

.courses-list a {
  color: white;
  text-decoration: none;
  display: block;
  padding: 5px 10px;
  border-radius: 14px;
  background-color: rgba(109, 124, 242, 0.8);
  transition: background-color 0.3s;
}

.courses-list a:hover {
  background-color: #a1acff;
  cursor: pointer;
}
</style>