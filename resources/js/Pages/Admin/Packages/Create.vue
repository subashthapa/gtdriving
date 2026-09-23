<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  package_name: '',
  subtitle: '',
  price: '',
  image: null,
  thumbnail: null,
  status: true,
  description: ''
})

const submit = () => form.post(route('admin.packages.store'), {
  forceFormData: true
})
</script>

<template>
  <AppLayout title="Create Package">
    <div class="p-6 max-w-2xl mx-auto">
      <h1 class="text-xl font-bold mb-4">Create Package</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <input v-model="form.package_name" type="text" placeholder="Package Name" class="w-full border px-3 py-2 rounded" />
        <input v-model="form.subtitle" type="text" placeholder="Subtitle" class="w-full border px-3 py-2 rounded" />
        <input v-model="form.price" type="number" placeholder="Price" class="w-full border px-3 py-2 rounded" step="0.01" />
        <input type="file" @change="e => form.image = e.target.files[0]" accept="image/*" class="w-full border px-3 py-2 rounded" />
        <input type="file" @change="e => form.thumbnail = e.target.files[0]" accept="image/*" class="w-full border px-3 py-2 rounded" />

        <label class="flex items-center space-x-2">
          <input type="checkbox" v-model="form.status" />
          <span>Show this package</span>
        </label>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
      </form>
    </div>
  </AppLayout>
</template>
