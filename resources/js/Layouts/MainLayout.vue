<template>
    <Navbar></Navbar>
    <div class="min-h-screen bg-gray-50 text-center">
      <slot />
    </div>
</template>
<script>
import Navbar from '@/components/Navbar.vue'

export default {
    components: { Navbar }
}
</script>