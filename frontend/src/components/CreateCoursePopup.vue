<template>
  <Toast/>
  <Dialog
      v-model:visible="visibleModel"
      :header="dialogHeader"
      :modal="true"
      :style="{ width: '450px' }"
  >
    <div class="content" v-if="!isCreated">
      <InputText
          v-model="courseData.name"
          placeholder="Название"
      />
      <Textarea
          v-model="courseData.description"
          placeholder="Описание"
          style="resize: none; height: 150px"
      />
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
          @click="saveCourse"
          class="w-full"
      />
    </template>

    <template #footer v-else>
      <Button
          label="Скопировать код"
          @click="copyCode"
          class="w-full"
      />
    </template>
  </Dialog>
</template>

<script setup>
import {computed, ref} from "vue";
import {Button, Dialog, InputText, Toast, useToast} from "primevue";
import Textarea from 'primevue/textarea';
import apiClient from "@/api/index.js";
import {getErrorMessage} from "@/utils/ErrorHelper.js";

const toast = useToast()

const emits = defineEmits(['update:visible', 'updateData']);

const isCreated = ref(false)
const loadingCreate = ref(false)

const props = defineProps({
  visible: Boolean
});

const courseData = ref({
  name: '',
  description: '',
  code: ''
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
  return !(courseData.value.name && courseData.value.description)
})

const dialogHeader = computed(() =>
    isCreated.value
        ? `Курс "${courseData.value.name}" успешно создан!`
        : 'Создание курса'
);

const closeDialog = () => {
  emits('update:visible', false);
  isCreated.value = false
  courseData.value = {
    name: '',
    description: '',
    code: ''
  };
};

const saveCourse = async () => {
  loadingCreate.value = true
  try {
    const dataCourse = await apiClient.post('api/course/create', {
      name: courseData.value.name,
      description: courseData.value.description,
    });
    courseData.value = {
      name: dataCourse.data.data.name,
      description: dataCourse.data.data.code
    };
    emits('updateData');
    isCreated.value = true;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Ошибка создания курса',
      detail: `${getErrorMessage(error)}`,
      life: 4000
    });
  } finally {
    loadingCreate.value = false
  }
}

const copyCode = () => {
  navigator.clipboard.writeText(courseData.value.code)
      .then(() => {
        toast.add({
          severity: 'success',
          summary: 'Код скопирован!',
          life: 4000
        });
      })
      .catch(() => {
        toast.add({
          severity: 'error',
          summary: 'Не удалось скопировать код.',
          life: 4000
        });
      });
}

</script>

<style lang="scss" scoped>
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