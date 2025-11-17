<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const submit = () => {
  form.put(route('profile.password.update'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    }
  })
}
</script>

<template>
  <BeLayout>
    <Head title="Change Password" />
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-400">Change Password</h5>
      <Link :href="route('profile.show')" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Profile
      </Link>
    </div>
    <hr />

    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-shield-lock"></i> Update Your Password</h6>
          </div>
          <div class="card-body">
            <div class="alert alert-info mb-4">
              <i class="bi bi-info-circle me-2"></i>
              Ensure your password is at least 8 characters long and contains a mix of letters, numbers, and symbols for better security.
            </div>

            <form @submit.prevent="submit">
              <div class="mb-3">
                <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                <input 
                  type="password" 
                  class="form-control" 
                  :class="{ 'is-invalid': form.errors.current_password }"
                  id="current_password" 
                  v-model="form.current_password"
                  required
                  autocomplete="current-password"
                />
                <div class="invalid-feedback" v-if="form.errors.current_password">
                  {{ form.errors.current_password }}
                </div>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                <input 
                  type="password" 
                  class="form-control" 
                  :class="{ 'is-invalid': form.errors.password }"
                  id="password" 
                  v-model="form.password"
                  required
                  minlength="8"
                  autocomplete="new-password"
                />
                <div class="form-text">Must be at least 8 characters long</div>
                <div class="invalid-feedback" v-if="form.errors.password">
                  {{ form.errors.password }}
                </div>
              </div>

              <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                <input 
                  type="password" 
                  class="form-control" 
                  :class="{ 'is-invalid': form.errors.password_confirmation }"
                  id="password_confirmation" 
                  v-model="form.password_confirmation"
                  required
                  minlength="8"
                  autocomplete="new-password"
                />
                <div class="invalid-feedback" v-if="form.errors.password_confirmation">
                  {{ form.errors.password_confirmation }}
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                  <i class="bi bi-check-circle me-1"></i>
                  <span v-if="!form.processing">Update Password</span>
                  <span v-else>Updating...</span>
                </button>
                <Link :href="route('profile.show')" class="btn btn-outline-secondary">
                  Cancel
                </Link>
              </div>
            </form>
          </div>
        </div>

        <!-- Password Tips -->
        <div class="card mt-3">
          <div class="card-body">
            <h6 class="card-title"><i class="bi bi-lightbulb text-warning me-2"></i>Password Tips</h6>
            <ul class="mb-0 small">
              <li>Use at least 8 characters</li>
              <li>Include uppercase and lowercase letters</li>
              <li>Add numbers and special characters</li>
              <li>Avoid common words or personal information</li>
              <li>Don't reuse passwords from other accounts</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </BeLayout>
</template>

<style scoped>
.fw-400 { font-weight: 400; }

.card-header h6 {
  font-weight: 600;
}

.form-label {
  font-weight: 500;
  font-size: 0.9rem;
}

.alert {
  font-size: 0.9rem;
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
}

ul {
  padding-left: 1.5rem;
}

ul li {
  margin-bottom: 0.5rem;
}

ul li:last-child {
  margin-bottom: 0;
}
</style>
