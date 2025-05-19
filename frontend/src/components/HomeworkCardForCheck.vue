<template>
    <Card class="card-wrapper">
      <template #title>
        <div style="display: flex; gap: 15px">
          <span>{{ homework.user.name }}</span>
          <div v-if="savedMark || isChecked">
            <Tag :value="isChecked? homework.score: savedMark" rounded style="width: 40px; height: 40px;"/>
          </div>
        </div>
      </template>
      <template #subtitle>
        Дата сдачи: {{ formatDeadline(homework.uploadedAt) }}
      </template>
      <template #content>
        <div class="edit-form flex flex-column gap-2">
          <span><strong>Прикрепленные файлы:</strong></span>
          <div v-for="(path, index) in homework.lessonUserFiles" :key="index"
               style="display: flex">
            <Button
                :label="path.name"
                @click.stop="openFile(path, 'user')"
                severity="info"
                variant="text"/>
          </div>
          <span><strong>Комментарий: </strong>{{ homework.comment }}</span>
          <FloatLabel variant="on" v-if="!savedMark && !isChecked">
            <InputNumber v-model="currentMark"
                         inputId="mark"
                         mode="decimal"
                         showButtons :min="0" :max="100"
                         fluid
                         style="max-width: 180px"/>
            <label for="mark">Оценка</label>
          </FloatLabel>
          <Button
              label="Удалить оценку"
              v-if="isChecked"
              @click="deleteMark"
              severity="danger"
              style="width: 180px"/>
          <Button
              label="Сохранить оценку"
              v-else-if="!savedMark"
              :disabled="currentMark === null"
              @click="chooseRate"
              style="width: 180px"/>
        </div>
      </template>
    </Card>
</template>

<script setup>
import Card from "primevue/card";
import InputNumber from 'primevue/inputnumber';
import FloatLabel from 'primevue/floatlabel';
import {defineEmits, defineProps, ref} from "vue";
import Button from "primevue/button";
import apiClient from "@/api/index.js";
import {getErrorMessage} from "@/utils/ErrorHelper.js";
import {Tag, useToast} from "primevue";

const toast = useToast()

const props = defineProps({
  homework: Object,
  isChecked: { // была ли работа изначально уже проверена
    type: Boolean,
    default: false
  }
});

const currentMark = ref(null);
const savedMark = ref(null);

const emit = defineEmits(['rate', 'unrated']);

const chooseRate = () => {
  emit('rate', {
    mark: currentMark.value,
    homework_id: props.homework.id,
    onComplete: (success) => {
      if (success) {
        savedMark.value = currentMark.value
      }
    }
  });
};

const deleteMark = () => {
  emit('unrated', {
    homework_id: props.homework.id,
    onComplete: (success) => {
      if (success) {
        savedMark.value = null
      }
    }
  });
};

const formatDeadline = (isoString) => {
  if (!isoString) return '';

  const date = new Date(isoString);
  return new Intl.DateTimeFormat('ru-RU', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'UTC'
  }).format(date);
};

const openFile = async (file, type) => {
  try {
    let response;
    if (type === 'user') {
      response = await apiClient.get(`/api/download/lesson-user-file/${file.id}`, {
        responseType: 'arraybuffer'
      });
    } else {
      response = await apiClient.get(`/api/download/lesson-file/${file.id}`, {
        responseType: 'arraybuffer'
      });
    }

    const blob = new Blob([response.data], {type: 'application/pdf'});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name || 'document.pdf';
    document.body.appendChild(a);
    a.click();

    setTimeout(() => {
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    }, 100);

  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при сохранении файла',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  }
};
</script>

<style scoped>
.card-wrapper {
  transition: box-shadow 0.2s;
}

.card-wrapper:hover {
  box-shadow: 0 0 9px rgba(0, 0, 0, 0.1);
}
</style>