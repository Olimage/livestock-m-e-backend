<script setup>
import { ref, computed, reactive } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    indicator: Object,
    tiers: Array,
    departments: Array,
    sectoralGoals: Array,
    disagregationCategories: Array,
})

const flash = computed(() => usePage().props.flash ?? {})

const currentStep = ref(1)

const steps = [
    { number: 1, label: 'Overview',            icon: 'bi-eye' },
    { number: 2, label: 'Basic Info',          icon: 'bi-info-circle' },
    { number: 3, label: 'Tiers',               icon: 'bi-layers' },
    { number: 4, label: 'Sectoral Goals',      icon: 'bi-bullseye' },
    { number: 5, label: 'Disaggregations',     icon: 'bi-funnel' },
    { number: 6, label: 'Main Department',     icon: 'bi-building-fill' },
    { number: 7, label: 'Supporting Depts',    icon: 'bi-building' },
    { number: 8, label: 'Review & Update',     icon: 'bi-check-circle' },
]

// Normalise array-cast frequency fields to string
const toFreqString = (val) => Array.isArray(val) ? val.join(', ') : (val || '')

const form = useForm({
    code:               props.indicator.code,
    title:              props.indicator.title,
    description:        props.indicator.description || '',
    indicator_type:     props.indicator.indicator_type || 'output',
    measurement_unit:   props.indicator.measurement_unit || '',
    baseline_value:     props.indicator.baseline_value,
    baseline_year:      props.indicator.baseline_year,
    collection_frequency:  toFreqString(props.indicator.collection_frequency),
    reporting_frequency:   toFreqString(props.indicator.reporting_frequency),
    tier_ids: props.indicator.tiers
        ? props.indicator.tiers.map(t => t.id) : [],
    sectoral_goal_ids: props.indicator.sectoral_goals
        ? props.indicator.sectoral_goals.map(g => g.id) : [],
    disagregation_item_ids: props.indicator.disagregation
        ? props.indicator.disagregation.map(i => i.id) : [],
    main_department_id: props.indicator.main_department && props.indicator.main_department.length > 0
        ? props.indicator.main_department[0].id : '',
    supporting_department_ids: props.indicator.supporting_departments
        ? props.indicator.supporting_departments.map(d => d.id) : [],
    new_disagregation_categories: [],
})

// Inline new category creation state
const showNewCategoryForm = ref(false)
const newCategoryDraft = reactive({ name: '', items: [''] })

const addItemToDraft = () => newCategoryDraft.items.push('')
const removeItemFromDraft = (idx) => {
    if (newCategoryDraft.items.length > 1) newCategoryDraft.items.splice(idx, 1)
}
const addNewCategory = () => {
    if (!newCategoryDraft.name.trim()) return
    form.new_disagregation_categories.push({
        name: newCategoryDraft.name.trim(),
        items: newCategoryDraft.items.map(i => i.trim()).filter(Boolean),
    })
    newCategoryDraft.name = ''
    newCategoryDraft.items = ['']
    showNewCategoryForm.value = false
}
const removeNewCategory = (idx) => form.new_disagregation_categories.splice(idx, 1)

const next = () => { if (currentStep.value < 8) currentStep.value++ }
const prev = () => { if (currentStep.value > 1) currentStep.value-- }
const goTo = (n) => { currentStep.value = n }

const submit = () => form.put(route('programs.indicators.update', props.indicator.id), {
    onSuccess: () => { currentStep.value = 1 },
})

// Review computed helpers
const selectedTiers = computed(() =>
    props.tiers.filter(t => form.tier_ids.includes(t.id))
)
const selectedGoals = computed(() =>
    props.sectoralGoals.filter(g => form.sectoral_goal_ids.includes(g.id))
)
const selectedMainDept = computed(() =>
    props.departments.find(d => d.id == form.main_department_id)
)
const selectedSupportingDepts = computed(() =>
    props.departments.filter(d => form.supporting_department_ids.includes(d.id))
)
const selectedDisaggItems = computed(() => {
    const result = []
    props.disagregationCategories.forEach(cat => {
        cat.items
            .filter(i => form.disagregation_item_ids.includes(i.id))
            .forEach(i => result.push({ ...i, categoryName: cat.name }))
    })
    return result
})

