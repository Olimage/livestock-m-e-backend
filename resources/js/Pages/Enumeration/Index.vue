<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'

const props = defineProps({
  records: Object,
  filters: Object,
  formTypes: Array,
  syncStatuses: Array
})

const search = ref(props.filters.search || '')
const formType = ref(props.filters.form_type || '')
const syncStatus = ref(props.filters.sync_status || '')
const perPage = ref(props.filters.per_page || 15)

let timeout = null
watch([search, formType, syncStatus, perPage], () => {
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    router.get(route('enumerations.index'), {
      search: search.value,
      form_type: formType.value,
      sync_status: syncStatus.value,
      per_page: perPage.value
    }, { preserveState: true, preserveScroll: true })
  }, 300)
})

const syncBadgeClass = (status) => {
  switch (status) {
    case 'synced': return 'bg-success'
    case 'failed': return 'bg-danger'
    default: return 'bg-warning text-dark'
  }
}

const formatDate = (dt) => {
  if (!dt) return ''
  const d = new Date(dt)
  const pad = n => n < 10 ? '0' + n : n
  let hours = d.getHours()
  const minutes = pad(d.getMinutes())
  const ampm = hours >= 12 ? 'pm' : 'am'
  hours = hours % 12
  hours = hours ? hours : 12
  return `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${d.getFullYear()} - ${pad(hours)}:${minutes} ${ampm}`
}
</script>

<template>
  <BeLayout>
    <Head title="Enumeration Records" />
    <div class="row mb-3">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <h5 class="mt-4 fw-400">Enumeration Records</h5>
        <div class="btn-group">
          <Link v-for="type in formTypes" :key="type" :href="route('enumerations.create', type)" class="btn btn-sm btn-success">
            <i class="bi bi-plus-circle"></i> New {{ type.replace('_',' ') }}
          </Link>
          <div class="btn-group ms-2">
            <Link :href="route('enumerations.export', { format: 'csv', form_type: formType, sync_status: syncStatus, search: search })" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-download"></i> CSV
            </Link>
            <Link :href="route('enumerations.export', { format: 'json', form_type: formType, sync_status: syncStatus, search: search })" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-download"></i> JSON
            </Link>
          </div>
        </div>
      </div>
    </div>
    <hr />

    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-3">
            <input v-model="search" type="text" class="form-control" placeholder="Search enumerator / type" />
          </div>
          <div class="col-md-2">
            <select v-model="formType" class="form-select">
              <option value="">All Types</option>
              <option v-for="t in formTypes" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <select v-model="syncStatus" class="form-select">
              <option value="">All Status</option>
              <option v-for="s in syncStatuses" :key="s" :value="s">{{ s }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <select v-model="perPage" class="form-select">
              <option :value="15">15</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Form Type</th>
              <th>Enumerator</th>
              <th>Sync Status</th>
              <th>Submitted</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="records.data.length === 0">
              <td colspan="6" class="text-center text-muted">No records found</td>
            </tr>
            <tr v-for="r in records.data" :key="r.id">
              <td>{{ r.id }}</td>
              <td><span class="badge bg-primary">{{ r.form_type }}</span></td>
              <td>{{ r.enumerator ? r.enumerator.full_name : r.enumerator_name }}</td>
              <td><span class="badge" :class="syncBadgeClass(r.sync_status)">{{ r.sync_status }}</span></td>
              <td><small>{{ formatDate(r.created_at) }}</small></td>
              <td>
                <Link :href="route('enumerations.show', r.id)" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center" v-if="records.data.length">
          <div class="small text-muted">Showing {{ records.from }} to {{ records.to }} of {{ records.total }}</div>
          <ul class="pagination mb-0">
            <li v-for="link in records.links" :key="link.label" :class="['page-item', { active: link.active, disabled: !link.url }]">
              <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label" />
              <span v-else class="page-link" v-html="link.label" />
            </li>
          </ul>
        </div>
      </div>
    </div>
  </BeLayout>
</template>

<style scoped>
.fw-400 { font-weight: 400; }
</style>
