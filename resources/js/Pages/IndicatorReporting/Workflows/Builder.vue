<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import BeLayout from '../../../Layouts/BeLayout.vue';

const props = defineProps({
    workflow: { type: Object, default: null },
    roles: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.workflow);

const form = useForm({
    name: props.workflow?.name ?? '',
    description: props.workflow?.description ?? '',
    is_active: props.workflow?.is_active ?? true,
    initiator_role_id: props.workflow?.initiator_role_id ?? null,
    resubmit_behavior: props.workflow?.resubmit_behavior ?? 'from_start',
    stages: props.workflow?.stages?.map((s) => ({
        name: s.name, assignment_type: s.assignment_type, role_id: s.role_id, approval_mode: s.approval_mode,
    })) ?? [{ name: '', assignment_type: 'role', role_id: null, approval_mode: 'any' }],
    department_ids: props.workflow?.departments?.map((d) => d.id) ?? [],
});

const addStage = () => form.stages.push({ name: '', assignment_type: 'role', role_id: null, approval_mode: 'any' });
const removeStage = (i) => form.stages.splice(i, 1);
const moveStage = (i, dir) => {
    const j = i + dir;
    if (j < 0 || j >= form.stages.length) return;
    const s = form.stages.splice(i, 1)[0];
    form.stages.splice(j, 0, s);
};

const submit = () => {
    if (isEdit.value) form.put(route('indicator-reporting.workflows.update', props.workflow.id));
    else form.post(route('indicator-reporting.workflows.store'));
};
</script>

<template>
    <BeLayout>
        <Head :title="isEdit ? 'Edit Workflow' : 'New Workflow'" />

        <div class="row mt-4"><div class="col"><h5 class="fw-400">{{ isEdit ? 'Edit' : 'New' }} Approval Workflow</h5><hr /></div></div>

        <form @submit.prevent="submit">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input v-model="form.name" class="form-control" />
                    <small class="text-danger" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Initiator role</label>
                    <select v-model="form.initiator_role_id" class="form-select">
                        <option :value="null">Any reporter</option>
                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">On resubmit</label>
                    <select v-model="form.resubmit_behavior" class="form-select">
                        <option value="from_start">Restart from stage 1</option>
                        <option value="from_declined_stage">Resume at declined stage</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea v-model="form.description" rows="2" class="form-control"></textarea>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">Stages (in order)</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="addStage"><i class="bi bi-plus"></i> Add stage</button>
                    </div>
                    <div v-for="(stage, i) in form.stages" :key="i" class="card mt-2">
                        <div class="card-body">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-1"><span class="badge bg-secondary">#{{ i + 1 }}</span></div>
                                <div class="col-md-4">
                                    <label class="form-label">Stage name</label>
                                    <input v-model="stage.name" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Approver role</label>
                                    <select v-model="stage.role_id" class="form-select form-select-sm">
                                        <option :value="null">Select role…</option>
                                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Mode</label>
                                    <select v-model="stage.approval_mode" class="form-select form-select-sm">
                                        <option value="any">Any approver</option>
                                        <option value="all">All approvers</option>
                                    </select>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-sm btn-light" @click="moveStage(i, -1)"><i class="bi bi-arrow-up"></i></button>
                                    <button type="button" class="btn btn-sm btn-light" @click="moveStage(i, 1)"><i class="bi bi-arrow-down"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" @click="removeStage(i)"><i class="bi bi-x"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Assigned departments</label>
                    <div class="row">
                        <div class="col-md-4" v-for="d in departments" :key="d.id">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" :id="'dept-' + d.id" :value="d.id" v-model="form.department_ids" />
                                <label class="form-check-label" :for="'dept-' + d.id">{{ d.name }}</label>
                            </div>
                        </div>
                    </div>
                    <small class="text-danger" v-if="form.errors.department_ids">{{ form.errors.department_ids }}</small>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="wf-active" v-model="form.is_active" />
                        <label class="form-check-label" for="wf-active">Active</label>
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ isEdit ? 'Save workflow' : 'Create workflow' }}</button>
            </div>
        </form>
    </BeLayout>
</template>
