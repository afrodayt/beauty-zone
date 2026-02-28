<template>
  <BaseModal id="client-create-modal" title="Новый клиент" size="lg" static-backdrop ref="modalRef">
    <form class="row g-2" @submit.prevent="createClient">
      <div class="col-md-6">
        <label class="form-label">ФИО</label>
        <input v-model="form.full_name" type="text" class="form-control" required />
      </div>
      <div class="col-md-6">
        <label class="form-label">Телефон</label>
        <input v-model="form.phone" type="text" class="form-control" required />
      </div>
      <div class="col-md-6">
        <label class="form-label">Дата рождения</label>
        <input v-model="form.birth_date" type="date" class="form-control" />
      </div>
      <div class="col-md-6">
        <label class="form-label">Статус</label>
        <select v-model="form.status" class="form-select" required>
          <option v-for="status in statuses" :key="status" :value="status">
            {{ enumLabel(status) }}
          </option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Заметки</label>
        <textarea v-model="form.notes" class="form-control" rows="3" />
      </div>
    </form>
    <template #footer>
      <button class="btn btn-outline-secondary" type="button" :disabled="loading" @click="close">Отмена</button>
      <button class="btn btn-success" type="button" :disabled="loading" @click="createClient">
        <i class="bi bi-plus-circle me-1" />
        Создать
      </button>
    </template>
  </BaseModal>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from "vue";
import { api } from "../services/api";
import { pushFlash } from "../services/flash";
import { enumLabel } from "../services/labels";
import { eventBus, events } from "../services/eventBus";
import BaseModal from "./BaseModal.vue";

const emit = defineEmits(["created"]);

defineProps({
  statuses: {
    type: Array,
    default: () => [],
  },
});

const loading = ref(false);
const modalRef = ref(null);

const form = reactive(getDefaultForm());

function getDefaultForm() {
  return {
    full_name: "",
    phone: "",
    birth_date: "",
    status: "NEW",
    notes: "",
    contra_pregnancy: false,
    contra_allergy: false,
    contra_skin_damage: false,
    contra_varicose: false,
  };
}

function resetForm() {
  Object.assign(form, getDefaultForm());
}

function open() {
  resetForm();
  modalRef.value?.open();
}

function close() {
  modalRef.value?.close();
}

async function createClient() {
  loading.value = true;
  try {
    await api.post("/clients", form);
    pushFlash("Клиент создан");
    emit("created");
    close();
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  eventBus.$on(events.SHOW_CLIENT_CREATE_MODAL, open);
});

onBeforeUnmount(() => {
  eventBus.$off(events.SHOW_CLIENT_CREATE_MODAL, open);
});
</script>
