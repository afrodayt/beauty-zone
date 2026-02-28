<template>
  <span class="badge rounded-pill" :class="badgeClass">
    {{ enumLabel(normalizedStatus) }}
  </span>
</template>

<script setup>
import { computed } from "vue";
import { enumLabel } from "../services/labels";

const props = defineProps({
  status: {
    type: String,
    default: "",
  },
});

const normalizedStatus = computed(() => String(props.status || "").toUpperCase());

const badgeClass = computed(() => {
  if (normalizedStatus.value === "NEW") {
    return "text-bg-warning";
  }

  if (normalizedStatus.value === "ACTIVE") {
    return "text-bg-success";
  }

  if (normalizedStatus.value === "LOST") {
    return "text-bg-danger";
  }

  return "text-bg-secondary";
});
</script>
