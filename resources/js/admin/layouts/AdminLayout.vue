<template>
  <div class="admin-main position-relative">
    <header class="sticky-top">
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
          <RouterLink class="navbar-brand d-flex align-items-center gap-2" to="/clients">
            <img :src="logoSrc" alt="Логотип" class="brand-logo" />
            <strong>Beauté Zone</strong>
          </RouterLink>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#adminNavbar"
            aria-controls="adminNavbar"
            aria-expanded="false"
            aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon" />
          </button>
          <div id="adminNavbar" class="collapse navbar-collapse">
            <div class="navbar-nav me-auto">
              <RouterLink
                v-for="item in navItems"
                :key="item.to"
                class="nav-link admin-nav-link me-1"
                :to="item.to"
                active-class="active">
                <i class="bi me-1" :class="item.icon" />
                {{ item.label }}
              </RouterLink>
            </div>
            <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
              <span class="text-white-50 small">{{ userDisplayName }}</span>
              <button type="button" class="btn btn-danger btn-sm" @click="logout">
                <i class="bi bi-box-arrow-right me-1" />
                Выйти
              </button>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <main class="container-fluid py-3">
      <div class="mb-3">
        <BreadcrumbsBar />
      </div>
      <RouterView />
    </main>
    <FlashNotifications />
  </div>
</template>

<script setup>
import { computed } from "vue";
import BreadcrumbsBar from "../components/BreadcrumbsBar.vue";
import FlashNotifications from "../components/FlashNotifications.vue";

const logoSrc = "/assets/img/logo.png";

const navItems = [
  { to: "/clients", label: "Клиенты", icon: "bi-people" },
  { to: "/calendar", label: "Календарь", icon: "bi-calendar3" },
  { to: "/visits/new", label: "Визит", icon: "bi-journal-plus" },
  { to: "/packages", label: "Пакеты", icon: "bi-box-seam" },
  { to: "/users", label: "Пользователи", icon: "bi-people-fill" },
  { to: "/devices", label: "Оборудование", icon: "bi-cpu" },
  { to: "/stats", label: "Отчеты", icon: "bi-bar-chart" },
  { to: "/finance", label: "Финансы", icon: "bi-cash-stack" },
];

const userDisplayName = computed(() => {
  const user = window.__ADMIN_USER__ || {};
  const name = user.name || "Пользователь";

  return user.email ? `${name} | ${user.email}` : name;
});

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
.brand-logo {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.admin-nav-link {
  padding: 4px 14px !important;
  border-radius: 20px;
  color: rgba(255, 255, 255, 0.75);
}

.admin-nav-link:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.08);
}

.admin-nav-link.active {
  color: #fff;
  background: rgba(255, 255, 255, 0.16);
}
</style>
