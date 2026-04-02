<script setup>
import { ref, computed } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'
import DisagregationSelector from '../../../../Components/DisagregationSelector.vue'

const props = defineProps({
    departments: Array,
    disagregationCategories: Array,
    impactIndicators: Array,
    sectoralGoals: Array,
})

const form = useForm({
    title: '', description: '',
    department_id: '',
    supporting_department_ids: [],
    measurement_unit: '',
    disagregation_item_ids: [],
    impact_indicator_ids: [],
    sectoral_goal_ids: [],
})

const impactSearch         = ref('')
const goalSearch           = ref('')
const supportingDeptSearch = ref('')

const filteredImpacts = computed(() => {
    const q = impactSearch.value.toLowerCase()
    return q ? props.impactIndicators.filter(i => i.code.toLowerCase().includes(q) || i.title.toLowerCase().includes(q)) : props.impactIndicators
})
const filteredGoals = computed(() => {
    const q = goalSearch.value.toLowerCase()
    return q ? props.sectoralGoals.filter(i => i.code.toLowerCase().includes(q) || i.title.toLowerCase().includes(q)) : props.sectoralGoals
})
const filteredSupportingDepts = computed(() => {
    const q = supportingDeptSearch.value.toLowerCase()
    const selected = form.department_id
    return props.departments
        .filter(d => d.id != selected)
        .filter(d => !q || d.name.toLowerCase().includes(q))
})

const submit = () => form.post(route('result-chain.outcome-indicators.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create Outcome Indicator" />
        <h5 class="mt-4">Create Outcome Indicator</h5>
        <hr />

        <div class="row">
            <div class="col-lg-11">
                <div class="card shadow-sm">
                    <div class="card-header card-header-green text-white">
                        <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>New Outcome Indicator <span class="badge bg-light text-primary ms-2">OUT</span></h6>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <!-- Code is auto-generated (OUT-{id}) — not shown on create -->
                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Main Department <span class="text-danger">*</span></label>
                                    <select v-model="form.department_id" class="form-select"
                                        :class="{ 'is-invalid': form.errors.department_id }" required>
                                        <option value="">— Select main department —</option>
                                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                    </select>
                                    <div class="invalid-feedback">{{ form.errors.department_id }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Measurement Unit</label>
                                    <input v-model="form.measurement_unit" type="text" class="form-control" placeholder="e.g., %" />
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input v-model="form.title" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.title }" required />
                                <div class="invalid-feedback">{{ form.errors.title }}</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="3" />
                            </div>

                            <div class="row">
                                <!-- Impact Indicators (required: min 1) -->
                                <div class="col-md-3 mb-4">
                                    <label class="form-label fw-semibold">
                                        Linked Impact Indicators <span class="text-danger">*</span>
                                        <small class="text-muted fw-normal d-block">(at least 1 required)</small>
                                    </label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input v-model="impactSearch" type="text" class="form-control" placeholder="Filter..." />
                                    </div>
                                    <div class="indicator-list border rounded p-2" :class="{ 'border-danger': form.errors.impact_indicator_ids }">
                                        <div v-if="!filteredImpacts.length" class="text-muted small p-2">No impacts match.</div>
                                        <div v-for="ind in filteredImpacts" :key="ind.id"
                                            class="form-check py-1 px-3 indicator-item"
                                            :class="{ selected: form.impact_indicator_ids.includes(ind.id) }">
                                            <input class="form-check-input" type="checkbox"
                                                :id="`imp-${ind.id}`" :value="ind.id" v-model="form.impact_indicator_ids" />
                                            <label class="form-check-label w-100" :for="`imp-${ind.id}`">
                                                <span class="badge badge-imp me-2">{{ ind.code }}</span>
                                                <span class="small">{{ ind.title }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="text-muted small mt-1">{{ form.impact_indicator_ids.length }} selected</div>
                                    <small class="text-danger">{{ form.errors.impact_indicator_ids }}</small>
                                </div>

                                <!-- Supporting Departments -->
                                <div class="col-md-3 mb-4">
                                    <label class="form-label fw-semibold">Supporting Departments <small class="text-muted fw-normal">(optional)</small></label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input v-model="supportingDeptSearch" type="text" class="form-control" placeholder="Filter..." />
                                    </div>
                                    <div class="indicator-list border rounded p-2">
                                        <div v-if="!filteredSupportingDepts.length" class="text-muted small p-2">No departments available.</div>
                                        <div v-for="d in filteredSupportingDepts" :key="d.id"
                                            class="form-check py-1 px-3 indicator-item"
                                            :class="{ selected: form.supporting_department_ids.includes(d.id) }">
                                            <input class="form-check-input" type="checkbox"
                                                :id="`sd-${d.id}`" :value="d.id"
                                                v-model="form.supporting_department_ids" />
                                            <label class="form-check-label w-100" :for="`sd-${d.id}`">
                                                <span class="small">{{ d.name }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="text-muted small mt-1">{{ form.supporting_department_ids.length }} selected</div>
                                </div>

                                <!-- Sectoral Goals -->
                                <div class="col-md-3 mb-4">
                                    <label class="form-label fw-semibold">Sectoral Goals <small class="text-muted fw-normal">(optional)</small></label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input v-model="goalSearch" type="text" class="form-control" placeholder="Filter..." />
                                    </div>
                                    <div class="indicator-list border rounded p-2">
                                        <div v-if="!filteredGoals.length" class="text-muted small p-2">No goals match.</div>
                                        <div v-for="goal in filteredGoals" :key="goal.id"
                                            class="form-check py-1 px-3 indicator-item"
                                            :class="{ selected: form.sectoral_goal_ids.includes(goal.id) }">
                                            <input class="form-check-input" type="checkbox"
                                                :id="`sg-${goal.id}`" :value="goal.id" v-model="form.sectoral_goal_ids" />
                                            <label class="form-check-label w-100" :for="`sg-${goal.id}`">
                                                <span class="badge badge-goal me-2">{{ goal.code }}</span>
                                                <span class="small">{{ goal.title }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="text-muted small mt-1">{{ form.sectoral_goal_ids.length }} selected</div>
                                </div>

                                <!-- Disaggregations -->
                                <div class="col-md-3 mb-4">
                                    <label class="form-label fw-semibold">Disaggregations</label>
                                    <DisagregationSelector
                                        v-model="form.disagregation_item_ids"
                                        :categories="disagregationCategories" />
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <Link :href="route('result-chain.outcome-indicators.index')" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancel
                                </Link>
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-save me-2"></i>Create Outcome Indicator
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.card-header-green { background-color: rgb(11,109,23); }
.btn-success { background-color: rgb(11,109,23); border-color: rgb(11,109,23); }
.btn-success:hover { background-color: rgb(9,87,18); border-color: rgb(9,87,18); }
.badge-imp { background-color: #b71c1c; color: #fff; font-size: .75rem; padding: .25em .5em; border-radius: .25rem; }
.badge-goal { background-color: #f57f17; color: #fff; font-size: .75rem; padding: .25em .5em; border-radius: .25rem; }
.indicator-list { max-height: 260px; overflow-y: auto; }
.indicator-item { border-radius: 4px; cursor: pointer; }
.indicator-item:hover { background-color: rgba(11,109,23,.06); }
.indicator-item.selected { background-color: rgba(11,109,23,.12); }
</style>
