<template>
  <div class="flash-stack">
    <div
      v-for="item in flash.items"
      :key="item.id"
      class="alert alert-dismissible fade show shadow-sm"
      :class="`alert-${item.variant}`"
      role="alert">
      {{ item.message }}
      <button type="button" class="btn-close" aria-label="Close" @click="dismissFlash(item.id)" />
    </div>
  </div>
</template>

<script setup>
import { dismissFlash, useFlash } from "../services/flash";

const flash = useFlash();
</script>
