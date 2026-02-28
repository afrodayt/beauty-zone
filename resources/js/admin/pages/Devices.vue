<template>
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Добавить оборудование</div>
        <div class="card-body">
          <form class="row g-2" @submit.prevent="createDevice">
            <div class="col-12">
              <label class="form-label">Название</label>
              <input v-model="form.name" type="text" class="form-control" required />
            </div>
            <div class="col-12">
              <label class="form-label">Дата покупки</label>
              <input v-model="form.purchased_at" type="date" class="form-control" />
            </div>
            <div class="col-12">
              <label class="form-label">Стоимость, EUR</label>
              <input v-model.number="form.cost" type="number" min="0" step="0.01" class="form-control" required />
            </div>
            <div class="col-12">
              <label class="form-label">Заметка</label>
              <textarea v-model="form.note" rows="2" class="form-control" />
            </div>
            <div class="col-12">
              <button class="btn btn-success" type="submit">Создать оборудование</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Оборудование</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Название</th>
                <th>Куплено</th>
                <th>Стоимость</th>
                <th>Заметка</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in devices" :key="item.id">
                <td>{{ item.id }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.purchased_at || "-" }}</td>
                <td>{{ formatMoney(item.cost) }}</td>
                <td>{{ item.note || "-" }}</td>
              </tr>
              <tr v-if="!devices.length">
                <td colspan="5" class="text-center text-secondary py-3">Оборудования пока нет.</td>
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

const devices = ref([]);
const form = reactive({
  name: "",
  purchased_at: "",
  cost: 0,
  note: "",
});

function formatMoney(value) {
  return Number(value || 0).toFixed(2);
}

async function loadDevices() {
  const payload = await api.get("/devices", { per_page: 100 });
  devices.value = payload.data || [];
}

async function createDevice() {
  await api.post("/devices", form);
  pushFlash("Оборудование создано");
  form.name = "";
  form.purchased_at = "";
  form.cost = 0;
  form.note = "";
  await loadDevices();
}

onMounted(loadDevices);
</script>