const typeBadgeClass = computed(() => ({
    impact:  'bg-danger',
    outcome: 'bg-warning text-dark',
    output:  'bg-info text-dark',
}[form.indicator_type] || 'bg-secondary'))
</script>

<template>
    <BeLayout>
        <Head title="Edit Indicator" />
        <h5 class="mt-4">Edit Indicator
            <span class="badge bg-success ms-2">{{ indicator.code }}</span>
        </h5>
        <hr />

        <div v-if="flash.success" class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Step Wizard -->
        <div class="step-wizard mb-4">
            <div class="d-flex align-items-center">
                <template v-for="(step, idx) in steps" :key="step.number">
                    <div class="step-item text-center" @click="goTo(step.number)" style="cursor:pointer; min-width:70px;">
                        <div class="step-circle mx-auto mb-1"
                             :class="{ 'step-active': currentStep === step.number, 'step-done': currentStep > step.number }">
                            <i v-if="currentStep > step.number" class="bi bi-check-lg"></i>
                            <span v-else>{{ step.number }}</span>
                        </div>
                        <div class="step-label" :class="currentStep === step.number ? 'fw-semibold text-green' : 'text-muted'">
                            {{ step.label }}
                        </div>
                    </div>
                    <div v-if="idx < steps.length - 1" class="step-connector"
                         :class="{ 'step-connector-done': currentStep > step.number }"></div>
                </template>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-green text-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0">
                    <i :class="['bi', steps[currentStep - 1].icon, 'me-2']"></i>
                    Step {{ currentStep }} of 8 — {{ steps[currentStep - 1].label }}
                </h6>
                <small class="opacity-75">{{ Math.round((currentStep / 8) * 100) }}% complete</small>
            </div>

            <!-- Progress bar -->
            <div class="progress" style="height: 3px; border-radius: 0;">
                <div class="progress-bar bg-success" :style="{ width: ((currentStep / 8) * 100) + '%' }"></div>
            </div>

            <div class="card-body">
                <form @submit.prevent="submit">

                    <!-- ── Step 1: Overview ── -->
                    <div v-show="currentStep === 1">
                        <p class="text-muted mb-4">Current saved state of this indicator. Edit in the following steps.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="review-card">
                                    <div class="review-card-header"><i class="bi bi-info-circle me-2"></i>Basic Info</div>
                                    <div class="review-card-body">
                                        <div class="review-row">
                                            <span class="review-label">Code</span>
                                            <span class="badge bg-success">{{ form.code || '—' }}</span>
                                        </div>
                                        <div class="review-row">
                                            <span class="review-label">Title</span>
                                            <span>{{ form.title || '—' }}</span>
                                        </div>
                                        <div class="review-row">
                                            <span class="review-label">Type</span>
                                            <span :class="['badge', typeBadgeClass]">{{ form.indicator_type }}</span>
                                        </div>
                                        <div v-if="form.measurement_unit" class="review-row">
                                            <span class="review-label">Unit</span>
                                            <span>{{ form.measurement_unit }}</span>
                                        </div>
                                        <div v-if="form.baseline_value" class="review-row">
                                            <span class="review-label">Baseline</span>
                                            <span>{{ form.baseline_value }} ({{ form.baseline_year }})</span>
                                        </div>
                                        <div v-if="form.collection_frequency" class="review-row">
                                            <span class="review-label">Collection Freq.</span>
                                            <span>{{ form.collection_frequency }}</span>
                                        </div>
                                        <div v-if="form.reporting_frequency" class="review-row">
                                            <span class="review-label">Reporting Freq.</span>
                                            <span>{{ form.reporting_frequency }}</span>
                                        </div>
                                        <div v-if="form.description" class="review-row">
                                            <span class="review-label">Description</span>
                                            <span class="text-muted small">{{ form.description }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex flex-column gap-3">
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <i class="bi bi-layers me-2"></i>Tiers
                                        <span class="badge bg-white text-success ms-auto">{{ selectedTiers.length }}</span>
                                    </div>
                                    <div class="review-card-body">
                                        <span v-if="selectedTiers.length === 0" class="text-muted small">None</span>
                                        <span v-for="t in selectedTiers" :key="t.id" class="badge bg-success me-1 mb-1">{{ t.tier }}</span>
                                    </div>
                                </div>
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <i class="bi bi-bullseye me-2"></i>Sectoral Goals
                                        <span class="badge bg-white text-success ms-auto">{{ selectedGoals.length }}</span>
                                    </div>
                                    <div class="review-card-body">
                                        <span v-if="selectedGoals.length === 0" class="text-muted small">None</span>
                                        <span v-for="g in selectedGoals" :key="g.id" class="badge bg-success me-1 mb-1">{{ g.code }}</span>
                                    </div>
                                </div>
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <i class="bi bi-funnel me-2"></i>Disaggregations
                                        <span class="badge bg-white text-success ms-auto">{{ selectedDisaggItems.length }}</span>
                                    </div>
                                    <div class="review-card-body">
                                        <span v-if="selectedDisaggItems.length === 0" class="text-muted small">None</span>
                                        <span v-for="i in selectedDisaggItems" :key="i.id"
                                              class="badge bg-info text-dark me-1 mb-1"
                                              :title="i.categoryName">{{ i.name }}</span>
                                    </div>
                                </div>
                                <div class="review-card">
                                    <div class="review-card-header"><i class="bi bi-building me-2"></i>Departments</div>
                                    <div class="review-card-body">
                                        <div v-if="selectedMainDept" class="mb-2">
                                            <span class="text-muted small d-block mb-1">Main</span>
                                            <span class="badge bg-success">
                                                <i class="bi bi-building-fill me-1"></i>{{ selectedMainDept.name }}
                                            </span>
                                        </div>
                                        <div v-if="selectedSupportingDepts.length > 0">
                                            <span class="text-muted small d-block mb-1">Supporting</span>
                                            <span v-for="d in selectedSupportingDepts" :key="d.id"
                                                  class="badge bg-secondary me-1 mb-1">{{ d.name }}</span>
                                        </div>
                                        <span v-if="!selectedMainDept && selectedSupportingDepts.length === 0"
                                              class="text-muted small">None</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm" @click="goTo(2)">
                                <i class="bi bi-pencil me-1"></i> Edit This Indicator
                            </button>
                        </div>
                    </div>

                    <!-- ── Step 2: Basic Info ── -->
                    <div v-show="currentStep === 2">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Code *</label>
                                <input v-model="form.code" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.code }" />
                                <div class="invalid-feedback">{{ form.errors.code }}</div>
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label">Title *</label>
                                <input v-model="form.title" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.title }" />
                                <div class="invalid-feedback">{{ form.errors.title }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea v-model="form.description" class="form-control" rows="3"
                                :class="{ 'is-invalid': form.errors.description }" />
                            <div class="invalid-feedback">{{ form.errors.description }}</div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Indicator Type *</label>
                                <select v-model="form.indicator_type" class="form-select"
                                    :class="{ 'is-invalid': form.errors.indicator_type }">
                                    <option value="output">Output</option>
                                    <option value="outcome">Outcome</option>
                                    <option value="impact">Impact</option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.indicator_type }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Measurement Unit</label>
                                <input v-model="form.measurement_unit" type="text" class="form-control"
                                    placeholder="e.g., kg, %, units" />
                                <small class="text-danger">{{ form.errors.measurement_unit }}</small>
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
                                <input v-model="form.baseline_year" type="number" class="form-control"
                                    placeholder="e.g., 2024" />
                                <small class="text-danger">{{ form.errors.baseline_year }}</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Collection Frequency</label>
                                <input v-model="form.collection_frequency" type="text" class="form-control"
                                    placeholder="e.g., Quarterly, Annually" />
                                <small class="text-danger">{{ form.errors.collection_frequency }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reporting Frequency</label>
                                <input v-model="form.reporting_frequency" type="text" class="form-control"
                                    placeholder="e.g., Monthly, Annual" />
                                <small class="text-danger">{{ form.errors.reporting_frequency }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- ── Step 3: Tiers ── -->
                    <div v-show="currentStep === 3">
                        <p class="text-muted mb-3">Select the measurement tier(s) for this indicator.</p>
                        <div v-if="props.tiers.length === 0" class="alert alert-warning">No tiers configured yet.</div>
                        <div class="row">
                            <div v-for="tier in props.tiers" :key="tier.id" class="col-md-6 mb-3">
                                <label :for="`tier-${tier.id}`" class="d-block">
                                    <div class="selection-card" :class="{ 'selected': form.tier_ids.includes(tier.id) }">
                                        <div class="d-flex align-items-start gap-2">
                                            <input class="form-check-input mt-1 flex-shrink-0"
                                                   type="checkbox"
                                                   :id="`tier-${tier.id}`"
                                                   :value="tier.id"
                                                   v-model="form.tier_ids" />
                                            <div>
                                                <div class="fw-semibold">{{ tier.tier }} — {{ tier.level }}</div>
                                                <div class="text-muted small">{{ tier.measurement_frequency }}</div>
                                                <div class="text-muted small">Attribution: {{ tier.attribution }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <small class="text-danger">{{ form.errors.tier_ids }}</small>
                        <div v-if="form.tier_ids.length > 0" class="mt-2 text-muted small">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            {{ form.tier_ids.length }} tier(s) selected
                        </div>
                    </div>

                    <!-- ── Step 4: Sectoral Goals ── -->
                    <div v-show="currentStep === 4">
                        <p class="text-muted mb-3">Select the sectoral goal(s) this indicator contributes to.</p>
                        <div v-if="props.sectoralGoals.length === 0" class="alert alert-warning">No sectoral goals configured yet.</div>
                        <div class="row">
                            <div v-for="goal in props.sectoralGoals" :key="goal.id" class="col-md-6 mb-3">
                                <label :for="`goal-${goal.id}`" class="d-block">
                                    <div class="selection-card" :class="{ 'selected': form.sectoral_goal_ids.includes(goal.id) }">
                                        <div class="d-flex align-items-start gap-2">
                                            <input class="form-check-input mt-1 flex-shrink-0"
                                                   type="checkbox"
                                                   :id="`goal-${goal.id}`"
                                                   :value="goal.id"
                                                   v-model="form.sectoral_goal_ids" />
                                            <div>
                                                <div class="fw-semibold">
                                                    <span class="badge bg-success me-1">{{ goal.code }}</span>
                                                    {{ goal.title }}
                                                </div>
                                                <div v-if="goal.description" class="text-muted small mt-1">{{ goal.description }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <small class="text-danger">{{ form.errors.sectoral_goal_ids }}</small>
                        <div v-if="form.sectoral_goal_ids.length > 0" class="mt-2 text-muted small">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            {{ form.sectoral_goal_ids.length }} goal(s) selected
                        </div>
                    </div>

                    <!-- ── Step 5: Disaggregations ── -->
                    <div v-show="currentStep === 5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <p class="text-muted mb-0">Select existing disaggregation items or create new categories.</p>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    @click="showNewCategoryForm = !showNewCategoryForm">
                                <i class="bi bi-plus-circle me-1"></i>
                                {{ showNewCategoryForm ? 'Cancel' : 'New Category' }}
                            </button>
                        </div>

                        <!-- Inline create new category form -->
                        <div v-if="showNewCategoryForm" class="card border-success mb-4">
                            <div class="card-header card-header-green text-white py-2">
                                <i class="bi bi-plus-circle me-1"></i> Create New Disaggregation Category
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Category Name *</label>
                                    <input v-model="newCategoryDraft.name" type="text" class="form-control"
                                           placeholder="e.g., Gender, Age Group" />
                                </div>
                                <label class="form-label fw-semibold">Items</label>
                                <div v-for="(item, idx) in newCategoryDraft.items" :key="idx"
                                     class="d-flex gap-2 mb-2">
                                    <input v-model="newCategoryDraft.items[idx]" type="text" class="form-control"
                                           :placeholder="`Item ${idx + 1}`" />
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            @click="removeItemFromDraft(idx)"
                                            :disabled="newCategoryDraft.items.length === 1">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            @click="addItemToDraft">
                                        <i class="bi bi-plus me-1"></i>Add Item
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success"
                                            @click="addNewCategory"
                                            :disabled="!newCategoryDraft.name.trim()">
                                        <i class="bi bi-check me-1"></i>Add Category
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Pending new categories -->
                        <div v-if="form.new_disagregation_categories.length > 0" class="mb-4">
                            <h6 class="text-uppercase fw-semibold mb-2 small text-success">
                                <i class="bi bi-plus-circle me-1"></i>New Categories to Create
                            </h6>
                            <div v-for="(cat, idx) in form.new_disagregation_categories" :key="idx"
                                 class="d-flex align-items-start gap-2 mb-2 p-2 border border-success rounded bg-light">
                                <div class="flex-grow-1">
                                    <span class="fw-semibold">{{ cat.name }}</span>
                                    <span v-if="cat.items.length > 0" class="ms-2 text-muted small">
                                        — {{ cat.items.join(', ') }}
                                    </span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                                        @click="removeNewCategory(idx)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Existing categories -->
                        <div v-if="props.disagregationCategories.length === 0 && form.new_disagregation_categories.length === 0"
                             class="alert alert-warning">
                            No disaggregation categories configured yet. Use "New Category" to create one.
                        </div>
                        <div v-for="cat in props.disagregationCategories" :key="cat.id" class="mb-4">
                            <h6 class="text-uppercase text-muted fw-semibold mb-2 small">
                                <i class="bi bi-tag me-1"></i>{{ cat.name }}
                            </h6>
                            <div class="row">
                                <div v-for="item in cat.items" :key="item.id" class="col-md-4 mb-2">
                                    <label :for="`item-${item.id}`" class="d-block">
                                        <div class="selection-card selection-card-sm"
                                             :class="{ 'selected': form.disagregation_item_ids.includes(item.id) }">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-check-input flex-shrink-0"
                                                       type="checkbox"
                                                       :id="`item-${item.id}`"
                                                       :value="item.id"
                                                       v-model="form.disagregation_item_ids" />
                                                <span>{{ item.name }}</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <small class="text-danger">{{ form.errors.disagregation_item_ids }}</small>
                        <div v-if="form.disagregation_item_ids.length > 0 || form.new_disagregation_categories.length > 0"
                             class="mt-2 text-muted small">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            {{ form.disagregation_item_ids.length }} existing item(s) selected
                            <span v-if="form.new_disagregation_categories.length > 0">
                                + {{ form.new_disagregation_categories.length }} new category(ies) to create
                            </span>
                        </div>
                    </div>

                    <!-- ── Step 6: Main Department ── -->
                    <div v-show="currentStep === 6">
                        <p class="text-muted mb-3">Select the primary department responsible for this indicator.</p>
                        <div v-if="props.departments.length === 0" class="alert alert-warning">No departments configured yet.</div>
                        <div class="row">
                            <div v-for="dept in props.departments" :key="dept.id" class="col-md-4 mb-3">
                                <label :for="`main-dept-${dept.id}`" class="d-block">
                                    <div class="selection-card"
                                         :class="{ 'selected': form.main_department_id == dept.id }">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input flex-shrink-0"
                                                   type="radio"
                                                   :id="`main-dept-${dept.id}`"
                                                   :value="dept.id"
                                                   v-model="form.main_department_id" />
                                            <div class="fw-semibold">{{ dept.name }}</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div v-if="selectedMainDept" class="alert alert-success mt-2 py-2">
                            <i class="bi bi-building-fill me-2"></i>
                            Main Department: <strong>{{ selectedMainDept.name }}</strong>
                        </div>
                        <small class="text-danger">{{ form.errors.main_department_id }}</small>
                    </div>

                    <!-- ── Step 7: Supporting Departments ── -->
                    <div v-show="currentStep === 7">
                        <p class="text-muted mb-3">
                            Select supporting (collaborating) departments.
                            <span v-if="selectedMainDept" class="badge bg-success ms-1">
                                <i class="bi bi-building-fill me-1"></i>Main: {{ selectedMainDept.name }}
                            </span>
                        </p>
                        <div v-if="!form.main_department_id" class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No main department selected. You can
                            <button type="button" class="btn btn-link p-0 alert-link" @click="goTo(6)">go back to Step 6</button>
                            to select one.
                        </div>
                        <div v-if="props.departments.filter(d => d.id != form.main_department_id).length === 0" class="alert alert-info">
                            No other departments available.
                        </div>
                        <div class="row">
                            <div v-for="dept in props.departments.filter(d => d.id != form.main_department_id)"
                                 :key="dept.id" class="col-md-4 mb-3">
                                <label :for="`supp-dept-${dept.id}`" class="d-block">
                                    <div class="selection-card"
                                         :class="{ 'selected': form.supporting_department_ids.includes(dept.id) }">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input flex-shrink-0"
                                                   type="checkbox"
                                                   :id="`supp-dept-${dept.id}`"
                                                   :value="dept.id"
                                                   v-model="form.supporting_department_ids" />
                                            <div class="fw-semibold">{{ dept.name }}</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <small class="text-danger">{{ form.errors.supporting_department_ids }}</small>
                        <div v-if="form.supporting_department_ids.length > 0" class="mt-2 text-muted small">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            {{ form.supporting_department_ids.length }} supporting department(s) selected
                        </div>
                    </div>

                    <!-- ── Step 8: Review & Update ── -->
                    <div v-show="currentStep === 8">
                        <p class="text-muted mb-4">Review all selections before updating.</p>

                        <div class="row g-3">
                            <!-- Basic Info -->
                            <div class="col-md-6">
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <i class="bi bi-info-circle me-2"></i>Basic Info
                                    </div>
                                    <div class="review-card-body">
                                        <div class="review-row">
                                            <span class="review-label">Code</span>
                                            <span>{{ form.code || '—' }}</span>
                                        </div>
                                        <div class="review-row">
                                            <span class="review-label">Title</span>
                                            <span>{{ form.title || '—' }}</span>
                                        </div>
                                        <div class="review-row">
                                            <span class="review-label">Type</span>
                                            <span :class="['badge', typeBadgeClass]">{{ form.indicator_type }}</span>
                                        </div>
                                        <div v-if="form.measurement_unit" class="review-row">
                                            <span class="review-label">Unit</span>
                                            <span>{{ form.measurement_unit }}</span>
                                        </div>
                                        <div v-if="form.baseline_value" class="review-row">
                                            <span class="review-label">Baseline</span>
                                            <span>{{ form.baseline_value }} ({{ form.baseline_year }})</span>
                                        </div>
                                        <div v-if="form.collection_frequency" class="review-row">
                                            <span class="review-label">Collection Freq.</span>
                                            <span>{{ form.collection_frequency }}</span>
                                        </div>
                                        <div v-if="form.reporting_frequency" class="review-row">
                                            <span class="review-label">Reporting Freq.</span>
                                            <span>{{ form.reporting_frequency }}</span>
                                        </div>
                                        <div v-if="form.description" class="review-row">
                                            <span class="review-label">Description</span>
                                            <span class="text-muted small">{{ form.description }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 d-flex flex-column gap-3">
                                <!-- Tiers -->
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <i class="bi bi-layers me-2"></i>Tiers
                                        <span class="badge bg-white text-success ms-auto">{{ selectedTiers.length }}</span>
                                    </div>
                                    <div class="review-card-body">
                                        <span v-if="selectedTiers.length === 0" class="text-muted small">None selected</span>
                                        <span v-for="t in selectedTiers" :key="t.id" class="badge bg-success me-1 mb-1">{{ t.tier }}</span>
                                    </div>
                                </div>

                                <!-- Sectoral Goals -->
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <i class="bi bi-bullseye me-2"></i>Sectoral Goals
                                        <span class="badge bg-white text-success ms-auto">{{ selectedGoals.length }}</span>
                                    </div>
                                    <div class="review-card-body">
                                        <span v-if="selectedGoals.length === 0" class="text-muted small">None selected</span>
                                        <span v-for="g in selectedGoals" :key="g.id" class="badge bg-success me-1 mb-1">{{ g.code }}</span>
                                    </div>
                                </div>

                                <!-- Disaggregations -->
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <i class="bi bi-funnel me-2"></i>Disaggregations
                                        <span class="badge bg-white text-success ms-auto">
                                            {{ selectedDisaggItems.length + form.new_disagregation_categories.length }}
                                        </span>
                                    </div>
                                    <div class="review-card-body">
                                        <span v-if="selectedDisaggItems.length === 0 && form.new_disagregation_categories.length === 0"
                                              class="text-muted small">None selected</span>
                                        <span v-for="i in selectedDisaggItems" :key="i.id"
                                              class="badge bg-info text-dark me-1 mb-1"
                                              :title="i.categoryName">{{ i.name }}</span>
                                        <div v-if="form.new_disagregation_categories.length > 0" class="mt-1">
                                            <span v-for="(cat, idx) in form.new_disagregation_categories" :key="'new-'+idx"
                                                  class="badge bg-success me-1 mb-1">
                                                <i class="bi bi-plus me-1"></i>{{ cat.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Departments -->
                                <div class="review-card">
                                    <div class="review-card-header">
                                        <i class="bi bi-building me-2"></i>Departments
                                    </div>
                                    <div class="review-card-body">
                                        <div v-if="selectedMainDept" class="mb-2">
                                            <span class="text-muted small d-block mb-1">Main</span>
                                            <span class="badge bg-success">
                                                <i class="bi bi-building-fill me-1"></i>{{ selectedMainDept.name }}
                                            </span>
                                        </div>
                                        <div v-if="selectedSupportingDepts.length > 0">
                                            <span class="text-muted small d-block mb-1">Supporting</span>
                                            <span v-for="d in selectedSupportingDepts" :key="d.id"
                                                  class="badge bg-secondary me-1 mb-1">{{ d.name }}</span>
                                        </div>
                                        <span v-if="!selectedMainDept && selectedSupportingDepts.length === 0"
                                              class="text-muted small">None selected</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Navigation ── -->
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <div>
                            <button v-if="currentStep > 1" type="button" class="btn btn-outline-secondary" @click="prev">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </button>
                            <Link v-else :href="route('programs.indicators.index')" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </Link>
                        </div>
                        <div>
                            <button v-if="currentStep < 8" type="button" class="btn btn-success" @click="next">
                                Next <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                            <button v-else type="submit" class="btn btn-success" :disabled="form.processing">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ form.processing ? 'Updating...' : 'Update Indicator' }}
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
/* ── Step Wizard ── */
.step-wizard {
    overflow-x: auto;
    padding-bottom: 4px;
}

.step-item {
    flex-shrink: 0;
}

.step-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.85rem;
    border: 2px solid #dee2e6;
    transition: all 0.2s;
}

.step-circle.step-active {
    background: rgb(11, 109, 23);
    color: #fff;
    border-color: rgb(11, 109, 23);
    box-shadow: 0 0 0 4px rgba(11, 109, 23, 0.15);
}

.step-circle.step-done {
    background: rgb(11, 109, 23);
    color: #fff;
    border-color: rgb(11, 109, 23);
}

.step-connector {
    flex-grow: 1;
    height: 2px;
    background: #dee2e6;
    margin: 0 6px;
    margin-bottom: 20px;
    transition: background 0.3s;
    min-width: 16px;
}

.step-connector.step-connector-done {
    background: rgb(11, 109, 23);
}

.step-label {
    font-size: 0.68rem;
    white-space: nowrap;
    margin-top: 4px;
}

.text-green {
    color: rgb(11, 109, 23);
}

/* ── Selection Cards ── */
.selection-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 0.75rem;
    cursor: pointer;
    transition: border-color 0.15s, background-color 0.15s;
    background: #fff;
}

.selection-card:hover {
    border-color: rgb(11, 109, 23);
    background: rgba(11, 109, 23, 0.03);
}

.selection-card.selected {
    border-color: rgb(11, 109, 23);
    background: rgba(11, 109, 23, 0.07);
}

.selection-card-sm {
    padding: 0.4rem 0.6rem;
}

/* ── Review Cards ── */
.review-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
}

.review-card-header {
    background: rgb(11, 109, 23);
    color: #fff;
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
}

.review-card-body {
    padding: 0.75rem;
    background: #fafafa;
}

.review-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.25rem 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.875rem;
}

.review-row:last-child {
    border-bottom: none;
}

.review-label {
    color: #6c757d;
    font-weight: 500;
    flex-shrink: 0;
}

/* ── Bootstrap overrides ── */
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

.progress-bar {
    background-color: rgb(11, 109, 23) !important;
    transition: width 0.3s ease;
}
</style>
