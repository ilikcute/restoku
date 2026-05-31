<template>
    <AppPage
        :title="$t('settings.printer_settings')"
        :breadcrumb="[$t('common.settings'), $t('settings.printer_settings')]"
    >
        <div class="grid gap-6 xl:grid-cols-2">
            <!-- ===== PRINTER KASIR ===== -->
            <div
                class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden"
            >
                <!-- Card Header -->
                <div
                    class="flex items-center gap-4 px-6 py-5 border-b border-slate-100 bg-slate-50/60"
                >
                    <div
                        class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"
                    >
                        <i class="pi pi-print text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">
                            Printer Kasir
                        </h2>
                        <p class="text-sm text-slate-400">
                            Printer utama untuk mencetak struk transaksi
                            pelanggan.
                        </p>
                    </div>
                    <div class="ml-auto">
                        <span
                            class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                            :class="
                                form.use_default
                                    ? 'bg-sky-50 text-sky-600 border border-sky-100'
                                    : 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                            "
                        >
                            {{
                                form.use_default
                                    ? "Mode Default Server"
                                    : "Mode Custom"
                            }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-1">
                    <!-- Toggle Use Default -->
                    <div
                        class="flex items-start gap-4 p-4 rounded-2xl border transition-colors cursor-pointer"
                        :class="
                            form.use_default
                                ? 'bg-sky-50 border-sky-100'
                                : 'bg-slate-50 border-slate-100'
                        "
                        @click="form.use_default = !form.use_default"
                    >
                        <Checkbox
                            v-model="form.use_default"
                            :binary="true"
                            inputId="useDefaultPrinter"
                            class="mt-0.5"
                            @click.stop
                        />
                        <div class="space-y-0.5">
                            <label
                                for="useDefaultPrinter"
                                class="font-bold text-slate-800 cursor-pointer block"
                            >
                                Gunakan konfigurasi default server
                            </label>
                            <p class="text-sm text-slate-500">
                                Jika aktif, sistem memakai nilai dari
                                <code
                                    class="bg-slate-100 px-1.5 py-0.5 rounded text-xs font-mono"
                                    >config/printer.php</code
                                >
                                dan
                                <code
                                    class="bg-slate-100 px-1.5 py-0.5 rounded text-xs font-mono"
                                    >.env</code
                                >. Nonaktifkan untuk menggunakan printer khusus
                                tenant ini.
                            </p>
                        </div>
                    </div>

                    <!-- Custom Printer Fields -->
                    <div
                        class="space-y-4 transition-opacity"
                        :class="
                            form.use_default
                                ? 'opacity-40 pointer-events-none'
                                : 'opacity-100'
                        "
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- Tipe Koneksi -->
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                                    >Tipe Koneksi</label
                                >
                                <Select
                                    v-model="form.connection_type"
                                    :options="connectionTypes"
                                    optionLabel="label"
                                    optionValue="value"
                                    class="w-full !rounded-xl !bg-slate-50 !border-slate-200"
                                    pt:input:class="!p-3"
                                    :disabled="form.use_default"
                                    placeholder="Pilih tipe koneksi"
                                />
                            </div>

                            <!-- Alamat Printer -->
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                                    >Alamat / Nama Printer</label
                                >
                                <InputText
                                    v-model="form.address"
                                    class="!rounded-xl !bg-slate-50 !border-slate-200"
                                    :disabled="form.use_default"
                                    :placeholder="addressPlaceholder"
                                />
                            </div>
                        </div>

                        <!-- Port (only for network) -->
                        <div
                            v-if="form.connection_type === 'network'"
                            class="grid gap-4 md:grid-cols-2"
                        >
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                                    >Port</label
                                >
                                <InputNumber
                                    v-model="form.port"
                                    :min="1"
                                    :max="65535"
                                    class="w-full"
                                    :disabled="form.use_default"
                                    pt:input:class="!rounded-xl !bg-slate-50 !p-3 !border-slate-200"
                                />
                            </div>
                        </div>

                        <!-- Printer Terdeteksi (Windows only) -->
                        <div
                            v-if="
                                !form.use_default &&
                                form.connection_type === 'windows'
                            "
                            class="flex flex-col gap-2"
                        >
                            <label
                                class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                                >Printer Windows Terdeteksi</label
                            >
                            <div class="flex gap-2">
                                <Select
                                    v-model="selectedDetectedPrinter"
                                    :options="detectedPrinters"
                                    optionLabel="label"
                                    optionValue="value"
                                    class="flex-1 !rounded-xl !bg-slate-50 !border-slate-200"
                                    pt:input:class="!p-3"
                                    placeholder="Pilih printer dari Windows atau isi manual"
                                    :disabled="scanning"
                                    @change="applyDetectedPrinter"
                                />
                                <Button
                                    icon="pi pi-refresh"
                                    severity="secondary"
                                    outlined
                                    class="!rounded-xl shrink-0"
                                    :loading="scanning"
                                    @click="scanPrinters"
                                    v-tooltip.top="'Scan ulang printer'"
                                />
                            </div>
                            <p class="text-xs text-slate-400 ml-1">
                                Menampilkan printer Windows yang siap/online.
                                Jika kosong, Anda bisa mengisi nama printer
                                secara manual di atas.
                            </p>
                        </div>
                    </div>

                    <!-- Config Preview -->
                    <div
                        class="grid md:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-900 text-white text-sm"
                    >
                        <div class="space-y-0.5">
                            <div
                                class="text-[10px] text-slate-400 uppercase tracking-widest font-bold"
                            >
                                Tipe Aktif
                            </div>
                            <div class="font-bold text-emerald-400">
                                {{ resolved.connection_type || "-" }}
                            </div>
                        </div>
                        <div class="space-y-0.5">
                            <div
                                class="text-[10px] text-slate-400 uppercase tracking-widest font-bold"
                            >
                                Alamat Aktif
                            </div>
                            <div class="font-bold">
                                {{ resolved.address || "-" }}
                            </div>
                        </div>
                        <div class="space-y-0.5">
                            <div
                                class="text-[10px] text-slate-400 uppercase tracking-widest font-bold"
                            >
                                Port
                            </div>
                            <div class="font-bold">
                                {{
                                    resolved.connection_type === "network"
                                        ? resolved.port
                                        : "-"
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-3 pt-1">
                        <Button
                            :label="$t('common.save')"
                            icon="pi pi-check"
                            class="!rounded-xl !px-8 h-11 font-bold !bg-emerald-600 !border-none shadow-sm shadow-emerald-100"
                            :loading="saving"
                            @click="save"
                        />
                        <Button
                            label="Test Print Kasir"
                            icon="pi pi-send"
                            severity="secondary"
                            outlined
                            class="!rounded-xl !px-6 h-11 font-bold"
                            :loading="testing"
                            @click="testPrinter('cashier')"
                        />
                    </div>
                </div>
            </div>

            <!-- ===== PRINTER DAPUR ===== -->
            <div
                class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden"
            >
                <!-- Card Header -->
                <div
                    class="flex items-center gap-4 px-6 py-5 border-b border-slate-100 bg-orange-50/60"
                >
                    <div
                        class="w-11 h-11 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0"
                    >
                        <i class="pi pi-th-large text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">
                            Printer Dapur (Kitchen)
                        </h2>
                        <p class="text-sm text-slate-400">
                            Printer khusus struk pesanan ke dapur. Kosongkan
                            jika sama dengan printer kasir.
                        </p>
                    </div>
                    <div class="ml-auto">
                        <span
                            class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                            :class="
                                kitchenForm.address
                                    ? 'bg-orange-50 text-orange-600 border-orange-100'
                                    : 'bg-slate-50 text-slate-400 border-slate-100'
                            "
                        >
                            {{
                                kitchenForm.address
                                    ? "Custom Dapur"
                                    : "Ikut Printer Kasir"
                            }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Info Alert -->
                    <div
                        class="flex items-start gap-3 p-4 rounded-2xl bg-orange-50 border border-orange-100 text-sm text-orange-800"
                    >
                        <i
                            class="pi pi-info-circle text-orange-500 mt-0.5 shrink-0"
                        ></i>
                        <div>
                            Isi kolom ini <strong>hanya jika</strong> printer
                            dapur berbeda dengan printer kasir. Jika
                            dikosongkan, struk dapur akan dicetak ke printer
                            kasir secara otomatis.
                        </div>
                    </div>

                    <!-- Kitchen Printer Fields -->
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                                >Tipe Koneksi Dapur</label
                            >
                            <Select
                                v-model="kitchenForm.connection_type"
                                :options="connectionTypes"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full !rounded-xl !bg-slate-50 !border-slate-200"
                                pt:input:class="!p-3"
                                placeholder="Kosongkan jika sama dengan kasir"
                                showClear
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label
                                class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                                >Alamat / Nama Printer Dapur</label
                            >
                            <InputText
                                v-model="kitchenForm.address"
                                class="!rounded-xl !bg-slate-50 !border-slate-200"
                                placeholder="Kosongkan jika sama dengan kasir"
                            />
                        </div>
                    </div>

                    <div
                        v-if="kitchenForm.connection_type === 'network'"
                        class="grid gap-4 md:grid-cols-2"
                    >
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1"
                                >Port Dapur</label
                            >
                            <InputNumber
                                v-model="kitchenForm.port"
                                :min="1"
                                :max="65535"
                                class="w-full"
                                pt:input:class="!rounded-xl !bg-slate-50 !p-3 !border-slate-200"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-3 pt-1">
                        <Button
                            label="Simpan Printer Dapur"
                            icon="pi pi-check"
                            class="!rounded-xl !px-8 h-11 font-bold !bg-orange-500 !border-none shadow-sm shadow-orange-100"
                            :loading="savingKitchen"
                            @click="saveKitchen"
                        />
                        <Button
                            label="Test Print Dapur"
                            icon="pi pi-send"
                            severity="secondary"
                            outlined
                            class="!rounded-xl !px-6 h-11 font-bold"
                            :loading="testingKitchen"
                            @click="testPrinter('kitchen')"
                        />
                    </div>
                </div>
            </div>

            <!-- ===== SERVER DEFAULTS INFO ===== -->
            <div
                class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden"
            >
                <div
                    class="flex items-center gap-4 px-6 py-5 border-b border-slate-100 bg-sky-50/60"
                >
                    <div
                        class="w-11 h-11 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center shrink-0"
                    >
                        <i class="pi pi-server text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">
                            Konfigurasi Default Server
                        </h2>
                        <p class="text-sm text-slate-400">
                            Nilai dari
                            <code
                                class="bg-sky-100 px-1.5 py-0.5 rounded text-xs font-mono"
                                >config/printer.php</code
                            >
                            yang digunakan saat mode default aktif.
                        </p>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid md:grid-cols-3 gap-4">
                        <div
                            class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-1"
                        >
                            <div
                                class="text-[10px] text-slate-400 uppercase tracking-widest font-bold"
                            >
                                Tipe Koneksi
                            </div>
                            <div class="font-bold text-slate-800">
                                {{ defaults.connection_type || "-" }}
                            </div>
                        </div>
                        <div
                            class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-1"
                        >
                            <div
                                class="text-[10px] text-slate-400 uppercase tracking-widest font-bold"
                            >
                                Alamat / Nama
                            </div>
                            <div class="font-bold text-slate-800">
                                {{ defaults.address || "-" }}
                            </div>
                        </div>
                        <div
                            class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-1"
                        >
                            <div
                                class="text-[10px] text-slate-400 uppercase tracking-widest font-bold"
                            >
                                Port
                            </div>
                            <div class="font-bold text-slate-800">
                                {{ defaults.port || "-" }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-2xl bg-amber-50 border border-amber-100 text-sm text-amber-800 flex items-start gap-3"
                    >
                        <i
                            class="pi pi-lightbulb text-amber-500 mt-0.5 shrink-0"
                        ></i>
                        <div>
                            <strong>Printer Windows:</strong> isi nama printer
                            persis seperti yang terdaftar di mesin server
                            (contoh:
                            <code class="bg-amber-100 px-1 rounded text-xs"
                                >POS-80</code
                            >).
                            <br />
                            <strong>Printer Network:</strong> isi IP address
                            printer dan nomor port-nya (contoh:
                            <code class="bg-amber-100 px-1 rounded text-xs"
                                >192.168.1.50</code
                            >
                            port
                            <code class="bg-amber-100 px-1 rounded text-xs"
                                >9100</code
                            >).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppPage>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "primevue/usetoast";
import AppPage from "@/components/layout/AppPage.vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { tenantApi } from "@/api/tenant";

const { t: $t } = useI18n();
const toast = useToast();
const saving = ref(false);
const testing = ref(false);
const testingKitchen = ref(false);
const savingKitchen = ref(false);
const scanning = ref(false);
const selectedDetectedPrinter = ref(null);
const detectedPrinters = ref([]);

const connectionTypes = [
    { label: "Windows Printer", value: "windows" },
    { label: "Network Printer", value: "network" },
    { label: "File / LPT", value: "file" },
];

const form = reactive({
    use_default: true,
    connection_type: "windows",
    address: "",
    port: 9100,
});

const kitchenForm = reactive({
    connection_type: null,
    address: "",
    port: 9100,
});

const defaults = reactive({
    connection_type: "windows",
    address: "",
    port: 9100,
});

const resolved = computed(() => ({
    connection_type: form.use_default
        ? defaults.connection_type
        : form.connection_type,
    address: form.use_default ? defaults.address : form.address,
    port: form.use_default ? defaults.port : form.port,
}));

const addressPlaceholder = computed(() => {
    if (form.connection_type === "network") return "Contoh: 192.168.1.50";
    if (form.connection_type === "file") return "Contoh: LPT1";
    return "Contoh: POS-58 / EPSON TM-T82";
});

async function load() {
    try {
        const response = await tenantApi.getPrinter();
        const data = response.data.data;

        form.use_default = data.use_default ?? true;
        form.connection_type = data.connection_type || "windows";
        form.address = data.address || "";
        form.port = data.port || 9100;

        kitchenForm.connection_type = data.kitchen_connection_type || null;
        kitchenForm.address = data.kitchen_address || "";
        kitchenForm.port = data.kitchen_port || 9100;

        defaults.connection_type = data.defaults?.connection_type || "windows";
        defaults.address = data.defaults?.address || "";
        defaults.port = data.defaults?.port || 9100;

        selectedDetectedPrinter.value =
            form.connection_type === "windows" ? form.address : null;
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: "Tidak dapat memuat pengaturan printer.",
            life: 3000,
        });
    }
}

function applyDetectedPrinter() {
    if (selectedDetectedPrinter.value) {
        form.address = selectedDetectedPrinter.value;
        form.connection_type = "windows";
    }
}

async function scanPrinters() {
    scanning.value = true;
    try {
        const response = await tenantApi.scanReadyPrinters();
        const printers = response.data.data || [];

        detectedPrinters.value = printers.map((printer) => ({
            label: `${printer.name}${printer.share_name ? ` (Share: ${printer.share_name})` : " (Belum di-share)"}${printer.is_default ? " ★" : ""}${printer.port_name ? ` — ${printer.port_name}` : ""}`,
            value: printer.share_name || printer.name,
        }));

        if (!detectedPrinters.value.length) {
            toast.add({
                severity: "warn",
                summary: "Printer",
                detail: "Tidak ada printer Windows dengan status siap yang terdeteksi.",
                life: 3000,
            });
            return;
        }

        const currentPrinter = printers.find((printer) =>
            [printer.name, printer.share_name].includes(form.address),
        );
        if (currentPrinter) {
            selectedDetectedPrinter.value =
                currentPrinter.share_name || currentPrinter.name;
            return;
        }

        if (
            !form.address &&
            !form.use_default &&
            form.connection_type === "windows"
        ) {
            const defaultPrinter =
                printers.find((printer) => printer.is_default) || printers[0];
            selectedDetectedPrinter.value =
                defaultPrinter?.share_name || defaultPrinter?.name || null;
            applyDetectedPrinter();
        }
    } catch (error) {
        const detail =
            error?.response?.data?.message ||
            "Gagal membaca printer dari Windows.";
        toast.add({ severity: "error", summary: "Gagal", detail, life: 3500 });
    } finally {
        scanning.value = false;
    }
}

async function save() {
    if (!form.use_default) {
        if (!form.connection_type) {
            toast.add({
                severity: "warn",
                summary: "Validasi",
                detail: "Tipe koneksi harus dipilih.",
                life: 3000,
            });
            return;
        }
        if (!form.address) {
            toast.add({
                severity: "warn",
                summary: "Validasi",
                detail: "Alamat/Nama printer harus diisi.",
                life: 3000,
            });
            return;
        }
    }

    saving.value = true;
    try {
        const payload = {
            use_default: form.use_default,
            connection_type: form.use_default ? null : form.connection_type,
            address: form.use_default ? null : form.address,
            port: form.use_default
                ? null
                : form.connection_type === "network"
                  ? form.port
                  : null,
        };
        await tenantApi.updatePrinter(payload);
        toast.add({
            severity: "success",
            summary: "✓ Tersimpan",
            detail: "Pengaturan printer kasir berhasil diperbarui.",
            life: 2500,
        });
        await load();
    } catch (error) {
        const detail =
            error?.response?.data?.message ||
            "Gagal menyimpan pengaturan printer.";
        toast.add({ severity: "error", summary: "Gagal", detail, life: 3000 });
    } finally {
        saving.value = false;
    }
}

async function saveKitchen() {
    savingKitchen.value = true;
    try {
        await tenantApi.updateKitchenPrinter({
            kitchen_connection_type: kitchenForm.connection_type || null,
            kitchen_address: kitchenForm.address || null,
            kitchen_port:
                kitchenForm.connection_type === "network"
                    ? kitchenForm.port
                    : null,
        });
        toast.add({
            severity: "success",
            summary: "✓ Tersimpan",
            detail: "Pengaturan printer dapur berhasil diperbarui.",
            life: 2500,
        });
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail:
                error?.response?.data?.message ||
                "Gagal menyimpan printer dapur.",
            life: 3000,
        });
    } finally {
        savingKitchen.value = false;
    }
}

async function testPrinter(type = "cashier") {
    const isKitchen = type === "kitchen";
    if (isKitchen) testingKitchen.value = true;
    else testing.value = true;
    try {
        await tenantApi.testPrinter(type);
        toast.add({
            severity: "success",
            summary: "Test Print",
            detail: `Perintah test print ${isKitchen ? "dapur" : "kasir"} berhasil dikirim.`,
            life: 3000,
        });
    } catch (error) {
        const detail = error?.response?.data?.message || "Test print gagal.";
        toast.add({ severity: "error", summary: "Gagal", detail, life: 3500 });
    } finally {
        if (isKitchen) testingKitchen.value = false;
        else testing.value = false;
    }
}

onMounted(async () => {
    await load();
    if (!form.use_default && form.connection_type === "windows") {
        scanPrinters();
    }
});
</script>
