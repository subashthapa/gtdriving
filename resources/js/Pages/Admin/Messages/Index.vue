<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Messages</h1>
    <Link class="btn btn-primary mb-4" :href="route('admin.messages.create')">New Message</Link>
    <table class="w-full table-auto border">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 text-left">Name</th>
          <th class="p-2 text-left">Email</th>
          <th class="p-2 text-left">Phone</th>
          <th class="p-2 text-left">Session</th>
          <th class="p-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="message in messages.data" :key="message.id" class="border-t">
          <td class="p-2">{{ message.name }}</td>
          <td class="p-2">{{ message.email }}</td>
          <td class="p-2">{{ message.phone }}</td>
          <td class="p-2">{{ message.session_type }}</td>
          <td class="p-2 space-x-2">
            <Link :href="route('admin.messages.show', message.id)" class="text-blue-500">View</Link>
            <Link :href="route('admin.messages.edit', message.id)" class="text-yellow-500">Edit</Link>
            <button @click="destroy(message.id)" class="text-red-500">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3'
const props = defineProps({ messages: Object })

function destroy(id) {
  if (confirm('Are you sure?')) {
    router.delete(route('admin.messages.destroy', id))
  }
}
</script>