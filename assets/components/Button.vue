<template>
  <button
      :class="['button', { 'button-full-width': fullWidth, 'text-left': textAlign === 'left' }]"
      :disabled="disabled"
      @click="handleClick"
  >
    <slot></slot> <!-- Слот для текста или иконки -->
  </button>
</template>

<script>
export default {
  props: {
    disabled: {
      type: Boolean,
      default: false,
    },
    fullWidth: {
      type: Boolean,
      default: false, // По умолчанию кнопка не растягивается на всю ширину
    },
    textAlign: {
      type: String,
      default: 'center', // По умолчанию текст выравнивается по центру
      validator: (value) => ['left', 'center'].includes(value), // Допустимые значения
    },
  },
  methods: {
    handleClick() {
      if (!this.disabled) {
        this.$emit('click');
      }
    },
  },
};
</script>

<style scoped>
.button {
  padding: 10px 20px;
  background-color: #d6d7d8;
  color: #2e2d2d;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.button:hover {
  background-color: #c3c3c8;
}

.button-disabled {
  background-color: #d6d7d8;
  cursor: not-allowed;
}

.button-full-width {
  width: 100%; /* Кнопка растягивается на всю ширину родителя */
}

.text-left {
  text-align: left; /* Выравнивание текста слева */
}
</style>