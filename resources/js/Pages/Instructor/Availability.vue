<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ availability: { type: Array, default: () => [] } })
const names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
const byWeekday = computed(() => Object.fromEntries(props.availability.map(item => [item.weekday, item])))
const form = useForm({ days: names.map((name, weekday) => ({
  weekday, name, is_active: byWeekday.value[weekday]?.is_active ?? (weekday > 0 && weekday < 6),
  start_time: (byWeekday.value[weekday]?.start_time || '08:00').slice(0, 5),
  end_time: (byWeekday.value[weekday]?.end_time || '17:00').slice(0, 5),
})) })
const submit = () => form.put(route('instructor.availability.update'), { preserveScroll: true })
</script>

<template>
  <AppLayout title="Manage Availability">
    <template #header><h2 class="text-xl font-semibold text-slate-900">Manage availability</h2></template>
    <div class="py-10"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
      <form class="overflow-hidden rounded-2xl bg-white shadow" @submit.prevent="submit">
        <div class="border-b p-6"><h1 class="text-2xl font-bold text-slate-950">Weekly teaching hours</h1><p class="mt-2 text-slate-600">Learners will only see lesson times inside these hours. Existing bookings are not changed.</p></div>
        <div class="divide-y">
          <div v-for="day in form.days" :key="day.weekday" class="grid items-center gap-4 p-4 sm:grid-cols-[10rem_1fr_1fr]">
            <label class="flex items-center gap-3 font-semibold text-slate-800"><input v-model="day.is_active" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-600" />{{ day.name }}</label>
            <div><label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Starts</label><input v-model="day.start_time" type="time" :disabled="!day.is_active" class="w-full rounded-lg border-slate-300 disabled:bg-slate-100" /></div>
            <div><label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Ends</label><input v-model="day.end_time" type="time" :disabled="!day.is_active" class="w-full rounded-lg border-slate-300 disabled:bg-slate-100" /></div>
          </div>
        </div>
        <div class="flex items-center justify-between border-t bg-slate-50 p-6"><p v-if="form.recentlySuccessful" class="text-sm font-semibold text-emerald-700">Availability saved.</p><span v-else></span><button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800 disabled:opacity-60">Save availability</button></div>
      </form>
    </div></div>
  </AppLayout>
</template>
