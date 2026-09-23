<template>
  <AppLayout title="Manage Users">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Users</h1>
      <Link :href="route('admin.users.create')" class="btn btn-primary mb-4">+ Create User</Link>
      <table class="table-auto w-full text-left border">
        <thead>
          <tr>
            <th class="border px-4 py-2">#</th>
            <th class="border px-4 py-2">Name</th>
            <th class="border px-4 py-2">Email</th>
            <th class="border px-4 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) in users" :key="user.id">
            <td class="border px-4 py-2">{{ index + 1 }}</td>
            <td class="border px-4 py-2">{{ user.name }}</td>
            <td class="border px-4 py-2">{{ user.email }}</td>
            <td class="border px-4 py-2 text-right space-x-2">
              <Link :href="route('admin.users.edit', user.id)" class="text-blue-600">Edit</Link>
              <button @click="destroy(user.id)" class="text-red-600">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, Link } from '@inertiajs/vue3';

defineProps({ users: Array });

const destroy = (id) => {
  if (confirm('Are you sure?')) {
    router.delete(route('admin.users.destroy', id));
  }
};
</script>
