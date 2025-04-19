<template>
  <div v-if="isVisible" class="fullscreen-loader">
    <ProgressSpinner/>
  </div>
</template>

<script setup>
import ProgressSpinner from 'primevue/progressspinner';
import {useLoader} from '@/utils/loader';

const {isVisible} = useLoader();
</script>

<style scoped>
.fullscreen-loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999;
}
</style>