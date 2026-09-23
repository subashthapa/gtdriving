<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GT Driving</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body class="font-sans bg-gray-50 text-gray-900" id="app">
    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <a href="#" class="flex items-center space-x-4 text-lg font-bold text-blue-800">
                    <img src="./img/logo-sm.jpg" class="w-12 h-12" alt="Logo" />
                    <span>GT Driving Solution</span>
                </a>
                <div class="hidden md:flex space-x-8">
                    <a href="#about" class="text-gray-700 px-4 py-2 hover:text-blue-500">About Us</a>
                    <a href="#features" class="text-gray-700 px-4 py-2 hover:text-blue-500">Features</a>
                    <a href="#testimonials" class="text-gray-700 px-4 py-2 hover:text-blue-500">Testimonials</a>
                    <a href="#book" class="text-white bg-blue-500 px-4 py-2 rounded hover:bg-blue-600">Book Now</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="relative bg-cover bg-center h-screen text-white hero-bg bg-cover">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div> <!-- Dark overlay -->
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-40 text-center">
            <h1 class="text-6xl font-bold leading-tight">
                GT Driving Solutions
            </h1>
            <p class="mt-8 text-2xl leading-relaxed">
                Your Path to Safe and Confident Driving
            </p>
            <a href="#book" 
            class="mt-10 bg-white text-blue-500 px-8 py-4 text-xl rounded-lg shadow hover:bg-gray-100 inline-block transition duration-300">
                Get Started
            </a>
        </div>
    </header>

    <section class="bg-gray-100 py-20" id="packages">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-extrabold text-gray-800 mb-8">Our Packages</h2>
            <p class="text-lg text-gray-600 mb-16">
                Choose the session that best fits your needs. Whether it's a single session or a tailored plan, we've got you covered.
            </p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- 45 Minutes Session -->
                <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-2xl transition duration-300">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Beginner Package</h3>
                    <p class="text-gray-600 mb-6">
                        Ideal for quick appointments or a focused consultation.
                    </p>
                    <p class="text-3xl font-bold text-blue-800 mb-4">$600</p>
                    <a href="#book" 
                    class="bg-yellow-500 text-white-800 px-6 py-3 rounded shadow hover:bg-yellow-600 transition duration-300">
                        Book Now
                    </a>
                </div>

                <!-- 1 Hour Session -->
                <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-2xl transition duration-300">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Intermediate Package</h3>
                    <p class="text-gray-600 mb-6">
                        Perfect for standard appointments or extended consultations.
                    </p>
                    <p class="text-3xl font-bold text-blue-800 mb-4">$360</p>
                    <a href="#book" 
                    class="bg-yellow-500 text-white-800 px-6 py-3 rounded shadow hover:bg-yellow-600 transition duration-300">
                        Book Now
                    </a>
                </div>

                <!-- 2 Hours Session -->
                <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-2xl transition duration-300">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Advanced Package</h3>
                    <p class="text-gray-600 mb-6">
                    Ideal for night driving, complex traffic scenarios
                    </p>
                    <p class="text-3xl font-bold text-blue-800 mb-4">$240</p>
                    <a href="#book" 
                    class="bg-yellow-500 text-white-800 px-6 py-3 rounded shadow hover:bg-yellow-600 transition duration-300">
                        Book Now
                    </a>
                </div>

                <!-- Weekly Session -->
                <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-2xl transition duration-300">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Test Preparation Package</h3>
                    <p class="text-gray-600 mb-6">
                        Great for Mock driving test, exam route practice & confidence building
                    </p>
                    <p class="text-3xl font-bold text-blue-800 mb-4">$180</p>
                    <a href="#book" 
                    class="bg-yellow-500 text-white-800 px-6 py-3 rounded shadow hover:bg-yellow-600 transition duration-300">
                        Book Now
                    </a>
                </div>

                <!-- Monthly Session -->
                <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-2xl transition duration-300">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Individual Lesson</h3>
                    <p class="text-gray-600 mb-6">
                        Customized to your specific needs, flexible scheduling
                    </p>
                    <p class="text-3xl font-bold text-blue-800 mb-4">$225</p>
                    <a href="#book" 
                    class="bg-yellow-500 text-white-800 px-6 py-3 rounded shadow hover:bg-yellow-600 transition duration-300">
                        Book Now
                    </a>
                </div>

                <!-- Custom Session -->
                <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-2xl transition duration-300">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Vehicle hiring for test</h3>
                    <p class="text-gray-600 mb-6">
                        Includes pick up and drop off to the student convenient location
                    </p>
                    <p class="text-3xl font-bold text-blue-800 mb-4">$225</p>
                    <a href="#contact" 
                    class="bg-yellow-500 text-white-800 px-6 py-3 rounded shadow hover:bg-yellow-600 transition duration-300">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
    </section>



    <!-- About Us Section -->
    <section id="about" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-8">About Us</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <!-- Image -->
                <div>
                    <img src="./img/instructor-note copy.jpg" alt="About Us" class="rounded-lg shadow-lg w-full">
                </div>
                <!-- Text -->
                <div>
                    <p class="text-lg text-gray-600 mb-6">
                        At GT Driving Solutions we are dedicated to transforming nervous learners into confident, skilled drivers with an experienced instructor, personalized approach, and comprehensive training programs, we provide a safe, supportive environment for drivers of all ages and experience levels. 
                    </p>
                    <p class="text-lg text-gray-600 mb-6">
                        At GT Driving Solutions, we are dedicated to transforming nervous learners into confident, skilled drivers with an experienced instructor, personalized approach, and comprehensive training programs. We provide a safe, supportive environment for drivers of all ages and experience levels.
                    </p>
                    
                </div>
            </div>
        </div>
    </section>


    <!-- Features Section -->
    <section id="features" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-8">Booking Calendar</h2>
            <div class="grid grid-cols-1 md:grid-cols-[80%_20%] gap-8">
                <div>
                    <booking-calendar></booking-calendar>
                </div>
                <div>
                    Book now
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

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-8">What Our Users Say</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="p-6 bg-white shadow rounded">
                    <p class="text-gray-600">"BookingApp has transformed the way we manage appointments. Highly recommend!"</p>
                    <h4 class="mt-4 font-bold text-blue-500">- John Doe</h4>
                </div>
                <div class="p-6 bg-white shadow rounded">
                    <p class="text-gray-600">"The interface is intuitive, and the calendar is a lifesaver. Love it!"</p>
                    <h4 class="mt-4 font-bold text-blue-500">- Jane Smith</h4>
                </div>
            </div>
        </div>
    </section>

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
                    <form action="/submit-booking" method="POST">
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
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="py-6 bg-gray-900 text-gray-300 text-center">
        <p>&copy; {{ date('Y') }} GT Driving. All Rights Reserved.</p>
    </footer>
</body>
</html>
