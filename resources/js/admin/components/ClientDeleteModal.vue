<template>
  <BaseModal
    id="client-delete-modal"
    title="Удаление клиента"
    size="sm"
    static-backdrop
    ref="modalRef"
    @closed="handleModalClosed">
    <p class="mb-2">
      Вы действительно хотите удалить клиента
      <strong>{{ clientName || `#${selectedClientId}` }}</strong>
      ?
    </p>
    <p class="mb-0 text-secondary small">Действие необратимо.</p>

    <template #footer>
      <button class="btn btn-outline-secondary" type="button" :disabled="loading" @click="close">Отмена</button>
      <button class="btn btn-danger" type="button" :disabled="loading || !selectedClientId" @click="deleteClient">
        <i class="bi bi-trash me-1" />
        Удалить
      </button>
    </template>
  </BaseModal>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import BaseModal from "./BaseModal.vue";
import { api } from "../services/api";
import { eventBus, events } from "../services/eventBus";
import { pushFlash } from "../services/flash";

const emit = defineEmits(["deleted"]);

const modalRef = ref(null);
const loading = ref(false);
const selectedClientId = ref(null);
const clientName = ref("");

function open(clientId, fullName = "") {
  selectedClientId.value = clientId;
  clientName.value = fullName;
  modalRef.value?.open();
}

function close() {
  modalRef.value?.close();
}

function handleOpenEvent(eventPayload) {
  const clientId = Number(eventPayload?.clientId ?? eventPayload);

  if (!Number.isInteger(clientId) || clientId <= 0) {
    return;
  }

  open(clientId, String(eventPayload?.clientName || "").trim());
}

async function deleteClient() {
  if (!selectedClientId.value || loading.value) {
    return;
  }

  loading.value = true;
  try {
    await api.delete(`/clients/${selectedClientId.value}`);
    pushFlash("Клиент удален");
    emit("deleted", selectedClientId.value);
    close();
  } finally {
    loading.value = false;
  }
}

function handleModalClosed() {
  selectedClientId.value = null;
  clientName.value = "";
}

onMounted(() => {
  eventBus.$on(events.SHOW_CLIENT_DELETE_MODAL, handleOpenEvent);
});

onBeforeUnmount(() => {
  eventBus.$off(events.SHOW_CLIENT_DELETE_MODAL, handleOpenEvent);
});
</script>
