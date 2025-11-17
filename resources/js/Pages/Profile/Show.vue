<script setup>
import { Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'

const props = defineProps({
  user: Object
})

const formatDate = (dt) => {
  if (!dt) return 'N/A'
  const d = new Date(dt)
  const pad = (n) => String(n).padStart(2, '0')
  const day = pad(d.getDate())
  const month = pad(d.getMonth() + 1)
  const year = d.getFullYear()
  let hours = d.getHours()
  const mins = pad(d.getMinutes())
  const ampm = hours >= 12 ? 'pm' : 'am'
  hours = hours % 12 || 12
  return `${day}-${month}-${year} - ${hours}:${mins} ${ampm}`
}

const getRoleBadgeClass = (role) => {
  const classes = {
    'super_admin': 'bg-danger',
    'admin': 'bg-primary',
    'supervisor': 'bg-info',
    'enumerator': 'bg-success'
  }
  return classes[role] || 'bg-secondary'
}

const getRoleLabel = (role) => {
  const labels = {
    'super_admin': 'Super Admin',
    'admin': 'Admin',
    'supervisor': 'Supervisor',
    'enumerator': 'Enumerator'
  }
  return labels[role] || role
}
</script>

<template>
  <BeLayout>
    <Head title="My Profile" />
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-400">My Profile</h5>
      <Link :href="route('profile.password.edit')" class="btn btn-sm btn-primary">
        <i class="bi bi-key"></i> Change Password
      </Link>
    </div>
    <hr />

    <!-- Profile Information -->
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-3">
          <div class="card-body text-center">
            <div class="profile-avatar mb-3">
              <i class="bi bi-person-circle display-1 text-primary"></i>
            </div>
            <h5 class="card-title mb-1">{{ user.full_name }}</h5>
            <p class="text-muted mb-2">{{ user.email }}</p>
            <span class="badge" :class="getRoleBadgeClass(user.role)">{{ getRoleLabel(user.role) }}</span>
          </div>
        </div>

        <div class="card">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Account Information</h6>
          </div>
          <div class="card-body">
            <div class="info-item mb-3">
              <label>Member Since</label>
              <div>{{ formatDate(user.created_at) }}</div>
            </div>
            <div class="info-item">
              <label>Last Updated</label>
              <div>{{ formatDate(user.updated_at) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-person"></i> Personal Information</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="info-item">
                  <label>Full Name</label>
                  <div>{{ user.full_name }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>Email Address</label>
                  <div>{{ user.email }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>User Role</label>
                  <div><span class="badge" :class="getRoleBadgeClass(user.role)">{{ getRoleLabel(user.role) }}</span></div>
                </div>
              </div>
              <div class="col-md-6" v-if="user.phone">
                <div class="info-item">
                  <label>Phone Number</label>
                  <div>{{ user.phone }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card" v-if="user.departments && user.departments.length > 0">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-building"></i> Departments</h6>
          </div>
          <div class="card-body">
            <div class="list-group list-group-flush">
              <div class="list-group-item px-0" v-for="dept in user.departments" :key="dept.id">
                <i class="bi bi-folder me-2 text-primary"></i>{{ dept.name }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BeLayout>
</template>

<style scoped>
.fw-400 { font-weight: 400; }

.profile-avatar {
  margin: 1rem 0;
}

.info-item {
  padding: 8px 0;
}

.info-item label {
  font-size: 0.85rem;
  color: #6c757d;
  margin-bottom: 4px;
  display: block;
  font-weight: 500;
}

.info-item > div {
  font-size: 0.95rem;
  color: #212529;
  font-weight: 400;
}

.card-header h6 {
  font-weight: 600;
}

.list-group-item {
  border: none;
  padding-left: 0;
  padding-right: 0;
}

.badge {
  font-size: 0.85rem;
  padding: 0.4em 0.8em;
}
</style>
