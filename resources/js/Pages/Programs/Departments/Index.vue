<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    departments: Object,
    filters: Object,
    totalCount: Number
})

const search    = ref(props.filters.search || '')
const perPage   = ref(props.filters.per_page || 15)
const sortBy    = ref(props.filters.sort_by || 'name')
const sortOrder = ref(props.filters.sort_order || 'asc')
const type      = ref(props.filters.type || '')
const agency    = ref(props.filters.agency || '')

const searchTimeout = ref(null)

watch([search, perPage, sortBy, sortOrder, type, agency], () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        router.get(route('programs.departments.index'), {
            search:     search.value,
            per_page:   perPage.value,
            sort_by:    sortBy.value,
            sort_order: sortOrder.value,
            type:       type.value,
            agency:     agency.value,
        }, { preserveState: true, preserveScroll: true })
    }, 300)
})

const deleteDepartment = (id) => {
    if (confirm('Delete this department?')) {
        router.delete(route('programs.departments.destroy', id), { preserveScroll: true })
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
        <Head title="Departments" />

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4 fw-400">Departments</h5>
                <hr />
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-lg-12">
                <p class="text-muted">
                    Manage departments and their indicator assignments. Total: <strong>{{ totalCount }}</strong>
                </p>
            </div>
        </div>

        <div class="row card mb-2">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-bg-success mb-3">
                                <div class="card-header">Total Departments</div>
                                <div class="card-body">
                                    <h3 class="card-title text-white">{{ totalCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input v-model="search" type="text" class="form-control"
                            placeholder="Search by department name..." />
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <select v-model="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="technical">Technical</option>
                        <option value="non-technical">Non-Technical</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select v-model="agency" class="form-select">
                        <option value="">Agency: All</option>
                        <option value="agency">Agency only</option>
                        <option value="non-agency">Non-Agency</option>
                    </select>
                </div>
                <div class="col-md-1 mb-2">
                    <select v-model="perPage" class="form-select">
                        <option :value="15">15</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <Link :href="route('programs.departments.create')" class="btn btn-success w-100">
                        <i class="bi bi-plus-circle"></i> Add Department
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
                                    <th @click="toggleSort('name')" class="sortable">
                                        Department Name <i :class="getSortIcon('name')"></i>
                                    </th>
                                    <th>Parent</th>
                                    <th>Type</th>
                                    <th>Agency</th>
                                    <th>Indicators</th>
                                    <th @click="toggleSort('created_at')" class="sortable">
                                        Created <i :class="getSortIcon('created_at')"></i>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="departments?.data && departments.data.length === 0">
                                    <td colspan="7" class="text-center text-muted">No departments found</td>
                                </tr>
                                <tr v-for="dept in departments?.data" :key="dept.id">
                                    <td>
                                        <strong>{{ dept.name }}</strong>
                                    </td>
                                    <td>
                                        <span v-if="dept.parent" class="text-muted small">
                                            <i class="bi bi-diagram-2 me-1"></i>{{ dept.parent.name }}
                                        </span>
                                        <span v-else class="badge bg-secondary">Root</span>
                                    </td>
                                    <td>
                                        <span v-if="dept.is_technical" class="badge bg-success">Technical</span>
                                        <span v-else class="badge bg-light text-dark border">Non-Technical</span>
                                    </td>
                                    <td>
                                        <span v-if="dept.is_agency" class="badge bg-info text-dark">
                                            <i class="bi bi-bank me-1"></i>Agency
                                        </span>
                                        <span v-else class="text-muted small">—</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ dept.indicators_count }} indicators</span>
                                    </td>
                                    <td><small>{{ new Date(dept.created_at).toLocaleDateString() }}</small></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <Link :href="route('programs.departments.show', dept.id)"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Manage indicators">
                                                <i class="bi bi-diagram-3"></i>
                                            </Link>
                                            <Link :href="route('programs.departments.edit', dept.id)"
                                                class="btn btn-sm btn-outline-success"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button @click="deleteDepartment(dept.id)"
                                                class="btn btn-sm btn-outline-danger"
                                                :disabled="dept.indicators_count > 0"
                                                :title="dept.indicators_count > 0 ? 'Cannot delete — has indicators' : 'Delete'">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="departments?.data && departments.data.length > 0"
                        class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ departments.from }} to {{ departments.to }} of {{ departments.total }} entries
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li v-for="link in departments.links" :key="link.label"
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
