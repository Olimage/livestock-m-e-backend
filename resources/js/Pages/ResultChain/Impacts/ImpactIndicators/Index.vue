<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({ indicators: Object, filters: Object, totalCount: Number })

const search = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 15)
const sortBy = ref(props.filters.sort_by || 'created_at')
const sortOrder = ref(props.filters.sort_order || 'desc')
const t = ref(null)

watch([search, perPage, sortBy, sortOrder], () => {
    clearTimeout(t.value)
    t.value = setTimeout(() => {
        router.get(route('result-chain.impact-indicators.index'), {
            search: search.value, per_page: perPage.value, sort_by: sortBy.value, sort_order: sortOrder.value,
        }, { preserveState: true, preserveScroll: true })
    }, 300)
})

const del = (id) => {
    if (confirm('Delete this Impact Indicator?'))
        router.delete(route('result-chain.impact-indicators.destroy', id), { preserveScroll: true })
}

const ts = (col) => { sortBy.value === col ? sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc' : (sortBy.value = col, sortOrder.value = 'asc') }
const si = (col) => sortBy.value !== col ? 'bi bi-arrow-down-up' : (sortOrder.value === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down')
</script>

<template>
    <BeLayout>
        <Head title="Impact Indicators" />
        <h5 class="mt-4 fw-400">Impact Indicators</h5>
        <hr />
        <p class="text-muted">Prefix: <span class="badge badge-imp">IMP</span> &nbsp; Total: <strong>{{ totalCount }}</strong></p>

        <div class="row card mb-3">
            <div class="col-sm-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><div class="card stat-card mb-3"><div class="card-header">Total Impact Indicators</div><div class="card-body"><h3 class="text-white">{{ totalCount }}</h3></div></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-5 mb-2">
                            <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input v-model="search" type="text" class="form-control" placeholder="Search code or title..." /></div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select v-model="perPage" class="form-select"><option :value="10">10 / page</option><option :value="25">25 / page</option><option :value="50">50 / page</option></select>
                        </div>
                        <div class="col-md-3 ms-auto mb-2">
                            <Link :href="route('result-chain.impact-indicators.create')" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle me-1"></i> Add Impact Indicator
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr>
                            <th @click="ts('code')" class="sortable">Code <i :class="si('code')"></i></th>
                            <th @click="ts('title')" class="sortable">Title <i :class="si('title')"></i></th>
                            <th>Department</th>
                            <th>Outcome Links</th>
                            <th>Priority Links</th>
                            <th>Actions</th>
                        </tr></thead>
                        <tbody>
                            <tr v-if="!indicators?.data?.length"><td colspan="6" class="text-center text-muted py-4">No impact indicators found</td></tr>
                            <tr v-for="ind in indicators?.data" :key="ind.id">
                                <td><span class="badge badge-imp">{{ ind.code }}</span></td>
                                <td>{{ ind.title }}</td>
                                <td><small>{{ ind.department?.name ?? '—' }}</small></td>
                                <td><span class="badge bg-secondary">{{ ind.outcome_indicators_count ?? 0 }}</span></td>
                                <td><span class="badge bg-secondary">{{ ind.presidential_priorities_count ?? 0 }}</span></td>
                                <td>
                                    <div class="btn-group">
                                        <Link :href="route('result-chain.impact-indicators.edit', ind.id)" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></Link>
                                        <button @click="del(ind.id)" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="indicators?.data?.length" class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">Showing {{ indicators.from }}–{{ indicators.to }} of {{ indicators.total }}</div>
                    <nav><ul class="pagination mb-0">
                        <li v-for="link in indicators.links" :key="link.label" :class="['page-item', { active: link.active, disabled: !link.url }]">
                            <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label"></Link>
                            <span v-else class="page-link" v-html="link.label"></span>
                        </li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.fw-400 { font-weight: 400; }
.stat-card { background-color: rgb(11,109,23); color: #fff; }
.stat-card .card-header { background-color: rgba(0,0,0,.1); color: #fff; border: none; }
.badge-imp { background-color: #b71c1c; color: #fff; }
.btn-success { background-color: rgb(11,109,23); border-color: rgb(11,109,23); }
.btn-success:hover { background-color: rgb(9,87,18); border-color: rgb(9,87,18); }
.sortable { cursor: pointer; user-select: none; }
.sortable:hover { background-color: rgba(0,0,0,.04); }
</style>
