<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'

const props = defineProps({
    users: Object,
    filters: Object,
    userCount: Number
})

const search = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)
const sortBy = ref(props.filters.sort_by || 'created_at')
const sortOrder = ref(props.filters.sort_order || 'desc')

const searchTimeout = ref(null)

// Helper function to safely get user initial
const getInitial = (user) => {
    const name = user?.full_name || user?.name || ''
    if (!name || typeof name !== 'string' || name.length === 0) return '?'
    return name.charAt(0).toUpperCase()
}

// Helper function to safely get user name
const getUserName = (user) => {
    return user?.full_name || user?.name || 'N/A'
}

// Helper function to format date
const formatDate = (dateString) => {
    if (!dateString) return 'N/A'

    try {
        // Handle MySQL datetime format (YYYY-MM-DD HH:MM:SS)
        // Replace space with 'T' to make it ISO 8601 compatible
        const isoDate = dateString.replace(' ', 'T')
        const date = new Date(isoDate)

        if (isNaN(date.getTime())) return 'N/A'

        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })
    } catch (e) {
        return 'N/A'
    }
}

watch([search, perPage, sortBy, sortOrder], () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        router.get('/users', {
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

const deleteUser = (userId) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${userId}`, {
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

        <Head title=" Users" />

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4 fw-400">Users Management</h5>
                <hr />
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-lg-12">
                <p class="text-muted">
                    Manage all users of the application. Total Users: <strong>{{ userCount }}</strong>
                </p>
            </div>
        </div>



        <div class="row card  mb-2">



            <div class=" col-sm-12 col-md-12 col-lg-12  ">

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-4">
                            <div class=" card text-bg-success mb-3">
                                <div class="card-header">Total Users</div>
                                <div class="card-body">
                                    <h4 class="mont-font fs-3 fw-800  mb-8 text-start">
                                        {{ userCount }}
                                    </h4>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>




                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8 mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input v-model="search" type="text" class="form-control"
                            placeholder="Search by name or email..." />
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
                    <Link :href="route('users.create')" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> Add User
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
                                <thead class="table-light">
                                    <tr>
                                        <th @click="toggleSort('role')" class="sortable">
                                            Role <i :class="getSortIcon('role')"></i>
                                        </th>
                                        <th @click="toggleSort('full_name')" class="sortable">
                                            Name <i :class="getSortIcon('full_name')"></i>
                                        </th>
                                        <th @click="toggleSort('email')" class="sortable">
                                            Department <i :class="getSortIcon('email')"></i>
                                        </th>
                                        <th @click="toggleSort('email')" class="sortable">
                                            Email <i :class="getSortIcon('email')"></i>
                                        </th>
                                        <th @click="toggleSort('created_at')" class="sortable">
                                            Created At <i :class="getSortIcon('created_at')"></i>
                                        </th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!users?.data || users.data.length === 0">
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i>
                                            <p class="text-muted mb-0">No users found</p>
                                        </td>
                                    </tr>
                                    <tr v-for="user in users?.data" :key="user.id">
                                        <td>{{ user.role }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    {{ getInitial(user) }}
                                                </div>
                                                <strong>{{ getUserName(user) }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            {{ (user.departments || []).map(d => d.name).join(' - ') || 'N/A' }}
                                        </td>
                                        <td>{{ user.email || 'N/A' }}</td>
                                        <td>{{ formatDate(user.created_at) }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <Link :href="`/users/${user.id}/edit`"
                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button @click="deleteUser(user.id)"
                                                    class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users?.data && users.data.length > 0"
                            class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} entries
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item" :class="{ disabled: !users.prev_page_url }">
                                        <Link :href="users.prev_page_url || '#'" class="page-link" preserve-state
                                            preserve-scroll>
                                        Previous
                                        </Link>
                                    </li>
                                    <li v-for="link in users?.links?.slice(1, -1) || []" :key="link.label"
                                        class="page-item" :class="{ active: link.active }">
                                        <Link :href="link.url || '#'" class="page-link" preserve-state preserve-scroll
                                            v-html="link.label" />
                                    </li>
                                    <li class="page-item" :class="{ disabled: !users.next_page_url }">
                                        <Link :href="users.next_page_url || '#'" class="page-link" preserve-state
                                            preserve-scroll>
                                        Next
                                        </Link>
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

.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background-color: rgb(11, 109, 23);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
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