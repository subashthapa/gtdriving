<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
const props = defineProps({ selectedDate: String, instructorId: [Number, String] })
const emit = defineEmits(['slot-selected'])
const slots = ref([]); const selectedDuration = ref('60'); const loading = ref(false); const error = ref('')
const fetchSlots = async () => {
  if (!props.selectedDate) return
  loading.value = true; error.value = ''
  try { const { data } = await axios.get('/api/available-slots', { params: { date: props.selectedDate, instructor: props.instructorId || undefined, duration: selectedDuration.value } }); slots.value = data }
  catch { slots.value = []; error.value = 'Unable to load available times.' }
  finally { loading.value = false }
}
watch(() => [props.selectedDate, props.instructorId], fetchSlots, { immediate: true })
watch(selectedDuration, fetchSlots)
const selectSlot = slot => emit('slot-selected', { slot, duration: selectedDuration.value, instructorId: slot.available_instructor_id || props.instructorId, instructorName: slot.available_instructor_name || '' })
</script>
<template><div>
  <div class="mb-5 max-w-sm"><label for="lesson-duration" class="mb-2 block text-sm font-semibold text-slate-700">Lesson duration</label><select id="lesson-duration" v-model="selectedDuration" class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600"><option value="60">1 hour</option><option value="90">1 hour 30 minutes</option><option value="120">2 hours</option></select></div>
  <p class="mb-3 text-sm font-semibold text-slate-700">Available start times for {{ selectedDate }}</p>
  <div v-if="loading" class="rounded-lg bg-slate-50 p-5 text-slate-500">Checking the schedule…</div>
  <div v-else-if="slots.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5"><button v-for="slot in slots" :key="slot.id" type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-3 text-left font-semibold text-slate-900 hover:border-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600" @click="selectSlot(slot)">{{ slot.start_time.slice(0, 5) }}<span v-if="slot.available_instructor_name" class="mt-1 block text-xs font-normal text-slate-500">{{ slot.available_instructor_name }}</span></button></div>
  <p v-else class="rounded-lg bg-slate-50 p-5 text-slate-600">No times are available on this day. Try another date or instructor.</p><p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
</div></template>
