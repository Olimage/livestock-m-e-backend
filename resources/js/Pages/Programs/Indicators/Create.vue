<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    tiers: Array,
    departments: Array,
    disagregationCategories: Array
})

const form = useForm({
    code: '',
    title: '',
    description: '',
    indicator_type: 'output',
    measurement_unit: '',
    baseline_value: null,
    baseline_year: null,
    target_value: null,
    target_year: null,
    data_source: '',
    collection_frequency: '',
    reporting_frequency: '',
    tier_ids: [],
    main_department_id: '',
    supporting_department_ids: [],
    disagregation_item_ids: []
})

const submit = () => {
    form.post(route('programs.indicators.store'))
}
</script>

<template>
    <BeLayout>
        <Head title="Create Indicator" />
        <h5 class="mt-4">Create Indicator</h5>
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

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tiers</label>
                            <select v-model="form.tier_ids" class="form-select" multiple size="4">
                                <option v-for="tier in props.tiers" :key="tier.id" :value="tier.id">
                                    {{ tier.tier }} - {{ tier.level }}
                                </option>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                            <small class="text-danger d-block">{{ form.errors.tier_ids }}</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Main Department</label>
                            <select v-model="form.main_department_id" class="form-select"
                                :class="{ 'is-invalid': form.errors.main_department_id }">
                                <option value="">— none —</option>
                                <option v-for="dept in props.departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                            <div class="invalid-feedback">{{ form.errors.main_department_id }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Disaggregation Items</label>
                            <select v-model="form.disagregation_item_ids" class="form-select" multiple size="4">
                                <optgroup v-for="cat in props.disagregationCategories" :key="cat.id" :label="cat.name">
                                    <option v-for="item in cat.items" :key="item.id" :value="item.id">
                                        {{ item.name }}
                                    </option>
                                </optgroup>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                            <small class="text-danger d-block">{{ form.errors.disagregation_item_ids }}</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supporting Departments</label>
                        <select v-model="form.supporting_department_ids" class="form-select" multiple size="4"
                            :class="{ 'is-invalid': form.errors.supporting_department_ids }">
                            <option v-for="dept in props.departments.filter(d => d.id != form.main_department_id)"
                                :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple. The main department is excluded.</small>
                        <div class="invalid-feedback">{{ form.errors.supporting_department_ids }}</div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-check-circle"></i> Create
                        </button>
                        <Link :href="route('programs.indicators.index')" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
