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

</script>

<template>
    <BeLayout>
        <Head title="Tiers" />

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-1">Tiers</h2>
                    <p class="text-muted mb-0">Manage measurement tiers for indicators and frameworks</p>
                </div>
                <Link :href="'/programs/tiers/create'" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Tier
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
                                placeholder="Search by tier, level, or attribution..."
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
                            <div class="form-control bg-light">{{ totalCount }} tiers</div>
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
                                    <th @click="toggleSort('tier')" style="cursor: pointer;">
                                        Tier
                                        <i v-if="sortBy === 'tier'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    </th>
                                    <th @click="toggleSort('level')" style="cursor: pointer;">
                                        Level
                                        <i v-if="sortBy === 'level'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    </th>
                                    <th>Measurement Frequency</th>
                                    <th>Attribution</th>
                                    <th>Usage</th>
                                    <th @click="toggleSort('created_at')" style="cursor: pointer;">
                                        Created
                                        <i v-if="sortBy === 'created_at'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    </th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="tiers.data.length === 0">
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No tiers found
                                    </td>
                                </tr>
                                <tr v-for="tier in tiers.data" :key="tier.id">
                                    <td>
                                        <span class="badge bg-info">{{ tier.tier }}</span>
                                    </td>
                                    <td>{{ tier.level }}</td>
                                    <td>{{ tier.measurement_frequency }}</td>
                                    <td>{{ tier.attribution }}</td>
                                    <td>
                                        <div class="small">
                                            <span class="badge bg-secondary me-1">{{ tier.indicators_count || 0 }} indicators</span>
                                            <span class="badge bg-secondary me-1">{{ tier.sectoral_goals_count || 0 }} goals</span>
                                            <span class="badge bg-secondary me-1">{{ tier.presidential_priorities_count || 0 }} priorities</span>
                                            <span class="badge bg-secondary">{{ tier.bond_outcomes_count || 0 }} outcomes</span>
                                        </div>
                                    </td>
                                    <td>{{ new Date(tier.created_at).toLocaleDateString() }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <Link :href="`/programs/tiers/${tier.id}/edit`" class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button @click="deleteTier(tier.id)" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav v-if="tiers.last_page > 1" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: !tiers.prev_page_url }">
                                <Link :href="tiers.prev_page_url || '#'" class="page-link">Previous</Link>
                            </li>
                            <li v-for="page in tiers.links.slice(1, -1)" :key="page.label" 
                                class="page-item" :class="{ active: page.active }">
                                <Link :href="page.url" class="page-link" v-html="page.label"></Link>
                            </li>
                            <li class="page-item" :class="{ disabled: !tiers.next_page_url }">
                                <Link :href="tiers.next_page_url || '#'" class="page-link">Next</Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
