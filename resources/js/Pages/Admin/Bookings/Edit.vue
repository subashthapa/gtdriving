<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ booking: Object, instructors: Array, learners: Array })

const trimTime = (value) => value?.slice(0, 5) || ''
const form = useForm({
  user_id: props.booking.user_id,
  instructor: props.booking.instructor,
  start_date: props.booking.start_date,
  end_date: props.booking.end_date,
  start_time: trimTime(props.booking.start_time),
  end_time: trimTime(props.booking.end_time),
  instructions: props.booking.instructions || '',
  payment_status: props.booking.payment_status || 'pending',
  payment_reference: props.booking.payment_reference || '',
})

const submit = () => form.put(route('admin.bookings.update', props.booking.id))
</script>

<template>
  <AppLayout title="Edit Booked Session">
    <template #header><h2 class="text-xl font-semibold text-gray-800">Edit Booked Session</h2></template>
    <div class="py-10">
      <form @submit.prevent="submit" class="mx-auto grid max-w-3xl gap-5 rounded-lg bg-white p-6 shadow md:grid-cols-2">
        <div class="md:col-span-2 rounded bg-gray-50 p-3 text-sm text-gray-600">Session #{{ booking.id }} · Amount recalculates automatically from duration.</div>
        <div>
          <label class="mb-1 block text-sm font-medium">Learner</label>
          <select v-model="form.user_id" class="w-full rounded border-gray-300"><option v-for="learner in learners" :key="learner.id" :value="learner.id">{{ learner.name }} — {{ learner.email }}</option></select>
          <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</p>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">Instructor</label>
          <select v-model="form.instructor" class="w-full rounded border-gray-300"><option v-for="instructor in instructors" :key="instructor.id" :value="instructor.id">{{ instructor.name }}</option></select>
          <p v-if="form.errors.instructor" class="mt-1 text-sm text-red-600">{{ form.errors.instructor }}</p>
        </div>
        <div><label class="mb-1 block text-sm font-medium">Date</label><input v-model="form.start_date" @change="form.end_date = form.start_date" type="date" class="w-full rounded border-gray-300" /><p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p></div>
        <div><label class="mb-1 block text-sm font-medium">Payment status</label><select v-model="form.payment_status" class="w-full rounded border-gray-300"><option value="pending">Pending</option><option value="paid">Paid</option><option value="refunded">Refunded</option></select></div>
        <div><label class="mb-1 block text-sm font-medium">Start time</label><input v-model="form.start_time" type="time" class="w-full rounded border-gray-300" /><p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">{{ form.errors.start_time }}</p></div>
        <div><label class="mb-1 block text-sm font-medium">End time</label><input v-model="form.end_time" type="time" class="w-full rounded border-gray-300" /><p v-if="form.errors.end_time" class="mt-1 text-sm text-red-600">{{ form.errors.end_time }}</p></div>
        <div class="md:col-span-2"><label class="mb-1 block text-sm font-medium">Instructions</label><textarea v-model="form.instructions" rows="3" class="w-full rounded border-gray-300"></textarea></div>
        <div class="md:col-span-2"><label class="mb-1 block text-sm font-medium">Payment reference</label><input v-model="form.payment_reference" type="text" class="w-full rounded border-gray-300" /></div>
        <input v-model="form.end_date" type="hidden" />
        <div class="flex gap-3 md:col-span-2"><button type="submit" :disabled="form.processing" class="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-50">Save Session</button><Link :href="route('admin.bookings.index')" class="rounded border px-4 py-2">Cancel</Link></div>
      </form>
    </div>
  </AppLayout>
</template>
