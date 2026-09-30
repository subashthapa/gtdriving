<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import MainLayout from '@/Layouts/MainLayout.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import BookingCalendar from './BookingCalendar.vue'
import TimeSlots from './TimeSlots.vue'
import BookingForm from './BookingForm.vue'

const props = defineProps({
  hourlyRate: { type: Number, default: 60 },
  currency: { type: String, default: 'AUD' },
  preferredInstructor: { type: Number, default: null },
  adminMode: { type: Boolean, default: false },
  learners: { type: Array, default: () => [] },
})

const instructors = ref([])
const selectedInstructorId = ref(props.preferredInstructor ? String(props.preferredInstructor) : '')
const selectedDate = ref(null)
const selectedSlot = ref(null)
const instructorError = ref('')
const bookingComplete = ref(false)

const selectedInstructor = computed(() => instructors.value.find(item => item.id === Number(selectedInstructorId.value)))
const activeStep = computed(() => bookingComplete.value ? 5 : selectedSlot.value ? 4 : selectedDate.value ? 3 : 2)

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/instructors')
    instructors.value = data
    if (props.preferredInstructor && !data.some(item => item.id === props.preferredInstructor)) selectedInstructorId.value = ''
  } catch {
    instructorError.value = 'Unable to load instructors. Please refresh and try again.'
  }
})

const resetAfterInstructor = () => { selectedDate.value = null; selectedSlot.value = null; bookingComplete.value = false }
const selectDate = date => { selectedDate.value = date; selectedSlot.value = null; bookingComplete.value = false }
const startAnotherBooking = () => { selectedDate.value = null; selectedSlot.value = null; bookingComplete.value = false }
</script>

<template>
  <component :is="adminMode ? AppLayout : MainLayout" :title="adminMode ? 'Create Booking' : 'Book a Lesson'">
    <template v-if="adminMode" #header><h2 class="text-xl font-semibold text-slate-900">Create booking</h2></template>
    <main class="min-h-screen bg-slate-50 py-8 text-left sm:py-12">
      <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 max-w-3xl">
          <p class="mb-2 text-sm font-bold uppercase tracking-[0.18em] text-blue-700">{{ adminMode ? 'Staff booking' : 'Book a lesson' }}</p>
          <h1 class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Find a lesson that fits your week.</h1>
          <p class="mt-3 text-base text-slate-600">Choose any available instructor for the earliest options, or select someone you already know.</p>
        </div>

        <ol class="mb-8 grid grid-cols-2 gap-2 sm:grid-cols-5" aria-label="Booking progress">
          <li v-for="(label, index) in ['Instructor', 'Day', 'Time', 'Review', 'Confirmed']" :key="label" :class="activeStep >= index + 1 ? 'border-blue-600 bg-blue-50 text-blue-900' : 'border-slate-200 bg-white text-slate-500'" class="rounded-xl border px-3 py-3 text-sm font-semibold">
            <span class="mr-1 text-xs">{{ index + 1 }}.</span> {{ label }}
          </li>
        </ol>

        <div v-if="bookingComplete" class="rounded-2xl border border-emerald-200 bg-white p-8 shadow-sm">
          <div class="mb-4 flex size-12 items-center justify-center rounded-full bg-emerald-100 text-2xl text-emerald-700">✓</div>
          <h2 class="text-2xl font-bold text-slate-950">Booking confirmed</h2>
          <p class="mt-2 text-slate-600">The lesson has been added to the schedule. We’ll use the supplied contact details for booking updates.</p>
          <button type="button" class="mt-6 rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800" @click="startAnotherBooking">Book another lesson</button>
        </div>

        <div v-else class="space-y-6">
          <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4 flex items-center gap-3"><span class="flex size-8 items-center justify-center rounded-full bg-blue-700 font-bold text-white">1</span><h2 class="text-xl font-bold text-slate-950">Choose an instructor</h2></div>
            <label for="booking-instructor" class="mb-2 block text-sm font-semibold text-slate-700">Instructor preference</label>
            <select id="booking-instructor" v-model="selectedInstructorId" class="w-full rounded-lg border-slate-300 text-base focus:border-blue-600 focus:ring-blue-600" @change="resetAfterInstructor">
              <option value="">Any available instructor</option>
              <option v-for="instructor in instructors" :key="instructor.id" :value="String(instructor.id)">{{ instructor.name }}</option>
            </select>
            <p class="mt-2 text-sm text-slate-500">“Any available” gives you the widest choice and assigns a free instructor when you select a time.</p>
            <p v-if="instructorError" class="mt-2 text-sm text-red-600">{{ instructorError }}</p>
          </section>

          <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-center gap-3"><span class="flex size-8 items-center justify-center rounded-full bg-blue-700 font-bold text-white">2</span><h2 class="text-xl font-bold text-slate-950">Choose a day</h2></div>
            <BookingCalendar @date-selected="selectDate" />
          </section>

          <section v-if="selectedDate" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-center gap-3"><span class="flex size-8 items-center justify-center rounded-full bg-blue-700 font-bold text-white">3</span><h2 class="text-xl font-bold text-slate-950">Choose a time and duration</h2></div>
            <TimeSlots :selected-date="selectedDate" :instructor-id="selectedInstructorId" @slot-selected="selectedSlot = $event" />
          </section>

          <section v-if="selectedSlot" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-center gap-3"><span class="flex size-8 items-center justify-center rounded-full bg-blue-700 font-bold text-white">4</span><h2 class="text-xl font-bold text-slate-950">Review and confirm</h2></div>
            <BookingForm :selected-date="selectedDate" :selected-slot="selectedSlot" :instructor-id="selectedSlot.instructorId || selectedInstructorId" :instructor-name="selectedSlot.instructorName || selectedInstructor?.name" :hourly-rate="hourlyRate" :currency="currency" :admin-mode="adminMode" :learners="learners" @booking-confirmed="bookingComplete = true" />
          </section>
        </div>
      </div>
    </main>
  </component>
</template>
