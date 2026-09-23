<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'

defineProps({ invitations: Object })

const form = useForm({ email: '' })

const invite = () => form.post(route('admin.instructor-invitations.store'), {
  preserveScroll: true,
  onSuccess: () => form.reset(),
})

const resend = (id) => router.post(route('admin.instructor-invitations.resend', id), {}, { preserveScroll: true })
const revoke = (id) => {
  if (confirm('Revoke this instructor invitation?')) {
    router.delete(route('admin.instructor-invitations.revoke', id), { preserveScroll: true })
  }
}

const badgeClass = (status) => ({
  pending: 'bg-amber-100 text-amber-800',
  accepted: 'bg-emerald-100 text-emerald-800',
  expired: 'bg-gray-100 text-gray-700',
  revoked: 'bg-red-100 text-red-800',
}[status] || 'bg-gray-100 text-gray-700')
</script>

<template>
  <AppLayout title="Instructor Invitations">
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Instructor Invitations</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="invite" class="rounded-lg bg-white p-6 shadow">
          <h3 class="text-lg font-semibold text-gray-900">Invite an instructor</h3>
          <p class="mt-1 text-sm text-gray-600">The invitation is single-use, expires after 72 hours, and assigns the Instructor role after acceptance.</p>
          <div class="mt-4 flex flex-col gap-3 sm:flex-row">
            <input v-model="form.email" type="email" required placeholder="instructor@example.com" class="flex-1 rounded border-gray-300" />
            <button type="submit" :disabled="form.processing" class="rounded bg-blue-600 px-5 py-2 text-white hover:bg-blue-700 disabled:opacity-50">Send invitation</button>
          </div>
          <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
        </form>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div v-if="!invitations.data.length" class="p-8 text-center text-gray-500">No instructor invitations yet.</div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr><th class="px-4 py-3">Email</th><th class="px-4 py-3">Invited by</th><th class="px-4 py-3">Expires</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="invitation in invitations.data" :key="invitation.id">
                  <td class="px-4 py-3"><div class="font-medium text-gray-900">{{ invitation.email }}</div><div v-if="invitation.accepted_user" class="text-xs text-gray-500">Accepted by {{ invitation.accepted_user.name }}</div></td>
                  <td class="px-4 py-3 text-gray-600">{{ invitation.inviter?.name || 'Deleted user' }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ new Date(invitation.expires_at).toLocaleString('en-AU') }}</td>
                  <td class="px-4 py-3"><span :class="badgeClass(invitation.status)" class="rounded-full px-2 py-1 text-xs font-medium capitalize">{{ invitation.status }}</span></td>
                  <td class="space-x-3 px-4 py-3 text-right">
                    <button v-if="invitation.status !== 'accepted'" @click="resend(invitation.id)" class="text-blue-600 hover:underline">Resend</button>
                    <button v-if="invitation.status === 'pending'" @click="revoke(invitation.id)" class="text-red-600 hover:underline">Revoke</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="invitations.links?.length > 3" class="flex flex-wrap gap-1 border-t p-4">
            <Link v-for="link in invitations.links" :key="link.label" :href="link.url || '#'" v-html="link.label" :class="[link.active ? 'bg-blue-600 text-white' : 'bg-white', !link.url ? 'pointer-events-none opacity-40' : '']" class="rounded border px-3 py-1 text-sm" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
