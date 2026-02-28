<template>
  <div ref="modalRef" class="modal fade" tabindex="-1" :aria-labelledby="titleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" :class="sizeClass">
      <div class="modal-content">
        <div class="modal-header">
          <h5 :id="titleId" class="modal-title">{{ title }}</h5>
          <button type="button" class="btn-close" aria-label="Закрыть" @click="close" />
        </div>
        <div class="modal-body">
          <slot />
        </div>
        <div v-if="$slots.footer" class="modal-footer">
          <slot name="footer" :close="close" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useModal } from "../composables/useModal";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: "",
  },
  size: {
    type: String,
    default: "md",
    validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
  },
  staticBackdrop: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["opened", "closed"]);

const modalRef = ref(null);

const { visible, open, close } = useModal(modalRef, {
  backdrop: props.staticBackdrop ? "static" : true,
  keyboard: !props.staticBackdrop,
  onShown: () => emit("opened"),
  onHidden: () => emit("closed"),
});

const titleId = computed(() => `${props.id}-title`);
const sizeClass = computed(() => {
  if (props.size === "md") {
    return "";
  }

  return `modal-${props.size}`;
});

defineExpose({
  open,
  close,
  visible,
});
</script>
