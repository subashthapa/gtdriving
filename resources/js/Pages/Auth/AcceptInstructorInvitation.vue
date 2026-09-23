<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticationCard from '@/components/AuthenticationCard.vue'
import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue'
import InputError from '@/components/InputError.vue'
import InputLabel from '@/components/InputLabel.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import TextInput from '@/components/TextInput.vue'

const props = defineProps({
  email: String,
  expiresAt: String,
  token: String,
  existingUser: Boolean,
})

const form = useForm({
  name: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const submit = () => form.post(route('instructor-invitations.accept', props.token), {
  onFinish: () => form.reset('password', 'password_confirmation'),
})
</script>

<template>
  <Head title="Accept Instructor Invitation" />
  <AuthenticationCard>
    <template #logo><AuthenticationCardLogo /></template>

    <h1 class="text-xl font-semibold text-gray-900">Join GT Driving as an instructor</h1>
    <p class="mt-2 text-sm text-gray-600">Invitation for <strong>{{ email }}</strong></p>
    <p class="mt-1 text-xs text-gray-500">Expires {{ new Date(expiresAt).toLocaleString('en-AU') }}</p>

    <form @submit.prevent="submit" class="mt-6 space-y-4">
      <template v-if="!existingUser">
        <div>
          <InputLabel for="name" value="Full name" />
          <TextInput id="name" v-model="form.name" required autocomplete="name" class="mt-1 block w-full" />
          <InputError :message="form.errors.name" class="mt-2" />
        </div>
        <div>
          <InputLabel for="phone" value="Phone" />
          <TextInput id="phone" v-model="form.phone" autocomplete="tel" class="mt-1 block w-full" />
          <InputError :message="form.errors.phone" class="mt-2" />
        </div>
        <div>
          <InputLabel for="password" value="Password" />
          <TextInput id="password" v-model="form.password" type="password" required autocomplete="new-password" class="mt-1 block w-full" />
          <p class="mt-1 text-xs text-gray-500">At least 12 characters with upper and lower case letters, a number, and a symbol.</p>
          <InputError :message="form.errors.password" class="mt-2" />
        </div>
        <div>
          <InputLabel for="password_confirmation" value="Confirm password" />
          <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" class="mt-1 block w-full" />
        </div>
      </template>

      <p v-else class="rounded bg-blue-50 p-3 text-sm text-blue-800">Your verified account will be changed to the Instructor role.</p>
      <InputError :message="form.errors.invitation" />

      <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
        Accept invitation
      </PrimaryButton>
    </form>
  </AuthenticationCard>
</template>
