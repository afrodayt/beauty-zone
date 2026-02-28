<template>
  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-5">
              <label class="form-label">Search</label>
              <input
                v-model="filters.search"
                type="text"
                class="form-control"
                placeholder="Name or phone"
                @keyup.enter="loadClients(1)" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select v-model="filters.status" class="form-select">
                <option value="">All statuses</option>
                <option v-for="status in statuses" :key="status" :value="status">
                  {{ status }}
                </option>
              </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
              <button class="btn btn-primary" type="button" :disabled="loading" @click="loadClients(1)">
                <i class="bi bi-search me-1" />
                Filter
              </button>
              <button class="btn btn-outline-secondary" type="button" :disabled="loading" @click="resetFilters">
                Reset
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-semibold">New Client</div>
        <div class="card-body">
          <form class="row g-2" @submit.prevent="createClient">
            <div class="col-12">
              <label class="form-label">Full Name</label>
              <input v-model="form.full_name" type="text" class="form-control" required />
            </div>
            <div class="col-12">
              <label class="form-label">Phone</label>
              <input v-model="form.phone" type="text" class="form-control" required />
            </div>
            <div class="col-12">
              <label class="form-label">Birth Date</label>
              <input v-model="form.birth_date" type="date" class="form-control" />
            </div>
            <div class="col-12">
              <label class="form-label">Status</label>
              <select v-model="form.status" class="form-select" required>
                <option v-for="status in statuses" :key="status" :value="status">
                  {{ status }}
                </option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea v-model="form.notes" class="form-control" rows="2" />
            </div>
            <div class="col-12">
              <button class="btn btn-success" type="submit" :disabled="loading">
                <i class="bi bi-plus-circle me-1" />
                Create
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
          <span>Clients</span>
          <small class="text-secondary">Total: {{ meta.total || clients.length }}</small>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Client</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Balance, EUR</th>
                <th />
              </tr>
            </thead>
            <tbody>
              <tr v-for="client in clients" :key="client.id">
                <td>{{ client.id }}</td>
                <td>{{ client.full_name }}</td>
                <td>{{ client.phone }}</td>
                <td>
                  <span class="badge text-bg-light">{{ client.status }}</span>
                </td>
                <td>{{ client.balance_eur.toFixed(2) }}</td>
                <td class="text-end">
                  <RouterLink class="btn btn-sm btn-outline-primary" :to="`/clients/${client.id}`">Open</RouterLink>
                </td>
              </tr>
              <tr v-if="!clients.length">
                <td colspan="6" class="text-center text-secondary py-4">No clients found.</td>
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
            Prev
          </button>
          <small class="text-secondary">Page {{ meta.current_page || 1 }} / {{ meta.last_page || 1 }}</small>
          <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            :disabled="meta.current_page >= meta.last_page"
            @click="loadClients(meta.current_page + 1)">
            Next
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

const form = reactive({
  full_name: "",
  phone: "",
  birth_date: "",
  status: "NEW",
  notes: "",
  contra_pregnancy: false,
  contra_allergy: false,
  contra_skin_damage: false,
  contra_varicose: false,
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

async function createClient() {
  loading.value = true;
  try {
    await api.post("/clients", form);
    pushFlash("Client created");
    Object.assign(form, {
      full_name: "",
      phone: "",
      birth_date: "",
      status: "NEW",
      notes: "",
      contra_pregnancy: false,
      contra_allergy: false,
      contra_skin_damage: false,
      contra_varicose: false,
    });
    await loadClients(1);
  } finally {
    loading.value = false;
  }
}

function resetFilters() {
  filters.search = "";
  filters.status = "";
  loadClients(1);
}

onMounted(async () => {
  await loadMeta();
  await loadClients(1);
});
</script>
