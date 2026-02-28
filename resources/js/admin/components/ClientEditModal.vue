<template>
  <BaseModal
    id="client-edit-modal"
    title="Редактирование клиента"
    size="xl"
    static-backdrop
    ref="modalRef"
    @closed="handleModalClosed">
    <div v-if="loading" class="py-5 text-center text-secondary">
      <div class="spinner-border spinner-border-sm me-2" role="status" />
      Загрузка данных клиента...
    </div>

    <div v-else-if="!selectedClientId" class="text-secondary">Клиент не выбран.</div>

    <div v-else class="row g-4">
      <div class="col-lg-5">
        <h6 class="mb-3">Данные клиента</h6>
        <form class="row g-2" @submit.prevent="saveClient">
          <div class="col-12">
            <label class="form-label">ФИО</label>
            <input v-model="form.full_name" type="text" class="form-control" required />
          </div>
          <div class="col-12">
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
          <div class="col-12">
            <div class="form-check">
              <input id="contra_pregnancy" v-model="form.contra_pregnancy" class="form-check-input" type="checkbox" />
              <label class="form-check-label" for="contra_pregnancy">Беременность</label>
            </div>
            <div class="form-check">
              <input id="contra_allergy" v-model="form.contra_allergy" class="form-check-input" type="checkbox" />
              <label class="form-check-label" for="contra_allergy">Аллергия</label>
            </div>
            <div class="form-check">
              <input
                id="contra_skin_damage"
                v-model="form.contra_skin_damage"
                class="form-check-input"
                type="checkbox" />
              <label class="form-check-label" for="contra_skin_damage">Повреждение кожи</label>
            </div>
            <div class="form-check">
              <input id="contra_varicose" v-model="form.contra_varicose" class="form-check-input" type="checkbox" />
              <label class="form-check-label" for="contra_varicose">Варикоз</label>
            </div>
          </div>
        </form>
      </div>

      <div class="col-lg-7">
        <div class="card bg-light border-0">
          <div class="card-body py-3">
            <div class="row g-2 small">
              <div class="col-md-6">
                <div class="text-secondary">Баланс</div>
                <div class="fw-semibold">{{ formatMoney(payload.client?.balance_eur) }} EUR</div>
              </div>
              <div class="col-md-6">
                <div class="text-secondary">Последний визит</div>
                <div class="fw-semibold">{{ formatDateTime(payload.client?.last_visit_at) }}</div>
              </div>
              <div class="col-md-6">
                <div class="text-secondary">Создан</div>
                <div class="fw-semibold">{{ formatDateTime(payload.client?.created_at) }}</div>
              </div>
              <div class="col-md-6">
                <div class="text-secondary">Обновлен</div>
                <div class="fw-semibold">{{ formatDateTime(payload.client?.updated_at) }}</div>
              </div>
            </div>
          </div>
        </div>

        <h6 class="mt-4 mb-2">Пакеты</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Название</th>
                <th>Осталось</th>
                <th>До</th>
                <th>Статус</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in payload.packages" :key="item.id">
                <td>{{ item.name }}</td>
                <td>{{ item.remaining_procedures }} / {{ item.total_procedures }}</td>
                <td>{{ formatDateTime(item.expires_at) }}</td>
                <td>{{ enumLabel(item.status) }}</td>
              </tr>
              <tr v-if="!payload.packages.length">
                <td colspan="4" class="text-center text-secondary py-3">Нет пакетов</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="col-lg-6">
        <h6 class="mb-2">Визиты</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Начало</th>
                <th>Услуга</th>
                <th>Статус</th>
                <th>Сумма</th>
                <th />
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in payload.visits" :key="item.id">
                <td>{{ formatDateTime(item.starts_at) }}</td>
                <td>{{ item.service?.name || "-" }}</td>
                <td>{{ enumLabel(item.visit_status) }}</td>
                <td>{{ formatMoney(item.price) }}</td>
                <td class="text-end">
                  <RouterLink class="btn btn-sm btn-outline-secondary" :to="`/visits/${item.id}/edit`">
                    <i class="bi bi-pencil-square" />
                  </RouterLink>
                </td>
              </tr>
              <tr v-if="!payload.visits.length">
                <td colspan="5" class="text-center text-secondary py-3">Нет визитов</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="col-lg-6">
        <h6 class="mb-2">Платежи</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Дата</th>
                <th>Метод</th>
                <th>Сумма</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in payload.payments" :key="item.id">
                <td>{{ formatDateTime(item.paid_at) }}</td>
                <td>{{ enumLabel(item.payment_method) }}</td>
                <td>{{ formatMoney(item.amount) }}</td>
              </tr>
              <tr v-if="!payload.payments.length">
                <td colspan="3" class="text-center text-secondary py-3">Нет платежей</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <template #footer>
      <button class="btn btn-outline-primary" type="button" :disabled="loading || saving" @click="goToNewVisit">
        <i class="bi bi-journal-plus me-1" />
        Новый визит
      </button>
      <button class="btn btn-outline-secondary" type="button" :disabled="saving" @click="close">Закрыть</button>
      <button class="btn btn-primary" type="button" :disabled="loading || saving" @click="saveClient">
        <i class="bi bi-floppy me-1" />
        Сохранить
      </button>
    </template>
  </BaseModal>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import BaseModal from "./BaseModal.vue";
