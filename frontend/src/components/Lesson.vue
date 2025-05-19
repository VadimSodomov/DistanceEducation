<template>
  <div @click="toggleExpand" class="card-wrapper">
    <Card :class="{ 'expanded-mode': isExpanded }">
      <template #title>
        <div style="display: flex; justify-content: space-between;">{{ lesson.name }}</div>
      </template>
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
            >
              <template #empty>
                <span>Загрузите материалы. Перетащите файлы сюда.</span>
              </template>
            </FileUpload>
          </div>
        </div>
        <div v-else>
          <p class="m-0">
            {{ lesson.description }}
          </p>
          <div v-if="isExpanded && lesson.lessonFiles?.length" style="margin-top: 10px">
            <span><strong>Материалы:</strong></span>
            <div v-for="(path, index) in lesson.lessonFiles" :key="index"
                 style="display: flex">
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

          <div v-if="isExpanded">
            <p><strong>Статистика:</strong></p>
            <div class="charts">
              <div class="flex flex-column align-items-center">
                <Knob
                    v-model="avgData.avg"
                    disabled
                    :valueColor="avgData.color"
                    :size="250"
                />

                <div class="text-center">
                  <div class="text-sm text-color-secondary mt-1">
                    <strong>Средний балл</strong> на основе <strong>{{ statsData.total_users || 0 }}</strong> участников
                  </div>
                </div>
              </div>

              <Chart type="pie"
                     :data="chartCountData"
                     :options="chartOptions"
                     style="height: 300px; width: 300px"
              />
              <Chart type="pie"
                     :data="chartResultsData"
                     :options="chartOptions"
                     style="height: 300px; width: 300px"
              />
            </div>
          </div>
        </div>
      </template>
      <template #footer v-if="isExpanded">
        <div class="flex gap-4 mt-1">
          <Button label="Проверить работы"
                  @click.stop="startChecking"/>
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
import {computed, defineEmits, defineProps, onMounted, watch} from 'vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import FileUpload from 'primevue/fileupload';
import {Knob, Tag} from "primevue";
import {ref} from 'vue';
import {loader} from "@/utils/loader.js";
import {getErrorMessage} from "@/utils/ErrorHelper.js";
import FloatLabel from "primevue/floatlabel";
import {useToast} from "primevue";
import Chart from 'primevue/chart';
import apiClient from "@/api/index.js";
import router from "@/router/index.js";

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
  courseId: Number,
});

const statsData = ref({});

const fileList = ref([]);

const emit = defineEmits(['delete', 'update']);

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
  fileList.value = fileList.value.filter(f => f.name !== event.file.name);
};

const clearAllFiles = () => {
  fileList.value = [];
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
    fileList.value = [];
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

// ДЛЯ ФАЙЛОВ
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

// Статистика
const chartCountData = computed(() => {
  const passed = statsData.value.passed_count ? parseInt(statsData.value.passed_count) : 0;
  const notPassed = statsData.value.not_passed_count ? parseInt(statsData.value.not_passed_count) : 0;

  return {
    labels: ['Сдано', 'Не сдано'],
    datasets: [
      {
        data: [passed, notPassed],
        backgroundColor: ['#14b8a6', '#f44336'],
        hoverBackgroundColor: ['#339a89', '#e57373']
      }
    ]
  };
});

const chartResultsData = computed(() => {
  const passed_count_80 = statsData.value.passed_count_80 ? parseInt(statsData.value.passed_count_80) : 0;
  const passed_count_60 = statsData.value.passed_count_60 ? parseInt(statsData.value.passed_count_60) : 0;
  const passed_count_40 = statsData.value.passed_count_40 ? parseInt(statsData.value.passed_count_40) : 0;
  const passed_count_20 = statsData.value.passed_count_20 ? parseInt(statsData.value.passed_count_20) : 0;
  const passed_count_0 = statsData.value.passed_count_0 ? parseInt(statsData.value.passed_count_0) : 0;

  return {
    labels: ['больше 80', 'от 60 до 80', 'от 40 до 60', 'от 20 до 40', 'менее 20'],
    datasets: [
      {
        data: [passed_count_80, passed_count_60, passed_count_40, passed_count_20, passed_count_0],
        backgroundColor: ['#14b8a6', '#cbe116', '#f4e436', '#d78500', '#f44336'],
        hoverBackgroundColor: ['#339a89', '#d0de5d', '#b9ad29', '#ad7110', '#e57373']
      }
    ]
  };
});

const avgData = computed(() => {
  const avg = statsData.value.avg_passed !== null ? parseFloat(statsData.value.avg_passed) : 0;
  let color;
  if (avg >= 80) color = '#14b8a6';
  else if (avg >= 50) color = '#f59e0b';
  else color = '#ef4444';

  return {
    avg: avg,
    color: color,
  }
});

const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: 'bottom',
    },
    tooltip: {
      enabled: true,
    }
  }
};

const fetchStatistic = async () => {
  loader.show()
  try {
    const response = await apiClient.get(`/api/lesson/${props.lesson.id}/short-statistics`);
    statsData.value = response.data;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка при получении статистики',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loader.hide()
  }
}

const startChecking = async () => {
  await router.push({
    name: 'CheckHomework',
    query: {
      courseId: props.courseId,
      lessonId: props.lesson.id
    },
  });
}

onMounted(async () => {
  await fetchStatistic();
  fileList.value = [];
});

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

.charts {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 50px;
  margin: 30px;
}
</style>