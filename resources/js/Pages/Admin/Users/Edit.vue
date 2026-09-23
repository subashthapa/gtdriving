<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  roles: { type: Array, default: () => [] },
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone || '',
  role: props.user.roles?.[0]?.name || '',
})

const submit = () => form.put(route('admin.users.update', props.user.id))
</script>

<template>
  <AppLayout title="Edit User">
    <template #header><h2 class="text-xl font-semibold text-gray-800">Edit User</h2></template>
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
          <label class="mb-1 block text-sm font-medium">Role</label>
          <select v-model="form.role" class="w-full rounded border-gray-300">
            <option value="">No role change</option>
            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
          </select>
          <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
        </div>
        <div class="flex gap-3">
          <button type="submit" :disabled="form.processing" class="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-50">Save Changes</button>
          <Link :href="route('admin.users.index')" class="rounded border px-4 py-2">Cancel</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
