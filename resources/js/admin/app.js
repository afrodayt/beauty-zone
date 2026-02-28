import { createApp, h } from "vue";
import "bootstrap";
import { RouterView } from "vue-router";
import { createRouter } from "./router";

const app = createApp({
  render: () => h(RouterView),
});

app.use(createRouter());
app.mount("#admin-app");
