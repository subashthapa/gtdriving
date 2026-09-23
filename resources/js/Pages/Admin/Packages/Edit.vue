<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { defineProps, ref } from 'vue'

const props = defineProps({
  package: Object,
})

const image = ref(null)
const thumbnail = ref(null)

const form = useForm({
  package_name: props.package.package_name ?? '',
  subtitle: props.package.subtitle ?? '',
  price: props.package.price ?? '',
  status: props.package.status ?? false,
})

const handleImage = (e) => {
  image.value = e.target.files[0] || null
}

const handleThumbnail = (e) => {
  thumbnail.value = e.target.files[0] || null
}


const submit = () => {
  const data = new FormData()

  data.append('package_name', form.package_name)
  data.append('subtitle', form.subtitle || '')
  data.append('price', form.price)
  data.append('status', form.status ? 1 : 0)

  if (image.value) data.append('image', image.value)
  if (thumbnail.value) data.append('thumbnail', thumbnail.value)

  data.append('_method', 'PUT')

  form.processing = true

  window.axios.post(route('admin.packages.update', props.package.id), data, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }).then(() => {
    form.processing = false
    window.location.href = route('admin.packages.index')
  }).catch(error => {
    form.processing = false
    if (error.response?.data?.errors) {
      form.errors = error.response.data.errors
    }
  })
}
</script>

<template>
  <AppLayout title="Edit Package">
    <div class="p-6 max-w-2xl mx-auto">
      <h1 class="text-xl font-bold mb-4">Edit Package</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <input v-model="form.package_name" type="text" class="w-full border px-3 py-2 rounded" />
        <div v-if="form.errors.package_name" class="text-red-600 text-sm">{{ form.errors.package_name }}</div>

        <input v-model="form.subtitle" type="text" class="w-full border px-3 py-2 rounded" />
        <div v-if="form.errors.subtitle" class="text-red-600 text-sm">{{ form.errors.subtitle }}</div>

        <input v-model="form.price" type="number" step="0.01" class="w-full border px-3 py-2 rounded" />
        <div v-if="form.errors.price" class="text-red-600 text-sm">{{ form.errors.price }}</div>

        <input type="file" @change="handleImage" accept="image/*" class="w-full border px-3 py-2 rounded" />
        <div v-if="form.errors.image" class="text-red-600 text-sm">{{ form.errors.image }}</div>

        <input type="file" @change="handleThumbnail" accept="image/*" class="w-full border px-3 py-2 rounded" />
        <div v-if="form.errors.thumbnail" class="text-red-600 text-sm">{{ form.errors.thumbnail }}</div>

        <label class="flex items-center space-x-2">
          <input type="checkbox" v-model="form.status" />
          <span>Show this package</span>
        </label>
        <div v-if="form.errors.status" class="text-red-600 text-sm">{{ form.errors.status }}</div>

        <button type="submit" :disabled="form.processing" class="bg-blue-500 text-white px-4 py-2 rounded">
          Update
        </button>
      </form>
    </div>
  </AppLayout>
</template>