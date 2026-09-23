<template>
  <div class="grid grid-cols-[80%_20%] gap-8">
    <div>
      <BookingCalendar @date-selected="selectDate" />
      <TimeSlots v-if="selectedDate" :selectedDate="selectedDate" @slot-selected="selectSlot" />
      <BookingForm v-if="selectedSlot" 
      :selectedDate="selectedDate" 
      :selectedSlot="selectedSlot"
      @booking-confirmed="handleBookingConfirmed" />
    </div>
  </div>
</template>

<script>
import BookingCalendar from './BookingCalendar.vue';
import TimeSlots from './TimeSlots.vue';
import BookingForm from './BookingForm.vue';

export default {
  components: { BookingCalendar, TimeSlots, BookingForm, Layout },
  data() {
    return {
      selectedDate: null,
      selectedSlot: null,
    };
  },
  methods: {
    selectDate(date) {
        // console.log("Date selected:", date);
        this.selectedDate = date;
        this.selectedSlot = null; // Reset slot when a new date is selected
    },
    selectSlot(slot) {
        // console.log("Slot selected:", slot);
        this.selectedSlot = slot;
    },
    // Reset selectedSlot so that the BookingForm is removed after confirmation
    handleBookingConfirmed() {
      // console.log("Booking confirmed, resetting form.");
      this.selectedSlot = null;
      this.selectedDate = null;
    }
  },
  watch: {
    selectedDate(newDate) {
      // console.log("Updated selectedDate:", newDate); // Debug log
    }
  }
};
</script>
