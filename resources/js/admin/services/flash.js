import { reactive } from "vue";

const state = reactive({
  items: [],
});

let sequence = 1;

export function useFlash() {
  return state;
}

export function pushFlash(message, variant = "success", timeout = 3000) {
  const id = sequence++;
  state.items.push({ id, message, variant });

  if (timeout > 0) {
    window.setTimeout(() => dismissFlash(id), timeout);
  }

  return id;
}

export function dismissFlash(id) {
  state.items = state.items.filter((item) => item.id !== id);
}
