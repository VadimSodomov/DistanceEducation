<template>
  <div @click="toggleExpand" class="card-wrapper">
    <Card :class="{ 'expanded-mode': isExpanded }">
      <template #title>{{ isEditing ? editData.name : lesson.name }}</template>
      <template #subtitle>
        {{
          isEditing
              ? ''
              : (lesson.hwDeadline ? 'Дедлайн: ' + formatDeadline(lesson.hwDeadline) : 'Без дедлайна')
        }}
      </template>
      <template #content>
        <div class="editing-content" v-if="isEditing">
          <div class="edit-form flex flex-column gap-3">
            <InputText v-model="editData.name"
                       placeholder="Название"
            />
            <Textarea v-model="editData.description"
                      placeholder="Описание"
                      style="resize: none; height: 150px"/>
            <FloatLabel variant="on">
              <DatePicker
                  v-model="editData.hwDeadline"
                  inputId="deadline"
                  showIcon fluid iconDisplay="input"
                  dateFormat="dd-mm-yy"
                  showTime
                  hourFormat="24"
              />
              <label for="deadline">Дедлайн</label>
            </FloatLabel>
            <FileUpload
                choose-label="Выбрать"
                cancel-label="Отмена"
                :customUpload="true"
                :multiple="true"
                :showUploadButton="false"
                @select="onFileSelect"
                @remove="onFileRemove"
                @clear="clearAllFiles"
                @change="handleFileChange"
            >
              <template #empty>
                <span>Загрузите материалы. Перетащите файлы сюда.</span>
              </template>
            </FileUpload>
          </div>
        </div>
        <div v-else style="display: flex; justify-content: space-between;">
          <div>
            <p class="m-0">
              {{ lesson.description }}
            </p>
            <p v-if="isAuthor && isExpanded">Статистика тут когда-нибудь будет</p>
          </div>
          <div v-if="isExpanded && lesson.lessonFiles?.length" class="file-list">
            <span>Материалы:</span>
            <div v-for="(path, index) in lesson.lessonFiles" :key="index"
                 style="display: flex; justify-content: space-between;">
              <Button
                  :label="path.name"
                  @click.stop="openFile(path)"
                  severity="info"
                  variant="text"/>
              <Button
                  icon="pi pi-trash"
                  severity="danger" variant="text"
                  @click.stop="deleteFile(path.id)"
              />
            </div>
          </div>
        </div>
      </template>
      <template #footer v-if="isAuthor && isExpanded">
        <div class="flex gap-4 mt-1">
          <Button v-if="!isEditing"
                  label="Редактировать"
                  @click.stop="startEdit"/>
          <template v-else>
            <Button label="Сохранить"
                    @click.stop="saveEdit"/>
            <Button label="Отмена" severity="secondary" @click.stop="cancelEdit"/>
          </template>
          <Button label="Удалить урок"
                  severity="danger"
                  @click.stop="handleDelete"/>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup>
import {defineEmits, defineProps} from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import FileUpload from 'primevue/fileupload';
import {ref} from 'vue';
import {loader} from "@/utils/loader.js";
import {getErrorMessage} from "@/utils/ErrorHelper.js";
import FloatLabel from "primevue/floatlabel";
import {useToast} from "primevue";
import apiClient from "@/api/index.js";

const toast = useToast()

const props = defineProps({
  lesson: {
    id: Number,
    name: String,
    description: String,
    hwDeadline: String,
    filePaths: Array
  },
  isAuthor: Boolean,
});

const fileList = ref([]);

const emit = defineEmits(['delete']);

const handleDelete = () => {
  emit('delete', props.lesson.id);
};

const isExpanded = ref(false);

const toggleExpand = () => {
  if (!isEditing.value) {
    isExpanded.value = !isExpanded.value;
  }
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


// РЕДАКТИРОВАНИЕ

const isEditing = ref(false);

const editData = ref({
  name: '',
  description: '',
  hwDeadline: null,
  filePaths: []
});

const startEdit = () => {
  isEditing.value = true;
  editData.value = {
    name: props.lesson.name,
    description: props.lesson.description,
    hwDeadline: props.lesson.hwDeadline
        ? new Date(props.lesson.hwDeadline)
        : null,
    filePaths: props.lesson.lessonFiles
  };
};

const cancelEdit = () => {
  isEditing.value = false;
};

const formatDateToDDMMYYYYHHMMSS = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  const hours = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');
  const seconds = '00';
  return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
};

const onFileSelect = (event) => {
  fileList.value = [...fileList.value, ...event.files];
};

const onFileRemove = (event) => {
  fileList.value = fileList.value.filter(f => f.name !== event.file.name);
};

const clearAllFiles = () => {
  fileList.value = [];
};

const handleFileChange = (data) => {
  if (data.fileList.length > 1) {
    fileList.value = [data.fileList[data.fileList.length - 1]];
  }
};

const saveEdit = async () => {
  try {
    loader.show();
    await apiClient.post(`/api/lesson/edit/${props.lesson.id}`, {
      name: editData.value.name,
      description: editData.value.description,
      hwDeadline: editData.value.hwDeadline ? formatDateToDDMMYYYYHHMMSS(editData.value.hwDeadline) : null,
    });

    if (fileList.value.length > 0) {
      const formData = new FormData();

      fileList.value.forEach((file, index) => {
        formData.append(`files[${index}]`, file);
      });
      try {
        await apiClient.post(`/api/upload/lesson/${props.lesson.id}`, formData, {
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

    isEditing.value = false;
    emit('update');
  } catch (e) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при изменении урока',
      detail: `${getErrorMessage(e)}`,
      life: 4000
    });
  } finally {
    loader.hide();
  }
};

// ДЛЯ ФАЙЛОВ (доделать как будет бэк)

const getFileUrl = (path) => {
  return path.startsWith('http') ? path : `${import.meta.env.VITE_API_BASE_URL || ''}${path}`;
};

const deleteFile = async (fileId) => {
  try {
    await apiClient.post(`/api/delete/lesson-file/${fileId}`);
    emit('update');
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при удалении файла',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  }
}

const openFile = async (file) => {
  try {
    const response = await apiClient.get(`/api/download/lesson-file/${file.id}`, {
      responseType: 'arraybuffer'
    });

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
.editing-content {
  width: 500px;
  max-width: 100%;
}

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