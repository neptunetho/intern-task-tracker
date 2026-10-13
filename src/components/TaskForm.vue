<script setup>
import { ref } from 'vue'

const emit = defineEmits(['add-task'])
const newTaskTitle = ref('')
const errorMessage = ref('')

function handleSubmit() {
  const title = newTaskTitle.value.trim()

  if (title.length < 3) {
    errorMessage.value = 'Task title must be at least 3 characters.'
    return
  }
  if (title.length > 50) {
    errorMessage.value = 'Task title cannot exceed 50 characters.'
    return
  }

  errorMessage.value = ''
  emit('add-task', title)
  newTaskTitle.value = ''
}
</script>

<template>
  <form @submit.prevent="handleSubmit" style="margin-bottom: 24px;">
    <div style="display: flex; gap: 8px;">
      <input
        v-model="newTaskTitle"
        placeholder="Enter a task (3-50 chars)..."
        style="flex: 1; padding: 10px 12px; border-radius: 6px; border: 1px solid #4b5563; background: #1f2937; color: white;"
      />
      <button
        type="submit"
        style="padding: 10px 18px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;"
      >
        Add
      </button>
    </div>

    <p v-if="errorMessage" style="color: #ef4444; font-size: 0.875rem; margin: 6px 0 0 0;">
      ⚠️ {{ errorMessage }}
    </p>
  </form>
</template>