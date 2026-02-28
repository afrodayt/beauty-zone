<template>
  <div class="row g-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">{{ payload.client?.full_name || "Client" }}</h4>
        <RouterLink class="btn btn-primary" :to="`/visits/new?client_id=${route.params.id}`">
          <i class="bi bi-journal-plus me-1" />
          New Visit
        </RouterLink>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header fw-semibold">Client Info</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-5">Phone</dt>
            <dd class="col-7">{{ payload.client?.phone || "-" }}</dd>
            <dt class="col-5">Status</dt>
            <dd class="col-7">{{ payload.client?.status || "-" }}</dd>
            <dt class="col-5">Birth Date</dt>
            <dd class="col-7">{{ payload.client?.birth_date || "-" }}</dd>
            <dt class="col-5">Balance</dt>
            <dd class="col-7">{{ formatMoney(payload.client?.balance_eur) }} EUR</dd>
            <dt class="col-5">Last Visit</dt>
            <dd class="col-7">{{ formatDateTime(payload.client?.last_visit_at) }}</dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Packages</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Name</th>
                <th>Remain/Total</th>
                <th>Expires At</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in payload.packages" :key="item.id">
                <td>{{ item.name }}</td>
                <td>{{ item.remaining_procedures }} / {{ item.total_procedures }}</td>
                <td>{{ formatDateTime(item.expires_at) }}</td>
                <td>{{ item.status }}</td>
              </tr>
              <tr v-if="!payload.packages.length">
                <td colspan="4" class="text-center text-secondary py-3">No packages</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Visits</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Start</th>
                <th>Service</th>
                <th>Status</th>
                <th>Price</th>
                <th />
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in payload.visits" :key="item.id">
                <td>{{ formatDateTime(item.starts_at) }}</td>
                <td>{{ item.service?.name || "-" }}</td>
                <td>{{ item.visit_status }}</td>
                <td>{{ formatMoney(item.price) }}</td>
                <td class="text-end">
                  <RouterLink class="btn btn-sm btn-outline-secondary" :to="`/visits/${item.id}/edit`">Edit</RouterLink>
                </td>
              </tr>
              <tr v-if="!payload.visits.length">
                <td colspan="5" class="text-center text-secondary py-3">No visits</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Payments</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Paid At</th>
                <th>Method</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in payload.payments" :key="item.id">
                <td>{{ formatDateTime(item.paid_at) }}</td>
                <td>{{ item.payment_method }}</td>
                <td>{{ formatMoney(item.amount) }}</td>
              </tr>
              <tr v-if="!payload.payments.length">
                <td colspan="3" class="text-center text-secondary py-3">No payments</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive } from "vue";
import { useRoute } from "vue-router";
import { api } from "../services/api";

const route = useRoute();

const payload = reactive({
  client: null,
  visits: [],
  payments: [],
  packages: [],
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

async function loadClient() {
  const response = await api.get(`/clients/${route.params.id}`);
  payload.client = response.client?.data || response.client;
  payload.visits = response.visits?.data || [];
  payload.payments = response.payments?.data || [];
  payload.packages = response.packages?.data || [];
}

onMounted(loadClient);
</script>
