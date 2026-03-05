<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    indicators: Array
})

const form = useForm({
    indicator_id: '',
    baseline_year: null,
    target_year: null,
    baseline: null,
    target: null,
    actual: null
})

const submit = () => {
    form.post('/programs/baselines')
}
</script>

<template>
    <BeLayout>
        <Head title="Add Baseline" />
        <h5 class="mt-4">Add Indicator Baseline</h5>
        <hr />
        <div class="card">
            <div class="card-header card-header-green text-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart-steps me-2"></i>Baseline Configuration</h6>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="mb-3">
                        <label class="form-label">Indicator *</label>
                        <select v-model="form.indicator_id" class="form-select"
                            :class="{ 'is-invalid': form.errors.indicator_id }" required>
                            <option value="" disabled>— select an indicator —</option>
                            <option v-for="ind in props.indicators" :key="ind.id" :value="ind.id">
                                [{{ ind.code }}] {{ ind.title }}
                            </option>
                        </select>
                        <div class="invalid-feedback">{{ form.errors.indicator_id }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Baseline Year</label>
                            <input v-model="form.baseline_year" type="number" class="form-control"
                                placeholder="e.g., 2023"
                                :class="{ 'is-invalid': form.errors.baseline_year }" />
                            <div class="invalid-feedback">{{ form.errors.baseline_year }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Year</label>
                            <input v-model="form.target_year" type="number" class="form-control"
                                placeholder="e.g., 2030"
                                :class="{ 'is-invalid': form.errors.target_year }" />
                            <div class="invalid-feedback">{{ form.errors.target_year }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Baseline Value *</label>
                            <input v-model="form.baseline" type="number" step="0.01" class="form-control"
                                placeholder="Starting value"
                                :class="{ 'is-invalid': form.errors.baseline }" />
                            <div class="invalid-feedback">{{ form.errors.baseline }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Target Value *</label>
                            <input v-model="form.target" type="number" step="0.01" class="form-control"
                                placeholder="Target to achieve"
                                :class="{ 'is-invalid': form.errors.target }" />
                            <div class="invalid-feedback">{{ form.errors.target }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Actual Value *</label>
                            <input v-model="form.actual" type="number" step="0.01" class="form-control"
                                placeholder="Actual achieved"
                                :class="{ 'is-invalid': form.errors.actual }" />
                            <div class="invalid-feedback">{{ form.errors.actual }}</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-check-circle"></i> Save Baseline
                        </button>
                        <Link href="/programs/baselines" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.card-header-green {
    background-color: rgb(11, 109, 23);
}

.btn-success {
    background-color: rgb(11, 109, 23);
    border-color: rgb(11, 109, 23);
}

.btn-success:hover {
    background-color: rgb(9, 87, 18);
    border-color: rgb(9, 87, 18);
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}
</style>
