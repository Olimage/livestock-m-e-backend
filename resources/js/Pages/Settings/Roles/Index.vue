<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    roles: Object,
    filters: Object,
    totalCount: Number,
})

const search = ref(props.filters?.search || '')
let t = null
watch(search, (v) => {
    clearTimeout(t)
    t = setTimeout(() => {
        router.get('/settings/roles', { search: v }, { preserveState: true, preserveScroll: true })
    }, 300)
})

const del = (id) => {
    if (confirm('Delete this role?')) {
        router.delete(`/settings/roles/${id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <BeLayout>
        <Head title="Roles" />
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h5 class="mb-0">Roles <span class="badge bg-secondary">{{ totalCount }}</span></h5>
            <Link :href="route('roles.create')" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> New Role
            </Link>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <input v-model="search" type="text" class="form-control mb-3" placeholder="Search roles..." />
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Permissions</th>
                                <th>Users</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!roles?.data?.length">
                                <td colspan="5" class="text-center text-muted py-4">No roles found</td>
                            </tr>
                            <tr v-for="role in roles?.data" :key="role.id">
                                <td>{{ role.name }}</td>
                                <td><code class="small">{{ role.slug }}</code></td>
                                <td><span class="badge bg-secondary">{{ role.permissions_count }}</span></td>
                                <td><span class="badge bg-secondary">{{ role.users_count }}</span></td>
                                <td>
                                    <div class="btn-group">
                                        <Link :href="route('roles.edit', role.id)" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button @click="del(role.id)" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
