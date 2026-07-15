<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    permissions: Object,
    filters: Object,
    totalCount: Number,
})

const search = ref(props.filters?.search || '')
let t = null
watch(search, (v) => {
    clearTimeout(t)
    t = setTimeout(() => {
        router.get('/settings/permissions', { search: v }, { preserveState: true, preserveScroll: true })
    }, 300)
})

const del = (id) => {
    if (confirm('Delete this permission? Any access check using its key will stop working.')) {
        router.delete(`/settings/permissions/${id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <BeLayout>
        <Head title="Permissions" />
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h5 class="mb-0">Permissions <span class="badge bg-secondary">{{ totalCount }}</span></h5>
            <Link :href="route('permissions.create')" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> New Permission
            </Link>
        </div>
        <hr />

        <div class="alert alert-warning py-2 small">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Permission keys are referenced in application code. Deleting or renaming one removes that access check.
        </div>

        <div class="card">
            <div class="card-body">
                <input v-model="search" type="text" class="form-control mb-3" placeholder="Search permissions..." />
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Label</th>
                                <th>Module</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!permissions?.data?.length">
                                <td colspan="4" class="text-center text-muted py-4">No permissions found</td>
                            </tr>
                            <tr v-for="p in permissions?.data" :key="p.id">
                                <td><code>{{ p.permission }}</code></td>
                                <td>{{ p.label || '—' }}</td>
                                <td><small>{{ p.module?.name || '—' }}</small></td>
                                <td>
                                    <div class="btn-group">
                                        <Link :href="route('permissions.edit', p.id)" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button @click="del(p.id)" class="btn btn-sm btn-outline-danger">
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
