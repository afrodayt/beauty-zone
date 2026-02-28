<template>
  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label">From</label>
              <input v-model="filters.from" type="date" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">To</label>
              <input v-model="filters.to" type="date" class="form-control" />
            </div>
            <div class="col-md-3">
              <button class="btn btn-primary" type="button" @click="loadFinance">
                <i class="bi bi-arrow-repeat me-1" />
                Refresh
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3" v-for="card in cards" :key="card.label">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <p class="text-secondary mb-1">{{ card.label }}</p>
          <h4 class="mb-0">{{ card.value }}</h4>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Add Payment</div>
        <div class="card-body">
          <form class="row g-2" @submit.prevent="createPayment">
            <div class="col-12">
              <label class="form-label">Client</label>
              <select v-model.number="paymentForm.client_id" class="form-select" required>
                <option value="">Select client</option>
                <option v-for="client in meta.clients" :key="client.id" :value="client.id">
                  {{ client.full_name }}
                </option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Amount, EUR</label>
              <input
                v-model.number="paymentForm.amount"
                type="number"
                min="0.01"
                step="0.01"
                class="form-control"
                required />
            </div>
            <div class="col-6">
              <label class="form-label">Method</label>
              <select v-model="paymentForm.payment_method" class="form-select" required>
                <option v-for="method in meta.enums.payment_methods" :key="method" :value="method">
                  {{ method }}
                </option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Paid At</label>
              <input v-model="paymentForm.paid_at" type="datetime-local" class="form-control" required />
            </div>
            <div class="col-12">
              <button class="btn btn-success" type="submit">Save Payment</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Add Expense</div>
        <div class="card-body">
          <form class="row g-2" @submit.prevent="createExpense">
            <div class="col-6">
              <label class="form-label">Type</label>
              <select v-model="expenseForm.type" class="form-select" required>
                <option v-for="type in meta.enums.expense_types" :key="type" :value="type">
                  {{ type }}
                </option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Amount, EUR</label>
              <input
                v-model.number="expenseForm.amount"
                type="number"
                min="0.01"
                step="0.01"
                class="form-control"
                required />
            </div>
            <div class="col-12">
              <label class="form-label">Date</label>
              <input v-model="expenseForm.date" type="date" class="form-control" required />
            </div>
            <div class="col-12">
              <button class="btn btn-success" type="submit">Save Expense</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Payments</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Client</th>
                <th>Paid At</th>
                <th>Method</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in payments" :key="item.id">
                <td>{{ item.client?.full_name || item.client_id }}</td>
                <td>{{ formatDateTime(item.paid_at) }}</td>
                <td>{{ item.payment_method }}</td>
                <td>{{ formatMoney(item.amount) }}</td>
              </tr>
              <tr v-if="!payments.length">
                <td colspan="4" class="text-center text-secondary py-3">No payments</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Expenses</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Note</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in expenses" :key="item.id">
                <td>{{ item.date }}</td>
                <td>{{ item.type }}</td>
                <td>{{ formatMoney(item.amount) }}</td>
                <td>{{ item.note || "-" }}</td>
              </tr>
              <tr v-if="!expenses.length">
                <td colspan="4" class="text-center text-secondary py-3">No expenses</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { api } from "../services/api";
import { pushFlash } from "../services/flash";

const today = new Date().toISOString().slice(0, 10);
const firstDay = `${today.slice(0, 8)}01`;

const filters = reactive({
  from: firstDay,
  to: today,
});

const summary = reactive({
  revenue: 0,
  expenses: 0,
  profit: 0,
  average_check: 0,
});

const payments = ref([]);
const expenses = ref([]);

const meta = reactive({
  clients: [],
  enums: {
    payment_methods: ["CASH", "CARD", "TRANSFER", "PACKAGE"],
    expense_types: ["RENT", "SALARY", "SUPPLIES", "MARKETING", "OTHER"],
  },
});

const paymentForm = reactive({
  client_id: "",
  visit_id: null,
  amount: 0,
  payment_method: "CASH",
  paid_at: "",
  note: "",
});

const expenseForm = reactive({
  type: "OTHER",
  amount: 0,
  date: today,
  note: "",
});

const cards = computed(() => [
  { label: "Revenue", value: `${formatMoney(summary.revenue)} EUR` },
  { label: "Expenses", value: `${formatMoney(summary.expenses)} EUR` },
  { label: "Profit", value: `${formatMoney(summary.profit)} EUR` },
  { label: "Average Check", value: `${formatMoney(summary.average_check)} EUR` },
]);

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
  meta.enums = payload.enums || meta.enums;
}

async function loadFinance() {
  const payload = await api.get("/finance", filters);
  Object.assign(summary, payload.summary || {});
  payments.value = payload.payments?.data || [];
  expenses.value = payload.expenses?.data || [];
}

async function createPayment() {
  await api.post("/payments", {
    ...paymentForm,
    paid_at: new Date(paymentForm.paid_at).toISOString(),
  });
  pushFlash("Payment saved");
  await loadFinance();
}

async function createExpense() {
  await api.post("/expenses", expenseForm);
  pushFlash("Expense saved");
  await loadFinance();
}

onMounted(async () => {
  await loadMeta();
  await loadFinance();
});
</script>
