<template>
  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-2">
              <label class="form-label">С</label>
              <input v-model="filters.from" type="date" class="form-control" />
            </div>
            <div class="col-md-2">
              <label class="form-label">По</label>
              <input v-model="filters.to" type="date" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Мастер</label>
              <select v-model.number="filters.master_id" class="form-select">
                <option value="">Все мастера</option>
                <option v-for="master in meta.masters" :key="master.id" :value="master.id">
                  {{ master.name }}
                </option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Услуга</label>
              <select v-model.number="filters.service_id" class="form-select">
                <option value="">Все услуги</option>
                <option v-for="service in meta.services" :key="service.id" :value="service.id">
                  {{ service.name }}
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Оборудование</label>
              <select v-model.number="filters.device_id" class="form-select">
                <option value="">Все устройства</option>
                <option v-for="device in meta.devices" :key="device.id" :value="device.id">
                  {{ device.name }}
                </option>
              </select>
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="button" @click="loadStats">
                <i class="bi bi-graph-up-arrow me-1" />
                Построить отчеты
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3" v-for="card in summaryCards" :key="card.label">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <p class="text-secondary mb-1">{{ card.label }}</p>
          <h4 class="mb-0">{{ card.value }}</h4>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">По услугам</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Услуга</th>
                <th>Визиты</th>
                <th>Выручка</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in reports.services" :key="item.service_id">
                <td>{{ item.service_name }}</td>
                <td>{{ item.visits_count }}</td>
                <td>{{ formatMoney(item.revenue) }}</td>
              </tr>
              <tr v-if="!reports.services.length">
                <td colspan="3" class="text-center text-secondary py-3">Нет данных</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">По мастерам</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Мастер</th>
                <th>Визиты</th>
                <th>Выручка</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in reports.masters" :key="item.master_id">
                <td>{{ item.master_name || "-" }}</td>
                <td>{{ item.visits_count }}</td>
                <td>{{ formatMoney(item.revenue) }}</td>
              </tr>
              <tr v-if="!reports.masters.length">
                <td colspan="3" class="text-center text-secondary py-3">Нет данных</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">По клиентам</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Клиент</th>
                <th>Визиты</th>
                <th>Начисления</th>
                <th>Оплаты</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in reports.clients" :key="item.client_id">
                <td>{{ item.client_name }}</td>
                <td>{{ item.visits_count }}</td>
                <td>{{ formatMoney(item.charges) }}</td>
                <td>{{ formatMoney(item.payments) }}</td>
              </tr>
              <tr v-if="!reports.clients.length">
                <td colspan="4" class="text-center text-secondary py-3">Нет данных</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Финансы по дням</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Дата</th>
                <th>Выручка</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in reports.daily_finance" :key="item.date">
                <td>{{ item.date }}</td>
                <td>{{ formatMoney(item.revenue) }}</td>
              </tr>
              <tr v-if="!reports.daily_finance.length">
                <td colspan="2" class="text-center text-secondary py-3">Нет данных</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive } from "vue";
import { api } from "../services/api";

const today = new Date().toISOString().slice(0, 10);
const firstDay = `${today.slice(0, 8)}01`;

const filters = reactive({
  from: firstDay,
  to: today,
  master_id: "",
  service_id: "",
  device_id: "",
});

const meta = reactive({
  masters: [],
  services: [],
  devices: [],
});

const summary = reactive({
  revenue: 0,
  expenses: 0,
  profit: 0,
  average_check: 0,
  device_revenue: 0,
  checks_count: 0,
});

const reports = reactive({
  services: [],
  masters: [],
  clients: [],
  daily_finance: [],
});

const summaryCards = computed(() => [
  { label: "Выручка", value: `${formatMoney(summary.revenue)} EUR` },
  { label: "Расходы", value: `${formatMoney(summary.expenses)} EUR` },
  { label: "Прибыль", value: `${formatMoney(summary.profit)} EUR` },
  { label: "Средний чек", value: `${formatMoney(summary.average_check)} EUR` },
  { label: "Выручка оборудования", value: `${formatMoney(summary.device_revenue)} EUR` },
  { label: "Количество чеков", value: summary.checks_count },
]);

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}

async function loadMeta() {
  const payload = await api.get("/meta");
  meta.masters = payload.masters || [];
  meta.services = payload.services || [];
  meta.devices = payload.devices || [];
}

async function loadStats() {
  const payload = await api.get("/stats", filters);
  Object.assign(summary, payload.summary || {});
  Object.assign(reports, payload.reports || {});
}

onMounted(async () => {
  await loadMeta();
  await loadStats();
});
</script>
