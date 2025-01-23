<template>
  <div class="create-course-popup" v-if="isVisible">
    <div class="popup-container">
      <button class="close-button" @click="closePopup">&times;</button>
      <div class="popup-content" v-if="!isCreated">
        <h2>Создание курса</h2>
        <input
            class="popup-input"
            type=text
            placeholder="Введите название"
            required
            v-model="courseName"
        />
        <textarea
            class="popup-input popup-textarea"
            placeholder="Введите Описание"
            required
            v-model="courseDescription"
        />
        <button class="create-course-btn" @click="saveCourse">Создать</button>
      </div>
      <div class="popup-content" v-else>
        <h2>Курс "{{ courseName }}" успешно создан!</h2>
        <div class="link-container">
          <a :href="courseCode" target="_blank" class="course-code">Ссылка на курс</a>
          <button @click="copyCode" class="copy-button">
            <i class="fas fa-copy"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import {getErrorMessage} from '../utils/ErrorHelper';

export default {
  data() {
    return {
      isCreated: false,
      courseName: '',
      courseDescription: '',
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
    closePopup() {
      this.courseName = '';
      this.courseDescription = '';
      this.courseCode = '';
      this.isCreated = false
      this.$emit('close');
    },
    async saveCourse() {
      try {
        const dataCourse = await axios.post('/course/create', {
          name: this.courseName,
          description: this.courseDescription,
        });
        this.courseName = dataCourse.data.data.name
        this.courseCode = dataCourse.data.data.code;
        this.isCreated = true;
      } catch (error) {
        alert(getErrorMessage(error));
      }

    },
    copyCode() {
      navigator.clipboard.writeText(this.courseCode)
          .then(() => {
            alert('Ссылка скопирована!');
          })
          .catch(() => {
            alert('Не удалось скопировать ссылку.');
          });
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