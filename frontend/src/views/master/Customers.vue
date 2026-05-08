<template>
  <SimpleCrudPage
    :title="$t('sidebar.customers')"
    :create-label="`${$t('common.add')} ${$t('sidebar.customers')}`"
    :columns="columns"
    :form-fields="fields"
    :loader="customerApi.getAll"
    :creator="customerApi.create"
    :updater="customerApi.update"
    :deleter="customerApi.delete"
  />
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { customerApi } from '@/api/master';
import SimpleCrudPage from '@/components/SimpleCrudPage.vue';

const { t: $t } = useI18n();

const columns = computed(() => [
  { field: 'name', header: $t('common.name') },
  { field: 'phone', header: $t('common.phone') },
  { field: 'email', header: $t('common.email') }
]);

const fields = computed(() => [
  { key: 'name', label: $t('common.name') },
  { key: 'phone', label: $t('common.phone') },
  { key: 'email', label: $t('common.email') },
  { key: 'address', label: $t('common.address'), type: 'textarea' }
]);
</script>
