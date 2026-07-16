<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import BeLayout from '../../../Layouts/BeLayout.vue';

defineProps({ reports: { type: Array, default: () => [] } });

const declineFor = ref(null);
const reason = ref('');

const approve = (uuid) => {
    if (confirm('Approve this report?')) {
        router.post(route('indicator-reporting.review.approve', uuid), {}, { preserveScroll: true });
    }
};

const openDecline = (uuid) => { declineFor.value = uuid; reason.value = ''; };

const submitDecline = () => {
    router.post(route('indicator-reporting.review.decline', declineFor.value), { reason: reason.value }, {
        preserveScroll: true,
        onSuccess: () => { declineFor.value = null; reason.value = ''; },
    });
};
</script>

<template>
    <BeLayout>
        <Head title="Review Queue" />

        <div class="row mt-4"><div class="col"><h5 class="fw-400">Review Queue</h5><hr /></div></div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr><th>Indicator</th><th>Period</th><th>Department</th><th>Stage</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="reports.length === 0"><td colspan="5" class="text-center text-muted py-4">Nothing awaiting your review</td></tr>
                            <tr v-for="r in reports" :key="r.uuid">
                                <td>{{ r.indicator_code }}</td>
                                <td>{{ r.period?.name ?? '—' }}</td>
                                <td>{{ r.department?.name ?? '—' }}</td>
                                <td>{{ r.current_stage?.name ?? '—' }}</td>
                                <td class="text-end">
                                    <Link :href="route('indicator-reporting.reports.show', r.uuid)" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></Link>
                                    <button class="btn btn-sm btn-success me-1" @click="approve(r.uuid)"><i class="bi bi-check-lg"></i></button>
                                    <button class="btn btn-sm btn-danger" @click="openDecline(r.uuid)"><i class="bi bi-x-lg"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="declineFor" class="modal d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Decline report</h5>
                        <button type="button" class="btn-close" @click="declineFor = null"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Reason (required)</label>
                        <textarea v-model="reason" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="declineFor = null">Cancel</button>
                        <button class="btn btn-danger" :disabled="reason.trim().length < 3" @click="submitDecline">Decline</button>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.modal { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,.5); z-index: 1050; }
</style>
