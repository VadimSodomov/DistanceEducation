import axios from 'axios';
import router from '@/router';

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8080',
  headers: {
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('jwt-token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response) {
      if (error.response.status === 401) {
        localStorage.removeItem('token');

        router.push({ name: 'LoginPage' });

        if (router.currentRoute.value.name !== 'LoginPage') {
          alert('Ваша сессия истекла. Пожалуйста, войдите снова.');
        }
      }
    }
    return Promise.reject(error);
  }
);

export default apiClient;