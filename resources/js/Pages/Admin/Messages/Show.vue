<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Message Detail</h1>
    <div class="mb-2"><strong>Name:</strong> {{ message.name }}</div>
    <div class="mb-2"><strong>Email:</strong> {{ message.email }}</div>
    <div class="mb-2"><strong>Phone:</strong> {{ message.phone }}</div>
    <div class="mb-2"><strong>Session Type:</strong> {{ message.session_type }}</div>
    <div class="mb-2"><strong>Message:</strong> {{ message.message }}</div>
    <Link :href="route('admin.messages.index')" class="text-blue-500">Back</Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
const props = defineProps({ message: Object })
</script>