import { api } from "../services/api";
import { eventBus, events } from "../services/eventBus";
import { pushFlash } from "../services/flash";
import { enumLabel } from "../services/labels";

const props = defineProps({
  statuses: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["updated"]);

const router = useRouter();
const modalRef = ref(null);
const loading = ref(false);
const saving = ref(false);
const selectedClientId = ref(null);

const payload = reactive({
  client: null,
  visits: [],
  payments: [],
  packages: [],
});

const form = reactive(getDefaultForm());

function getDefaultForm() {
  return {
    full_name: "",
    phone: "",
    birth_date: "",
    status: props.statuses[0] || "NEW",
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

function resetPayload() {
  payload.client = null;
  payload.visits = [];
  payload.payments = [];
  payload.packages = [];
}

function mapClientToForm(client) {
  const contraindications = client?.contraindications || {};

  Object.assign(form, {
    full_name: client?.full_name || "",
    phone: client?.phone || "",
    birth_date: client?.birth_date || "",
    status: client?.status || props.statuses[0] || "NEW",
    notes: client?.notes || "",
    contra_pregnancy: Boolean(contraindications.pregnancy),
    contra_allergy: Boolean(contraindications.allergy),
    contra_skin_damage: Boolean(contraindications.skin_damage),
    contra_varicose: Boolean(contraindications.varicose),
  });
}

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}

function formatDateTime(value) {
  if (!value) {
    return "-";
  }

  return new Date(value).toLocaleString("ru-RU");
}

async function loadClient(clientId) {
  loading.value = true;
  try {
    const response = await api.get(`/clients/${clientId}`);
    payload.client = response.client?.data || response.client || null;
    payload.visits = response.visits?.data || [];
    payload.payments = response.payments?.data || [];
    payload.packages = response.packages?.data || [];
    mapClientToForm(payload.client);
  } finally {
    loading.value = false;
  }
}

function open(clientId) {
  selectedClientId.value = clientId;
  modalRef.value?.open();
  loadClient(clientId);
}

function close() {
  modalRef.value?.close();
}

function handleOpenEvent(eventPayload) {
  const clientId = Number(eventPayload?.clientId ?? eventPayload);

  if (!Number.isInteger(clientId) || clientId <= 0) {
    return;
  }

  resetForm();
  resetPayload();
  open(clientId);
}

async function saveClient() {
  if (!selectedClientId.value || loading.value) {
    return;
  }

  saving.value = true;
  try {
    const response = await api.put(`/clients/${selectedClientId.value}`, form);
    payload.client = response.data || response;
    pushFlash("Клиент обновлен");
    emit("updated");
    close();
  } finally {
    saving.value = false;
  }
}

function goToNewVisit() {
  if (!selectedClientId.value) {
    return;
  }

  close();
  router.push({
    path: "/visits/new",
    query: { client_id: String(selectedClientId.value) },
  });
}

function handleModalClosed() {
  selectedClientId.value = null;
  resetForm();
  resetPayload();
}

onMounted(() => {
  eventBus.$on(events.SHOW_CLIENT_EDIT_MODAL, handleOpenEvent);
});

onBeforeUnmount(() => {
  eventBus.$off(events.SHOW_CLIENT_EDIT_MODAL, handleOpenEvent);
});
</script>
