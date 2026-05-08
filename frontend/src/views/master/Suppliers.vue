<template>
  <SimpleCrudPage
    :title="$t('sidebar.suppliers')"
    :create-label="`${$t('common.add')} ${$t('sidebar.suppliers')}`"
    :columns="columns"
    :form-fields="fields"
    :loader="supplierApi.getAll"
    :creator="supplierApi.create"
    :updater="supplierApi.update"
    :deleter="supplierApi.delete"
  />
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { supplierApi } from '@/api/master';
import SimpleCrudPage from '@/components/SimpleCrudPage.vue';

const { t: $t } = useI18n();

const columns = computed(() => [
  { field: 'name', header: $t('common.name') },
  { field: 'is_pkp', header: 'PKP Status' },
  { field: 'contact_person', header: $t('common.contact_person') },
  { field: 'phone', header: $t('common.phone') }
]);

const fields = computed(() => [
  { key: 'name', label: $t('common.name') },
  { key: 'is_pkp', label: 'Tax Status', type: 'checkbox', checkboxLabel: 'Supplier PKP (Include PPN 11%)', defaultValue: false },
  { key: 'contact_person', label: $t('common.contact_person') },
  { key: 'phone', label: $t('common.phone') },
  { key: 'email', label: $t('common.email') },
  { key: 'address', label: $t('common.address'), type: 'textarea' }
]);
</script>
