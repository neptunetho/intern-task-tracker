<script setup>
import { ref, computed, onMounted } from 'vue'
import TaskForm from './components/TaskForm.vue'
import TaskItem from './components/TaskItem.vue'

const API_URL = 'http://localhost:3000/tasks'

const tasks = ref([])
const pendingTasks = computed(() => tasks.value.filter(t => !t.completed))
const completedTasks = computed(() => tasks.value.filter(t => t.completed))
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

async function handleAddTask(taskData) {
  try {
    const formattedCreatedDate = new Date().toLocaleDateString('en-GB')

    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        title: taskData.title,
        description: taskData.description,
        dueDate: taskData.dueDate,
        createdAt: formattedCreatedDate,
        completed: false
      })
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
    
    <header class="w-full border-b border-slate-800 bg-slate-900/80">
  <div class="max-w-7xl mx-auto px-8 py-4 flex items-center justify-center">
    <h1 class="text-xl font-bold tracking-tight text-white">Task Manager</h1>
  </div>
</header>

    <div class="max-w-7xl w-full mx-auto p-8 grid grid-cols-1 lg:grid-cols-2 gap-8 flex-1">
      
      <main class="space-y-6">
        
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 shadow-sm">
          <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Add Task</h2>
          <TaskForm @add-task="handleAddTask" />
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">To-Do</h2>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
              {{ pendingTasks.length }}
            </span>
          </div>

          <div v-if="loading" class="text-center py-6 text-slate-500 text-sm">
            Loading tasks...
          </div>

          <div v-else-if="error" class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
            ⚠️ {{ error }}
          </div>

          <div v-else class="space-y-3">
            <TaskItem
              v-for="task in pendingTasks"
              :key="task.id"
              :task="task"
              @toggle-task="handleToggleTask"
              @delete-task="handleDeleteTask"
            />

            <p v-if="pendingTasks.length === 0" class="text-center py-6 text-slate-500 text-sm">
              All caught up! No pending tasks.
            </p>
          </div>
        </div>

      </main>

      <aside class="space-y-6">
        
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Completed</h2>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              {{ completedTasks.length }}
            </span>
          </div>

          <div class="space-y-3">
            <TaskItem
              v-for="task in completedTasks"
              :key="task.id"
              :task="task"
              @toggle-task="handleToggleTask"
              @delete-task="handleDeleteTask"
            />

            <p v-if="completedTasks.length === 0" class="text-center py-8 text-slate-500 text-sm">
              No completed tasks yet. Check one off!
            </p>
          </div>
        </div>

      </aside>

    </div>

  </div>
</template>