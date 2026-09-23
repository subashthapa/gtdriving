<template>
    <!-- Booking CTA Section -->
    <section id="book" class="py-16 bg-gradient-to-r from-blue-300 via-blue-600 to-purple-900 text-white text-center">
        <h2 class="text-3xl font-bold mb-4">Ready to Book Your Slot?</h2>
        <p class="text-lg mb-8">Click below to get started with your booking process!</p>
        <a href="/book" class="bg-white text-blue-500 px-6 py-3 rounded shadow hover:bg-gray-100">Book Now</a>
    </section>

    <section id="booking" class="bg-gray-100 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <!-- Booking Form -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Book a Session</h2>
                    <form action="/custom-booking" method="POST">
                        @csrf
                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-medium">Name</label>
                            <input type="text" id="name" name="name" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium">Email</label>
                            <input type="email" id="email" name="email" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <!-- Phone -->
                        <div class="mb-4">
                            <label for="phone" class="block text-gray-700 font-medium">Phone</label>
                            <input type="text" id="phone" name="phone" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <!-- Session Type -->
                        <div class="mb-4">
                            <label for="session" class="block text-gray-700 font-medium">Session Type</label>
                            <select id="session" name="session" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="45-min">1 hour Session</option>
                                <option value="1-hour">1 hour Session</option>
                                <option value="2-hour">2 hours Session</option>
                                <option value="weekly">Weekly Session</option>
                                <option value="custom">Custom Session</option>
                            </select>
                        </div>
                        <!-- Submit Button -->
                        <button type="submit"
                                class="bg-blue-500 text-white px-6 py-3 rounded-md shadow hover:bg-blue-600 transition duration-300">
                            Book Now
                        </button>
                    </form>
                </div>

                <!-- Map -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Our Location</h2>
                    <div class="rounded-lg shadow-lg overflow-hidden">
                        <iframe
                            class="w-full h-80"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.8392313408963!2d-122.47825528468254!3d37.81992897975162!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8085818c5ebc8b19%3A0x7f9d70e35de2de91!2sGolden%20Gate%20Bridge!5e0!3m2!1sen!2sus!4v1696703134728!5m2!1sen!2sus"
                            allowfullscreen="true"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>