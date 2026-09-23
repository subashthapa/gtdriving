<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Create Message</h1>
    <form @submit.prevent="submit" class="space-y-4">
      <Input label="Name" v-model="form.name" />
      <Input label="Email" v-model="form.email" />
      <Input label="Phone" v-model="form.phone" />
      <Input label="Session Type" v-model="form.session_type" />
      <Textarea label="Message" v-model="form.message" />
      <button class="btn btn-primary" type="submit">Save</button>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import Input from '@/components/Form/Input.vue'
import Textarea from '@/components/Form/Textarea.vue'

const form = useForm({
  name: '',
  email: '',
  phone: '',
  session_type: '',
  message: '',
})

function submit() {
  form.post(route('admin.messages.store'))
}
</script>