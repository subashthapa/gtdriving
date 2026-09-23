<script setup>
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({
  isStudent: Boolean,
  isInstructor: Boolean,
  isAdmin: Boolean,
  pastBookings: {
    type: Array,
    default: () => []
  },
  futureBookings: {
    type: Array,
    default: () => []
  },
  instructorPastBookings: {
    type: Array,
    default: () => []
  },
  instructorFutureBookings: {
    type: Array,
    default: () => []
  },
  instructorLearners: {
    type: Array,
    default: () => []
  },
  instructorStats: {
    type: Object,
    default: () => ({
      hourly_rate: 0,
      total_earnings: 0,
      outstanding_payments: 0,
      total_lessons: 0,
      total_learners: 0,
      past_lessons: 0,
      upcoming_lessons: 0,
    })
  },
})

const editingId = ref(null)
const cancelError = ref('')
const form = useForm({
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  instructions: ''
})

const stats = ref(null)
const loading = ref(false)
const error = ref(null)

const fetchAdminStats = async () => {
  loading.value = true
  error.value = null

  await axios.get('/sanctum/csrf-cookie')

  try {
    const { data } = await axios.get('/api/admin/stats')
    stats.value = data
  } catch (e) {
    error.value = e
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (props.isAdmin) {
    await fetchAdminStats()
  }
})

const startEdit = (booking) => {
  editingId.value = booking.id
  form.start_date = booking.start_date
  form.end_date = booking.end_date
  form.start_time = booking.start_time
  form.end_time = booking.end_time
  form.instructions = booking.instructions
}

const cancelEdit = () => {
  editingId.value = null
  form.reset()
}

const submitEdit = (bookingId) => {
  form.put(route('bookings.update', bookingId), {
    onSuccess: () => {
      cancelEdit()
    }
  })
}

const deleteBooking = (bookingId) => {
  cancelError.value = ''
  if (confirm('Are you sure you want to cancel this booking?')) {
    router.delete(route('bookings.destroy', bookingId), {
      preserveScroll: true,
      onError: (errors) => {
        cancelError.value = errors.booking || 'Unable to cancel this booking.'
      },
    })
  }
}

