<template>
  <div>
    <!-- Instructor Selection -->
    <div class="max-w-md mx-auto p-4 bg-white shadow-lg rounded-lg">
      <label for="instructor-select" class="block text-gray-700 font-semibold mb-2">Select an Instructor:</label>
      <select
        id="instructor-select"
        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
        v-model="selectedInstructor"
        @change="fetchEvents"
      >
        <option value="" disabled selected>Select an instructor</option>
        <option v-for="instructor in instructors" :key="instructor.id" :value="instructor.id">
          {{ instructor.name }}
        </option>
      </select>
    </div>

    <!-- Calendar -->
    <vue-cal
      v-if="selectedInstructor"
      :events="events"
      @event-click="onEventClick"
      @cell-click="onCellClick"
      default-view="week"
      :time="true"
      :time-from="7 * 60"
      :time-to="19 * 60"
      :disable-views="['years', 'year', 'month']"
      :disable-past="true"
    />

    <!-- Booking Modal -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 px-4 z-10">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg md:max-w-md">
        <h2 class="text-xl font-semibold mb-4 text-center">Book an Hour</h2>

        <label class="block mb-2">Name:</label>
        <input v-model="userDetails.name" type="text" class="w-full px-3 py-2 border rounded-lg mb-3" placeholder="Enter your name" required />

        <label class="block mb-2">Email:</label>
        <input v-model="userDetails.email" type="email" class="w-full px-3 py-2 border rounded-lg mb-3" placeholder="Enter your email" required />

        <label class="block mb-2">Phone:</label>
        <input v-model="userDetails.phone" type="tel" class="w-full px-3 py-2 border rounded-lg mb-3" placeholder="Enter your phone number" required />

        <label class="block mb-2">Start Time:</label>
        <input
          v-model="userDetails.startTime"
          type="time"
          class="w-full px-3 py-2 border rounded-lg mb-3"
          :min="minStartTime"
          max="19:00"
          required
        />

        <label class="block mb-2">End Time:</label>
        <input
          v-model="userDetails.endTime"
          type="time"
          class="w-full px-3 py-2 border rounded-lg mb-3"
          :min="userDetails.startTime"
          max="19:00"
          required
        />

        <div class="flex justify-end space-x-2">
          <button @click="closeModal" class="px-4 py-2 bg-gray-300 rounded-lg w-full md:w-auto">Cancel</button>
          <button @click="confirmBooking" class="px-4 py-2 bg-blue-500 text-white rounded-lg w-full md:w-auto">Confirm</button>
        </div>
      </div>
    </div>

    <!-- Warning Modal (Past Date/Time Selection) -->
    <div v-if="showWarningModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 px-4">
      <div class="bg-red-100 p-6 rounded-lg shadow-lg w-full max-w-sm md:max-w-md">
        <h2 class="text-xl font-semibold text-red-600 mb-4 text-center">Invalid Selection</h2>
        <p class="mb-4 text-center">You cannot book a past date or time. Please select a valid future time slot.</p>
        <button @click="closeWarningModal" class="px-4 py-2 bg-red-500 text-white rounded-lg w-full">OK</button>
      </div>
    </div>
  </div>
</template>


<!---

### 🚀 **Changes & Fixes**
1. **🔴 New Warning Modal**:  
   - Shows when a user **selects a past date or time**.
   - Prevents the booking modal from opening in such cases.

2. **⏳ Better Time Validation**:  
   - Now dynamically restricts past time **only for today's date**.
   - Users can **only select future time slots**.

---

### 📌 **New Methods in `<script>`**
--->
<script>
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css'; // Import Vue Cal styles
import axios from 'axios';

