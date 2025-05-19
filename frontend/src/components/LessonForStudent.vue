<template>
  <div @click="toggleExpand" class="card-wrapper">
    <Card :class="{ 'expanded-mode': isExpanded }">
      <template #title>
        <div style="display: flex; justify-content: space-between;">
          {{ lesson.name }}
          <div v-if="isCompleted">
            <Tag icon="pi pi-check" severity="success" rounded style="width: 30px; height: 30px;"/>
          </div>
        </div>
      </template>
      <template #subtitle>
        {{ (lesson.hwDeadline ? 'Дедлайн: ' + formatDeadline(lesson.hwDeadline) : 'Без дедлайна') }}
      </template>
      <template #content>
        <div>
          <p class="m-0">
            {{ lesson.description }}
          </p>
          <div v-if="isExpanded && lesson.lessonFiles?.length" style="margin-top: 10px">
            <span><strong>Материалы:</strong></span>
            <div v-for="(path, index) in lesson.lessonFiles" :key="index"
                 style="display: flex">
              <Button
                  :label="path.name"
                  @click.stop="openFile(path, 'teacher')"
                  severity="info"
                  variant="text"/>
            </div>
          </div>

          <div v-if="isExpanded" style="margin-top: 10px">
            <div v-if="isEmptyAnswer || isEditing" style="display: flex; gap: 20px;">
                <Textarea v-model="answerDescription"
                          placeholder="Комментарий"
                          style="resize: none; height: 150px; width: 300px"
                          @click.stop=""
                />
              <FileUpload
                  :key="'uploader-' + isExpanded"
                  choose-label="Выбрать"
                  cancel-label="Отмена"
                  :customUpload="true"
                  :multiple="true"
                  :showUploadButton="false"
                  @select="onFileSelect"
                  @remove="onFileRemove"
                  @clear="clearAllFiles"
                  @click.stop=""
              >
                <template #empty>
                  <span>Загрузите материалы. Перетащите файлы сюда.</span>
                </template>
              </FileUpload>
            </div>
            <div style="display: flex; gap: 20px; flex-direction: column">
              <span v-if="!isEditing && !isEmptyAnswer"><strong>Оценка:</strong> {{ myAnswer.score || "Не проверено" }}</span>
              <span v-if="!isEditing && !isEmptyAnswer"><strong>Дата ответа:</strong> {{ formatDeadline(myAnswer.uploadedAt) }}</span>
              <div v-if="myAnswer.lessonUserFiles?.length" style="margin-top: 10px">
                <span><strong>Прикрепленные файлы:</strong></span>
                <div v-for="(path, index) in myAnswer.lessonUserFiles" :key="index"
                     style="display: flex">
                  <Button
                      :label="path.name"
                      @click.stop="openFile(path, 'user')"
                      severity="info"
                      variant="text"/>
                  <Button
                      icon="pi pi-trash"
                      severity="danger" variant="text"
                      @click.stop="deleteFile(path.id)"
                      v-if="isEditing"
                  />
                </div>
              </div>
              <span v-if="!isEditing && !isEmptyAnswer"><strong>Комментарий:</strong> {{ myAnswer.comment }}</span>
            </div>
          </div>
        </div>
      </template>
      <template #footer v-if="isExpanded">
        <div class="flex gap-4 mt-1">
          <Button label="Отправить ответ"
                  v-if="isEmptyAnswer"
                  @click.stop="saveAnswer"
                  :disabled="btnDisabled"
          />
          <Button label="Сохранить изменения"
                  v-else-if="isEditing"
                  @click.stop="editAnswer"
                  :disabled="btnDisabled"
          />
          <div v-else class="flex gap-4 mt-1">
            <Button label="Редактировать ответ"
                    @click.stop="isEditing = true"
            />
            <Button label="Удалить ответ"
                    severity="danger"
                    @click.stop="deleteAnswer"
            />
          </div>
          <Button label="Отменить редактирование"
                  v-if="isEditing && !isEmptyAnswer"
                  severity="danger"
                  @click.stop="isEditing = false"
          />
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup>
import {computed, defineEmits, defineProps, onMounted, watch} from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import FileUpload from 'primevue/fileupload';
import {Tag} from "primevue";
import {ref} from 'vue';
import {getErrorMessage} from "@/utils/ErrorHelper.js";
import {useToast} from "primevue";
import apiClient from "@/api/index.js";
import {loader} from "@/utils/loader.js";

const toast = useToast()

const props = defineProps({
  lesson: {
    id: Number,
    name: String,
    description: String,
    hwDeadline: String,
    filePaths: Array
  },
  isCompleted: Boolean,
});

const statsData = ref({});

const fileList = ref([]);

