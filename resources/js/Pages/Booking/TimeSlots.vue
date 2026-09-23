<template>
  <div>
    <h2>Available Slots for {{ selectedDate }}</h2>

    <!-- Select Duration Option -->
    <div class="mb-4">
      <label class="block text-gray-700 font-semibold mb-2">Select Duration:</label>
      <select v-model="selectedDuration" class="p-2 border rounded w-full">
        <option value="30">30 minutes</option>
        <option value="60">1 hour</option>
        <option value="90">1 hour 30 minutes</option>
        <option value="120">2 hours</option>
        <option value="150">2 hours 30 minutes</option>
        <option value="180">3 hours</option>
      </select>
    </div>

    <!-- Slots Grid -->
    <div v-if="loading" class="text-gray-500">Loading available times...</div>
    <div v-else-if="slots.length" class="grid grid-cols-2 gap-2 sm:grid-cols-3">
      <button
        v-for="slot in slots"
        :key="slot.id"
        class="p-2 border rounded hover:bg-gray-200"
        @click="selectSlot(slot)"
      >
        {{ slot.start_time.slice(0,5) }}
      </button>
    </div>
    <p v-else class="text-gray-500">No times are available for this instructor on this day.</p>
    <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: ['selectedDate', 'instructorId'],
  data() {
    return {
      slots: [],
      selectedDuration: '60',
      loading: false,
      error: '',
    };
  },
  watch: {
    selectedDate: {
      immediate: true,
      handler() {
        this.fetchSlots();
      },
    },
    instructorId() {
      this.slots = [];
      this.fetchSlots();
    },
  },
  methods: {
    fetchSlots() {
      if (!this.selectedDate || !this.instructorId) return;

      this.loading = true;
      this.error = '';
      axios.get('/api/available-slots', {
        params: { date: this.selectedDate, instructor: this.instructorId },
      })
        .then(response => {
          this.slots = response.data;
        })
        .catch(() => {
          this.slots = [];
          this.error = 'Unable to load available times.';
        })
        .finally(() => {
          this.loading = false;
        });
    },
    selectSlot(slot) {
      // console.log(slot);
      // Emit both the selected slot and the chosen duration
      this.$emit('slot-selected', { slot, duration: this.selectedDuration });
    }
  },
};
</script>
