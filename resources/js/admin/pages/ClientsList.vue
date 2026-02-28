<template>
  <div>
    <div class="row g-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <div class="col-md-5">
                <label class="form-label">Поиск</label>
                <input
                  v-model="filters.search"
                  type="text"
                  class="form-control"
                  placeholder="Имя или телефон"
                  @keyup.enter="loadClients(1)" />
              </div>
              <div class="col-md-3">
                <label class="form-label">Статус</label>
                <select v-model="filters.status" class="form-select">
                  <option value="">Все статусы</option>
                  <option v-for="status in statuses" :key="status" :value="status">
                    {{ enumLabel(status) }}
                  </option>
                </select>
              </div>
              <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary" type="button" :disabled="loading" @click="loadClients(1)">
                  <i class="bi bi-search me-1" />
                  Применить
                </button>
                <button class="btn btn-outline-secondary" type="button" :disabled="loading" @click="resetFilters">
                  Сброс
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
            <span>Клиенты</span>
            <div class="d-flex align-items-center gap-3">
              <small class="text-secondary">Всего: {{ meta.total || clients.length }}</small>
              <button class="btn btn-success btn-sm" type="button" :disabled="loading" @click="openCreateModal">
                <i class="bi bi-plus-circle me-1" />
                Создать клиента
              </button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Клиент</th>
                  <th>Телефон</th>
                  <th>Статус</th>
                  <th>Баланс, EUR</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                <tr v-for="client in clients" :key="client.id">
                  <td>{{ client.id }}</td>
                  <td>{{ client.full_name }}</td>
                  <td>
                    <a
                      v-if="phoneToTelHref(client.phone)"
                      :href="phoneToTelHref(client.phone)"
                      class="link-primary text-decoration-none">
                      {{ client.phone }}
                    </a>
                    <span v-else>{{ client.phone || "-" }}</span>
                  </td>
                  <td>
                    <ClientStatusBadge :status="client.status" />
                  </td>
                  <td>{{ client.balance_eur.toFixed(2) }}</td>
                  <td class="text-end">
                    <div class="d-inline-flex gap-2">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        title="Редактировать клиента"
                        @click="openEditModal(client.id)">
                        <i class="bi bi-pencil-square" />
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        title="Удалить клиента"
                        @click="openDeleteModal(client)">
                        <i class="bi bi-trash" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!clients.length">
                  <td colspan="6" class="text-center text-secondary py-4">Клиенты не найдены.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer d-flex justify-content-between">
            <button
              class="btn btn-sm btn-outline-secondary"
              type="button"
              :disabled="meta.current_page <= 1"
              @click="loadClients(meta.current_page - 1)">
              Назад
            </button>
            <small class="text-secondary">Страница {{ meta.current_page || 1 }} / {{ meta.last_page || 1 }}</small>
            <button
              class="btn btn-sm btn-outline-secondary"
              type="button"
              :disabled="meta.current_page >= meta.last_page"
              @click="loadClients(meta.current_page + 1)">
              Вперед
            </button>
          </div>
        </div>
      </div>
    </div>
    <ClientCreateModal :statuses="statuses" @created="handleClientCreated" />
    <ClientEditModal :statuses="statuses" @updated="handleClientUpdated" />
    <ClientDeleteModal @deleted="handleClientDeleted" />
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import ClientCreateModal from "../components/ClientCreateModal.vue";
import ClientDeleteModal from "../components/ClientDeleteModal.vue";
import ClientEditModal from "../components/ClientEditModal.vue";
import ClientStatusBadge from "../components/ClientStatusBadge.vue";
import { api } from "../services/api";
import { eventBus, events } from "../services/eventBus";
import { enumLabel } from "../services/labels";

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const statuses = ref(["NEW", "ACTIVE", "LOST"]);
const clients = ref([]);
const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const filters = reactive({
  search: "",
  status: "",
});

async function loadMeta() {
  const payload = await api.get("/meta");
  statuses.value = payload.enums?.client_statuses || statuses.value;
}

async function loadClients(page = 1) {
  loading.value = true;
  try {
    const payload = await api.get("/clients", {
      ...filters,
      page,
    });
    clients.value = payload.data || [];
    meta.value = payload.meta || meta.value;
  } finally {
    loading.value = false;
  }
}

function resetFilters() {
  filters.search = "";
  filters.status = "";
  loadClients(1);
}

function openCreateModal() {
  eventBus.$emit(events.SHOW_CLIENT_CREATE_MODAL);
}

function openEditModal(clientId) {
  eventBus.$emit(events.SHOW_CLIENT_EDIT_MODAL, { clientId });
}

function openDeleteModal(client) {
  eventBus.$emit(events.SHOW_CLIENT_DELETE_MODAL, {
    clientId: client.id,
    clientName: client.full_name,
  });
}

function handleClientCreated() {
  loadClients(1);
}

function handleClientUpdated() {
  loadClients(meta.value.current_page || 1);
}

function handleClientDeleted() {
  const currentPage = meta.value.current_page || 1;
  const nextPage = clients.value.length === 1 ? Math.max(1, currentPage - 1) : currentPage;
  loadClients(nextPage);
}

function phoneToTelHref(phone) {
  const raw = String(phone || "").trim();

  if (!raw) {
    return "";
  }

  const hasLeadingPlus = raw.startsWith("+");
  const digits = raw.replace(/\D/g, "");

  if (!digits) {
    return "";
  }

  return hasLeadingPlus ? `tel:+${digits}` : `tel:${digits}`;
}

watch(
  () => route.query.edit_client_id,
  (value) => handleEditClientFromQuery(value)
);

onMounted(async () => {
  await loadMeta();
  await loadClients(1);
  handleEditClientFromQuery(route.query.edit_client_id);
});

function handleEditClientFromQuery(queryValue) {
  const clientId = Number(queryValue);

  if (!Number.isInteger(clientId) || clientId <= 0) {
    return;
  }

  openEditModal(clientId);

  const nextQuery = { ...route.query };
  delete nextQuery.edit_client_id;
  router.replace({ path: route.path, query: nextQuery });
}
</script>
