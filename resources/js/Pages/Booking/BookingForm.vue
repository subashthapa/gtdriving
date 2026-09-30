<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  selectedDate: String, selectedSlot: Object, instructorId: [Number, String], instructorName: String,
  hourlyRate: { type: Number, default: 60 }, currency: { type: String, default: 'AUD' },
  adminMode: { type: Boolean, default: false }, learners: { type: Array, default: () => [] },
})
const emit = defineEmits(['booking-confirmed'])
const page = usePage()
const submitting = ref(false); const error = ref('')
const form = reactive({ learner_id: '', name: '', email: '', phone: '', pickup_details: '', instructions: '', payment_method: 'cash' })

onMounted(() => {
  const user = page.props.auth?.user
  if (user && !props.adminMode) { form.name = user.name || ''; form.email = user.email || ''; form.phone = user.phone || '' }
})
watch(() => form.learner_id, id => {
  const learner = props.learners.find(item => item.id === Number(id))
  if (learner) { form.name = learner.name || ''; form.email = learner.email || ''; form.phone = learner.phone || '' }
})

const amount = computed(() => (Number(props.selectedSlot?.duration || 0) / 60) * props.hourlyRate)
const formattedAmount = computed(() => new Intl.NumberFormat('en-AU', { style: 'currency', currency: props.currency }).format(amount.value))
const endTime = computed(() => {
  const [hours, minutes] = props.selectedSlot.slot.start_time.split(':').map(Number)
  const total = hours * 60 + minutes + Number(props.selectedSlot.duration)
  return `${String(Math.floor(total / 60) % 24).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`
})
const submitBooking = async () => {
  submitting.value = true; error.value = ''
  try {
    await axios.post('/book', {
      date: props.selectedDate, start_time: props.selectedSlot.slot.start_time.slice(0, 5), end_time: endTime.value,
      instructor: Number(props.instructorId), learner_id: form.learner_id || undefined,
      name: form.name, email: form.email, phone: form.phone,
      instructions: [form.pickup_details && `Pickup: ${form.pickup_details}`, form.instructions].filter(Boolean).join('\n'),
      payment_method: form.payment_method,
    })
    emit('booking-confirmed')
  } catch (requestError) {
    error.value = requestError.response?.data?.message || Object.values(requestError.response?.data?.errors || {}).flat()[0] || 'Booking failed. Please try again.'
  } finally { submitting.value = false }
}
</script>

<template>
  <form class="grid gap-6 lg:grid-cols-[1fr_.9fr]" @submit.prevent="submitBooking">
    <div class="space-y-4">
      <div v-if="adminMode">
        <label for="booking-learner" class="mb-2 block text-sm font-semibold text-slate-700">Learner</label>
        <select id="booking-learner" v-model="form.learner_id" required class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600"><option value="" disabled>Select a learner</option><option v-for="learner in learners" :key="learner.id" :value="learner.id">{{ learner.name }} · {{ learner.email }}</option></select>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div><label for="booking-name" class="mb-2 block text-sm font-semibold text-slate-700">Full name</label><input id="booking-name" v-model="form.name" required class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600" /></div>
        <div><label for="booking-phone" class="mb-2 block text-sm font-semibold text-slate-700">Phone</label><input id="booking-phone" v-model="form.phone" type="tel" required class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600" /></div>
      </div>
      <div><label for="booking-email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label><input id="booking-email" v-model="form.email" type="email" required class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600" /></div>
      <div><label for="pickup-details" class="mb-2 block text-sm font-semibold text-slate-700">Pickup address or meeting point</label><input id="pickup-details" v-model="form.pickup_details" required placeholder="Enter the pickup location" class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600" /></div>
      <div><label for="booking-notes" class="mb-2 block text-sm font-semibold text-slate-700">Notes <span class="font-normal text-slate-500">(optional)</span></label><textarea id="booking-notes" v-model="form.instructions" rows="3" placeholder="Accessibility needs or anything your instructor should know" class="w-full rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600" /></div>
    </div>

    <aside class="rounded-xl bg-slate-950 p-5 text-white sm:p-6">
      <h3 class="text-lg font-bold">Lesson summary</h3>
      <dl class="mt-5 space-y-3 text-sm">
        <div class="flex justify-between gap-4"><dt class="text-slate-400">Instructor</dt><dd class="text-right font-semibold">{{ instructorName || 'Any available instructor' }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-slate-400">Date</dt><dd class="font-semibold">{{ selectedDate }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-slate-400">Time</dt><dd class="font-semibold">{{ selectedSlot.slot.start_time.slice(0, 5) }}–{{ endTime }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-slate-400">Duration</dt><dd class="font-semibold">{{ selectedSlot.duration }} minutes</dd></div>
        <div class="flex justify-between gap-4 border-t border-slate-700 pt-3 text-base"><dt>Price</dt><dd class="font-bold">{{ formattedAmount }}</dd></div>
      </dl>
      <p class="mt-5 text-sm leading-6 text-slate-300">Payment is made in cash to your instructor. Cancel or reschedule at least 24 hours before the lesson to avoid a cancellation fee.</p>
      <button type="submit" :disabled="submitting" class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-3 font-bold text-white hover:bg-blue-500 disabled:cursor-wait disabled:opacity-60">{{ submitting ? 'Confirming…' : 'Confirm booking' }}</button>
      <p v-if="error" class="mt-3 rounded-lg bg-red-950 p-3 text-sm text-red-100">{{ error }}</p>
    </aside>
  </form>
</template>
