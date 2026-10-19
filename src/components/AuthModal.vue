<script setup>
import { ref } from 'vue'

const emit = defineEmits(['auth-success'])

const isLogin = ref(true)
const name = ref('')
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const AUTH_URL = 'http://127.0.0.1:8000/api'

async function handleSubmit() {
  error.value = ''
  loading.value = true

  const endpoint = isLogin.value ? `${AUTH_URL}/login` : `${AUTH_URL}/register`
  const payload = isLogin.value
    ? { email: email.value, password: password.value }
    : { name: name.value, email: email.value, password: password.value }

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    const data = await res.json()

    if (!res.ok) {
      throw new Error(data.message || 'Authentication failed')
    }

    // Save token and user info
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    emit('auth-success', { user: data.user, token: data.token })
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-xl space-y-6">
      
      <div class="text-center space-y-1">
        <h2 class="text-xl font-bold text-white tracking-tight">
          {{ isLogin ? 'Sign In to Task Manager' : 'Create an Account' }}
        </h2>
        <p class="text-xs text-slate-400">
          {{ isLogin ? 'Access your private tasks and dashboard' : 'Start organizing your tasks with ease' }}
        </p>
      </div>

      <div v-if="error" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs">
        ⚠️ {{ error }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        
        <div v-if="!isLogin" class="space-y-1">
          <label class="text-xs font-medium text-slate-400">Full Name</label>
          <input
            v-model="name"
            type="text"
            required
            placeholder="John Doe"
            class="w-full rounded-xl bg-slate-950/60 border border-slate-800 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div class="space-y-1">
          <label class="text-xs font-medium text-slate-400">Email Address</label>
          <input
            v-model="email"
            type="email"
            required
            placeholder="user@example.com"
            class="w-full rounded-xl bg-slate-950/60 border border-slate-800 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div class="space-y-1">
          <label class="text-xs font-medium text-slate-400">Password</label>
          <input
            v-model="password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full rounded-xl bg-slate-950/60 border border-slate-800 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 py-2.5 text-sm font-semibold text-white transition-colors cursor-pointer"
        >
          {{ loading ? 'Processing...' : (isLogin ? 'Sign In' : 'Register') }}
        </button>
      </form>

      <div class="text-center pt-2 border-t border-slate-800/80">
        <button
          type="button"
          @click="isLogin = !isLogin; error = ''"
          class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors"
        >
          {{ isLogin ? "Don't have an account? Sign up" : 'Already have an account? Sign in' }}
        </button>
      </div>

    </div>
  </div>
</template>