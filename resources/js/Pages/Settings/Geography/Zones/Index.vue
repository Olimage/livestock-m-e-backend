<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({
    zones: Object,
    filters: Object,
    totalCount: Number,
})

const search = ref(props.filters?.search || '')
let t = null
watch(search, (v) => {
    clearTimeout(t)
    t = setTimeout(() => router.get('/settings/zones', { search: v }, { preserveState: true, preserveScroll: true }), 300)
})

const del = (id) => {
    if (confirm('Delete this zone?')) {
        router.delete(`/settings/zones/${id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <BeLayout>
        <Head title="Zones" />
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h5 class="mb-0">Zones <span class="badge bg-secondary">{{ totalCount }}</span></h5>
            <Link :href="route('zones.create')" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> New Zone</Link>
        </div>
        <hr />
        <div class="card">
            <div class="card-body">
                <input v-model="search" type="text" class="form-control mb-3" placeholder="Search zones..." />
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th>Name</th><th>Code</th><th>States</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="!zones?.data?.length"><td colspan="4" class="text-center text-muted py-4">No zones found</td></tr>
                            <tr v-for="z in zones?.data" :key="z.id">
                                <td>{{ z.name }}</td>
                                <td><code class="small">{{ z.code || '—' }}</code></td>
                                <td><span class="badge bg-secondary">{{ z.states_count }}</span></td>
                                <td>
                                    <div class="btn-group">
                                        <Link :href="route('zones.edit', z.id)" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></Link>
                                        <button @click="del(z.id)" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
