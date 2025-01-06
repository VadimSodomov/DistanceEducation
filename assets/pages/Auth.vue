<template>
  <div class="auth-page">
    <div class="auth-form">
      <div class="form-box">
        <div class="main-title">Авторизация</div>
        <label for="email">Email</label>
        <input
          type="email"
          id="email"
          v-model="email"
          placeholder="Введите email"
          required
        />

        <label for="password">Пароль</label>
        <input
          type="password"
          id="password"
          v-model="password"
          placeholder="Введите пароль"
          required
        />

        <!-- Ссылка "Создать учетную запись" -->
        <div class="create-account-link">
          <a href="/register">Создать учетную запись</a>
        </div>

        <button @click="handleLogin" :disabled="isLoading">
          {{ isLoading ? 'Загрузка...' : 'Войти' }}
        </button>

        <!-- Блок для отображения ошибки -->
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      password: '',
      isLoading: false,
      errorMessage: '',
    };
  },
  methods: {
    async handleLogin() {
      this.isLoading = true;
      this.errorMessage = '';

      try {
        const response = await axios.post('/login', {
          email: this.email,
          password: this.password,
        });

        window.location.href = '/';
      } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
          this.errorMessage = error.response.data.message;
        } else {
          // Если сообщение об ошибке отсутствует, показываем стандартное сообщение
          this.errorMessage = 'Неверный логин или пароль';
        }
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
.auth-page {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f5f5f5;
}

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
}

.main-title {
  font-size: 20px;
  margin-bottom: 20px;
  font-weight: 600;
  color: #333;
}

label {
  display: block;
  font-weight: 500;
  margin-bottom: 5px;
  color: #555;
}

input {
  width: 92%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin-bottom: 15px;
  font-size: 13px;
}

.create-account-link {
  margin-bottom: 15px;
}

.create-account-link a {
  color: #190f8b;
  text-decoration: none;
  font-size: 13px;
}

.create-account-link a:hover {
  text-decoration: underline;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #46a003;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
}

button:hover {
  background-color: #3d8f04;
}

button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error-message {
  margin-top: 10px;
  color: #ff0000;
  font-size: 14px;
  text-align: center;
}
</style>