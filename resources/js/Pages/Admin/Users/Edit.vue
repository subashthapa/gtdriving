<template>
  <AppLayout title="Edit User">
    <div class="p-6 max-w-xl mx-auto">
      <h1 class="text-2xl font-bold mb-4">Edit User</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <input v-model="form.name" type="text" class="input w-full" />
        <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>

        <input v-model="form.email" type="email" class="input w-full" />
        <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>

        <button type="submit" class="btn btn-primary">Update</button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({ user: Object });

const form = useForm({
  name: props.user.name,
  email: props.user.email,
});

const submit = () => {
  form.put(route('admin.users.update', props.user.id));
};
</script>