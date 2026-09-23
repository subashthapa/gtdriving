<template>
  <div>
    <FullCalendar
      :options="calendarOptions"
      :selectable="true"
    />
  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import axios from 'axios';

export default {
  components: { FullCalendar },
  data() {
    return {
      defaultEvents: [
          { title: 'Event 1', date: '2025-03-15' },
          { title: 'Event 2', date: '2025-03-20' },
        ],
        fetchedEvents: []
    };
  },
  mounted() {
    console.log('BookingCalendar component mounted');
    this.getBookedDates();
  },
  methods: {
    onDateClick(info) {
      // console.log("Clicked date", info);
      this.$emit('date-selected', info.dateStr);
    },
    getBookedDates() {
      console.log('Fetching booked dates from API');
      axios.get('/api/booked-dates').then(response => {
        this.fetchedEvents = response.data.map(booking => ({
          title: booking.instructions || 'Booked',
          date: booking.start_date,
        }));
      }).catch(error => {
        console.error('Error fetching booked dates: ', error);
      });
    }
  },
  computed: {
    calendarOptions() {
      return {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        selectable: true,
        dateClick: this.onDateClick,  // Now correctly references the method
        events: [...this.defaultEvents, ...this.fetchedEvents],
      };
    }
  },
};
</script>
