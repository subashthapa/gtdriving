<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const dropdownOpen = ref(false)
const logoutForm = useForm({})

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const logout = () => {
  logoutForm.post(route('logout'))
}

// Optional: close dropdown if clicking outside
const handleClickOutside = (event) => {
  const dropdown = document.getElementById('user-dropdown')
  if (dropdown && !dropdown.contains(event.target)) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <!-- Logo -->
        <Link :href="route('home')" class="flex items-center space-x-4 text-lg font-bold text-blue-800">
          <img src="/img/logo-sm.jpg" class="w-12 h-12" alt="Logo" />
          <span>GT Driving Solution</span>
        </Link>

        <!-- Navigation Links -->
        <div class="hidden md:flex space-x-8 items-center">
          <a href="#about" class="text-gray-700 px-4 py-2 hover:text-blue-500">About Us</a>
          <a href="#features" class="text-gray-700 px-4 py-2 hover:text-blue-500">Features</a>
          <a href="#testimonials" class="text-gray-700 px-4 py-2 hover:text-blue-500">Testimonials</a>

          <!-- If user not logged in -->
          <template v-if="!$page.props.auth.user">
            <a href="#book" class="text-white bg-blue-500 px-4 py-2 rounded hover:bg-blue-600">Book Now</a>
            <Link :href="route('login')" class="text-gray-700 hover:text-blue-600 px-4">Login</Link>
            <Link :href="route('register')" class="text-gray-700 hover:text-blue-600 px-4">Register</Link>

          </template>

          <!-- If user is logged in -->
          <template v-else>
            <div class="relative" id="user-dropdown">
              <button
                @click="toggleDropdown"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600"
              >
                {{ $page.props.auth.user.name }}
                <svg
                  class="w-4 h-4 ml-1 transition-transform duration-200"
                  :class="{ 'rotate-180': dropdownOpen }"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <div v-show="dropdownOpen" class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-md z-50">
                <Link :href="route('dashboard')" class="block px-4 py-2 hover:bg-gray-100">Dashboard</Link>
                <Link :href="route('bookings.index')" class="block px-4 py-2 hover:bg-gray-100">My Bookings</Link>
                <form @submit.prevent="logout">
                  <button class="block w-full text-left px-4 py-2 hover:bg-gray-100">Log Out</button>
                </form>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>
