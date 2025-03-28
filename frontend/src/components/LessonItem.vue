<template>
  <div class="lesson-item" @click="toggleExpand">
    <div class="lesson-header">
      <span class="lesson-name">Урок {{ lesson.id }}. {{ lesson.name }}</span>
      <button class="edit-button" @click.stop="editLesson">
        <i class="fas fa-edit"></i>
      </button>
    </div>

    <div v-if="isExpanded" class="lesson-details">
      <div class="section">
        <h3>Учебные материалы</h3>
        <p>{{ lesson.materials || 'тут файлики' }}</p>
      </div>

      <div class="section">
        <h3>Домашнее задание</h3>
        <p>{{ lesson.homework || 'тут дз' }}</p>
      </div>

      <div class="section">
        <h3>Статистика</h3>
        <p>{{ lesson.statistics || 'тут статистика' }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    lesson: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      isExpanded: false,
    };
  },
  methods: {
    editLesson() {
      this.$emit('edit', this.lesson.id);
    },
    toggleExpand() {
      this.isExpanded = !this.isExpanded;
      this.$el.classList.toggle('expanded', this.isExpanded);
    },
  },
};
</script>

<style scoped>
.lesson-item {
  display: flex;
  flex-direction: column;
  padding: 10px;
  border-radius: 14px;
  background: #babfea;
  cursor: pointer;
  transition: background-color 0.3s;
}

.lesson-item:hover {
  background-color: #d2d6ef;
}

.lesson-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.lesson-name {
  font-size: 16px;
  color: #2e2d2d;
}

.edit-button {
  background: none;
  border: none;
  color: #6D7CF2;
  cursor: pointer;
  font-size: 16px;
  padding: 5px;
}

.edit-button:hover {
  opacity: 0.8;
}

.lesson-details {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #2e2d2d;
  width: 100%;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease-out;
}

.lesson-item.expanded .lesson-details {
  max-height: 500px;
}

.section {
  margin-bottom: 15px;
}

.section h3 {
  font-size: 14px;
  color: #2e2d2d;
  margin-bottom: 5px;
}

.section p {
  font-size: 14px;
  color: #2e2d2d;
  margin: 0;
}
</style>