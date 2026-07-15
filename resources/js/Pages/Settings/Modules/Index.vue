<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    modules: Object,
    filters: Object,
    totalCount: Number,
})

const search = ref(props.filters?.search || '')
let t = null
watch(search, (v) => {
    clearTimeout(t)
    t = setTimeout(() => {
        router.get('/settings/modules', { search: v }, { preserveState: true, preserveScroll: true })
    }, 300)
})

const del = (id) => {
    if (confirm('Delete this module? Its permissions will be ungrouped (not deleted).')) {
        router.delete(`/settings/modules/${id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <BeLayout>
        <Head title="Modules" />
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h5 class="mb-0">Modules <span class="badge bg-secondary">{{ totalCount }}</span></h5>
            <Link :href="route('modules.create')" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> New Module
            </Link>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <input v-model="search" type="text" class="form-control mb-3" placeholder="Search modules..." />
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!modules?.data?.length">
                                <td colspan="5" class="text-center text-muted py-4">No modules found</td>
                            </tr>
                            <tr v-for="m in modules?.data" :key="m.id">
                                <td>{{ m.name }}</td>
                                <td><code class="small">{{ m.slug }}</code></td>
                                <td><small>{{ m.description || '—' }}</small></td>
                                <td><span class="badge bg-secondary">{{ m.permissions_count }}</span></td>
                                <td>
                                    <div class="btn-group">
                                        <Link :href="route('modules.edit', m.id)" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </Link>
                                        <button @click="del(m.id)" class="btn btn-sm btn-outline-danger">
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
