import { Modal } from "bootstrap";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";

export function useModal(modalRef, options = {}) {
  const visible = ref(false);
  const instance = ref(null);
  const router = useRouter();

  const onShown = () => {
    if (typeof options.onShown === "function") {
      options.onShown();
    }
  };

  const onHidden = () => {
    visible.value = false;

    if (typeof options.onHidden === "function") {
      options.onHidden();
    }
  };

  const open = () => {
    visible.value = true;
  };

  const close = () => {
    visible.value = false;
  };

  watch(
    () => visible.value,
    (value) => {
      if (!instance.value) {
        return;
      }

      if (value) {
        instance.value.show();
      } else {
        instance.value.hide();
      }
    }
  );

  watch(
    () => router.currentRoute.value.fullPath,
    (value, oldValue) => {
      if (value !== oldValue) {
        close();
      }
    }
  );

  onMounted(() => {
    instance.value = new Modal(modalRef.value, {
      backdrop: options.backdrop ?? true,
      keyboard: options.keyboard ?? true,
    });

    modalRef.value.addEventListener("shown.bs.modal", onShown);
    modalRef.value.addEventListener("hidden.bs.modal", onHidden);
  });

  onBeforeUnmount(() => {
    if (!modalRef.value) {
      return;
    }

    modalRef.value.removeEventListener("shown.bs.modal", onShown);
    modalRef.value.removeEventListener("hidden.bs.modal", onHidden);
    instance.value?.dispose();
  });

  return {
    visible,
    open,
    close,
  };
}