export default {
  components: { VueCal },
  data() {
    return {
      selectedInstructor: 3,
      events: [],
      instructors: [],
      showModal: false,
      showWarningModal: false,
      selectedDate: null,
      userDetails: {
        name: '',
        email: '',
        phone: '',
        startTime: '',
        endTime: '',
        startDate: '',
        endDate: '',
        instructor: '',
        instructions: ''
      }
    };
  },
  mounted() {
    this.fetchInstructors();
    this.fetchEvents();

  },
  methods: {
    fetchEvents() {
      if (!this.selectedInstructor) {
        this.events = [];
        return;
      }

      axios.get(`/instructors/${this.selectedInstructor}/bookings`)
        .then(response => {
          this.events = response.data.map(booking => ({
            id: booking.id,
            title: booking.instructions,
            start: new Date(booking.start),
            end: new Date(booking.end),
          }));
          console.log(this.events);
        })
        .catch(error => {
          console.error('Error fetching bookings:', error);
        });
    },
    fetchInstructors() {
      axios.get(`/instructors`)
        .then(response => {
          this.instructors = response.data;
        })
        .catch(error => {
          console.error('Error fetching instructors:', error);
        });
    },
    onEventClick(event) {
      alert(`Event: ${event.title}`);
    },
    onCellClick(date) {
      const now = new Date();
      const selected = new Date(date);

      // Block past date and past time for today
      if (selected < now || (selected.toDateString() === now.toDateString() && selected.getHours() < now.getHours())) {
        this.showWarningModal = true;  // Show warning modal
        return;
      }

      // Allow booking for valid future time
      this.selectedDate = date;
      this.userDetails.startTime = this.formatTime(date);
      this.userDetails.endTime = this.formatTime(date, 1); // Default 1-hour duration
      this.userDetails.startDate = this.selectedDate;
      this.userDetails.endDate = this.selectedDate;
      this.userDetails.instructor = this.selectedInstructor;
      this.showModal = true;
    },
    formatTime(date, hoursToAdd = 0) {
      let d = new Date(date);
      d.setHours(d.getHours() + hoursToAdd);
      let hours = d.getHours().toString().padStart(2, '0');
      let minutes = d.getMinutes().toString().padStart(2, '0');
      return `${hours}:${minutes}`;
    },
    closeModal() {
      this.showModal = false;
      this.userDetails = { name: '', email: '', phone: '', startTime: '', endTime: '' };
    },
    closeWarningModal() {
      this.showWarningModal = false;
    },
    confirmBooking() {
      // Validate required fields
      if (
        !this.userDetails.name ||
        !this.userDetails.email ||
        !this.userDetails.phone ||
        !this.userDetails.startTime ||
        !this.userDetails.endTime
      ) {
        alert('Please fill in all fields.');
        return;
      }

      // Check for overlapping bookings
      if (this.isBookingOverlapping(this.userDetails.startTime, this.userDetails.endTime, this.selectedDate)) {
        alert('This time slot overlaps with an existing booking. Please choose a different time.');
        return;
      }

      // Create the new booking object
      const newBooking = {
        instructions: `Booked by ${this.userDetails.name}`,
        start_time: this.userDetails.startTime,
        end_time: this.userDetails.endTime,
        instructor_id: this.selectedInstructor,
        start_date: this.selectedDate,
        end_date: this.selectedDate,
        instructor: this.selectedInstructor,
        user: this.userDetails
      };

      axios.post('/booking', newBooking)
        .then(response => {
          this.events.push({
            title: response.data.title,
            start: new Date(response.data.start),
            end: new Date(response.data.end),
          });
          this.closeModal();
          alert('Booking confirmed!');
        })
        .catch(error => {
          console.error('Error creating event:', error);
        });
    },
    
    // Checks if the new booking overlaps with any existing event
    isBookingOverlapping(startTime, endTime, date) {
      // Convert the selected date with the provided times to Date objects
      const newStart = new Date(date);
      const [startHours, startMinutes] = startTime.split(':');
      newStart.setHours(parseInt(startHours), parseInt(startMinutes));

      const newEnd = new Date(date);
      const [endHours, endMinutes] = endTime.split(':');
      newEnd.setHours(parseInt(endHours), parseInt(endMinutes));

      // Check against each existing event for an overlap.
      return this.events.some(event => {
        const eventStart = new Date(event.start);
        const eventEnd = new Date(event.end);

        // Overlap occurs if the new booking starts before an event ends
        // and ends after the event starts.
        return newStart < eventEnd && newEnd > eventStart;
      });
    },
  }
};
</script>
