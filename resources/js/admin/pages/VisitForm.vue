<template>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">
          {{ isEdit ? "Редактирование визита" : "Создание визита" }}
        </div>
        <div class="card-body">
          <form class="row g-3" @submit.prevent="submitVisit">
            <div class="col-md-6">
              <label class="form-label">Клиент</label>
              <select v-model.number="form.client_id" class="form-select" required>
                <option value="">Выберите клиента</option>
                <option v-for="client in meta.clients" :key="client.id" :value="client.id">
                  {{ client.full_name }} ({{ client.phone }})
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Услуга</label>
              <select v-model.number="form.service_id" class="form-select" required>
                <option value="">Выберите услугу</option>
                <option v-for="service in meta.services" :key="service.id" :value="service.id">
                  {{ service.name }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Мастер</label>
              <select v-model.number="form.master_id" class="form-select">
                <option value="">Без мастера</option>
                <option v-for="master in meta.masters" :key="master.id" :value="master.id">
                  {{ master.name }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Оборудование</label>
              <select v-model.number="form.device_id" class="form-select">
                <option value="">Без оборудования</option>
                <option v-for="device in meta.devices" :key="device.id" :value="device.id">
                  {{ device.name }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Зона</label>
              <input v-model="form.zone" type="text" class="form-control" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Начало (UTC)</label>
              <input v-model="form.starts_at" type="datetime-local" class="form-control" required />
            </div>
            <div class="col-md-3">
              <label class="form-label">Цена, EUR</label>
              <input v-model.number="form.price" type="number" min="0" step="0.01" class="form-control" required />
            </div>
            <div class="col-md-3">
              <label class="form-label">Метод оплаты</label>
              <select v-model="form.payment_method" class="form-select" required>
                <option v-for="method in meta.enums.payment_methods" :key="method" :value="method">
                  {{ enumLabel(method) }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Статус визита</label>
              <select v-model="form.visit_status" class="form-select" required>
                <option v-for="status in meta.enums.visit_statuses" :key="status" :value="status">
                  {{ enumLabel(status) }}
                </option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Комментарий мастера</label>
              <input v-model="form.master_comment" type="text" class="form-control" />
            </div>
            <div class="col-12 form-check">
              <input id="deduct" v-model="form.deduct_from_package" class="form-check-input" type="checkbox" />
              <label class="form-check-label" for="deduct">Списывать из пакета</label>
            </div>
            <div class="col-md-6" v-if="form.deduct_from_package">
              <label class="form-label">Пакет клиента</label>
              <select v-model.number="form.client_package_id" class="form-select">
                <option value="">Выберите пакет</option>
                <option v-for="item in filteredPackages" :key="item.id" :value="item.id">
                  {{ item.name }} ({{ item.remaining_procedures }} осталось)
                </option>
              </select>
            </div>
            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary" type="submit">{{ isEdit ? "Сохранить" : "Создать" }}</button>
              <RouterLink class="btn btn-outline-secondary" to="/calendar">Перейти в календарь</RouterLink>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Правила и заметки</div>
        <div class="card-body small text-secondary">
          <ul class="mb-0">
            <li>Все даты и время визитов отправляются в UTC.</li>
            <li>Проверка конфликта идет по мастеру и starts_at.</li>
            <li>Если включено списание пакета, списывается одна процедура.</li>
            <li>Суммы хранятся как DECIMAL(10,2) в EUR.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import { api } from "../services/api";
import { pushFlash } from "../services/flash";
import { enumLabel } from "../services/labels";

const route = useRoute();
const router = useRouter();

const meta = reactive({
  clients: [],
  services: [],
  devices: [],
  masters: [],
  active_packages: [],
  enums: {
    visit_statuses: ["SCHEDULED", "ARRIVED", "CANCELED", "NO_SHOW"],
    payment_methods: ["CASH", "CARD", "TRANSFER", "PACKAGE"],
  },
});

const form = reactive({
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
  master_comment: "",
  deduct_from_package: false,
});

const isEdit = computed(() => Boolean(route.params.id));

const filteredPackages = computed(() =>
  meta.active_packages.filter((item) => Number(item.client_id) === Number(form.client_id))
);

function toDateTimeLocal(value) {
  if (!value) {
    return "";
  }
  const date = new Date(value);
  const offset = date.getTimezoneOffset();
  const normalized = new Date(date.getTime() - offset * 60 * 1000);
  return normalized.toISOString().slice(0, 16);
}

function toUtcIso(value) {
  if (!value) {
    return null;
  }
  return new Date(value).toISOString();
}

async function loadMeta() {
  const response = await api.get("/meta");
  meta.clients = response.clients || [];
  meta.services = response.services || [];
  meta.devices = response.devices || [];
  meta.masters = response.masters || [];
  meta.active_packages = response.active_packages || [];
  meta.enums = response.enums || meta.enums;
}

async function loadVisit() {
  if (!isEdit.value) {
    if (route.query.client_id) {
      form.client_id = Number(route.query.client_id);
    }
    return;
  }

  const response = await api.get(`/visits/${route.params.id}`);
  Object.assign(form, {
    client_id: response.client_id,
    service_id: response.service_id,
    device_id: response.device_id || "",
    master_id: response.master_id || "",
    client_package_id: response.client_package_id || "",
    zone: response.zone,
    starts_at: toDateTimeLocal(response.starts_at),
    price: Number(response.price),
    payment_method: response.payment_method,
    visit_status: response.visit_status,
    master_comment: response.master_comment || "",
    deduct_from_package: Boolean(response.deduct_from_package),
  });
}

async function submitVisit() {
  const payload = {
    ...form,
    starts_at: toUtcIso(form.starts_at),
  };

  if (!payload.deduct_from_package) {
    payload.client_package_id = null;
  }

  if (isEdit.value) {
    await api.put(`/visits/${route.params.id}`, payload);
    pushFlash("Визит обновлен");
  } else {
    await api.post("/visits", payload);
    pushFlash("Визит создан");
  }

  await router.push({
    path: "/clients",
    query: {
      edit_client_id: String(form.client_id),
    },
  });
}

onMounted(async () => {
  await loadMeta();
  await loadVisit();
});
</script>
