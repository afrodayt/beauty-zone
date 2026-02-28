<template>
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
                placeholder="Имя или e-mail"
                @keyup.enter="loadUsers(1)" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Роль</label>
              <select v-model="filters.role" class="form-select">
                <option value="">Все роли</option>
                <option v-for="role in roles" :key="role" :value="role">
                  {{ enumLabel(role) }}
                </option>
              </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
              <button class="btn btn-primary" type="button" :disabled="loading" @click="loadUsers(1)">
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

    <div class="col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-semibold">Новый пользователь</div>
        <div class="card-body">
          <form class="row g-2" @submit.prevent="createUser">
            <div class="col-12">
              <label class="form-label">Имя</label>
              <input v-model="form.name" type="text" class="form-control" required />
            </div>
            <div class="col-12">
              <label class="form-label">E-mail</label>
              <input v-model="form.email" type="email" class="form-control" required />
            </div>
            <div class="col-12">
              <label class="form-label">Роль</label>
              <select v-model="form.role" class="form-select" required>
                <option v-for="role in roles" :key="role" :value="role">
                  {{ enumLabel(role) }}
                </option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Пароль</label>
              <input v-model="form.password" type="text" class="form-control" required />
            </div>
            <div class="col-12">
              <button class="btn btn-success" type="submit" :disabled="loading">
                <i class="bi bi-person-plus me-1" />
                Добавить
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
          <span>Пользователи</span>
          <small class="text-secondary">Всего: {{ meta.total || users.length }}</small>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Имя</th>
                <th>E-mail</th>
                <th>Роль</th>
                <th>Создан</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="badge text-bg-light">{{ enumLabel(user.role) }}</span>
                </td>
                <td>{{ formatDateTime(user.created_at) }}</td>
              </tr>
              <tr v-if="!users.length">
                <td colspan="5" class="text-center text-secondary py-4">Пользователи не найдены.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            :disabled="meta.current_page <= 1"
            @click="loadUsers(meta.current_page - 1)">
            Назад
          </button>
          <small class="text-secondary">Страница {{ meta.current_page || 1 }} / {{ meta.last_page || 1 }}</small>
          <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            :disabled="meta.current_page >= meta.last_page"
            @click="loadUsers(meta.current_page + 1)">
            Вперед
          </button>
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

const loading = ref(false);
const roles = ref(["ADMIN", "MASTER"]);
const users = ref([]);
const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const filters = reactive({
  search: "",
  role: "",
});

const form = reactive({
  name: "",
  email: "",
  role: "MASTER",
  password: "",
});

function formatDateTime(value) {
  if (!value) {
    return "-";
  }

  return new Date(value).toLocaleString("ru-RU");
}

async function loadUsers(page = 1) {
  loading.value = true;
  try {
    const payload = await api.get("/users", {
      ...filters,
      page,
    });
    users.value = payload.data || [];
    meta.value = payload.meta || meta.value;
  } finally {
    loading.value = false;
  }
}

async function createUser() {
  loading.value = true;
  try {
    await api.post("/users", form);
    pushFlash("Пользователь добавлен");
    Object.assign(form, {
      name: "",
      email: "",
      role: "MASTER",
      password: "",
    });
    await loadUsers(1);
  } finally {
    loading.value = false;
  }
}

function resetFilters() {
  filters.search = "";
  filters.role = "";
  loadUsers(1);
}

onMounted(() => {
  loadUsers(1);
});
</script>
