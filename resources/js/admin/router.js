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

export function createRouter() {
  return createVueRouter({
    history: createWebHashHistory(),
    routes: [
      {
        path: "/",
        component: AdminLayout,
        children: [
          { path: "", redirect: "/clients" },
          { path: "/clients", name: "clients", component: ClientsList, meta: { breadcrumb: "Clients" } },
          { path: "/clients/:id", name: "client-show", component: ClientShow, meta: { breadcrumb: "Client" } },
          { path: "/visits/new", name: "visit-create", component: VisitForm, meta: { breadcrumb: "Visit Form" } },
          { path: "/visits/:id/edit", name: "visit-edit", component: VisitForm, meta: { breadcrumb: "Edit Visit" } },
          { path: "/calendar", name: "calendar", component: CalendarView, meta: { breadcrumb: "Calendar" } },
          { path: "/packages", name: "packages", component: Packages, meta: { breadcrumb: "Packages" } },
          { path: "/devices", name: "devices", component: Devices, meta: { breadcrumb: "Devices" } },
          { path: "/stats", name: "stats", component: Stats, meta: { breadcrumb: "Stats" } },
          { path: "/finance", name: "finance", component: Finance, meta: { breadcrumb: "Finance" } },
        ],
      },
      {
        path: "/:pathMatch(.*)*",
        redirect: "/clients",
      },
    ],
  });
}
