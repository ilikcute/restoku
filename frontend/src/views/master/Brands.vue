<template>
  <SimpleCrudPage
    :title="$t('sidebar.brands')"
    :create-label="`${$t('common.add')} ${$t('sidebar.brands')}`"
    :columns="columns"
    :form-fields="fields"
    :loader="brandApi.getAll"
    :creator="brandApi.create"
    :updater="brandApi.update"
    :deleter="brandApi.delete"
  />
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { brandApi } from '@/api/master';
import SimpleCrudPage from '@/components/SimpleCrudPage.vue';

const { t: $t } = useI18n();

const columns = computed(() => [
  { field: 'name', header: $t('common.name') },
  { field: 'description', header: $t('common.description') }
]);

const fields = computed(() => [
  { key: 'name', label: $t('common.name') },
  { key: 'description', label: $t('common.description'), type: 'textarea' }
]);
</script>
