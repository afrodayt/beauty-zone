import axios from "axios";
import { pushFlash } from "./flash";

const client = axios.create({
  baseURL: "/api/admin",
  headers: {
    "X-Requested-With": "XMLHttpRequest",
    Accept: "application/json",
  },
  timeout: 30000,
});

client.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      window.location.href = "/admin/login";
      return Promise.reject(error);
    }

    const message =
      error?.response?.data?.message || error?.response?.data?.error || "Ошибка запроса. Попробуйте еще раз.";

    pushFlash(message, "danger", 5000);
    return Promise.reject(error);
  }
);

function sanitizeParams(params = {}) {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => value !== "" && value !== null && value !== undefined)
  );
}

export const api = {
  get(url, params = {}) {
    return client.get(url, { params: sanitizeParams(params) }).then((response) => response.data);
  },
  post(url, payload = {}) {
    return client.post(url, payload).then((response) => response.data);
  },
  put(url, payload = {}) {
    return client.put(url, payload).then((response) => response.data);
  },
  patch(url, payload = {}) {
    return client.patch(url, payload).then((response) => response.data);
  },
  delete(url) {
    return client.delete(url).then((response) => response.data);
  },
};
