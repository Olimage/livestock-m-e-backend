<script setup>
import { Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    department: Object,
    indicators: Array
})

const typeBadge = (label) => {
    const map = {
        impact: 'bg-danger',
        outcome: 'bg-warning text-dark',
        output: 'bg-info text-dark',
        'bond output': 'bg-secondary',
        'program output': 'bg-primary'
    }
    return map[(label || '').toLowerCase()] || 'bg-primary'
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

                    <!-- Result Chain Indicators (read-only) -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>Result Chain Indicators
                                <span class="badge bg-success ms-2">{{ indicators.length }}</span>
                            </h6>
                            <small class="text-muted">Indicators whose main department is this one</small>
                        </div>
                        <div class="card-body p-0">
                            <div v-if="indicators.length === 0" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No Result Chain indicators reference this department.
                            </div>
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Title</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="indicator in indicators" :key="indicator.code">
                                            <td><span class="badge bg-success">{{ indicator.code }}</span></td>
                                            <td><small>{{ indicator.title }}</small></td>
                                            <td>
                                                <span :class="['badge', typeBadge(indicator.type_label)]">
                                                    {{ indicator.type_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}
</style>
