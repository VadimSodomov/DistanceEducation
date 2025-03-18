<template>
  <div class="course-card">
    <div class="course-card-top">
      <div class="course-info">
        <a :href="courseUrl" class="course-title">{{ course.name }}</a>
        <p class="course-author">{{ course.author.name }}</p>
      </div>
      <button v-if="!course.isAuthored" class="leave-course-button" @click="leaveCourse" ref="tooltip">
        <i class="fas fa-sign-out-alt"></i>
      </button>
      <button v-else class="leave-course-button" @click="deleteCourse" ref="leavetooltip">
        &times;
      </button>
    </div>

    <div class="course-card-bottom">
      <p>{{ course.description }}</p>
    </div>
  </div>
</template>

<script>
import tippy from 'tippy.js';
import axios from 'axios'
import {getErrorMessage} from '../utils/ErrorHelper';
import 'tippy.js/dist/tippy.css';

export default {
  props: {
    course: {
      type: Object,
      required: true,
    },
    courseUrl: {
      type: String,
      required: true,
    },
  },
  mounted() {
    tippy(this.$refs.tooltip, {
      content: 'Покинуть курс',
    });
    tippy(this.$refs.leavetooltip, {
      content: 'Удалить курс',
    });
  },
  methods: {
    async leaveCourse() {
      try {
        await axios.post(`api/course/unsubscribe/${this.course.id}`);
        window.location.href = '/';
      } catch (error) {
        console.log('error', error)
        alert(getErrorMessage(error));
      }
    },
    async deleteCourse() {
      try {
        await axios.post(`api/course/delete/${this.course.id}`);
        window.location.href = '/';
      } catch (error) {
        alert(getErrorMessage(error));
      }
    },
  },
};
</script>

<style scoped>
.course-card {
  display: flex;
  flex-direction: column;
  height: 300px;
  width: 23%; /* Ширина карточки (4 карточки в ряд) */
  margin: 1%;
  background-color: white;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.course-card-top {
  flex: 0 0 30%; /* Верхняя часть занимает 30% высоты */
  background-color: rgba(109, 124, 242, 0.8);
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.course-info {
  flex: 1;
}

.course-title {
  margin: 0;
  font-size: 18px;
  color: white;
  text-decoration: none;
  cursor: pointer;
}

.course-title:hover {
  text-decoration: underline;
}

.course-author {
  margin: 5px 0 0;
  font-size: 14px;
  opacity: 0.8;
}

.leave-course-button {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 16px;
  padding: 5px;
}

.leave-course-button:hover {
  opacity: 0.8;
}

.course-card-bottom {
  flex: 1;
  padding: 15px;
  background-color: white;
  color: #2e2d2d;
}

/* Адаптация для маленьких экранов */
@media (max-width: 900px) {
  .course-card {
    width: 48%; /* 2 карточки в ряд */
    margin: 1%;
  }
}

@media (max-width: 680px) {
  .course-card {
    width: 98%; /* 1 карточка в ряд */
    margin: 1%;
  }
}
</style>