<template>
  <Card class="border border-slate-100 shadow-sm !rounded-2xl overflow-hidden hover:shadow-md transition-shadow duration-200">
    <template #content>
      <div class="flex items-start justify-between">
        <div class="min-w-0 flex-1">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block truncate">
            {{ title }}
          </span>
          <div class="mt-2 flex items-baseline gap-2">
            <span class="text-2xl font-black text-slate-800 tracking-tight truncate">
              {{ value }}
            </span>
          </div>
          <p v-if="subtitle" class="text-xs text-slate-400 font-medium mt-0.5 truncate">
            {{ subtitle }}
          </p>
        </div>
        <div
          v-if="icon"
          :class="[
            'w-11 h-11 rounded-2xl flex items-center justify-center text-xl shrink-0 ml-3',
            colorStyles[color]?.bg || 'bg-slate-50',
            colorStyles[color]?.text || 'text-slate-600'
          ]"
        >
          <i :class="icon"></i>
        </div>
      </div>

      <!-- Trend or Difference Row -->
      <div
        v-if="trend || percent != null || diffText"
        class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs"
      >
        <div v-if="trend || percent != null" class="flex items-center gap-1.5 font-bold">
          <i
            :class="[
              'text-[10px] pi',
              trend === 'up' ? 'pi-arrow-up text-emerald-500' : 'pi-arrow-down text-rose-500'
            ]"
          />
          <span :class="trend === 'up' ? 'text-emerald-600' : 'text-rose-600'">
            {{ percent }}%
          </span>
          <span v-if="trendText" class="text-slate-400 font-normal ml-0.5">{{ trendText }}</span>
        </div>

        <div v-if="diffText" class="flex items-center justify-between w-full">
          <span class="text-slate-400 font-medium">{{ diffLabel || 'Selisih:' }}</span>
          <span
            :class="[
              'font-black',
              diffType === 'positive'
                ? 'text-emerald-600'
                : diffType === 'negative'
                  ? 'text-rose-600'
                  : 'text-slate-700'
            ]"
          >
            {{ diffText }}
          </span>
        </div>
      </div>

      <!-- Optional custom slot -->
      <slot />
    </template>
  </Card>
</template>

<script setup>
import Card from 'primevue/card';

defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], required: true },
  subtitle: { type: String, default: '' },
  icon: { type: String, default: '' },
  color: { type: String, default: 'emerald' },
  trend: { type: String, default: null }, // 'up' | 'down' | null
  percent: { type: [Number, String], default: null },
  trendText: { type: String, default: '' },
  diffLabel: { type: String, default: '' },
  diffText: { type: String, default: '' },
  diffType: { type: String, default: 'neutral' } // 'positive' | 'negative' | 'neutral'
});

const colorStyles = {
  emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600' },
  blue:    { bg: 'bg-blue-50', text: 'text-blue-600' },
  amber:   { bg: 'bg-amber-50', text: 'text-amber-600' },
  rose:    { bg: 'bg-rose-50', text: 'text-rose-600' },
  purple:  { bg: 'bg-purple-50', text: 'text-purple-600' },
  indigo:  { bg: 'bg-indigo-50', text: 'text-indigo-600' },
  slate:   { bg: 'bg-slate-50', text: 'text-slate-600' }
};
</script>

