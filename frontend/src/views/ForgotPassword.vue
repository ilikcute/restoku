<template>
    <div class="min-h-screen bg-slate-50 flex font-sans">
        <!-- Left Section: Visual Branding & Marketing (Desktop only) -->
        <div
            class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-12 overflow-hidden bg-cover bg-left-top bg-no-repeat"
            style="
                background-image:
                    linear-gradient(
                        to right,
                        rgba(248, 250, 252, 0) 50%,
                        rgba(248, 250, 252, 1) 95%
                    ),
                    url('/app/images/auth-bg-original.png');
                background-color: #f3f2ee;
            "
        >
        </div>


        <!-- Right Section: Forgot Password / Email Sent Card -->
        <div
            class="w-full lg:w-1/2 h-screen overflow-y-auto bg-slate-50 flex flex-col"
        >
            <div
                class="min-h-full flex flex-col justify-center items-center p-6 md:p-12"
            >
                <div
                    class="w-full max-w-md bg-white rounded-3xl border border-slate-100 shadow-xl p-8 md:p-10 my-auto"
                >
                    <!-- State 1: Send Reset Link Form -->
                    <div v-if="!isSent" class="space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">
                                Reset Password
                            </h2>
                            <p
                                class="text-slate-500 mt-2 text-sm leading-relaxed"
                            >
                                Enter your email address and we'll send you a
                                link to reset your password.
                            </p>
                        </div>

                        <form @submit.prevent="handleReset" class="space-y-5">
                            <div>
                                <label
                                    class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2"
                                    >Email Address</label
                                >
                                <div class="relative flex items-center">
                                    <i
                                        class="pi pi-envelope absolute left-4 text-slate-400"
                                    ></i>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="Enter your email address"
                                        required
                                        class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:border-green-600 focus:ring-1 focus:ring-green-600 outline-none transition-all placeholder-slate-400"
                                    />
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button
                                type="submit"
                                :disabled="loading"
                                class="w-full bg-green-700 hover:bg-green-800 active:bg-green-900 disabled:bg-green-700/50 text-white font-semibold py-3.5 rounded-xl border border-transparent shadow-sm transition-all text-sm flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <i
                                    v-if="loading"
                                    class="pi pi-spinner pi-spin text-sm"
                                ></i>
                                <i v-else class="pi pi-send text-sm"></i>
                                <span>Send Reset Link</span>
                            </button>
                        </form>

                        <!-- Back to Login -->
                        <div class="text-center mt-6">
                            <router-link
                                to="/login"
                                class="text-sm font-semibold text-green-700 hover:text-green-800 hover:underline"
                            >
                                Back to Login
                            </router-link>
                        </div>
                    </div>

                    <!-- State 2: Success Message -->
                    <div v-else class="text-center space-y-6">
                        <div class="flex justify-center">
                            <div
                                class="relative bg-green-50 text-green-700 p-6 rounded-full border border-green-100/50"
                            >
                                <i class="pi pi-envelope text-4xl"></i>
                                <div
                                    class="absolute -bottom-1 -right-1 bg-green-700 text-white rounded-full p-1 border-4 border-white flex items-center justify-center"
                                >
                                    <i
                                        class="pi pi-check text-[10px] font-bold"
                                    ></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-2xl font-bold text-slate-800">
                                Password reset link sent!
                            </h2>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                We've sent a password reset link to <br />
                                <span class="font-semibold text-slate-800">{{
                                    form.email
                                }}</span
                                >.
                            </p>
                            <p class="text-slate-400 text-xs">
                                Please check your inbox and follow the
                                instructions to reset your password.
                            </p>
                        </div>

                        <!-- Bottom controls -->
                        <div class="pt-4 border-t border-slate-100 space-y-4">
                            <p class="text-slate-400 text-xs">
                                Didn't receive the email?
                                <button
                                    @click="isSent = false"
                                    class="text-green-700 font-semibold hover:underline bg-transparent border-none p-0 cursor-pointer"
                                >
                                    Check your spam folder or try again.
                                </button>
                            </p>
                            <router-link
                                to="/login"
                                class="inline-block text-sm font-semibold text-green-700 hover:text-green-800 hover:underline"
                            >
                                Back to Login
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";

const form = reactive({
    email: "",
});

const loading = ref(false);
const isSent = ref(false);

const handleReset = () => {
    if (!form.email) return;
    loading.value = true;

    // Simulate API delay
    setTimeout(() => {
        loading.value = false;
        isSent.value = true;
    }, 1000);
};
</script>
