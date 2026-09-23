<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Edit Message</h1>
    <form @submit.prevent="submit" class="space-y-4">
      <Input label="Name" v-model="form.name" />
      <Input label="Email" v-model="form.email" />
      <Input label="Phone" v-model="form.phone" />
      <Input label="Session Type" v-model="form.session_type" />
      <Textarea label="Message" v-model="form.message" />
      <button class="btn btn-primary" type="submit">Update</button>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import Input from '@/Components/Form/Input.vue'
import Textarea from '@/Components/Form/Textarea.vue'
const props = defineProps({ message: Object })

const form = useForm({
  name: props.message.name,
  email: props.message.email,
  phone: props.message.phone,
  session_type: props.message.session_type,
  message: props.message.message,
})

function submit() {
  form.put(route('admin.messages.update', props.message.id))
}
</script>
