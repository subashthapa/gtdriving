<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

const emit = defineEmits(['date-selected'])
const calendarOptions = {
  plugins: [dayGridPlugin, interactionPlugin], initialView: 'dayGridMonth', selectable: true,
  fixedWeekCount: false, validRange: { start: new Date().toISOString().slice(0, 10) },
  dateClick: info => emit('date-selected', info.dateStr), height: 'auto', buttonText: { today: 'Today' },
}
</script>
<template><div class="booking-calendar"><FullCalendar :options="calendarOptions" /></div></template>
<style scoped>
.booking-calendar :deep(.fc-toolbar-title){font-size:1.15rem}.booking-calendar :deep(.fc-button-primary){background:#1d4ed8;border-color:#1d4ed8}.booking-calendar :deep(.fc-daygrid-day:not(.fc-day-disabled)){cursor:pointer}
@media(max-width:640px){.booking-calendar :deep(.fc-header-toolbar){align-items:flex-start;gap:.75rem}.booking-calendar :deep(.fc-daygrid-day-number){padding:.5rem .35rem}}
</style>
