<template>
  <div v-if="selectedSlot" class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <div class="max-w-lg mx-auto bg-white p-6 rounded">
      <h2 class="text-xl font-semibold mb-4 text-center">
        Booking for <span class="text-blue-500">{{ selectedSlot.slot.start_time.slice(0,5) }}</span> on 
        <span class="text-blue-500">{{ selectedDate }}</span>
      </h2>
      <p class="mb-4 text-center text-sm text-gray-600">Instructor: {{ instructorName }}</p>
      <div class="mb-4 rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
        <p><strong>Lesson fee:</strong> {{ formattedAmount }}</p>
        <p>Payment: Cash to the instructor. The booking remains unpaid until the instructor records receipt.</p>
      </div>
      <form @submit.prevent="submitBooking" class="space-y-4">
        <div>
          <input v-model="form.name" placeholder="Full Name" required 
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <input v-model="form.email" type="email" placeholder="Email" required 
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <input v-model="form.phone" type="tel" placeholder="Phone Number" required 
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <textarea v-model="form.instructions" placeholder="Special Instructions"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" ></textarea>
        </div>
        <div>
          <button type="submit" :disabled="submitting"
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" >Confirm Booking</button>
          <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    selectedDate: String,
    selectedSlot: Object,
    instructorId: [Number, String],
    instructorName: String,
    hourlyRate: { type: Number, default: 60 },
    currency: { type: String, default: 'AUD' },
  },
  data() {
    return {
      form: {
        name: '',
        email: '',
        phone: '',
        instructions: '',
        payment_method: 'cash',
      },
      submitting: false,
      error: '',
    };
  },
  async mounted() {
    if (this.isAuthenticated) {
      const user = this.$page.props.auth.user;
      this.form.name = user.name || '';
      this.form.email = user.email || '';
      this.form.phone = user.phone || ''; // Make sure phone exists
    }
  },
  /**
   * Methods
   */
  methods: {
    submitBooking() {
      this.submitting = true;
      this.error = '';
      axios.post('/book', {
        date: this.selectedDate,
        start_time: this.selectedSlot.slot.start_time.slice(0, 5),
        end_time: this.getEndTime(),
        duration: this.selectedSlot.duration,
        instructor: this.instructorId,
        ...this.form,
      })
      .then(() => {
        alert('Booking Confirmed!');
        this.$emit('booking-confirmed');
      })
      .catch((error) => {
        this.error = error.response?.data?.message ||
          Object.values(error.response?.data?.errors || {}).flat()[0] ||
          'Booking failed. Please try again.';
      })
      .finally(() => {
        this.submitting = false;
      });
    },
    /**
     * calculate end time
     */
    getEndTime() {
      const timeString = this.selectedSlot.slot.start_time;
      let [hours, minutes, seconds] = timeString.split(':').map(Number);
      const date = new Date();
      date.setHours(hours, minutes, seconds, 0);

      // Add minutes
      date.setMinutes(date.getMinutes() + this.selectedSlot.duration);

      const newHours = date.getHours().toString().padStart(2, '0');
      const newMinutes = date.getMinutes().toString().padStart(2, '0');
      const newSeconds = date.getSeconds().toString().padStart(2, '0');

      const newTimeString = `${newHours}:${newMinutes}`;
      // console.log(newTimeString); // Expected output: "11:30:00"
      return newTimeString;
    },
  },
  /**
   * Computed
   */
  computed: {
    isAuthenticated() {
      return !!this.$page.props.auth.user;
    },
    amount() {
      return (Number(this.selectedSlot?.duration || 0) / 60) * this.hourlyRate;
    },
    formattedAmount() {
      return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: this.currency,
      }).format(this.amount);
    },
  },
};
</script>
