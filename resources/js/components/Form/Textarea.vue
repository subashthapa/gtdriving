<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  modelValue: {
    type: String,
    default: '',
  },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <label class="block">
    <span class="mb-1 block text-sm font-medium text-gray-700">{{ label }}</span>
    <textarea
      :value="modelValue"
      rows="5"
      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </label>
</template>
