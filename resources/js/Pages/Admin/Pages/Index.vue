<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { defineProps } from 'vue'

const props = defineProps({
  pages: Array
})

const deletePackage = (id) => {
  if (confirm('Are you sure you want to delete this package?')) {
    router.delete(route('admin.pages.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Packages">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Pages</h1>
        <Link :href="route('admin.pages.create')" class="bg-blue-500 text-white px-4 py-2 rounded">Add Page</Link>
      </div>

      <table class="w-full table-auto border">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-4 py-2 text-left">Name</th>
            <th class="border px-4 py-2">Price</th>
            <th class="border px-4 py-2">Status</th>
            <th class="border px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pkg in packages" :key="pkg.id">
            <td class="border px-4 py-2 text-left">{{ pkg.package_name }}</td>
            <td class="border px-4 py-2">${{ pkg.price }}</td>
            <td class="border px-4 py-2">{{ pkg.status ? 'Shown' : 'Hidden' }}</td>
            <td class="border px-4 py-2 space-x-2">
              <Link :href="route('admin.packages.edit', pkg.id)" class="text-blue-600 hover:underline">Edit</Link>
              <button @click="deletePackage(pkg.id)" class="text-red-600 hover:underline">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
