<template>
  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Create Package Template</div>
        <div class="card-body">
          <form class="row g-2" @submit.prevent="createTemplate">
            <div class="col-12">
              <label class="form-label">Name</label>
              <input v-model="templateForm.name" type="text" class="form-control" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Service</label>
              <select v-model.number="templateForm.service_id" class="form-select">
                <option value="">No service</option>
                <option v-for="service in meta.services" :key="service.id" :value="service.id">
                  {{ service.name }}
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Procedures</label>
              <input
                v-model.number="templateForm.procedure_count"
                type="number"
                min="1"
                class="form-control"
                required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Price, EUR</label>
              <input
                v-model.number="templateForm.price"
                type="number"
                min="0"
                step="0.01"
                class="form-control"
                required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Duration (days)</label>
              <input v-model.number="templateForm.duration_days" type="number" min="1" class="form-control" required />
            </div>
            <div class="col-12">
              <button class="btn btn-success" type="submit">Create Template</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Templates</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Name</th>
                <th>Procedures</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in templates" :key="item.id">
                <td>{{ item.name }}</td>
                <td>{{ item.procedure_count }}</td>
                <td>{{ formatMoney(item.price) }}</td>
              </tr>
              <tr v-if="!templates.length">
                <td colspan="3" class="text-center text-secondary py-3">No templates</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Assign Client Package</div>
        <div class="card-body">
          <form class="row g-2" @submit.prevent="createClientPackage">
            <div class="col-md-6">
              <label class="form-label">Client</label>
              <select v-model.number="clientPackageForm.client_id" class="form-select" required>
                <option value="">Select client</option>
                <option v-for="client in meta.clients" :key="client.id" :value="client.id">
                  {{ client.full_name }}
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Template</label>
              <select
                v-model.number="clientPackageForm.package_template_id"
                class="form-select"
                @change="applyTemplate">
                <option value="">Custom package</option>
                <option v-for="item in templates" :key="item.id" :value="item.id">
                  {{ item.name }}
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input v-model="clientPackageForm.name" type="text" class="form-control" required />
            </div>
            <div class="col-md-3">
              <label class="form-label">Total</label>
              <input
                v-model.number="clientPackageForm.total_procedures"
                type="number"
                min="1"
                class="form-control"
                required />
            </div>
            <div class="col-md-3">
              <label class="form-label">Remaining</label>
              <input
                v-model.number="clientPackageForm.remaining_procedures"
                type="number"
                min="0"
                class="form-control"
                required />
            </div>
            <div class="col-md-4">
              <label class="form-label">Amount, EUR</label>
              <input
                v-model.number="clientPackageForm.purchased_amount"
                type="number"
                min="0"
                step="0.01"
                class="form-control"
                required />
            </div>
            <div class="col-md-4">
              <label class="form-label">Expires At</label>
              <input v-model="clientPackageForm.expires_at" type="datetime-local" class="form-control" />
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select v-model="clientPackageForm.status" class="form-select" required>
                <option v-for="status in meta.enums.package_statuses" :key="status" :value="status">
                  {{ status }}
                </option>
              </select>
            </div>
            <div class="col-12">
              <button class="btn btn-success" type="submit">Assign Package</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Client Packages</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Client</th>
                <th>Name</th>
                <th>Remain/Total</th>
                <th>Expires</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in clientPackages" :key="item.id">
                <td>{{ item.client?.full_name || item.client_id }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.remaining_procedures }} / {{ item.total_procedures }}</td>
                <td>{{ formatDateTime(item.expires_at) }}</td>
                <td>{{ item.status }}</td>
              </tr>
              <tr v-if="!clientPackages.length">
                <td colspan="5" class="text-center text-secondary py-3">No client packages</td>
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

const templates = ref([]);
const clientPackages = ref([]);
const meta = reactive({
  clients: [],
  services: [],
  enums: {
    package_statuses: ["ACTIVE", "EXPIRED", "EXHAUSTED", "CANCELED"],
  },
});

const templateForm = reactive({
  name: "",
  service_id: "",
  procedure_count: 10,
  price: 0,
  duration_days: 90,
  description: "",
  is_active: true,
});

const clientPackageForm = reactive({
  client_id: "",
  package_template_id: "",
  name: "",
  total_procedures: 10,
  remaining_procedures: 10,
  purchased_amount: 0,
  expires_at: "",
  status: "ACTIVE",
});

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}

function formatDateTime(value) {
  if (!value) {
    return "-";
  }
  return new Date(value).toLocaleString();
}

async function loadMeta() {
  const payload = await api.get("/meta");
  meta.clients = payload.clients || [];
  meta.services = payload.services || [];
  meta.enums = payload.enums || meta.enums;
}

async function loadTemplates() {
  const payload = await api.get("/package-templates", { per_page: 100 });
  templates.value = payload.data || [];
}

async function loadClientPackages() {
  const payload = await api.get("/client-packages", { per_page: 100 });
  clientPackages.value = payload.data || [];
}

function applyTemplate() {
  const template = templates.value.find((item) => item.id === Number(clientPackageForm.package_template_id));
  if (!template) {
    return;
  }
  clientPackageForm.name = template.name;
  clientPackageForm.total_procedures = template.procedure_count;
  clientPackageForm.remaining_procedures = template.procedure_count;
  clientPackageForm.purchased_amount = template.price;
}

async function createTemplate() {
  await api.post("/package-templates", templateForm);
  pushFlash("Template created");
  templateForm.name = "";
  await loadTemplates();
}

async function createClientPackage() {
  await api.post("/client-packages", {
    ...clientPackageForm,
    expires_at: clientPackageForm.expires_at ? new Date(clientPackageForm.expires_at).toISOString() : null,
  });
  pushFlash("Client package assigned");
  await loadClientPackages();
}

onMounted(async () => {
  await loadMeta();
  await loadTemplates();
  await loadClientPackages();
});
</script>
