<template>
  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label">Режим</label>
              <select v-model="filters.view" class="form-select">
                <option value="day">День</option>
                <option value="week">Неделя</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Дата</label>
              <input v-model="filters.date" type="date" class="form-control" />
            </div>
            <div class="col-md-4">
              <label class="form-label">Мастер</label>
              <select v-model.number="filters.master_id" class="form-select">
                <option value="">Все мастера</option>
                <option v-for="master in masters" :key="master.id" :value="master.id">
                  {{ master.name }}
                </option>
              </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
              <button class="btn btn-primary" type="button" @click="loadEvents">
                <i class="bi bi-arrow-repeat me-1" />
                Обновить
              </button>
              <button class="btn btn-success" type="button" @click="showQuickForm = !showQuickForm">
                <i class="bi bi-plus-circle me-1" />
                Быстро
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12" v-if="showQuickForm">
      <div class="card border-success shadow-sm">
        <div class="card-header fw-semibold">Быстрое создание визита</div>
        <div class="card-body">
          <form class="row g-2" @submit.prevent="quickCreateVisit">
            <div class="col-md-3">
              <label class="form-label">Клиент</label>
              <select v-model.number="quickForm.client_id" class="form-select" required>
                <option value="">Выберите клиента</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.full_name }}
                </option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Услуга</label>
              <select v-model.number="quickForm.service_id" class="form-select" required>
                <option value="">Выберите услугу</option>
                <option v-for="service in services" :key="service.id" :value="service.id">
                  {{ service.name }}
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Мастер</label>
              <select v-model.number="quickForm.master_id" class="form-select">
                <option value="">Без мастера</option>
                <option v-for="master in masters" :key="master.id" :value="master.id">
                  {{ master.name }}
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Начало</label>
              <input v-model="quickForm.starts_at" type="datetime-local" class="form-control" required />
            </div>
            <div class="col-md-1">
              <label class="form-label">Цена</label>
              <input v-model.number="quickForm.price" type="number" step="0.01" min="0" class="form-control" required />
            </div>
            <div class="col-md-1 d-flex align-items-end">
              <button class="btn btn-success w-100" type="submit">Сохранить</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold d-flex justify-content-between">
          <span>Расписание</span>
          <small class="text-secondary">{{ calendar.from_utc }} - {{ calendar.to_utc }}</small>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>Начало</th>
                <th>Окончание</th>
                <th>Событие</th>
                <th>Мастер</th>
                <th>Зона</th>
                <th>Статус</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in calendar.events" :key="event.id">
                <td>{{ formatDateTime(event.starts_at) }}</td>
                <td>{{ formatDateTime(event.ends_at) }}</td>
                <td>
                  <span class="badge me-2" :style="{ backgroundColor: event.color }">&nbsp;</span>
                  {{ event.title }}
                </td>
                <td>{{ event.master_name || "-" }}</td>
                <td>{{ event.zone }}</td>
                <td>{{ enumLabel(event.visit_status) }}</td>
              </tr>
              <tr v-if="!calendar.events.length">
                <td colspan="6" class="text-center text-secondary py-4">На выбранный период событий нет.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { api } from "../services/api";
import { pushFlash } from "../services/flash";
import { enumLabel } from "../services/labels";

const showQuickForm = ref(false);
const masters = ref([]);
const clients = ref([]);
const services = ref([]);

const filters = reactive({
  view: "day",
  date: new Date().toISOString().slice(0, 10),
  master_id: "",
});

const calendar = reactive({
  from_utc: "",
  to_utc: "",
  events: [],
});

const quickForm = reactive({
  client_id: "",
  service_id: "",
  device_id: "",
  master_id: "",
  client_package_id: "",
  zone: "Тело",
  starts_at: "",
  price: 0,
  payment_method: "CASH",
  visit_status: "SCHEDULED",
  master_comment: "Быстрое создание",
  deduct_from_package: false,
});

function formatDateTime(value) {
  if (!value) {
    return "-";
  }
  return new Date(value).toLocaleString("ru-RU");
}

async function loadMeta() {
  const payload = await api.get("/meta");
  masters.value = payload.masters || [];
  clients.value = payload.clients || [];
  services.value = payload.services || [];
}

async function loadEvents() {
  const payload = await api.get("/calendar", filters);
  calendar.from_utc = payload.from_utc;
  calendar.to_utc = payload.to_utc;
  calendar.events = payload.events || [];
}

async function quickCreateVisit() {
  await api.post("/calendar/quick-visit", {
    ...quickForm,
    starts_at: new Date(quickForm.starts_at).toISOString(),
  });
  pushFlash("Визит создан из календаря");
  showQuickForm.value = false;
  await loadEvents();
}

onMounted(async () => {
  await loadMeta();
  await loadEvents();
});
</script>
