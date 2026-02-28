import { createRouter as createVueRouter, createWebHashHistory } from "vue-router";
import AdminLayout from "./layouts/AdminLayout.vue";
import ClientsList from "./pages/ClientsList.vue";
import ClientShow from "./pages/ClientShow.vue";
import VisitForm from "./pages/VisitForm.vue";
import CalendarView from "./pages/CalendarView.vue";
import Packages from "./pages/Packages.vue";
import Devices from "./pages/Devices.vue";
import Stats from "./pages/Stats.vue";
import Finance from "./pages/Finance.vue";
import Users from "./pages/Users.vue";

export function createRouter() {
  return createVueRouter({
    history: createWebHashHistory(),
    routes: [
      {
        path: "/",
        component: AdminLayout,
        children: [
          { path: "", redirect: "/clients" },
          { path: "/clients", name: "clients", component: ClientsList, meta: { breadcrumb: "Клиенты" } },
          {
            path: "/clients/:id",
            name: "client-show",
            component: ClientShow,
            meta: { breadcrumb: "Карточка клиента" },
          },
          { path: "/visits/new", name: "visit-create", component: VisitForm, meta: { breadcrumb: "Новый визит" } },
          {
            path: "/visits/:id/edit",
            name: "visit-edit",
            component: VisitForm,
            meta: { breadcrumb: "Редактирование визита" },
          },
          { path: "/calendar", name: "calendar", component: CalendarView, meta: { breadcrumb: "Календарь" } },
          { path: "/packages", name: "packages", component: Packages, meta: { breadcrumb: "Пакеты" } },
          { path: "/devices", name: "devices", component: Devices, meta: { breadcrumb: "Оборудование" } },
          { path: "/users", name: "users", component: Users, meta: { breadcrumb: "Пользователи" } },
          { path: "/stats", name: "stats", component: Stats, meta: { breadcrumb: "Отчеты" } },
          { path: "/finance", name: "finance", component: Finance, meta: { breadcrumb: "Финансы" } },
        ],
      },
      {
        path: "/:pathMatch(.*)*",
        redirect: "/clients",
      },
    ],
  });
}
