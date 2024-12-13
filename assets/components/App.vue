<template>
  <div>
    <h1>{{ message }}</h1>
    <button @click="increment">Click me</button>
    <p>Counter: {{ counter }}</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      message: 'Hello from Vue 3!',
      counter: 0,
    };
  },
  methods: {
    increment() {
      this.counter++;
    },
  },
};
</script>

<style scoped>
h1 {
  color: blue;
}
</style>