<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    metrics: Object,
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
        router.get('/programs/cross-cutting-metrics', {
            search: search.value,
            per_page: perPage.value,
            sort_by: sortBy.value,
            sort_order: sortOrder.value
        }, {
            preserveState: true,
            preserveScroll: true
        })
    }, 300)
})

const deleteMetric = (metricId) => {
    if (confirm('Are you sure you want to delete this cross-cutting metric?')) {
        router.delete(`/programs/cross-cutting-metrics/${metricId}`, {
            preserveScroll: true
        })
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

</script>

<template>
    <BeLayout>
        <Head title="Cross-Cutting Metrics" />

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-1">Cross-Cutting Metrics</h2>
                    <p class="text-muted mb-0">Manage transversal metrics across strategic frameworks</p>
                </div>
                <Link :href="'/programs/cross-cutting-metrics/create'" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Metric
                </Link>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                placeholder="Search by code, area, or key metric..."
                            />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Per Page</label>
                            <select v-model="perPage" class="form-select">
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Count</label>
                            <div class="form-control bg-light">{{ totalCount }} metrics</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th @click="toggleSort('code')" style="cursor: pointer;">
                                        Code
                                        <i v-if="sortBy === 'code'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    </th>
                                    <th @click="toggleSort('area')" style="cursor: pointer;">
                                        Area
                                        <i v-if="sortBy === 'area'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    </th>
                                    <th>Key Metric</th>
                                    <th>Purpose</th>
                                    <th @click="toggleSort('created_at')" style="cursor: pointer;">
                                        Created
                                        <i v-if="sortBy === 'created_at'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    </th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="metrics.data.length === 0">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No metrics found
                                    </td>
                                </tr>
                                <tr v-for="metric in metrics.data" :key="metric.id">
                                    <td>
                                        <span class="badge bg-success">{{ metric.code }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ metric.area }}</strong>
                                    </td>
                                    <td>{{ metric.key_metric }}</td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" :title="metric.purpose">
                                            {{ metric.purpose }}
                                        </div>
                                    </td>
                                    <td>{{ new Date(metric.created_at).toLocaleDateString() }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <Link :href="`/programs/cross-cutting-metrics/${metric.id}/edit`" class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button @click="deleteMetric(metric.id)" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav v-if="metrics.last_page > 1" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: !metrics.prev_page_url }">
                                <Link :href="metrics.prev_page_url || '#'" class="page-link">Previous</Link>
                            </li>
                            <li v-for="page in metrics.links.slice(1, -1)" :key="page.label" 
                                class="page-item" :class="{ active: page.active }">
                                <Link :href="page.url" class="page-link" v-html="page.label"></Link>
                            </li>
                            <li class="page-item" :class="{ disabled: !metrics.next_page_url }">
                                <Link :href="metrics.next_page_url || '#'" class="page-link">Next</Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
