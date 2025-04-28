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
                :customUpload="true"
                @upload="onUpload($event)"
                :multiple="true"
                :showUploadButton="false"
                choose-label="Выбрать"
                cancel-label="Отмена">
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
          <div v-if="isExpanded && lesson.filePaths?.length" class="file-list">
            <p>Материалы:</p>
            <ul>
              <li v-for="(path, index) in lesson.filePaths" :key="index">
                <Button :label="extractFileName(path)"
                        @click.stop="openFile(path)"
                        link/>
              </li>
            </ul>
          </div>
          <div v-if="isAuthor && isExpanded" style="width: 30%">
            <p><strong>Статистика</strong></p>
            <Chart type="pie"
                   :data="chartData"
                   :options="chartOptions"
                   class="w-full md:w-[30rem]" />
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
import Chart from 'primevue/chart';

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
        : null
  };
};

const cancelEdit = () => {
  isEditing.value = false;
};

const saveEdit = async () => {
  try {
    loader.show();
    // запрос на обновление урока сюда

    // await dataLesson.id запрос на отправку материалов fileList

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

const extractFileName = (path) => {
  return path.split('/').pop();
};

const openFile = (path) => {
  const url = getFileUrl(path);
  window.open(url, '_blank');
};

// TODO этого запроса быть не должно. сделать как в попапе создания урока
const onUpload = async (event) => {
  const filePaths = new FormData()

  for (const file of event.files) {
    filePaths.append('files[]', file)
  }

  try {
    // запрос будет тут
  } catch (err) {
    console.error('Ошибка загрузки:', err)
  }
}

// СТАТИСТИКА

const chartData = {
  labels: ['Сдано', 'Не сдано'],
  datasets: [
    {
      data: [70, 30], // сюда потом вставить процент сданных и несданных
      backgroundColor: ['#14b8a6', '#f44336'],
      hoverBackgroundColor: ['#339a89', '#e57373']
    }
  ]
};

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