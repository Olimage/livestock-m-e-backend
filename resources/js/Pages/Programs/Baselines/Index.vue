<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    baselines: Object,
    indicators: Array,
    filters: Object,
    totalCount: Number
})

const search = ref(props.filters.search || '')
const indicatorId = ref(props.filters.indicator_id || '')
const perPage = ref(props.filters.per_page || 15)
const sortBy = ref(props.filters.sort_by || 'baseline_year')
const sortOrder = ref(props.filters.sort_order || 'desc')
const searchTimeout = ref(null)

watch([search, indicatorId, perPage, sortBy, sortOrder], () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        router.get('/programs/baselines', {
            search: search.value,
            indicator_id: indicatorId.value,
            per_page: perPage.value,
            sort_by: sortBy.value,
            sort_order: sortOrder.value
        }, { preserveState: true, preserveScroll: true })
    }, 300)
})

const deleteBaseline = (id) => {
    if (confirm('Delete this baseline record?')) {
        router.delete(`/programs/baselines/${id}`, { preserveScroll: true })
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

const getSortIcon = (column) => {
    if (sortBy.value !== column) return 'bi bi-arrow-down-up'
    return sortOrder.value === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'
}
</script>

<template>
    <BeLayout>
        <Head title="Indicator Baselines" />

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4 fw-400">Indicator Baselines</h5>
                <hr />
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-lg-12">
                <p class="text-muted">
                    Manage baseline, target, and actual values per indicator per year. Total: <strong>{{ totalCount }}</strong>
                </p>
            </div>
        </div>

        <div class="row card mb-2">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-bg-success mb-3">
                                <div class="card-header">Total Records</div>
                                <div class="card-body">
                                    <h3 class="card-title text-white">{{ totalCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input v-model="search" type="text" class="form-control"
                            placeholder="Search by indicator code or title..." />
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select v-model="indicatorId" class="form-select">
                        <option value="">All Indicators</option>
                        <option v-for="ind in indicators" :key="ind.id" :value="ind.id">
                            [{{ ind.code }}] {{ ind.title }}
                        </option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select v-model="perPage" class="form-select">
                        <option :value="15">15 per page</option>
                        <option :value="25">25 per page</option>
                        <option :value="50">50 per page</option>
                        <option :value="100">100 per page</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <Link :href="route('programs.baselines.create')" class="btn btn-success w-100">
                        <i class="bi bi-plus-circle"></i> Add Baseline
                    </Link>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Indicator</th>
                                        <th @click="toggleSort('baseline_year')" class="sortable">
                                            Baseline Year <i :class="getSortIcon('baseline_year')"></i>
                                        </th>
                                        <th @click="toggleSort('target_year')" class="sortable">
                                            Target Year <i :class="getSortIcon('target_year')"></i>
                                        </th>
                                        <th @click="toggleSort('baseline')" class="sortable">
                                            Baseline <i :class="getSortIcon('baseline')"></i>
                                        </th>
                                        <th @click="toggleSort('target')" class="sortable">
                                            Target <i :class="getSortIcon('target')"></i>
                                        </th>
                                        <th @click="toggleSort('actual')" class="sortable">
                                            Actual <i :class="getSortIcon('actual')"></i>
                                        </th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="baselines?.data && baselines.data.length === 0">
                                        <td colspan="7" class="text-center text-muted">No baseline records found</td>
                                    </tr>
                                    <tr v-for="row in baselines?.data" :key="row.id">
                                        <td>
                                            <span class="badge bg-success me-1">{{ row.indicator?.code }}</span>
                                            <small>{{ row.indicator?.title }}</small>
                                        </td>
                                        <td>{{ row.baseline_year ?? '—' }}</td>
                                        <td>{{ row.target_year ?? '—' }}</td>
                                        <td><strong>{{ row.baseline }}</strong></td>
                                        <td><strong>{{ row.target }}</strong></td>
                                        <td>
                                            <span :class="['badge', parseFloat(row.actual) >= parseFloat(row.target) ? 'bg-success' : 'bg-warning text-dark']">
                                                {{ row.actual }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <Link :href="route('programs.baselines.edit', row.id)"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button @click="deleteBaseline(row.id)"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="baselines?.data && baselines.data.length > 0"
                            class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ baselines.from }} to {{ baselines.to }} of {{ baselines.total }} entries
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li v-for="link in baselines.links" :key="link.label"
                                        :class="['page-item', { active: link.active, disabled: !link.url }]">
                                        <Link v-if="link.url" :href="link.url" class="page-link"
                                            v-html="link.label"></Link>
                                        <span v-else class="page-link" v-html="link.label"></span>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.fw-400 {
    font-weight: 400;
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}

.sortable {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s;
}

.sortable:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.sortable i {
    font-size: 0.8rem;
    opacity: 0.6;
}

.btn-success {
    background-color: rgb(11, 109, 23);
    border-color: rgb(11, 109, 23);
}

.btn-success:hover {
    background-color: rgb(9, 87, 18);
    border-color: rgb(9, 87, 18);
}

.btn-outline-success {
    color: rgb(11, 109, 23);
    border-color: rgb(11, 109, 23);
}

.btn-outline-success:hover {
    background-color: rgb(11, 109, 23);
    border-color: rgb(11, 109, 23);
    color: #fff;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.table-responsive {
    min-height: 300px;
}

@media (max-width: 768px) {
    .table {
        font-size: 0.875rem;
    }

    .btn-group {
        display: flex;
        flex-direction: column;
    }

    .btn-group .btn {
        margin-bottom: 2px;
    }
}
</style>
