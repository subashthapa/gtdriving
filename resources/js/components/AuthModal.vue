<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black opacity-50 z-40" @click="$emit('close')"></div>
    <div class="bg-white rounded-lg p-6 relative z-50 w-full max-w-md" @click.stop>
      <h3 class="text-lg font-semibold mb-4">Login</h3>

      <div v-if="error" class="text-red-600 mb-3">{{ error }}</div>

      <form @submit.prevent="submit">
        <div class="mb-3">
          <input v-model="form.email" type="email" placeholder="Email" required
            class="w-full px-3 py-2 border rounded" />
        </div>
        <div class="mb-3">
          <input v-model="form.password" type="password" placeholder="Password" required
            class="w-full px-3 py-2 border rounded" />
        </div>
        <div class="flex items-center justify-between">
          <button type="button" class="text-sm text-gray-600" @click="$emit('close')">Cancel</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Sign in</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
// Use the Inertia instance provided on the Vue app via plugin (this.$inertia)

export default {
  name: 'AuthModal',
  data() {
    return {
      form: {
        email: '',
        password: '',
      },
      error: null,
    };
  },
  methods: {
    async submit() {
      this.error = null;

      try {
        await axios.get('/sanctum/csrf-cookie');
      } catch (e) {
        this.error = 'Unable to initialize session. Please refresh and try again.';
        return;
      }

      axios.post('/login', this.form)
        .then(() => {
          // Force a full refresh so CSRF meta + headers are regenerated with the new session.
          window.location.reload();
          this.$emit('authenticated');
          this.$emit('close');
        })
        .catch(err => {
          if (err.response && err.response.data && err.response.data.message) {
            this.error = err.response.data.message;
            return;
          }
          if (err.response && err.response.status === 422) {
            const errors = err.response.data.errors || {};
            this.error = Object.values(errors).flat()[0] || 'Invalid credentials';
            return;
          }
          if (err.response && err.response.status === 419) {
            this.error = 'Session expired. Please refresh and try again.';
            return;
          }
          this.error = 'Login failed';
        });
    }
  }
};
</script>
