<template>
  <div title="Dashboard">
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
      <div class="max-w-xl rounded-lg bg-white p-5 shadow">
        <label for="booking-instructor" class="mb-2 block font-semibold text-gray-800">
          Choose an instructor
        </label>
        <select
          id="booking-instructor"
          v-model="selectedInstructorId"
          class="w-full rounded border-gray-300"
        >
          <option value="" disabled>Select an instructor</option>
          <option v-for="instructor in instructors" :key="instructor.id" :value="instructor.id">
            {{ instructor.name }}
          </option>
        </select>
        <p v-if="instructorError" class="mt-2 text-sm text-red-600">{{ instructorError }}</p>
      </div>

      <!-- Booking Calendar -->
      <BookingCalendar v-if="selectedInstructorId" @date-selected="selectDate" />
      <p v-else class="rounded-lg bg-blue-50 p-4 text-blue-800">
        Select an instructor to view their available booking times.
      </p>
      
      <!-- Time Slots Container (only shown after a date is selected) -->
      <div ref="timeSlotsRef" v-if="selectedDate">
        <TimeSlots
          v-if="selectedDate"
          :selectedDate="selectedDate"
          :instructorId="selectedInstructorId"
          @slot-selected="selectSlot"
        />
      </div>
      
      <!-- Booking Form Container (only shown after a time slot is selected) -->
      <div ref="bookingFormRef" v-if="selectedSlot">
        <BookingForm v-if="selectedSlot" 
        :selectedDate="selectedDate" 
        :selectedSlot="selectedSlot"
        :instructorId="selectedInstructorId"
        :instructorName="selectedInstructor?.name"
        :hourlyRate="hourlyRate"
        :currency="currency"
        @booking-confirmed="handleBookingConfirmed" />
      </div>
      
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import BookingCalendar from './BookingCalendar.vue';
import TimeSlots from './TimeSlots.vue';
import BookingForm from './BookingForm.vue';

export default {
  components: { BookingCalendar, TimeSlots, BookingForm, MainLayout },
  props: {
    hourlyRate: { type: Number, default: 60 },
    currency: { type: String, default: 'AUD' },
  },
  data() {
    return {
      selectedDate: null,
      selectedSlot: null,
      selectedInstructorId: '',
      instructors: [],
      instructorError: '',
    };
  },
  computed: {
    selectedInstructor() {
      return this.instructors.find((instructor) => instructor.id === Number(this.selectedInstructorId));
    },
  },
  mounted() {
    axios.get('/api/instructors')
      .then(({ data }) => {
        this.instructors = data;
      })
      .catch(() => {
        this.instructorError = 'Unable to load instructors. Please refresh and try again.';
      });
  },
  methods: {
    selectDate(date) {
        // console.log("Date selected:", date);
        this.selectedDate = date;
        this.selectedSlot = null; // Reset slot when a new date is selected

        // Wait for the UI to update, then scroll to the TimeSlots section
        this.$nextTick(() => {
          if (this.$refs.timeSlotsRef) {
            this.$refs.timeSlotsRef.scrollIntoView({ behavior: 'smooth' });
          }
        });
    },
    selectSlot(slot) {
        // console.log("Slot selected:", slot);
        this.selectedSlot = slot;

        // Scroll to the BookingForm section
        this.$nextTick(() => {
          if (this.$refs.bookingFormRef) {
            this.$refs.bookingFormRef.scrollIntoView({ behavior: 'smooth' });
          }
        });
    },
    // Reset selectedSlot so that the BookingForm is removed after confirmation
    handleBookingConfirmed() {
      // console.log("Booking confirmed, resetting form.");
      this.selectedSlot = null;
      this.selectedDate = null;
    }
  },
  watch: {
    selectedInstructorId() {
      this.selectedDate = null;
      this.selectedSlot = null;
    }
  }
};
</script>
