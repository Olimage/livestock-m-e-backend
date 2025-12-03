<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
  indicators: Object,
  filters: Object,
  totalCount: Number
})

const search = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)
const sortBy = ref(props.filters.sort_by || 'created_at')
const sortOrder = ref(props.filters.sort_order || 'desc')
const searchTimeout = ref(null)

watch([search, perPage, sortBy, sortOrder], () => {
  clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    router.get(route('programs.indicators.index'), {
      search: search.value,
      per_page: perPage.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value
    }, { preserveState: true, preserveScroll: true })
  }, 300)
})

const deleteIndicator = (id) => {
  if (confirm('Delete this indicator?')) {
    router.delete(route('programs.indicators.destroy', id), { preserveScroll: true })
  }
}

const toggleSort = (column) => {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = column
    sortOrder.value = 'asc'
  }
}

const sortIcon = (column) => {
  if (sortBy.value !== column) return 'bi bi-arrow-down-up'
  return sortOrder.value === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'
}
</script>

<template>
  <BeLayout>
    <Head title="Indicators" />
    <h5 class="mt-4 fw-400">Indicators Management</h5>
    <hr />
    <p class="text-muted">Manage indicators. Total: <strong>{{ totalCount }}</strong></p>

    <div class="card mb-3 p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input v-model="search" type="text" class="form-control" placeholder="Search code or title" />
          </div>
        </div>
        <div class="col-md-2">
          <select v-model="perPage" class="form-select">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="col-md-2">
          <Link :href="route('programs.indicators.create')" class="btn btn-success w-100">
            <i class="bi bi-plus-circle"></i> New Indicator
          </Link>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th @click="toggleSort('code')" class="sortable">Code <i :class="sortIcon('code')"></i></th>
                <th @click="toggleSort('title')" class="sortable">Title <i :class="sortIcon('title')"></i></th>
                <th>Program</th>
                <th>Goal</th>
                <th>Outcome</th>
                <th>Pillar</th>
                <th>Dept</th>
                <th>Type</th>
                <th>Target</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!indicators?.data || indicators.data.length === 0">
                <td colspan="10" class="text-center text-muted">No indicators found</td>
              </tr>
              <tr v-for="i in indicators?.data" :key="i.id">
                <td><span class="badge bg-success">{{ i.code }}</span></td>
                <td><strong>{{ i.title }}</strong></td>
                <td><small>{{ i.program?.code || '—' }}</small></td>
                <td><small>{{ i.sectoral_goal?.code || '—' }}</small></td>
                <td><small>{{ i.bond_outcome?.code || '—' }}</small></td>
                <td><small>{{ i.nlgas_pillar?.code || '—' }}</small></td>
                <td><small>{{ i.department?.name || '—' }}</small></td>
                <td><span class="badge bg-info" v-if="i.indicator_type">{{ i.indicator_type }}</span></td>
                <td><small>{{ i.target_value || '—' }} {{ i.measurement_unit || '' }}</small></td>
                <td>
                  <div class="btn-group">
                    <Link :href="route('programs.indicators.edit', i.id)" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-pencil"></i>
                    </Link>
                    <button @click="deleteIndicator(i.id)" class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="indicators?.data && indicators.data.length > 0" class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small">Showing {{ indicators.from }} to {{ indicators.to }} of {{ indicators.total }}</div>
          <nav>
            <ul class="pagination mb-0">
              <li v-for="link in indicators.links" :key="link.label" :class="['page-item', { active: link.active, disabled: !link.url }]">
                <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label"></Link>
                <span v-else class="page-link" v-html="link.label"></span>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </BeLayout>
</template>

<style scoped>
.sortable { cursor: pointer; user-select: none; }
.fw-400 { font-weight: 400; }
</style>
