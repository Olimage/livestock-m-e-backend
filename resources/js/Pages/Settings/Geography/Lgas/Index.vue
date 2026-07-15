<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({
    lgas: Object,
    states: Array,
    filters: Object,
    totalCount: Number,
})

const search = ref(props.filters?.search || '')
const stateId = ref(props.filters?.state_id || '')
let t = null
const go = () => router.get('/settings/lgas', { search: search.value, state_id: stateId.value }, { preserveState: true, preserveScroll: true })
watch(search, () => { clearTimeout(t); t = setTimeout(go, 300) })
watch(stateId, go)

const del = (id) => {
    if (confirm('Delete this LGA?')) {
        router.delete(`/settings/lgas/${id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <BeLayout>
        <Head title="LGAs" />
        <div class="d-flex justify-content-between align-items-center mt-4">
            <h5 class="mb-0">LGAs <span class="badge bg-secondary">{{ totalCount }}</span></h5>
            <Link :href="route('lgas.create')" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> New LGA</Link>
        </div>
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-8"><input v-model="search" type="text" class="form-control" placeholder="Search LGAs..." /></div>
                    <div class="col-md-4">
                        <select v-model="stateId" class="form-select">
                            <option value="">All States</option>
                            <option v-for="s in states" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th>Name</th><th>State</th><th>Zone</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="!lgas?.data?.length"><td colspan="4" class="text-center text-muted py-4">No LGAs found</td></tr>
                            <tr v-for="l in lgas?.data" :key="l.id">
                                <td>{{ l.name }}</td>
                                <td><small>{{ l.state?.name || '—' }}</small></td>
                                <td><small class="text-muted">{{ l.state?.zone?.name || '—' }}</small></td>
                                <td>
                                    <div class="btn-group">
                                        <Link :href="route('lgas.edit', l.id)" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></Link>
                                        <button @click="del(l.id)" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="lgas?.data?.length && lgas.links" class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted">Showing {{ lgas.from }}–{{ lgas.to }} of {{ lgas.total }}</small>
                    <nav><ul class="pagination mb-0">
                        <li v-for="link in lgas.links" :key="link.label" :class="['page-item', { active: link.active, disabled: !link.url }]">
                            <Link v-if="link.url" :href="link.url" class="page-link" preserve-scroll v-html="link.label"></Link>
                            <span v-else class="page-link" v-html="link.label"></span>
                        </li>
                    </ul></nav>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
