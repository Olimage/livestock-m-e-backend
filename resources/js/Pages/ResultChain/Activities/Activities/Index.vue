<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({
    activities: Object,
    programs: Array,
    filters: Object,
    totalCount: Number,
})

const search   = ref(props.filters.search || '')
const perPage  = ref(props.filters.per_page || 15)
const sortBy   = ref(props.filters.sort_by || 'created_at')
const sortOrder = ref(props.filters.sort_order || 'desc')
const programId = ref(props.filters.program_id || '')

const searchTimeout = ref(null)

watch([search, perPage, sortBy, sortOrder, programId], () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        router.get(route('result-chain.activities.index'), {
            search: search.value,
            per_page: perPage.value,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
            program_id: programId.value,
        }, { preserveState: true, preserveScroll: true })
    }, 300)
})

const deleteActivity = (id) => {
    if (confirm('Are you sure you want to delete this activity?')) {
        router.delete(route('result-chain.activities.destroy', id), { preserveScroll: true })
    }
}

const toggleSort = (col) => {
    if (sortBy.value === col) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortBy.value = col
        sortOrder.value = 'asc'
    }
}

const getSortIcon = (col) => {
    if (sortBy.value !== col) return 'bi bi-arrow-down-up'
    return sortOrder.value === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'
}
</script>

<template>
    <BeLayout>
        <Head title="Activities" />

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4 fw-400">Activities</h5>
                <hr />
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-lg-12">
                <p class="text-muted">Activities linked to programs. Total: <strong>{{ totalCount }}</strong></p>
            </div>
        </div>

        <div class="row card mb-3">
            <div class="col-sm-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card stat-card mb-3">
                                <div class="card-header">Total Activities</div>
                                <div class="card-body">
                                    <h3 class="card-title text-white">{{ totalCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input v-model="search" type="text" class="form-control" placeholder="Search by code or title..." />
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select v-model="programId" class="form-select">
                                <option value="">All Programs</option>
                                <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.code }} - {{ p.title }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select v-model="perPage" class="form-select">
                                <option :value="10">10 per page</option>
                                <option :value="25">25 per page</option>
                                <option :value="50">50 per page</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <Link :href="route('result-chain.activities.create')" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle me-1"></i> Add Activity
                            </Link>
                        </div>
                    </div>
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
                                        <th @click="toggleSort('code')" class="sortable">Code <i :class="getSortIcon('code')"></i></th>
                                        <th @click="toggleSort('title')" class="sortable">Title <i :class="getSortIcon('title')"></i></th>
                                        <th>Program</th>
                                        <th>Output Indicators</th>
                                        <th @click="toggleSort('created_at')" class="sortable">Created <i :class="getSortIcon('created_at')"></i></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!activities?.data?.length">
                                        <td colspan="6" class="text-center text-muted py-4">No activities found</td>
                                    </tr>
                                    <tr v-for="activity in activities?.data" :key="activity.id">
                                        <td><span class="badge bg-success">{{ activity.code }}</span></td>
                                        <td><strong>{{ activity.title }}</strong></td>
                                        <td>
                                            <small v-if="activity.program">{{ activity.program.code }} — {{ activity.program.title }}</small>
                                            <span v-else class="text-muted">—</span>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ activity.output_indicators_count ?? 0 }}</span></td>
                                        <td><small>{{ new Date(activity.created_at).toLocaleDateString() }}</small></td>
                                        <td>
                                            <div class="btn-group">
                                                <Link :href="route('result-chain.activities.edit', activity.id)" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button @click="deleteActivity(activity.id)" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="activities?.data?.length" class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">Showing {{ activities.from }} to {{ activities.to }} of {{ activities.total }}</div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li v-for="link in activities.links" :key="link.label"
                                        :class="['page-item', { active: link.active, disabled: !link.url }]">
                                        <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label"></Link>
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
.fw-400 { font-weight: 400; }
.stat-card { background-color: rgb(11,109,23); color: #fff; }
.stat-card .card-header { background-color: rgba(0,0,0,0.1); color: #fff; border-bottom: none; }
.btn-success { background-color: rgb(11,109,23); border-color: rgb(11,109,23); }
.btn-success:hover { background-color: rgb(9,87,18); border-color: rgb(9,87,18); }
.bg-success { background-color: rgb(11,109,23) !important; }
.sortable { cursor: pointer; user-select: none; }
.sortable:hover { background-color: rgba(0,0,0,.04); }
.sortable i { font-size: .8rem; opacity: .6; }
</style>
