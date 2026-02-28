<template>
  <div class="admin-main position-relative">
    <header class="bg-white border-bottom sticky-top shadow-sm">
      <div class="container-fluid py-2 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <h1 class="h5 mb-0">
            <i class="bi bi-stars me-2" />
            Beauté Zone
          </h1>
          <nav class="nav nav-pills admin-nav">
            <RouterLink v-for="item in navItems" :key="item.to" class="nav-link" :to="item.to" active-class="active">
              <i class="bi me-1" :class="item.icon" />
              {{ item.label }}
            </RouterLink>
          </nav>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="text-secondary small">UTC: {{ utcNow }}</span>
          <span class="text-secondary small">Все суммы в EUR</span>
          <button type="button" class="btn btn-sm btn-outline-secondary" @click="logout">Выйти</button>
        </div>
      </div>
      <div class="container-fluid pb-2">
        <BreadcrumbsBar />
      </div>
    </header>

    <main class="container-fluid py-4">
      <RouterView />
    </main>
    <FlashNotifications />
  </div>
</template>

<script setup>
import { computed } from "vue";
import BreadcrumbsBar from "../components/BreadcrumbsBar.vue";
import FlashNotifications from "../components/FlashNotifications.vue";

const navItems = [
  { to: "/clients", label: "Клиенты", icon: "bi-people" },
  { to: "/calendar", label: "Календарь", icon: "bi-calendar3" },
  { to: "/visits/new", label: "Визит", icon: "bi-journal-plus" },
  { to: "/packages", label: "Пакеты", icon: "bi-box-seam" },
  { to: "/devices", label: "Оборудование", icon: "bi-cpu" },
  { to: "/stats", label: "Отчеты", icon: "bi-bar-chart" },
  { to: "/finance", label: "Финансы", icon: "bi-cash-stack" },
];

const utcNow = computed(() => new Date().toISOString().replace("T", " ").slice(0, 16));

async function logout() {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

  try {
    await fetch("/admin/logout", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken || "",
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
      },
    });
  } finally {
    window.location.href = "/admin/login";
  }
}
</script>

<style scoped>
.admin-nav .nav-link {
  color: #495057;
}

.admin-nav .nav-link.active {
  background-color: #0d6efd;
  color: #fff;
}
</style>
