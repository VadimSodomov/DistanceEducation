<template>
  <div class="create-course-popup" v-if="isVisible">
    <div class="popup-container">
      <button class="close-button" @click="closePopupAdd">&times;</button>
      <div class="popup-content">
        <h2>Найти</h2>
        <input
            class="popup-input"
            type=text
            placeholder="Введите код"
            required
            v-model="courseCode"
        />
        <button class="create-course-btn" @click="addCourse">Найти</button>
      </div>
    </div>
  </div>
</template>

<script>
import {getErrorMessage} from '@/utils/ErrorHelper';
import apiClient from "@/api/index.js";

export default {
  data() {
    return {
      isCreated: false,
      courseCode: '',
    }
  },
  props: {
    isVisible: {
      type: Boolean,
      required: true,
    },
  },
  methods: {
    closePopupAdd() {
      this.courseCode = '';
      this.$emit('closeFind');
    },
    async addCourse() {
      try {
        const response = await apiClient.get(`api/course?code=${this.courseCode}`);
        this.$router.push(`/course/${response.data.data.course.id}`);
      } catch (error) {
        alert(getErrorMessage(error));
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.create-course-popup {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;

  .popup-container {
    border-radius: 10px;
    background-color: white;
    padding: 20px;
    width: auto;
    max-width: 300px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    animation: fadeIn 0.3s ease-out;
    position: relative;

    .popup-content {
      display: flex;
      flex-direction: column;
      align-items: center;
    }


    .close-button {
      position: absolute;
      top: 10px;
      right: 10px;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #6D7CF2;
      padding: 0;
      line-height: 1;
      transition: color 0.2s ease;

      &:hover {
        color: #4a5ad4;
      }
    }

    .popup-input {
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #6D7CF2;
      margin-bottom: 10px;
      outline: none;
    }

    .popup-textarea {
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #6D7CF2;
      margin-bottom: 10px;
      outline: none;
      height: 50px;
      resize: none;
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

    .link-container {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .course-code {
      color: #6D7CF2;
      text-decoration: none;
      font-size: 16px;
      cursor: pointer;

      &:hover {
        text-decoration: underline;
      }
    }

    .copy-button {
      background: none;
      border: none;
      cursor: pointer;
      color: #6D7CF2;
      font-size: 16px;
      padding: 0;
      transition: color 0.2s ease;

      &:hover {
        color: #4a5ad4;
      }
    }
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>