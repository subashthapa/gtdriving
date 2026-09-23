<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  bookings: Object,
  filters: { type: Object, default: () => ({}) },
})

const setFilter = (status) => router.get(route('admin.bookings.index'), status ? { status } : {}, { preserveState: true })

const currency = (amount) => new Intl.NumberFormat('en-AU', {
  style: 'currency',
  currency: 'AUD',
}).format(Number(amount || 0))
</script>

<template>
  <AppLayout title="Booked Sessions">
    <template #header><h2 class="text-xl font-semibold text-gray-800">Booked Sessions</h2></template>
    <div class="py-10">
      <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-2">
          <button v-for="item in [{v:'',l:'All'}, {v:'upcoming',l:'Upcoming'}, {v:'past',l:'Past'}, {v:'pending',l:'Pending'}, {v:'paid',l:'Paid'}, {v:'refunded',l:'Refunded'}]" :key="item.v" @click="setFilter(item.v)" :class="filters.status === item.v ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'" class="rounded border px-3 py-2 text-sm">{{ item.l }}</button>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div v-if="!bookings.data.length" class="p-8 text-center text-gray-500">No booked sessions found.</div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr><th class="px-4 py-3">Date & time</th><th class="px-4 py-3">Learner</th><th class="px-4 py-3">Instructor</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3 text-right">Action</th></tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="booking in bookings.data" :key="booking.id">
                  <td class="px-4 py-3"><div class="font-medium">{{ booking.start_date }}</div><div class="text-gray-500">{{ booking.start_time }} – {{ booking.end_time }}</div></td>
                  <td class="px-4 py-3"><div>{{ booking.user?.name || 'Unknown' }}</div><div class="text-gray-500">{{ booking.user?.email || '—' }}</div></td>
                  <td class="px-4 py-3">{{ booking.instructor_user?.name || 'Unassigned' }}</td>
                  <td class="px-4 py-3"><div class="capitalize">{{ booking.payment_status }}</div><div class="text-gray-500">{{ currency(booking.amount) }}</div></td>
                  <td class="px-4 py-3 text-right"><Link :href="route('admin.bookings.edit', booking.id)" class="text-blue-600 hover:underline">Edit session</Link></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="bookings.links?.length > 3" class="flex flex-wrap gap-1 border-t p-4">
            <Link v-for="link in bookings.links" :key="link.label" :href="link.url || '#'" v-html="link.label" :class="[link.active ? 'bg-blue-600 text-white' : 'bg-white', !link.url ? 'pointer-events-none opacity-40' : '']" class="rounded border px-3 py-1 text-sm" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
