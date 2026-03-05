<script setup>
import { ref, watch } from 'vue'
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    department: Object,
    availableIndicators: Array,
    indicatorSearch: String
})

// Assign indicator form
const assignForm = useForm({
    indicator_id: ''
})

const submitAssign = () => {
    assignForm.post(`/programs/departments/${props.department.id}/indicators`, {
        onSuccess: () => { assignForm.reset() }
    })
}

const removeIndicator = (indicatorId) => {
    if (confirm('Remove this indicator from the department?')) {
        router.delete(`/programs/departments/${props.department.id}/indicators/${indicatorId}`, {
            preserveScroll: true
        })
    }
}

// Indicator search (filters available list)
const searchTimeout = ref(null)
const indicatorQuery = ref(props.indicatorSearch || '')

watch(indicatorQuery, (val) => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        router.get(
            `/programs/departments/${props.department.id}`,
            { indicator_search: val },
            { preserveState: true, preserveScroll: true }
        )
    }, 300)
})

const indicatorTypeBadge = (type) => {
    const map = { impact: 'bg-danger', outcome: 'bg-warning text-dark', output: 'bg-info text-dark' }
    return map[type] || 'bg-secondary'
}
</script>

<template>
    <BeLayout>
        <Head :title="`Department: ${department.name}`" />

        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-md-10">

                    <!-- Department Info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header card-header-green text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-building me-2"></i>{{ department.name }}
                            </h5>
                            <Link href="/programs/departments" class="btn btn-sm btn-light">
                                <i class="bi bi-arrow-left me-1"></i>Back to Departments
                            </Link>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Type</small>
                                    <span v-if="department.is_technical" class="badge bg-success">Technical</span>
                                    <span v-else class="badge bg-light text-dark border">Non-Technical</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Parent Department</small>
                                    <span v-if="department.parent" class="fw-semibold">{{ department.parent.name }}</span>
                                    <span v-else class="text-muted">—</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Sub-Departments</small>
                                    <span v-if="department.children && department.children.length">
                                        <span v-for="child in department.children" :key="child.id"
                                            class="badge bg-light text-dark border me-1">{{ child.name }}</span>
                                    </span>
                                    <span v-else class="text-muted">None</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Assigned Indicators -->
                        <div class="col-md-7">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bi bi-graph-up me-2"></i>Assigned Indicators
                                        <span class="badge bg-success ms-2">{{ department.indicators.length }}</span>
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div v-if="department.indicators.length === 0" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        No indicators assigned yet.
                                    </div>
                                    <div v-else class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="indicator in department.indicators" :key="indicator.id">
                                                    <td>
                                                        <span class="badge bg-success">{{ indicator.code }}</span>
                                                    </td>
                                                    <td>
                                                        <small>{{ indicator.title }}</small>
                                                    </td>
                                                    <td>
                                                        <span :class="['badge', indicatorTypeBadge(indicator.indicator_type)]">
                                                            {{ indicator.indicator_type }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            @click="removeIndicator(indicator.id)"
                                                            title="Remove from department">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Indicators Panel -->
                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header card-header-green text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-plus-circle me-2"></i>Add Indicator
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Search available indicators -->
                                    <div class="mb-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input
                                                v-model="indicatorQuery"
                                                type="text"
                                                class="form-control"
                                                placeholder="Search by code or title..."
                                            />
                                        </div>
                                        <small class="text-muted">Showing {{ availableIndicators.length }} available indicators</small>
                                    </div>

                                    <form @submit.prevent="submitAssign">
                                        <div class="mb-3">
                                            <select
                                                v-model="assignForm.indicator_id"
                                                class="form-select"
                                                :class="{ 'is-invalid': assignForm.errors.indicator_id }"
                                                size="8"
                                                required
                                            >
                                                <option value="" disabled>— select an indicator —</option>
                                                <option v-for="ind in availableIndicators" :key="ind.id" :value="ind.id">
                                                    [{{ ind.code }}] {{ ind.title }}
                                                </option>
                                            </select>
                                            <div v-if="assignForm.errors.indicator_id" class="invalid-feedback">
                                                {{ assignForm.errors.indicator_id }}
                                            </div>
                                            <div v-if="availableIndicators.length === 0" class="text-muted small mt-1">
                                                All indicators are already assigned, or none match your search.
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success w-100"
                                            :disabled="assignForm.processing || !assignForm.indicator_id">
                                            <i class="bi bi-plus-circle me-1"></i>Assign to Department
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
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

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}

select[size] {
    overflow-y: auto;
}
</style>
