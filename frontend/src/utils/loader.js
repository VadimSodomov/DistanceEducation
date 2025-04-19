import { ref } from 'vue';

const isVisible = ref(false);

export const loader = {
  show() {
    isVisible.value = true;
  },
  hide() {
    isVisible.value = false;
  },
};

export const useLoader = () => ({ isVisible });