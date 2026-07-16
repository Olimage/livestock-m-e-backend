<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import BeLayout from '../../../Layouts/BeLayout.vue';
import ApprovalTimeline from '../../../Components/ApprovalTimeline.vue';

const props = defineProps({
    report: { type: Object, required: true },
    can: { type: Object, default: () => ({ edit: false, submit: false }) },
});

const proofForm = useForm({ file: null });

const uploadProof = (e) => {
    proofForm.file = e.target.files[0];
    proofForm.post(route('indicator-reporting.reports.proofs.store', props.report.uuid), {
        preserveScroll: true, onSuccess: () => proofForm.reset(),
    });
};

const submitReport = () => {
    if (confirm('Submit this report for review?')) {
        router.post(route('indicator-reporting.reports.submit', props.report.uuid), {}, { preserveScroll: true });
    }
};

const removeProof = (proofId) => {
    router.delete(route('indicator-reporting.reports.proofs.destroy', [props.report.uuid, proofId]), { preserveScroll: true });
};

const badgeClass = (s) => ({
    draft: 'bg-secondary', pending: 'bg-warning text-dark',
    returned: 'bg-danger', approved: 'bg-success',
}[s] ?? 'bg-secondary');
</script>

<template>
    <BeLayout>
        <Head title="Report Detail" />

        <div class="row align-items-center mt-4">
            <div class="col">
                <h5 class="fw-400 mb-0">
                    {{ report.indicator_code }}
                    <span class="badge ms-2" :class="badgeClass(report.status)">{{ report.status }}</span>
                </h5>
            </div>
            <div class="col-auto">
                <Link v-if="can.edit" :href="route('indicator-reporting.reports.edit', report.uuid)" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bi bi-pencil"></i> Edit
                </Link>
                <button v-if="can.submit" class="btn btn-sm btn-primary" @click="submitReport">
                    <i class="bi bi-send"></i> Submit
                </button>
            </div>
            <div class="col-12"><hr /></div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Period</dt><dd class="col-sm-9">{{ report.period?.name ?? '—' }}</dd>
                            <dt class="col-sm-3">Department</dt><dd class="col-sm-9">{{ report.department?.name ?? '—' }}</dd>
                            <dt class="col-sm-3">Target</dt><dd class="col-sm-9">{{ report.target_value ?? '—' }}</dd>
                            <dt class="col-sm-3">Actual</dt><dd class="col-sm-9">{{ report.actual_value ?? '—' }}</dd>
                            <dt class="col-sm-3">Current stage</dt><dd class="col-sm-9">{{ report.current_stage?.name ?? '—' }}</dd>
                            <dt class="col-sm-3">Narrative</dt><dd class="col-sm-9">{{ report.narrative ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="card mb-3" v-if="report.values?.length">
                    <div class="card-header">Disaggregated values</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Breakdown</th><th class="text-end">Value</th></tr></thead>
                            <tbody>
                                <tr v-for="v in report.values" :key="v.id">
                                    <td>{{ v.disagregation_item ? `${v.disagregation_item.category?.name} — ${v.disagregation_item.name}` : 'Aggregate' }}</td>
                                    <td class="text-end">{{ v.value }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Proof files</span>
                        <label v-if="can.edit" class="btn btn-sm btn-outline-primary mb-0">
                            <i class="bi bi-upload"></i> Upload
                            <input type="file" hidden @change="uploadProof" />
                        </label>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush" v-if="report.proofs?.length">
                            <li v-for="p in report.proofs" :key="p.id" class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-paperclip"></i> {{ p.original_name }}</span>
                                <button v-if="can.edit" class="btn btn-sm btn-outline-danger" @click="removeProof(p.id)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </li>
                        </ul>
                        <p v-else class="text-muted mb-0">No proof files attached.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Approval trail</div>
                    <div class="card-body">
                        <ApprovalTimeline :approvals="report.approvals ?? []" />
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
