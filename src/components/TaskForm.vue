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
  <form @submit.prevent="handleSubmit" class="space-y-2">
    <div class="flex items-center gap-3">
      <input
        v-model="newTaskTitle"
        placeholder="Enter a task (3-50 chars)..."
        class="flex-1 rounded-xl bg-slate-950/60 border border-slate-800 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors"
      />
      <button
        type="submit"
        class="rounded-xl bg-indigo-600 hover:bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white transition-colors cursor-pointer"
      >
        Add
      </button>
    </div>

    <p v-if="errorMessage" class="text-xs text-rose-400 pl-1">
      ⚠️ {{ errorMessage }}
    </p>
  </form>
</template>