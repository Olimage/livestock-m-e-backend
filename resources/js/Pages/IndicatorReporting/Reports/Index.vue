<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import BeLayout from '../../../Layouts/BeLayout.vue';

const props = defineProps({
    reports: { type: Object, required: true },
    filters: { type: Object, default: () => ({ status: null }) },
    can: { type: Object, default: () => ({ report: false, viewAll: false }) },
});

const status = ref(props.filters.status ?? '');

const applyFilter = () => {
    router.get(route('indicator-reporting.reports.index'), { status: status.value || undefined }, {
        preserveState: true, replace: true,
    });
};

const badgeClass = (s) => ({
    draft: 'bg-secondary', pending: 'bg-warning text-dark',
    returned: 'bg-danger', approved: 'bg-success',
}[s] ?? 'bg-secondary');
</script>

<template>
    <BeLayout>
        <Head title="Indicator Reports" />

        <div class="row align-items-center mt-4">
            <div class="col">
                <h5 class="fw-400 mb-0">{{ can.viewAll ? 'All Indicator Reports' : 'My Indicator Reports' }}</h5>
            </div>
            <div class="col-auto" v-if="can.report">
                <Link :href="route('indicator-reporting.reports.create')" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> New Report
                </Link>
            </div>
            <div class="col-12"><hr /></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <select v-model="status" class="form-select form-select-sm" @change="applyFilter">
                    <option value="">All statuses</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending</option>
                    <option value="returned">Returned</option>
                    <option value="approved">Approved</option>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Indicator</th><th>Period</th><th>Department</th>
                                <th>Stage</th><th>Status</th><th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="reports.data.length === 0">
                                <td colspan="6" class="text-center text-muted py-4">No reports yet</td>
                            </tr>
                            <tr v-for="r in reports.data" :key="r.uuid">
                                <td>{{ r.indicator_code }}</td>
                                <td>{{ r.period?.name ?? '—' }}</td>
                                <td>{{ r.department?.name ?? '—' }}</td>
                                <td>{{ r.current_stage?.name ?? '—' }}</td>
                                <td><span class="badge" :class="badgeClass(r.status)">{{ r.status }}</span></td>
                                <td class="text-end">
                                    <Link :href="route('indicator-reporting.reports.show', r.uuid)" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
