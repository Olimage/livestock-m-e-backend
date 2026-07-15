<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({
    states: Object,
    zones: Array,
    filters: Object,
    totalCount: Number,
})

const search = ref(props.filters?.search || '')
const zoneId = ref(props.filters?.zone_id || '')
let t = null
const go = () => router.get('/settings/states', { search: search.value, zone_id: zoneId.value }, { preserveState: true, preserveScroll: true })
watch(search, () => { clearTimeout(t); t = setTimeout(go, 300) })
watch(zoneId, go)

const del = (id) => {
    if (confirm('Delete this state?')) {
        router.delete(`/settings/states/${id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <BeLayout>
        <Head title="States" />
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h5 class="mb-0">States <span class="badge bg-secondary">{{ totalCount }}</span></h5>
            <Link :href="route('states.create')" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> New State</Link>
        </div>
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-8"><input v-model="search" type="text" class="form-control" placeholder="Search states..." /></div>
                    <div class="col-md-4">
                        <select v-model="zoneId" class="form-select">
                            <option value="">All Zones</option>
                            <option v-for="z in zones" :key="z.id" :value="z.id">{{ z.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th>Name</th><th>Zone</th><th>LGAs</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="!states?.data?.length"><td colspan="4" class="text-center text-muted py-4">No states found</td></tr>
                            <tr v-for="s in states?.data" :key="s.id">
                                <td>{{ s.name }}</td>
                                <td><small>{{ s.zone?.name || '—' }}</small></td>
                                <td><span class="badge bg-secondary">{{ s.lgas_count }}</span></td>
                                <td>
                                    <div class="btn-group">
                                        <Link :href="route('states.edit', s.id)" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></Link>
                                        <button @click="del(s.id)" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="states?.data?.length && states.links" class="d-flex justify-content-end">
                    <nav><ul class="pagination mb-0">
                        <li v-for="link in states.links" :key="link.label" :class="['page-item', { active: link.active, disabled: !link.url }]">
                            <Link v-if="link.url" :href="link.url" class="page-link" preserve-scroll v-html="link.label"></Link>
                            <span v-else class="page-link" v-html="link.label"></span>
                        </li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
