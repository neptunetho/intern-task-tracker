<script setup>
import { ref, onMounted } from 'vue'
import TaskForm from './components/TaskForm.vue'
import TaskItem from './components/TaskItem.vue'

const API_URL = 'http://localhost:3000/tasks'

const tasks = ref([])
const loading = ref(false)
const error = ref(null)

async function fetchTasks() {
  loading.value = true
  error.value = null
  try {
    const res = await fetch(API_URL)
    if (!res.ok) throw new Error('Failed to load tasks')
    tasks.value = await res.json()
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

async function handleAddTask(title) {
  try {
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title, completed: false })
    })
    if (!res.ok) throw new Error('Failed to save task')
    await fetchTasks()
  } catch (err) {
    error.value = err.message
  }
}

async function handleToggleTask(task) {
  try {
    await fetch(`${API_URL}/${task.id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ completed: !task.completed })
    })
    task.completed = !task.completed
  } catch (err) {
    error.value = 'Failed to update task'
  }
}

async function handleDeleteTask(id) {
  try {
    await fetch(`${API_URL}/${id}`, { method: 'DELETE' })
    tasks.value = tasks.value.filter(task => task.id !== id)
  } catch (err) {
    error.value = 'Failed to delete task'
  }
}

onMounted(fetchTasks)
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    
    <!-- 1. Header (Full Width) -->
    <header class="w-full border-b border-slate-800 bg-slate-900/80 pl-14 pr-8 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-xl font-bold tracking-tight text-white">Task Manager</h1>
      </div>

      <!-- Profile Avatar -->
      <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sm text-indigo-400">
        AH
      </div>
    </header>

    <!-- 2. Main Dashboard (2 Columns like your Figma) -->
    <div class="max-w-7xl w-full mx-auto p-8 grid grid-cols-1 lg:grid-cols-3 gap-8 flex-1">
      
      <!-- LEFT AREA (Takes 2 of 3 columns): Add Task + Cards Grid -->
      <main class="lg:col-span-2 space-y-6">
        
        <!-- Add Task Container -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 shadow-sm">
          <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Add Task</h2>
          <TaskForm @add-task="handleAddTask" />
        </div>

        <!-- Task Cards Grid -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 shadow-sm">
          <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Task List</h2>

          <div v-if="loading" class="text-center py-8 text-slate-500 text-sm">
            Loading tasks...
          </div>

          <div v-else-if="error" class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
            ⚠️ {{ error }}
          </div>

          <!-- Cards in a 2-Column Grid (Like your yellow boxes) -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <TaskItem
              v-for="task in tasks"
              :key="task.id"
              :task="task"
              @toggle-task="handleToggleTask"
              @delete-task="handleDeleteTask"
            />

            <div v-if="tasks.length === 0" class="col-span-2 text-center py-8 text-slate-500 text-sm">
              No tasks yet. Add one above!
            </div>
          </div>
        </div>

      </main>

      <!-- RIGHT AREA (Takes 1 column): Calendar Sidebar -->
      <aside class="space-y-6">
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 shadow-sm">
          <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Calendar</h2>
          
          <!-- Calendar Placeholder Box -->
          <div class="h-80 rounded-xl bg-slate-950/60 border border-dashed border-slate-800 flex flex-col items-center justify-center text-slate-500 text-sm p-4 text-center">
            <span class="text-3xl mb-2">📅</span>
            <span>Calendar Widget</span>
            <span class="text-xs text-slate-600 mt-1">We'll drop the interactive date picker here</span>
          </div>
        </div>
      </aside>

    </div>

  </div>
</template>