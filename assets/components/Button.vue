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
    }
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
  background-color: #6D7CF2;
  color: white;
  border: none;
  border-radius: 14px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.button:hover {
  background-color: #8C99FF;
}

.button-disabled {
  background-color: #ABAFCD;
  cursor: not-allowed;
}

.button-full-width {
  width: 100%; /* Кнопка растягивается на всю ширину родителя */
}

.text-left {
  text-align: left; /* Выравнивание текста слева */
}
</style>