<template>
  <div class="d-flex admin-main position-relative">
    <aside class="admin-sidebar bg-dark text-white p-3 d-flex flex-column">
      <h5 class="mb-4">
        <i class="bi bi-stars me-2" />
        Beaute Zone
      </h5>
      <nav class="nav nav-pills flex-column gap-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          class="nav-link text-white"
          :to="item.to"
          active-class="active">
          <i class="bi me-2" :class="item.icon" />
          {{ item.label }}
        </RouterLink>
      </nav>
      <div class="mt-auto text-white-50 small pt-3">UTC: {{ utcNow }}</div>
    </aside>

    <div class="flex-grow-1">
      <header class="bg-white border-bottom">
        <div class="container-fluid py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
          <BreadcrumbsBar />
          <span class="text-secondary small">All amounts in EUR</span>
        </div>
      </header>
      <main class="container-fluid py-4">
        <RouterView />
      </main>
    </div>
    <FlashNotifications />
  </div>
</template>

<script setup>
import { computed } from "vue";
import BreadcrumbsBar from "../components/BreadcrumbsBar.vue";
import FlashNotifications from "../components/FlashNotifications.vue";

const navItems = [
  { to: "/clients", label: "Clients", icon: "bi-people" },
  { to: "/calendar", label: "Calendar", icon: "bi-calendar3" },
  { to: "/visits/new", label: "Visit Form", icon: "bi-journal-plus" },
  { to: "/packages", label: "Packages", icon: "bi-box-seam" },
  { to: "/devices", label: "Devices", icon: "bi-cpu" },
  { to: "/stats", label: "Stats", icon: "bi-bar-chart" },
  { to: "/finance", label: "Finance", icon: "bi-cash-stack" },
];

const utcNow = computed(() => new Date().toISOString().replace("T", " ").slice(0, 16));
</script>

<style scoped>
.nav-link.active {
  background-color: #0d6efd;
}
</style>
