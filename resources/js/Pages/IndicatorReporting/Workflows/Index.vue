<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import BeLayout from '../../../Layouts/BeLayout.vue';

defineProps({ workflows: { type: Array, default: () => [] } });

const remove = (wf) => {
    if (confirm(`Delete workflow "${wf.name}"?`)) {
        router.delete(route('indicator-reporting.workflows.destroy', wf.id), { preserveScroll: true });
    }
};
</script>

<template>
    <BeLayout>
        <Head title="Approval Workflows" />

        <div class="row align-items-center mt-4">
            <div class="col"><h5 class="fw-400 mb-0">Approval Workflows</h5></div>
            <div class="col-auto">
                <Link :href="route('indicator-reporting.workflows.create')" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> New Workflow</Link>
            </div>
            <div class="col-12"><hr /></div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Name</th><th>Stages</th><th>Departments</th><th>Active</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <tr v-if="workflows.length === 0"><td colspan="5" class="text-center text-muted py-4">No workflows yet</td></tr>
                        <tr v-for="wf in workflows" :key="wf.id">
                            <td>{{ wf.name }}</td>
                            <td>{{ wf.stages?.map(s => s.name).join(' → ') || '—' }}</td>
                            <td>{{ wf.departments?.map(d => d.name).join(', ') || '—' }}</td>
                            <td><span class="badge" :class="wf.is_active ? 'bg-success' : 'bg-secondary'">{{ wf.is_active ? 'Yes' : 'No' }}</span></td>
                            <td class="text-end">
                                <Link :href="route('indicator-reporting.workflows.edit', wf.id)" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil"></i></Link>
                                <button class="btn btn-sm btn-outline-danger" @click="remove(wf)"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </BeLayout>
</template>
