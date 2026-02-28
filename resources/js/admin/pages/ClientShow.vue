<template>
  <div class="row g-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h5 class="mb-0">Карточка клиента #{{ clientId || "-" }}</h5>
        <small class="text-secondary">{{ payload.client?.full_name || "-" }}</small>
      </div>
      <div class="d-flex gap-2">
        <RouterLink class="btn btn-outline-secondary" to="/clients">
          <i class="bi bi-arrow-left me-1" />
          К списку
        </RouterLink>
        <button class="btn btn-outline-primary" type="button" :disabled="loading || saving" @click="goToNewVisit">
          <i class="bi bi-journal-plus me-1" />
          Новый визит
        </button>
        <button class="btn btn-primary" type="button" :disabled="loading || saving" @click="saveClient">
          <i class="bi bi-floppy me-1" />
          Сохранить
        </button>
      </div>
    </div>

    <div v-if="loading" class="col-12 py-5 text-center text-secondary">
      <div class="spinner-border spinner-border-sm me-2" role="status" />
      Загрузка данных клиента...
    </div>

    <template v-else>
      <div class="col-lg-5">
        <h6 class="mb-3">Данные клиента</h6>
        <form class="row g-2" @submit.prevent="saveClient">
          <div class="col-12">
            <label class="form-label">ФИО</label>
            <input v-model="form.full_name" type="text" class="form-control" required />
          </div>
          <div class="col-12">
            <label class="form-label">Телефон</label>
            <div class="input-group">
              <input v-model="form.phone" type="text" class="form-control" required />
              <a v-if="phoneLink" :href="phoneLink" class="btn btn-outline-success" title="Позвонить клиенту">
                <i class="bi bi-telephone" />
              </a>
            </div>
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
            <div class="mt-2">
              <ClientStatusBadge :status="form.status" />
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Заметки</label>
            <textarea v-model="form.notes" class="form-control" rows="3" />
          </div>
          <div class="col-12">
            <div class="form-check">
              <input
                id="show-contra-pregnancy"
                v-model="form.contra_pregnancy"
                class="form-check-input"
                type="checkbox" />
              <label class="form-check-label" for="show-contra-pregnancy">Беременность</label>
            </div>
            <div class="form-check">
              <input id="show-contra-allergy" v-model="form.contra_allergy" class="form-check-input" type="checkbox" />
              <label class="form-check-label" for="show-contra-allergy">Аллергия</label>
            </div>
            <div class="form-check">
              <input
                id="show-contra-skin-damage"
                v-model="form.contra_skin_damage"
                class="form-check-input"
                type="checkbox" />
              <label class="form-check-label" for="show-contra-skin-damage">Повреждение кожи</label>
            </div>
            <div class="form-check">
              <input
                id="show-contra-varicose"
                v-model="form.contra_varicose"
                class="form-check-input"
                type="checkbox" />
              <label class="form-check-label" for="show-contra-varicose">Варикоз</label>
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
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import ClientStatusBadge from "../components/ClientStatusBadge.vue";
import { api } from "../services/api";
import { pushFlash } from "../services/flash";
import { enumLabel } from "../services/labels";
import { phoneToTelHref } from "../services/phone";

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const saving = ref(false);
const statuses = ref(["NEW", "ACTIVE", "LOST"]);

const payload = reactive({
  client: null,
  visits: [],
  payments: [],
  packages: [],
});

const form = reactive(getDefaultForm());

const clientId = computed(() => Number(route.params.id));
const phoneLink = computed(() => phoneToTelHref(form.phone));

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

function mapClientToForm(client) {
  const contraindications = client?.contraindications || {};

  Object.assign(form, {
    full_name: client?.full_name || "",
    phone: client?.phone || "",
    birth_date: client?.birth_date || "",
    status: client?.status || statuses.value[0] || "NEW",
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

async function loadMeta() {
  const response = await api.get("/meta");
  statuses.value = response.enums?.client_statuses || statuses.value;
}

async function loadClient() {
  const id = clientId.value;

  if (!Number.isInteger(id) || id <= 0) {
    await router.replace("/clients");
    return;
  }

  loading.value = true;
  try {
    const response = await api.get(`/clients/${id}`);
    payload.client = response.client?.data || response.client || null;
    payload.visits = response.visits?.data || [];
    payload.payments = response.payments?.data || [];
    payload.packages = response.packages?.data || [];
    mapClientToForm(payload.client);
  } finally {
    loading.value = false;
  }
}

async function saveClient() {
  const id = clientId.value;

  if (!Number.isInteger(id) || id <= 0 || loading.value) {
    return;
  }

  saving.value = true;
  try {
    const response = await api.put(`/clients/${id}`, form);
    payload.client = response.data || response;
    pushFlash("Клиент обновлен");
    await loadClient();
  } finally {
    saving.value = false;
  }
}

function goToNewVisit() {
  const id = clientId.value;

  if (!Number.isInteger(id) || id <= 0) {
    return;
  }

  router.push({
    path: "/visits/new",
    query: { client_id: String(id) },
  });
}

watch(
  () => route.params.id,
  () => {
    loadClient();
  }
);

onMounted(async () => {
  await loadMeta();
  await loadClient();
});
</script>
