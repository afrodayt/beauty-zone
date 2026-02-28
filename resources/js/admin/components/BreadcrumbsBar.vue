<template>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item">
        <RouterLink to="/clients">Админка</RouterLink>
      </li>
      <li v-if="currentLabel" class="breadcrumb-item active" aria-current="page">
        {{ currentLabel }}
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();

const currentLabel = computed(() => route.meta?.breadcrumb || "");
</script>
