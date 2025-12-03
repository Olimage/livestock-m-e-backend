<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    programs: Object,
    filters: Object,
    pillars: Array,
    totalCount: Number
})

const search = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)
const sortBy = ref(props.filters.sort_by || 'created_at')
const sortOrder = ref(props.filters.sort_order || 'desc')
const pillarId = ref(props.filters.pillar_id || '')

const searchTimeout = ref(null)

watch([search, perPage, sortBy, sortOrder, pillarId], () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        router.get('/programs/programs', {
            search: search.value,
            per_page: perPage.value,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
            pillar_id: pillarId.value
        }, {
            preserveState: true,
            preserveScroll: true
        })
    }, 300)
})

const deleteProgram = (programId) => {
    if (confirm('Are you sure you want to delete this program?')) {
        router.delete(`/programs/programs/${programId}`, {
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
        <Head title="Programs" />

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-1">Programs</h2>
                    <p class="text-muted mb-0">Manage programs under NLGAS pillars</p>
                </div>
                <Link :href="'/programs/programs/create'" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Program
                </Link>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                class="form-control"
                                placeholder="Search by code or title..."
                            />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filter by Pillar</label>
                            <select v-model="pillarId" class="form-select">
                                <option value="">All Pillars</option>
                                <option v-for="pillar in pillars" :key="pillar.id" :value="pillar.id">
                                    {{ pillar.code }} - {{ pillar.title }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
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
                            <div class="form-control bg-light">{{ totalCount }} programs</div>
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
                                    <th @click="toggleSort('title')" style="cursor: pointer;">
                                        Title
                                        <i v-if="sortBy === 'title'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    </th>
                                    <th>NLGAS Pillar</th>
                                    <th @click="toggleSort('created_at')" style="cursor: pointer;">
                                        Created
                                        <i v-if="sortBy === 'created_at'" :class="sortOrder === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                    </th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="programs.data.length === 0">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No programs found
                                    </td>
                                </tr>
                                <tr v-for="program in programs.data" :key="program.id">
                                    <td>
                                        <span class="badge bg-primary">{{ program.code }}</span>
                                    </td>
                                    <td>{{ program.title }}</td>
                                    <td>
                                        <span v-if="program.nlgas_pillar" class="text-muted">
                                            {{ program.nlgas_pillar.code }} - {{ program.nlgas_pillar.title }}
                                        </span>
                                    </td>
                                    <td>{{ new Date(program.created_at).toLocaleDateString() }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <Link :href="`/programs/programs/${program.id}/edit`" class="btn btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button @click="deleteProgram(program.id)" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav v-if="programs.last_page > 1" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: !programs.prev_page_url }">
                                <Link :href="programs.prev_page_url || '#'" class="page-link">Previous</Link>
                            </li>
                            <li v-for="page in programs.links.slice(1, -1)" :key="page.label" 
                                class="page-item" :class="{ active: page.active }">
                                <Link :href="page.url" class="page-link" v-html="page.label"></Link>
                            </li>
                            <li class="page-item" :class="{ disabled: !programs.next_page_url }">
                                <Link :href="programs.next_page_url || '#'" class="page-link">Next</Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
