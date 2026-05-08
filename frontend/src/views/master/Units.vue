<template>
  <SimpleCrudPage
    :title="$t('sidebar.units')"
    :create-label="`${$t('common.add')} ${$t('sidebar.units')}`"
    :columns="columns"
    :form-fields="fields"
    :loader="unitApi.getAll"
    :creator="unitApi.create"
    :updater="unitApi.update"
    :deleter="unitApi.delete"
  />
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { unitApi } from '@/api/master';
import SimpleCrudPage from '@/components/SimpleCrudPage.vue';

const { t: $t } = useI18n();

const columns = computed(() => [
  { field: 'name', header: $t('common.name') },
  { field: 'short_name', header: $t('product.short_name') }
]);

const fields = computed(() => [
  { key: 'name', label: $t('common.name') },
  { key: 'short_name', label: $t('product.short_name') }
]);
</script>
