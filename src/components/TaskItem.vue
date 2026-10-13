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
  <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-slate-950/50 border border-slate-800/80 hover:border-slate-700 transition-colors">
    <label class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer select-none">
      <input
        type="checkbox"
        :checked="task.completed"
        @change="$emit('toggle-task', task)"
        class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 accent-indigo-600 cursor-pointer"
      />
      <span
        class="text-sm truncate transition-colors"
        :class="task.completed ? 'line-through text-slate-500' : 'text-slate-200'"
      >
        {{ task.title }}
      </span>
    </label>

    <button
      @click="$emit('delete-task', task.id)"
      class="rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer"
    >
      Delete
    </button>
  </div>
</template>