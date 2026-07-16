<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import BeLayout from '../../../Layouts/BeLayout.vue';

const props = defineProps({
    report: { type: Object, default: null },
    indicators: { type: Array, default: () => [] },
    periods: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    disaggregationItems: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.report);

const form = useForm({
    indicator_type: props.report?.indicator_type ?? '',
    indicator_id: props.report?.indicator_id ?? '',
    department_id: props.report?.department_id ?? '',
    reporting_period_id: props.report?.reporting_period_id ?? '',
    target_value: props.report?.target_value ?? '',
    actual_value: props.report?.actual_value ?? '',
    narrative: props.report?.narrative ?? '',
    values: props.report?.values?.map((v) => ({
        disagregation_item_id: v.disagregation_item_id, value: v.value,
    })) ?? [],
});

// Encode the "type|id" pair in one selector, then split into the two form fields.
const indicatorKey = computed({
    get: () => (form.indicator_type && form.indicator_id ? `${form.indicator_type}|${form.indicator_id}` : ''),
    set: (val) => {
        const [type, id] = val.split('|');
        form.indicator_type = type ?? '';
        form.indicator_id = id ? Number(id) : '';
        const chosen = props.indicators.find((i) => i.type === type && String(i.id) === String(id));
        if (chosen?.department_id) form.department_id = chosen.department_id;
    },
});

const addValueRow = () => form.values.push({ disagregation_item_id: null, value: '' });
const removeValueRow = (i) => form.values.splice(i, 1);

const submit = () => {
    if (isEdit.value) {
        form.put(route('indicator-reporting.reports.update', props.report.uuid));
    } else {
        form.post(route('indicator-reporting.reports.store'));
    }
};
</script>

<template>
    <BeLayout>
        <Head :title="isEdit ? 'Edit Report' : 'New Report'" />

        <div class="row mt-4"><div class="col"><h5 class="fw-400">{{ isEdit ? 'Edit' : 'New' }} Indicator Report</h5><hr /></div></div>

        <form @submit.prevent="submit">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Indicator</label>
                    <select v-model="indicatorKey" class="form-select" :disabled="isEdit">
                        <option value="">Select an indicator…</option>
                        <option v-for="i in indicators" :key="i.type + i.id" :value="`${i.type}|${i.id}`">
                            [{{ i.type_label }}] {{ i.code }} — {{ i.title }}
                        </option>
                    </select>
                    <small class="text-danger" v-if="form.errors.indicator_id">{{ form.errors.indicator_id }}</small>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select v-model="form.department_id" class="form-select">
                        <option value="">Select…</option>
                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                    <small class="text-danger" v-if="form.errors.department_id">{{ form.errors.department_id }}</small>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Reporting Period</label>
                    <select v-model="form.reporting_period_id" class="form-select">
                        <option value="">Select…</option>
                        <option v-for="p in periods" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <small class="text-danger" v-if="form.errors.reporting_period_id">{{ form.errors.reporting_period_id }}</small>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Target</label>
                    <input v-model="form.target_value" type="number" step="any" class="form-control" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">Actual</label>
                    <input v-model="form.actual_value" type="number" step="any" class="form-control" />
                </div>

                <div class="col-12">
                    <label class="form-label">Narrative</label>
                    <textarea v-model="form.narrative" rows="3" class="form-control"></textarea>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">Disaggregated values</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="addValueRow">
                            <i class="bi bi-plus"></i> Add row
                        </button>
                    </div>
                    <table class="table table-sm mt-2" v-if="form.values.length">
                        <thead><tr><th>Disaggregation</th><th>Value</th><th></th></tr></thead>
                        <tbody>
                            <tr v-for="(row, i) in form.values" :key="i">
                                <td>
                                    <select v-model="row.disagregation_item_id" class="form-select form-select-sm">
                                        <option :value="null">Aggregate (no breakdown)</option>
                                        <option v-for="it in disaggregationItems" :key="it.id" :value="it.id">
                                            {{ it.category?.name }} — {{ it.name }}
                                        </option>
                                    </select>
                                </td>
                                <td><input v-model="row.value" type="number" step="any" class="form-control form-control-sm" /></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" @click="removeValueRow(i)"><i class="bi bi-x"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    {{ isEdit ? 'Save changes' : 'Save draft' }}
                </button>
            </div>
        </form>
    </BeLayout>
</template>
