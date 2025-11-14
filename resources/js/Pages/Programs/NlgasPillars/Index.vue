<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    pillars: Object,
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
        router.get('/programs/nlgas-pillars', {
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

const deletePillar = (pillarId) => {
    if (confirm('Are you sure you want to delete this NLGAS pillar?')) {
        router.delete(`/programs/nlgas-pillars/${pillarId}`, {
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

const getSortIcon = (column) => {
    if (sortBy.value !== column) return 'bi bi-arrow-down-up'
    return sortOrder.value === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'
}
</script>

<template>
    <BeLayout>
        <Head title="NLGAS Pillars" />

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4 fw-400">NLGAS Pillars Management</h5>
                <hr />
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-lg-12">
                <p class="text-muted">
                    Manage all NLGAS pillars. Total: <strong>{{ totalCount }}</strong>
                </p>
            </div>
        </div>

        <div class="row card mb-2">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-bg-secondary mb-3">
                                <div class="card-header">Total NLGAS Pillars</div>
                                <div class="card-body">
                                    <h3 class="card-title text-white">{{ totalCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8 mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input v-model="search" type="text" class="form-control"
                            placeholder="Search by code, title, or description..." />
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <select v-model="perPage" class="form-select">
                        <option :value="10">10 per page</option>
                        <option :value="25">25 per page</option>
                        <option :value="50">50 per page</option>
                        <option :value="100">100 per page</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <Link :href="route('programs.nlgas-pillars.create')" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> Add Pillar
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
                                        <th @click="toggleSort('code')" class="sortable">
                                            Code <i :class="getSortIcon('code')"></i>
                                        </th>
                                        <th @click="toggleSort('title')" class="sortable">
                                            Title <i :class="getSortIcon('title')"></i>
                                        </th>
                                        <th>Description</th>
                                        <th>Responsible Entity</th>
                                        <th>Relationships</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="pillars?.data && pillars.data.length === 0">
                                        <td colspan="6" class="text-center text-muted">No pillars found</td>
                                    </tr>
                                    <tr v-for="pillar in pillars?.data" :key="pillar.id">
                                        <td><span class="badge bg-secondary">{{ pillar.code }}</span></td>
                                        <td><strong>{{ pillar.title }}</strong></td>
                                        <td>
                                            <small>{{ pillar.description ? pillar.description.substring(0, 60) + '...' : 'N/A' }}</small>
                                        </td>
                                        <td><small>{{ pillar.responsible_entity || 'N/A' }}</small></td>
                                        <td>
                                            <small>
                                                <span class="badge bg-primary me-1">{{ pillar.presidential_priorities?.length || 0 }} Priorities</span>
                                                <span class="badge bg-info me-1">{{ pillar.sectoral_goals?.length || 0 }} Goals</span>
                                                <span class="badge bg-warning">{{ pillar.bond_outcomes?.length || 0 }} Outcomes</span>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <Link :href="route('programs.nlgas-pillars.edit', pillar.id)"
                                                    class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button @click="deletePillar(pillar.id)"
                                                    class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="pillars?.data && pillars.data.length > 0"
                            class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ pillars.from }} to {{ pillars.to }} of {{ pillars.total }} entries
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li v-for="link in pillars.links" :key="link.label"
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
