<template>
  <Toast/>
  <Dialog
      v-model:visible="visibleModel"
      header="Создание урока"
      :modal="true"
      :style="{ width: '450px' }"
  >
    <div class="content" v-if="!isCreated">
      <InputText
          v-model="lessonData.name"
          placeholder="Название"
      />
      <FloatLabel variant="on">
        <DatePicker
            v-model="lessonData.deadline"
            inputId="deadline"
            showIcon fluid iconDisplay="input"
            dateFormat="dd-mm-yy"
            showTime
            hourFormat="24"
        />
        <label for="deadline">Дедлайн</label>
      </FloatLabel>
      <Textarea
          v-model="lessonData.description"
          placeholder="Описание"
          style="resize: none; height: 150px"
      />
      <FileUpload
          :customUpload="true"
          @select="onFileSelect"
          @remove="onFileRemove"
          @clear="clearAllFiles"
          :multiple="true"
          :showUploadButton="false"
          choose-label="Выбрать"
          cancel-label="Отмена"
      >
        <template #empty>
          <span>Загрузите материалы. Перетащите файлы сюда.</span>
        </template>
      </FileUpload>
    </div>

    <template #footer v-if="!isCreated" class="footer">
      <Button
          label="Отмена"
          severity="secondary"
          @click="closeDialog"
          class="w-full"
      />
      <Button
          label="Создать"
          :loading="loadingCreate"
          :disabled="isBtnDisabled"
          @click="saveLesson"
          class="w-full"
      />
    </template>
  </Dialog>
</template>

<script setup>
import {computed, ref} from "vue";
import {Button, Dialog, InputText, Toast, useToast} from "primevue";
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import FloatLabel from 'primevue/floatlabel';
import FileUpload from 'primevue/fileupload';
import apiClient from "@/api/index.js";
import {getErrorMessage} from "@/utils/ErrorHelper.js";

const toast = useToast()

const emits = defineEmits(['update:visible', 'updateData']);

const isCreated = ref(false)
const loadingCreate = ref(false)
const fileList = ref([]);

const props = defineProps({
  visible: Boolean,
  courseId: Number,
});

const lessonData = ref({
  name: '',
  description: '',
  deadline: null,
  filePaths: []
})

const visibleModel = computed({
  get() {
    return props.visible;
  },
  set(value) {
    closeDialog()
  }
});

const isBtnDisabled = computed(() => {
  return !(lessonData.value.name && lessonData.value.description && lessonData.value.deadline)
})

const onFileSelect = (event) => {
  fileList.value = [...fileList.value, ...event.files];
};

const onFileRemove = (event) => {
  fileList.value = fileList.value.filter(f => f.name !== event.file.name);
};

const clearAllFiles = () => {
  fileList.value = [];
};

const closeDialog = () => {
  emits('update:visible', false);
  isCreated.value = false
  lessonData.value = {
    name: '',
    description: '',
    deadline: null,
    filePaths: []
  };
};

// Перевод даты в нужный формат для сервера
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

const saveLesson = async () => {
  loadingCreate.value = true
  try {
    const dataLesson = await apiClient.post('api/lesson/create', {
      name: lessonData.value.name,
      description: lessonData.value.description,
      hwDeadline: lessonData.value.deadline ? formatDateToDDMMYYYYHHMMSS(lessonData.value.deadline) : null,
      courseId: props.courseId
    });

    // await dataLesson.id запрос на отправку материалов fileList

    emits('updateData');
    closeDialog()
    isCreated.value = true;
    toast.add({
      severity: 'success',
      summary: 'Урок успешно создан',
      life: 4000
    });
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка создания урока',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loadingCreate.value = false
  }
}
</script>

<style scoped>
.content {
  display: flex;
  flex-direction: column;
  width: 100%;
  gap: 20px;
}

.footer {
  display: flex;
  justify-content: space-between;
}
</style>