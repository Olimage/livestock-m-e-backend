<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    indicator: Object,
    tiers: Array,
})

// collection_frequency and reporting_frequency are cast as arrays in the model;
// normalise to a comma-separated string for the text input
const toFreqString = (val) => Array.isArray(val) ? val.join(', ') : (val || '')

const form = useForm({
    code: props.indicator.code,
    title: props.indicator.title,
    description: props.indicator.description || '',
    indicator_type: props.indicator.indicator_type || 'output',
    measurement_unit: props.indicator.measurement_unit || '',
    baseline_value: props.indicator.baseline_value,
    baseline_year: props.indicator.baseline_year,
    target_value: props.indicator.target_value,
    target_year: props.indicator.target_year,
    data_source: props.indicator.data_source || '',
    collection_frequency: toFreqString(props.indicator.collection_frequency),
    reporting_frequency: toFreqString(props.indicator.reporting_frequency),
    tier_ids: props.indicator.tiers ? props.indicator.tiers.map(t => t.id) : [],
})

const submit = () => {
    form.put(route('programs.indicators.update', props.indicator.id))
}
</script>

<template>
    <BeLayout>
        <Head title="Edit Indicator" />
        <h5 class="mt-4">Edit Indicator</h5>
        <hr />
        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Code *</label>
                            <input v-model="form.code" type="text" class="form-control" :class="{ 'is-invalid': form.errors.code }" />
                            <div class="invalid-feedback">{{ form.errors.code }}</div>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label class="form-label">Title *</label>
                            <input v-model="form.title" type="text" class="form-control" :class="{ 'is-invalid': form.errors.title }" />
                            <div class="invalid-feedback">{{ form.errors.title }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-control" rows="3" />
                        <small class="text-danger">{{ form.errors.description }}</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Indicator Type *</label>
                            <select v-model="form.indicator_type" class="form-select" :class="{ 'is-invalid': form.errors.indicator_type }">
                                <option value="output">Output</option>
                                <option value="outcome">Outcome</option>
                                <option value="impact">Impact</option>
                            </select>
                            <div class="invalid-feedback">{{ form.errors.indicator_type }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Measurement Unit</label>
                            <input v-model="form.measurement_unit" type="text" class="form-control" placeholder="e.g., kg, %, units" />
                            <small class="text-danger">{{ form.errors.measurement_unit }}</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data Source</label>
                            <input v-model="form.data_source" type="text" class="form-control" placeholder="e.g., Ministry Reports" />
                            <small class="text-danger">{{ form.errors.data_source }}</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Baseline Value</label>
                            <input v-model="form.baseline_value" type="number" step="0.01" class="form-control" />
                            <small class="text-danger">{{ form.errors.baseline_value }}</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Baseline Year</label>
                            <input v-model="form.baseline_year" type="number" class="form-control" placeholder="e.g., 2024" />
                            <small class="text-danger">{{ form.errors.baseline_year }}</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Target Value</label>
                            <input v-model="form.target_value" type="number" step="0.01" class="form-control" />
                            <small class="text-danger">{{ form.errors.target_value }}</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Target Year</label>
                            <input v-model="form.target_year" type="number" class="form-control" placeholder="e.g., 2030" />
                            <small class="text-danger">{{ form.errors.target_year }}</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Collection Frequency</label>
                            <input v-model="form.collection_frequency" type="text" class="form-control" placeholder="e.g., Quarterly, Annually" />
                            <small class="text-danger">{{ form.errors.collection_frequency }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reporting Frequency</label>
                            <input v-model="form.reporting_frequency" type="text" class="form-control" placeholder="e.g., Monthly, Annual" />
                            <small class="text-danger">{{ form.errors.reporting_frequency }}</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tiers</label>
                        <select v-model="form.tier_ids" class="form-select" multiple size="4">
                            <option v-for="tier in props.tiers" :key="tier.id" :value="tier.id">
                                {{ tier.tier }} - {{ tier.level }}
                            </option>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                        <small class="text-danger d-block">{{ form.errors.tier_ids }}</small>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-check-circle"></i> Update
                        </button>
                        <Link :href="route('programs.indicators.index')" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
