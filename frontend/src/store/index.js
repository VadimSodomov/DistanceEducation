import { createStore } from 'vuex';
import apiClient from "@/api/index.js";

export default createStore({
  state() {
    return {
      user: JSON.parse(localStorage.getItem('user')) || null,
    };
  },
  mutations: {
    setUser(state, user) {
      state.user = user;
      localStorage.setItem('user', JSON.stringify(user));
    },
    clearUser(state) {
      state.user = null;
      localStorage.removeItem('user');
    },
  },
  actions: {
    logout({ commit }) {
      localStorage.removeItem('jwt-token');
      commit('clearUser');
    },
    fetchCurrentUser: async function ({commit}) {
      try {
        const token = localStorage.getItem('jwt-token');
        if (!token) {
          throw new Error('No token found');
        }

        const response = await apiClient.get('/api/user');

        commit('setUser', response.data.user);
      } catch (error) {
        console.error('Failed to fetch current user:', error);
        commit('setUser', null);
      }
    },
  },
});