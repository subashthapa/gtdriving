<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ roles: { type: Array, default: () => [] } })

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  role: props.roles.includes('Learner') ? 'Learner' : props.roles[0] || '',
})

const submit = () => form.post(route('admin.users.store'))
</script>

<template>
  <AppLayout title="Create User">
    <template #header><h2 class="text-xl font-semibold text-gray-800">Create User</h2></template>
    <div class="py-10">
      <form @submit.prevent="submit" class="mx-auto max-w-xl space-y-5 rounded-lg bg-white p-6 shadow">
        <div>
          <label class="mb-1 block text-sm font-medium">Name</label>
          <input v-model="form.name" type="text" required class="w-full rounded border-gray-300" />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">Email</label>
          <input v-model="form.email" type="email" required class="w-full rounded border-gray-300" />
          <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">Phone</label>
          <input v-model="form.phone" type="tel" class="w-full rounded border-gray-300" />
          <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">Temporary password</label>
          <input v-model="form.password" type="password" required class="w-full rounded border-gray-300" />
          <p class="mt-1 text-xs text-gray-500">Use at least 12 characters with upper and lower case letters, a number, and a symbol.</p>
          <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium">Role</label>
          <select v-model="form.role" class="w-full rounded border-gray-300">
            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
          </select>
          <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
        </div>
        <div class="flex gap-3">
          <button type="submit" :disabled="form.processing" class="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-50">Create User</button>
          <Link :href="route('admin.users.index')" class="rounded border px-4 py-2">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
