<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Logo from '../../../../../images/logo.png'

// Inertia form object
const form = useForm({
    username: '',
    password: '',
    remember: false,
})

// submit handler
const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <div class="auth-root min-vh-100 d-flex align-items-center">

        <Head title="Login" />

        <div class="container py-5">
            <div class="row g-0 shadow-sm rounded overflow-hidden auth-card">
                <!-- LEFT SIDE - Login Form -->
                <div class="col-12 col-lg-6 bg-white p-4 p-lg-5">
                    <div class="mb-4 text-center">
                        <img :src="Logo" alt="FMLD Logo" class="login-logo mb-3"  />
                        <h3 class="fw-bold mb-0">Welcome back</h3>
                        <p class="text-muted small">Sign in to access your dashboard</p>
                    </div>

                    <form @submit.prevent="submit" class="mt-3">
                        <!-- Username -->
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input v-model="form.username" type="text" class="form-control"
                                :class="{ 'is-invalid': form.errors.username }" placeholder="Enter your username"
                                required autofocus />
                            <div class="invalid-feedback">{{ form.errors.username }}</div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input v-model="form.password" type="password" class="form-control"
                                :class="{ 'is-invalid': form.errors.password }" placeholder="••••••••" required />
                            <div class="invalid-feedback">{{ form.errors.password }}</div>
                        </div>

                        <!-- Remember + Forgot -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input v-model="form.remember" class="form-check-input" type="checkbox" id="remember" />
                                <label class="form-check-label small" for="remember">Remember me</label>
                            </div>
                            <Link href="#" class="small text-decoration-none">Forgot password?</Link>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mb-3">
                            <button class="btn btn-success btn-lg" :disabled="form.processing">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" />
                                Sign in
                            </button>
                        </div>
                    </form>
                </div>

                <!-- RIGHT SIDE - Green Decorative Panel -->
                <div class="col-12 col-lg-6 d-none d-lg-block p-0">
                    <div class="decor-panel h-100 d-flex align-items-center justify-content-center text-white p-4">
                        <div class="decor-content text-center px-4">
                            <h2 class="fw-bold mb-2">FMLD</h2>
                            <p class="lead mb-4">Baseline Dashboard</p>
                            <p class="small opacity-85">
                                Secure access to enumeration data and analytics tools.
                            </p>
                        </div>

                        <!-- Background SVG -->
                        <svg class="decor-svg" viewBox="0 0 800 600" preserveAspectRatio="none"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden>
                            <path d="M0 120 C200 220 400 20 800 120 L800 600 L0 600 Z" fill="#0a4e12" opacity="0.18" />
                            <path d="M0 200 C240 120 480 320 800 200 L800 600 L0 600 Z" fill="#073a0f" opacity="0.12" />
                            <g stroke="#05320c" stroke-width="0" opacity="0.12" fill="none">
                                <path d="M0 50 L800 50" />
                                <path d="M0 150 L800 150" />
                                <path d="M0 250 L800 250" />
                                <path d="M0 350 L800 350" />
                                <path d="M0 450 L800 450" />
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

.login-logo {
  height: 90px;
  width: auto;
  display: block;
  margin: 0 auto 1rem;
}
.auth-root {
    background: #f6f8f9;
}
.bg-white {
    background-color: rgb(252 252 252) !important;
}

.auth-card {
    max-width: 1100px;
    margin: 0 auto;
    overflow: hidden;
}

.decor-panel {
    background: linear-gradient(180deg, #38bd4a 0%, #0a4e12 100%);
    position: relative;
    overflow: hidden;
    min-height: 420px;
}

.decor-svg {
    position: absolute;
    right: -10%;
    top: 0;
    width: 120%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}

.decor-content {
    position: relative;
    z-index: 2;
    max-width: 380px;
}

@media (max-width: 991.98px) {
    .decor-panel {
        display: none;
    }
}

.btn-success {
    background-color: #0fa64a;
    border-color: #0fa64a;
}

.btn-success:hover {
    background-color: #0da245;
    border-color: #0da245;
}
</style>
