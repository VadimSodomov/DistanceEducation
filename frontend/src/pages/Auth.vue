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
        <div v-if="message" class="message" :class="{ success: isSuccess, error: !isSuccess }">
          {{ message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import apiClient from "@/api/index.js";
import {mapActions} from "vuex";
import {getErrorMessage} from "@/utils/ErrorHelper.js";

export default {
  data() {
    return {
      isEntry: true,
      name: '',
      email: '',
      password: '',
      isLoading: false,
      message: '',
      isSuccess: false,

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
    ...mapActions(['fetchCurrentUser']),

    toggleEntry() {
      this.isEntry = !this.isEntry;
      this.message = '';
    },

    async handleLogin() {
      this.isLoading = true;
      this.message = '';

      let response;
      try {
        if (this.isEntry) {
          response = await apiClient.post('/api/login', {
            email: this.email,
            password: this.password,
          });
        } else {
          response = await apiClient.post('/api/register', {
            name: this.name,
            email: this.email,
            password: this.password,
          });
        }

        const token = response.data.token;
        localStorage.setItem('jwt-token', token);

        await this.fetchCurrentUser();
        this.isSuccess = true;

        this.$router.push({name: 'AllCoursesPage'});
      } catch (error) {
        this.isSuccess = false;
        this.message = getErrorMessage(error)
        if (this.message === null) {
          if (this.isEntry)
            this.message = 'Неверный логин или пароль';
          else
            this.message = 'Ошибка при регистрации';
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

.message {
  margin-top: 10px;
  font-size: 14px;
  text-align: center;

  &.error{
    color: #ff0000;
  }

  &.success{
    color: #48980a;
  }
}

</style>