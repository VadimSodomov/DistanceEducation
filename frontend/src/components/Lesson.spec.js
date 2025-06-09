import { mount } from '@vue/test-utils';
import Lesson from "@/components/Lesson.vue";
import { describe, expect, test, vi } from 'vitest';
import { ref } from 'vue';

// Мокируем PrimeVue-компоненты
const mockComponents = {
  Card: { template: '<div><slot name="title" /><slot name="subtitle" /><slot name="content" /><slot name="footer" /></div>' },
  Button: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
  InputText: { template: '<input type="text" />' },
  Textarea: { template: '<textarea />' },
  DatePicker: { template: '<input type="date" />' },
  FileUpload: { template: '<div><slot name="empty" /></div>' },
  Knob: { template: '<div class="knob"></div>' },
  Chart: { template: '<div class="chart"></div>' },
};

describe('LessonCard.vue', () => {
  test('renders lesson name and description', () => {
    const wrapper = mount(Lesson, {
      props: {
        lesson: {
          id: 1,
          name: 'Тестовый урок',
          description: 'Описание урока',
          hwDeadline: '2024-01-01T00:00:00Z',
          lessonFiles: [],
        },
        courseId: 1,
      },
      global: {
        components: mockComponents,
      },
    });

    expect(wrapper.text()).toContain('Тестовый урок');
    expect(wrapper.text()).toContain('Описание урока');
    expect(wrapper.text()).toContain('Дедлайн: 01 января 2024, 00:00');
  });
});