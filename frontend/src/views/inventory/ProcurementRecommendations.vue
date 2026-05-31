<template>
    <AppPage
        :title="$t('inventory.recommendations')"
        :breadcrumb="[$t('common.purchasing'), $t('sidebar.procurement')]"
    >
        <div class="space-y-1">
            <!-- Controls -->
            <div
                class="flex gap-3 bg-slate-50/50 p-1 rounded-2xl border border-slate-100"
            >
                <div class="flex-1 !rounded-xl">
                    <span class="text-sm font-medium text-slate-500"
                        >{{ $t("inventory.days_of_sales") }}:</span
                    >
                    <SelectButton
                        v-model="days"
                        :options="dayOptions"
                        optionLabel="label"
                        optionValue="value"
                        class="custom-select-button"
                    />
                </div>
                <Button
                    icon="pi pi-refresh"
                    severity="secondary"
                    outlined
                    @click="load"
                    :loading="loading"
                    v-tooltip="'Recalculate'"
                    class="!rounded-xl shadow-sm"
                />
            </div>

            <!-- Recommendations Table -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden"
            >
                <AppDataTable
                    framed
                    :value="rows"
                    :loading="loading"
                    paginator
                    :rows="10"
                    responsiveLayout="stack"
                    breakpoint="960px"
                    stripedRows
                >
                    <template #empty>
                        <div class="text-center py-12 text-slate-400">
                            <div
                                class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4"
                            >
                                <i
                                    class="pi pi-check-circle text-4xl text-green-500"
                                ></i>
                            </div>
                            <p class="text-lg font-medium text-slate-600">
                                Stok Anda dalam kondisi prima!
                            </p>
                            <p>
                                Tidak ada item yang perlu dipesan kembali saat
                                ini.
                            </p>
                        </div>
                    </template>

                    <Column
                        :header="$t('common.name')"
                        field="name"
                        sortable
                        class="font-bold text-slate-800"
                    />

                    <Column
                        :header="$t('common.category')"
                        field="category"
                        sortable
                    >
                        <template #body="{ data }">
                            <span
                                class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold uppercase"
                            >
                                {{ data.category }}
                            </span>
                        </template>
                    </Column>

                    <Column
                        header="Daily Avg"
                        field="daily_avg_sales"
                        class="text-center"
                    >
                        <template #body="{ data }">
                            <span class="font-medium">{{
                                data.daily_avg_sales
                            }}</span>
                            <span class="text-xs text-slate-400 ml-1">{{
                                data.unit
                            }}</span>
                        </template>
                    </Column>

                    <Column
                        :header="$t('inventory.current')"
                        field="current_stock"
                        class="text-center"
                    >
                        <template #body="{ data }">
                            <span
                                :class="
                                    data.current_stock <= data.safety_stock
                                        ? 'text-red-600 font-bold'
                                        : 'text-orange-600 font-bold'
                                "
                            >
                                {{ data.current_stock }}
                            </span>
                        </template>
                    </Column>

                    <Column
                        :header="$t('inventory.rop')"
                        field="calculated_rop"
                        class="text-center"
                    >
                        <template #body="{ data }">
                            <div class="flex flex-col items-center">
                                <span class="font-bold text-slate-700">{{
                                    data.calculated_rop
                                }}</span>
                                <span
                                    class="text-[10px] text-slate-400 uppercase tracking-tighter"
                                    >Point to Reorder</span
                                >
                            </div>
                        </template>
                    </Column>

                    <Column
                        :header="$t('inventory.suggested_order')"
                        field="reorder_quantity"
                        class="text-center"
                    >
                        <template #body="{ data }">
                            <div
                                class="px-3 py-1 bg-primary-50 text-primary-700 rounded-lg font-black inline-block border border-primary-100"
                            >
                                +{{ data.reorder_quantity }}
                            </div>
                        </template>
                    </Column>

                    <Column
                        :header="$t('inventory.priority')"
                        field="priority"
                        class="text-center"
                    >
                        <template #body="{ data }">
                            <Tag
                                :value="data.priority.toUpperCase()"
                                :severity="
                                    data.priority === 'high' ? 'danger' : 'warn'
                                "
                                rounded
                            />
                        </template>
                    </Column>

                    <Column header="" class="w-24">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-shopping-cart"
                                size="small"
                                rounded
                                severity="primary"
                                v-tooltip="'Buat Purchase Order'"
                            />
                        </template>
                    </Column>
                </AppDataTable>
            </div>
        </div>
    </AppPage>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import AppDataTable from "@/components/AppDataTable.vue";
import StatusBadge from "@/components/StatusBadge.vue";
import { inventoryApi } from "@/api/inventory";
import AppPage from "@/components/layout/AppPage.vue";
import Column from "primevue/column";
import Button from "primevue/button";
import Tag from "primevue/tag";
import SelectButton from "primevue/selectbutton";

const rows = ref([]);
const loading = ref(false);
const days = ref(30);

const dayOptions = [
    { label: "7 Hari", value: 7 },
    { label: "15 Hari", value: 15 },
    { label: "30 Hari", value: 30 },
];

async function load() {
    loading.value = true;
    try {
        const response = await inventoryApi.getRecommendations({
            days: days.value,
        });
        rows.value = response?.data?.data || [];
    } catch (error) {
        console.error("Failed to load recommendations", error);
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: "Gagal memuat rekomendasi pengadaan.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

watch(days, () => {
    load();
});

onMounted(() => {
    load();
});
</script>
