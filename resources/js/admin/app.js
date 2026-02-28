import { createApp } from "vue";
import "bootstrap";
import { createRouter } from "./router";

const app = createApp({
  template: "<RouterView />",
});

app.use(createRouter());
app.mount("#admin-app");
