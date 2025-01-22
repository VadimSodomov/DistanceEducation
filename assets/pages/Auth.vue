<template>
  <div class="auth-page">
    <div class="auth-form">
      <div class="form-box">
        <div class="main-title">
          <div :class="[{ 'active': isEntry }]" @click="toggleEntry">
            Вход
          </div>
          <div :class="[{ 'active': !isEntry }]" @click="toggleEntry">
            Регистрация
          </div>
        </div>
        <div v-if="!isEntry">
          <input
              class="auth-input"
              type=text
              id="name"
              v-model="name"
              placeholder="Введите имя"
              required
          />
        </div>
        <input
            class="auth-input"
            type="email"
            id="email"
            v-model="email"
            placeholder="Введите email"
            required
        />
        <input
            class="auth-input"
            type="password"
            id="password"
            v-model="password"
            placeholder="Введите пароль"
            required
        />
        <button class="auth-btn" @click="handleLogin" :disabled="isLoading || disabledBtn">
          {{ isLoading ? 'Загрузка...' : (isEntry ?  'Войти' : 'Создать аккаунт') }}
        </button>
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { getErrorMessage } from '../utils/ErrorHelper';

export default {
  data() {
    return {
      isEntry: true,
      name: '',
      email: '',
      password: '',
      isLoading: false,
      errorMessage: '',
    };
  },
  computed: {
    disabledBtn() {
      if (this.isEntry)
        return !(this.email && this.password && this.password.length >= 5)
      else
        return !(this.name && this.email && this.password && this.password.length >= 5)
    },
  },

  methods: {
    toggleEntry() {
      this.isEntry = !this.isEntry;
    },

    async handleLogin() {
      this.isLoading = true;
      this.errorMessage = '';

      try {
        if (this.isEntry) {
          await axios.post('/login', {
            email: this.email,
            password: this.password,
          });
        } else {
          await axios.post('/register', {
            name: this.name,
            email: this.email,
            password: this.password,
          });
        }
        window.location.href = '/';
      } catch (error) {
        this.errorMessage = getErrorMessage(error)
        if (this.errorMessage === null)
        {
          if (this.isEntry)
            this.errorMessage = 'Неверный логин или пароль';
          else
            this.errorMessage = 'Ошибка при регистрации';
        }
      } finally {
        this.isLoading = false;
      }
    }
  },
};
</script>

<style scoped>
.auth-page {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;

  .auth-form {
    width: 100%;
    max-width: 400px;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }

  .form-box {
    text-align: center;

    .auth-input {
      width: 92%;
      padding: 10px;
      border: 1px solid #6D7CF2;
      border-radius: 14px;
      margin-bottom: 15px;
      font-size: 13px;
    }

    .auth-btn {
      width: 100%;
      padding: 10px;
      background-color: #6D7CF2;
      color: white;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      font-size: 18px;

      &:hover {
        background-color: #8C99FF;
      }

      &:disabled {
        background-color: #ABAFCD;
        cursor: not-allowed;
      }
    }
  }
}

.main-title {
  display: flex;
  justify-content: space-around;
  font-size: 20px;
  margin-bottom: 20px;
  font-weight: 600;
  color: #333;
  cursor: pointer;

  .active {
    color: #6D7CF2;
    position: relative;

    &:after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -2px;
      width: 100%;
      height: 2px;
      background-color: #6D7CF2;
    }
  }
}

.error-message {
  margin-top: 10px;
  color: #ff0000;
  font-size: 14px;
  text-align: center;
}
</style>