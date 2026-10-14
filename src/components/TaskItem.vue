<script setup>
defineProps({
  task: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-task', 'delete-task'])

function formatDate(dateStr) {
  if (!dateStr) return ''
  const [year, month, day] = dateStr.split('-')
  if (!year || !month || !day) return dateStr
  return `${day}/${month}/${year}`
}
</script>

<template>
  <div class="flex flex-col gap-3 p-4 rounded-xl bg-slate-950/50 border border-slate-800/80 hover:border-slate-700 transition-colors">
    <div class="flex items-start justify-between gap-4">
      <label class="flex items-start gap-3 flex-1 min-w-0 cursor-pointer select-none">
        <input
          type="checkbox"
          :checked="task.completed"
          @change="$emit('toggle-task', task)"
          class="w-4 h-4 mt-0.5 rounded border-slate-700 bg-slate-900 text-indigo-600 accent-indigo-600 cursor-pointer"
        />
        <div class="flex-1 min-w-0">
          <p
            class="text-sm font-medium truncate transition-colors"
            :class="task.completed ? 'line-through text-slate-500' : 'text-slate-100'"
          >
            {{ task.title }}
          </p>

          <!-- Render description if present -->
          <p
            v-if="task.description"
            class="text-xs mt-1 transition-colors leading-relaxed"
            :class="task.completed ? 'line-through text-slate-600' : 'text-slate-400'"
          >
            {{ task.description }}
          </p>
        </div>
      </label>

      <button
        @click="$emit('delete-task', task.id)"
        class="rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white px-2.5 py-1 text-xs font-medium transition-colors cursor-pointer shrink-0"
      >
        Delete
      </button>
    </div>

    <div class="flex items-center justify-between text-[11px] text-slate-500 border-t border-slate-800/50 pt-2">
      <div>
        <span v-if="task.dueDate" class="text-amber-400/90 font-medium">
          Due: {{ formatDate(task.dueDate) }}
        </span>
      </div>

      <span v-if="task.createdAt">
        Created on: {{ task.createdAt }}
      </span>
    </div>
  </div>
</template>