<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    tiers: Object,
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
        router.get('/programs/tiers', {
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

const deleteTier = (tierId) => {
    if (confirm('Are you sure you want to delete this tier?')) {
        router.delete(`/programs/tiers/${tierId}`, {
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
        <Head title="Tiers" />

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4 fw-400">Tiers Management</h5>
                <hr />
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-lg-12">
                <p class="text-muted">
                    Manage measurement tiers for indicators and frameworks. Total: <strong>{{ totalCount }}</strong>
                </p>
            </div>
        </div>

        <div class="row card mb-2">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-bg-success mb-3">
                                <div class="card-header">Total Tiers</div>
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
                            placeholder="Search by tier, level, or attribution..." />
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
                    <Link :href="route('programs.tiers.create')" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> Add Tier
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
                                    <th @click="toggleSort('tier')" class="sortable">
                                        Tier <i :class="getSortIcon('tier')"></i>
                                    </th>
                                    <th @click="toggleSort('level')" class="sortable">
                                        Level <i :class="getSortIcon('level')"></i>
                                    </th>
                                    <th>Measurement Frequency</th>
                                    <th>Attribution</th>
                                    <th>Usage</th>
                                    <th @click="toggleSort('created_at')" class="sortable">
                                        Created <i :class="getSortIcon('created_at')"></i>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="tiers?.data && tiers.data.length === 0">
                                    <td colspan="7" class="text-center text-muted">No tiers found</td>
                                </tr>
                                <tr v-for="tier in tiers?.data" :key="tier.id">
                                    <td><span class="badge bg-info">{{ tier.tier }}</span></td>
                                    <td><strong>{{ tier.level }}</strong></td>
                                    <td><small>{{ tier.measurement_frequency }}</small></td>
                                    <td><small>{{ tier.attribution }}</small></td>
                                    <td>
                                        <small>
                                            <span class="badge bg-secondary me-1">{{ tier.indicators_count || 0 }} indicators</span>
                                            <span class="badge bg-secondary me-1">{{ tier.sectoral_goals_count || 0 }} goals</span>
                                            <span class="badge bg-secondary me-1">{{ tier.presidential_priorities_count || 0 }} priorities</span>
                                            <span class="badge bg-secondary">{{ tier.bond_outcomes_count || 0 }} outcomes</span>
                                        </small>
                                    </td>
                                    <td><small>{{ new Date(tier.created_at).toLocaleDateString() }}</small></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <Link :href="route('programs.tiers.edit', tier.id)"
                                                class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button @click="deleteTier(tier.id)"
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
                    <div v-if="tiers?.data && tiers.data.length > 0"
                        class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ tiers.from }} to {{ tiers.to }} of {{ tiers.total }} entries
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li v-for="link in tiers.links" :key="link.label"
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
