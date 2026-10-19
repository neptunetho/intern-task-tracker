<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import TaskForm from './components/TaskForm.vue'
import TaskItem from './components/TaskItem.vue'
import AuthModal from './components/AuthModal.vue'

const API_URL = 'http://127.0.0.1:8000/api/tasks'

const token = ref(localStorage.getItem('auth_token') || null)
const currentUser = ref(JSON.parse(localStorage.getItem('user') || 'null'))

const tasks = ref([])
const loading = ref(false)
const error = ref(null)

// Sorting State
const sortBy = ref('created_at')
const sortDirection = ref('desc')

const pendingTasks = computed(() => tasks.value.filter(t => !t.completed))
const completedTasks = computed(() => tasks.value.filter(t => t.completed))

function getAuthHeaders() {
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token.value}`
  }
}

async function fetchTasks() {
  if (!token.value) return

  loading.value = true
  error.value = null
  try {
    const url = `${API_URL}?sort=${sortBy.value}&direction=${sortDirection.value}`
    const res = await fetch(url, { headers: getAuthHeaders() })

    if (res.status === 401) {
      handleLogout()
      throw new Error('Session expired. Please log in again.')
    }

    if (!res.ok) throw new Error('Failed to load tasks')
    tasks.value = await res.json()
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

watch([sortBy, sortDirection], fetchTasks)

async function handleAddTask(taskData) {
  try {
    const res = await fetch(API_URL, {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify({
        title: taskData.title,
        description: taskData.description,
        due_date: taskData.dueDate,
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
      headers: getAuthHeaders(),
      body: JSON.stringify({ completed: !task.completed })
    })
    task.completed = !task.completed
  } catch (err) {
    error.value = 'Failed to update task'
  }
}

async function handleDeleteTask(id) {
  try {
    await fetch(`${API_URL}/${id}`, {
      method: 'DELETE',
      headers: getAuthHeaders()
    })
    tasks.value = tasks.value.filter(task => task.id !== id)
  } catch (err) {
    error.value = 'Failed to delete task'
  }
}

function handleAuthSuccess({ user, token: newToken }) {
  token.value = newToken
  currentUser.value = user
  fetchTasks()
}

async function handleLogout() {
  if (token.value) {
    try {
      await fetch('http://127.0.0.1:8000/api/logout', {
        method: 'POST',
        headers: getAuthHeaders()
      })
    } catch (_) {
      // Continue cleanup even if server call fails
    }
  }

  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')
  token.value = null
  currentUser.value = null
  tasks.value = []
}

onMounted(() => {
  if (token.value) {
    fetchTasks()
  }
})
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
    
    <!-- Top Header -->
    <header class="w-full border-b border-slate-800 bg-slate-900/80">
      <div class="max-w-7xl mx-auto px-8 py-4 flex items-center justify-between">
        <h1 class="text-xl font-bold tracking-tight text-white">Task Manager</h1>

        <!-- User Info / Logout Button -->
        <div v-if="currentUser" class="flex items-center gap-4">
          <div class="text-right hidden sm:block">
            <p class="text-xs font-semibold text-slate-200">{{ currentUser.name }}</p>
            <p class="text-[11px] text-slate-500">{{ currentUser.email }}</p>
          </div>
          <button
            @click="handleLogout"
            class="rounded-lg bg-slate-800 hover:bg-rose-500/20 text-slate-300 hover:text-rose-400 border border-slate-700 hover:border-rose-500/30 px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer"
          >
            Logout
          </button>
        </div>
      </div>
    </header>

    <!-- Show Auth Modal if not signed in -->
    <AuthModal v-if="!token" @auth-success="handleAuthSuccess" />

    <!-- Dashboard Content (Protected) -->
    <div v-else class="max-w-7xl w-full mx-auto p-8 flex flex-col gap-6 flex-1">
      
      <!-- Sorting Controls Bar -->
      <div class="flex items-center justify-between bg-slate-900/40 border border-slate-800/80 rounded-xl px-4 py-2.5">
        <span class="text-xs font-medium text-slate-400">Sort Tasks</span>
        
        <div class="flex items-center gap-3">
          <select
            v-model="sortBy"
            class="rounded-lg bg-slate-950/60 border border-slate-800 px-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-indigo-500 cursor-pointer"
          >
            <option value="created_at">Date Created</option>
            <option value="due_date">Due Date</option>
            <option value="title">Title (A-Z)</option>
          </select>

          <button
            @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
            class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 text-xs font-medium transition-colors flex items-center gap-1 cursor-pointer"
          >
            {{ sortDirection === 'asc' ? '↑ Ascending' : '↓ Descending' }}
          </button>
        </div>
      </div>

      <!-- 2 Columns Dashboard -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- LEFT COLUMN: Add Task + Pending Tasks -->
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

        <!-- RIGHT COLUMN: Completed Tasks -->
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

  </div>
</template>