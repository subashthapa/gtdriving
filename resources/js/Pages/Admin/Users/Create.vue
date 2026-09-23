<template>
  <AppLayout title="Create User">
    <div class="p-6 max-w-xl mx-auto">
      <h1 class="text-2xl font-bold mb-4">Create User</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <input v-model="form.name" type="text" placeholder="Name" class="input w-full" />
        <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>

        <input v-model="form.email" type="email" placeholder="Email" class="input w-full" />
        <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>

        <input v-model="form.password" type="password" placeholder="Password" class="input w-full" />
        <div v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</div>

        <button type="submit" class="btn btn-primary">Create</button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  password: '',
});

const submit = () => {
  form.post(route('admin.users.store'));
};
</script>