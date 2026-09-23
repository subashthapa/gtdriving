<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, Link } from '@inertiajs/vue3'

defineProps({
  users: { type: Array, default: () => [] },
  title: { type: String, default: 'All Users' },
  role: { type: String, default: null },
})

const destroy = (id) => {
  if (confirm('Are you sure you want to delete this user?')) {
    router.delete(route('admin.users.destroy', id))
  }
}
</script>

<template>
  <AppLayout :title="title">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ title }}</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap gap-2">
            <Link :href="route('admin.users.index')" class="rounded border bg-white px-3 py-2 text-sm">All Users</Link>
            <Link :href="route('admin.instructors.index')" class="rounded border bg-white px-3 py-2 text-sm">Instructors</Link>
            <Link :href="route('admin.learners.index')" class="rounded border bg-white px-3 py-2 text-sm">Learners</Link>
          </div>
          <Link :href="route('admin.users.create')" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Create User</Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div v-if="!users.length" class="p-8 text-center text-gray-500">No {{ role ? role.toLowerCase() : 'user' }} accounts found.</div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                  <th class="px-4 py-3">Name</th>
                  <th class="px-4 py-3">Contact</th>
                  <th class="px-4 py-3">Role</th>
                  <th class="px-4 py-3">Sessions</th>
                  <th class="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="user in users" :key="user.id">
                  <td class="px-4 py-3 font-medium text-gray-900">{{ user.name }}</td>
                  <td class="px-4 py-3 text-gray-600">
                    <div>{{ user.email }}</div>
                    <div>{{ user.phone || 'No phone' }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <span v-for="item in user.roles" :key="item.id" class="mr-1 rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800">{{ item.name }}</span>
                    <span v-if="!user.roles.length" class="text-gray-400">No role</span>
                  </td>
                  <td class="px-4 py-3 text-gray-600">
                    {{ user.roles.some((item) => item.name === 'Instructor') ? user.instructor_bookings_count : user.learner_bookings_count }}
                  </td>
                  <td class="space-x-3 px-4 py-3 text-right">
                    <Link :href="route('admin.users.edit', user.id)" class="text-blue-600 hover:underline">Edit</Link>
                    <button @click="destroy(user.id)" class="text-red-600 hover:underline">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
