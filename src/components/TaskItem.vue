<script setup>
defineProps({
  task: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-task', 'delete-task'])
</script>

<template>
  <li style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 10px 14px; margin-bottom: 8px; background: #1f2937; border-radius: 6px;">
    <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
      <!-- When clicked, notify parent to toggle -->
      <input
        type="checkbox"
        :checked="task.completed"
        @change="$emit('toggle-task', task)"
        style="cursor: pointer;"
      />
      <span :style="{ textDecoration: task.completed ? 'line-through' : 'none', color: task.completed ? '#9ca3af' : 'white' }">
        {{ task.title }}
      </span>
    </div>

    <!-- When clicked, notify parent to delete -->
    <button
      @click="$emit('delete-task', task.id)"
      style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; flex-shrink: 0;"
    >
      Delete
    </button>
  </li>
</template>