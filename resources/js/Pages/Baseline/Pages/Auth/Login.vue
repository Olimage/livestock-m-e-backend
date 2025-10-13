<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'

// Reactive form
const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <Head title="Login" />
    
    <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
      <div class="text-center mb-3">
        <!-- <img src="/images/logo.png" alt="Logo" class="mb-2" style="height: 60px;"> -->
        <h4 class="fw-bold">Welcome Back</h4>
        <p class="text-muted small">Sign in to continue</p>
      </div>

      <form @submit.prevent="submit">
        <!-- Email -->
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="form-control"
            :class="{ 'is-invalid': form.errors.email }"
            placeholder="Enter your email"
            required
          >
          <div class="invalid-feedback">{{ form.errors.email }}</div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="form-control"
            :class="{ 'is-invalid': form.errors.password }"
            placeholder="Enter your password"
            required
          >
          <div class="invalid-feedback">{{ form.errors.password }}</div>
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-3">
          <input
            v-model="form.remember"
            type="checkbox"
            id="remember"
            class="form-check-input"
          >
          <label class="form-check-label" for="remember">Remember Me</label>
        </div>

        <!-- Submit -->
        <div class="d-grid mb-3">
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="form.processing"
          >
            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
            Login
          </button>
        </div>

        <div class="text-center">
          <Link href="/register" class="text-decoration-none small">
            Don’t have an account? Register
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>