const answerDescription = ref("")

const myAnswer = ref({});

const emit = defineEmits(['delete', 'update']);

const isExpanded = ref(false);

const btnDisabled = computed(() => {
  return !(answerDescription.value)
})

const isEmptyAnswer = computed(() => {
  return Object.keys(myAnswer.value).length === 0;
});

const isEditing = ref(false);

watch(isEditing, (newVal) => {
  if (newVal) {
    answerDescription.value = myAnswer.value.comment || '';
  }
});

const toggleExpand = () => {
  if (isExpanded) {
    fileList.value = []
  }
  isExpanded.value = !isExpanded.value;
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

const onFileSelect = (event) => {
  const newFiles = event.files.filter(newFile =>
      !fileList.value.some(existingFile =>
          existingFile.name === newFile.name &&
          existingFile.size === newFile.size &&
          existingFile.lastModified === newFile.lastModified
      )
  );
  fileList.value = [...fileList.value, ...newFiles];
};

const onFileRemove = (event) => {
  if (!fileList.value) return; // Защита от null
  fileList.value = fileList.value.filter(f => f.name !== event.file.name);
};

const clearAllFiles = () => {
  if (fileList.value) fileList.value = []; // Очистка с проверкой
};

const deleteFile = async (fileId) => {
  loader.show();
  try {
    await apiClient.post(`/api/delete/lesson-user-file/${fileId}`);
    await getAnswer();
    emit('update');
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при удалении файла',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide();
  }
}

const saveAnswer = async () => {
  loader.show();
  try {
    const response = await apiClient.post(`/api/lesson-user/create`, {
      lessonId: props.lesson.id,
      comment: answerDescription.value,
    });

    if (fileList.value.length > 0) {
      const formData = new FormData();

      fileList.value.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
      });
      try {
        await apiClient.post(`/api/upload/lesson-user/${response.data.id}`, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });
      } catch (error) {
        toast.add({
          severity: 'warn',
          summary: 'Ошибка при загрузке файлов',
          life: 4000
        });
      }
    }

    await getAnswer(); // Обновить данные сразу

    toast.add({
      severity: 'success',
      summary: 'Ваш ответ успешно отправлен!',
      life: 4000
    });

    clearAllFiles();
    isExpanded.value = false;
    answerDescription.value = "";
    emit('update');
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при отправке ответа',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide();
  }
}

const editAnswer = async () => {
  loader.show();
  try {
    const response = await apiClient.post(`/api/lesson-user/edit/${myAnswer.value.id}`, {
      lessonId: props.lesson.id,
      comment: answerDescription.value,
    });

    if (fileList.value.length > 0) {
      const formData = new FormData();

      fileList.value.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
      });
      try {
        await apiClient.post(`/api/upload/lesson-user/${myAnswer.value.id}`, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });
      } catch (error) {
        toast.add({
          severity: 'warn',
          summary: 'Ошибка при загрузке файлов',
          life: 4000
        });
      }
    }

    await getAnswer();

    toast.add({
      severity: 'success',
      summary: 'Ваш ответ успешно отредактирован!',
      life: 4000
    });

    clearAllFiles();
    isExpanded.value = false;
    isEditing.value = false;
    answerDescription.value = "";
    emit('update');
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при редактировании ответа',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide();
  }
}

const deleteAnswer = async () => {
  loader.show();
  try {
    const response = await apiClient.post(`/api/lesson-user/delete/${myAnswer.value.id}`);

    toast.add({
      severity: 'success',
      summary: 'Ваш ответ успешно удален!',
      life: 4000
    });

    clearAllFiles();
    isExpanded.value = false;
    answerDescription.value = "";
    myAnswer.value = {};
    emit('update');
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при удалении ответа',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide();
  }
}

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

const getAnswer = async () => {
  try {
    const response = await apiClient.get(`/api/lesson-user/get/${props.lesson.id}`);
    myAnswer.value = response.data;
    console.log(myAnswer.value)
  } catch (error) {
    if (error.response) {
      if (error.response.status !== 404) {
        toast.add({
          severity: 'error',
          summary: 'Ошибка при загрузке ответа',
          detail: `${getErrorMessage(error)}`,
          life: 4000
        });
      }
    }
  }
};

onMounted(async () => {
  await getAnswer();
  fileList.value = [];
});

</script>

<style scoped>
.card-wrapper {
  cursor: pointer;
  transition: box-shadow 0.2s;
}

.card-wrapper:hover {
  box-shadow: 0 0 9px rgba(0, 0, 0, 0.1);
}

:deep(.p-card.expanded-mode) {
  border: 1px solid var(--p-button-primary-background);
}
</style>