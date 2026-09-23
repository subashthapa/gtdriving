<template>
    <!-- Features Section -->
    <section id="features" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-8">Booking Calendar</h2>
            <div class="grid grid-cols-1">
                <div>
                    <booking-app
                        :hourly-rate="$page.props.hourlyRate"
                        :currency="$page.props.currency"
                    ></booking-app>
                </div>
            </div>

            <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="text-blue-500 text-4xl mb-4">&#128197;</div>
                    <h3 class="text-xl font-semibold">Easy Booking</h3>
                    <p class="mt-2 text-gray-600">Select and book available time slots in seconds.</p>
                </div>
                <div class="text-center">
                    <div class="text-blue-500 text-4xl mb-4">&#128100;</div>
                    <h3 class="text-xl font-semibold">User-Friendly</h3>
                    <p class="mt-2 text-gray-600">A seamless experience with a clean interface.</p>
                </div>
                <div class="text-center">
                    <div class="text-blue-500 text-4xl mb-4">&#9881;&#65039;</div>
                    <h3 class="text-xl font-semibold">Admin Dashboard</h3>
                    <p class="mt-2 text-gray-600">Effortlessly manage bookings and time slots.</p>
                </div>
            </div> -->
        </div>
    </section>
</template>
<script setup>
import BookingApp from '@/Pages/Booking/Create.vue';
</script>