const updatePaymentStatus = (booking, paymentStatus) => {
  router.patch(route('bookings.payment.update', booking.id), {
    payment_status: paymentStatus,
  }, {
    preserveScroll: true,
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'AUD',
    maximumFractionDigits: 2,
  }).format(amount || 0)
}
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        <div v-if="isAdmin" class="flex flex-wrap justify-end gap-2">
          <Link :href="route('admin.bookings.index')" class="rounded bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">Booked Sessions</Link>
          <Link :href="route('admin.instructors.index')" class="rounded bg-white px-3 py-2 text-sm text-blue-700 ring-1 ring-blue-200">Instructors</Link>
          <Link :href="route('admin.learners.index')" class="rounded bg-white px-3 py-2 text-sm text-blue-700 ring-1 ring-blue-200">Learners</Link>
          <Link :href="route('admin.packages.index')" class="rounded bg-white px-3 py-2 text-sm text-blue-700 ring-1 ring-blue-200">Packages</Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div v-if="(isAdmin || isInstructor) && !$page.props.auth.user?.two_factor_enabled" class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-300 bg-amber-50 p-4 text-amber-900">
          <div>
            <strong>Protect your staff account.</strong>
            Enable two-factor authentication and save your recovery codes.
          </div>
          <Link :href="route('profile.show')" class="rounded bg-amber-900 px-4 py-2 text-sm font-medium text-white hover:bg-amber-800">Enable 2FA</Link>
        </div>
        <div class="bg-white shadow-xl sm:rounded-lg p-6">
          <div v-if="isStudent">
            <h3 class="text-lg font-bold mb-4">Upcoming Bookings</h3>
            <p v-if="cancelError" class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700">{{ cancelError }}</p>
            <div v-if="futureBookings.length">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 mb-8">
                  <thead class="bg-gray-100">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instructor</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instructions</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="booking in futureBookings" :key="booking.id">
                      <template v-if="editingId === booking.id">
                        <td class="px-4 py-2">
                          <input type="date" v-model="form.start_date" class="border p-1 rounded w-full" />
                        </td>
                        <td class="px-4 py-2">
                          <input type="date" v-model="form.end_date" class="border p-1 rounded w-full" />
                        </td>
                        <td class="px-4 py-2">
                          <div class="flex space-x-2">
                            <input type="time" v-model="form.start_time" class="border p-1 rounded w-full" />
                            <input type="time" v-model="form.end_time" class="border p-1 rounded w-full" />
                          </div>
                        </td>
                        <td class="px-4 py-2">{{ booking.instructor_user?.name || '—' }}</td>
                        <td class="px-4 py-2">
                          <textarea v-model="form.instructions" rows="2" class="border p-1 rounded w-full" />
                        </td>
                        <td class="px-4 py-2">{{ formatCurrency(booking.amount) }} · {{ booking.payment_status }}</td>
                        <td class="px-4 py-2 space-x-2">
                          <button @click="submitEdit(booking.id)" class="text-sm text-white bg-blue-500 px-3 py-1 rounded hover:bg-blue-600">Save</button>
                          <button @click="cancelEdit" class="text-sm text-gray-600 hover:underline">Cancel</button>
                        </td>
                      </template>

                      <template v-else>
                        <td class="px-4 py-2">{{ booking.start_date }}</td>
                        <td class="px-4 py-2">{{ booking.end_date }}</td>
                        <td class="px-4 py-2">{{ booking.start_time }} - {{ booking.end_time }}</td>
                        <td class="px-4 py-2">{{ booking.instructor_user?.name || '—' }}</td>
                        <td class="px-4 py-2">{{ booking.instructions ?? '—' }}</td>
                        <td class="px-4 py-2 capitalize">{{ formatCurrency(booking.amount) }} · {{ booking.payment_status }}</td>
                        <td class="px-4 py-2 space-x-2">
                          <button @click="startEdit(booking)" class="text-sm text-blue-600 hover:underline">Edit</button>
                          <button @click="deleteBooking(booking.id)" class="text-sm text-red-600 hover:underline">Cancel booking</button>
                        </td>
                      </template>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div v-else class="text-gray-500 mb-8">No upcoming bookings.</div>

            <h3 class="text-lg font-bold mb-4 mt-4">Past Bookings</h3>
            <div v-if="pastBookings.length">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-100">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instructor</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instructions</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="booking in pastBookings" :key="booking.id">
                      <td class="px-4 py-2">{{ booking.start_date }}</td>
                      <td class="px-4 py-2">{{ booking.end_date }}</td>
                      <td class="px-4 py-2">{{ booking.start_time }} - {{ booking.end_time }}</td>
                      <td class="px-4 py-2">{{ booking.instructor_user?.name || '—' }}</td>
                      <td class="px-4 py-2">{{ booking.instructions ?? '—' }}</td>
                      <td class="px-4 py-2 capitalize">{{ formatCurrency(booking.amount) }} · {{ booking.payment_status }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div v-else class="text-gray-500">No past bookings yet.</div>
          </div>

          <div v-else-if="isInstructor">
            <h3 class="text-lg font-bold mb-4">Instructor Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <div class="bg-emerald-100 text-emerald-800 p-4 rounded shadow">Total Earnings: <strong>{{ formatCurrency(instructorStats.total_earnings) }}</strong></div>
              <div class="bg-orange-100 text-orange-800 p-4 rounded shadow">Outstanding: <strong>{{ formatCurrency(instructorStats.outstanding_payments) }}</strong></div>
              <div class="bg-blue-100 text-blue-800 p-4 rounded shadow">Assigned Learners: <strong>{{ instructorStats.total_learners }}</strong></div>
              <div class="bg-yellow-100 text-yellow-800 p-4 rounded shadow">Past Lessons: <strong>{{ instructorStats.past_lessons }}</strong></div>
              <div class="bg-purple-100 text-purple-800 p-4 rounded shadow">Upcoming Lessons: <strong>{{ instructorStats.upcoming_lessons }}</strong></div>
            </div>

            <h3 class="text-lg font-bold mb-2">Assigned Learners</h3>
            <div v-if="instructorLearners.length" class="overflow-x-auto mb-8">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Learner</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lessons</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="learner in instructorLearners" :key="learner.id">
                    <td class="px-4 py-2">{{ learner.name }}</td>
                    <td class="px-4 py-2">{{ learner.email || '—' }}</td>
                    <td class="px-4 py-2">{{ learner.phone || '—' }}</td>
                    <td class="px-4 py-2">{{ learner.lessons_count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-gray-500 mb-8">No learners assigned yet.</div>

            <h3 class="text-lg font-bold mb-2">Future / Planned Calendar</h3>
            <div v-if="instructorFutureBookings.length" class="overflow-x-auto mb-8">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Learner</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instructions</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="booking in instructorFutureBookings" :key="booking.id">
                    <td class="px-4 py-2">{{ booking.start_date }}</td>
                    <td class="px-4 py-2">{{ booking.start_time }} - {{ booking.end_time }}</td>
                    <td class="px-4 py-2">{{ booking.user?.name || 'Guest learner' }}</td>
                    <td class="px-4 py-2">{{ booking.instructions || '—' }}</td>
                    <td class="px-4 py-2">
                      <select :value="booking.payment_status" @change="updatePaymentStatus(booking, $event.target.value)" class="rounded border-gray-300 text-sm capitalize">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="refunded">Refunded</option>
                      </select>
                      <span class="ml-2">{{ formatCurrency(booking.amount) }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-gray-500 mb-8">No upcoming lessons planned.</div>

            <h3 class="text-lg font-bold mb-2">Booking History</h3>
            <div v-if="instructorPastBookings.length" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Learner</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instructions</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="booking in instructorPastBookings" :key="booking.id">
                    <td class="px-4 py-2">{{ booking.start_date }}</td>
                    <td class="px-4 py-2">{{ booking.start_time }} - {{ booking.end_time }}</td>
                    <td class="px-4 py-2">{{ booking.user?.name || 'Guest learner' }}</td>
                    <td class="px-4 py-2">{{ booking.instructions || '—' }}</td>
                    <td class="px-4 py-2">
                      <select :value="booking.payment_status" @change="updatePaymentStatus(booking, $event.target.value)" class="rounded border-gray-300 text-sm capitalize">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="refunded">Refunded</option>
                      </select>
                      <span class="ml-2">{{ formatCurrency(booking.amount) }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-gray-500">No completed lessons yet.</div>
          </div>

          <div v-else-if="isAdmin && stats">
            <h3 class="text-lg font-bold mb-4">Admin Stats</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <div class="bg-blue-100 text-blue-800 p-4 rounded shadow">Users: <strong>{{ stats.total_users }}</strong></div>
              <div class="bg-green-100 text-green-800 p-4 rounded shadow">Bookings: <strong>{{ stats.total_bookings }}</strong></div>
              <div class="bg-yellow-100 text-yellow-800 p-4 rounded shadow">Pages: <strong>{{ stats.total_pages }}</strong></div>
              <div class="bg-purple-100 text-purple-800 p-4 rounded shadow">Packages: <strong>{{ stats.total_packages }}</strong></div>
            </div>

            <h3 class="text-lg font-bold mb-2">Upcoming Bookings</h3>
            <div v-if="stats.upcoming_bookings.length">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instructions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="booking in stats.upcoming_bookings" :key="booking.id">
                    <td class="px-4 py-2">{{ booking.user?.name || 'N/A' }}</td>
                    <td class="px-4 py-2">{{ booking.start_date }}</td>
                    <td class="px-4 py-2">{{ booking.start_time }} - {{ booking.end_time }}</td>
                    <td class="px-4 py-2">{{ booking.instructions ?? '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-gray-500">No upcoming bookings found.</div>
          </div>

          <div v-else-if="loading" class="text-gray-500">Loading dashboard...</div>
          <div v-else-if="error" class="text-red-600">Unable to load dashboard data.</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
