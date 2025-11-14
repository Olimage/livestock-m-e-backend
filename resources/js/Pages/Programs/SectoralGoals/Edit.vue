<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    goal: Object,
    presidentialPriorities: Array,
    bondOutcomes: Array,
    nlgasPillars: Array,
})

const form = useForm({
    code: props.goal.code,
    title: props.goal.title,
    description: props.goal.description,
    baseline_year: props.goal.baseline_year,
    target_year: props.goal.target_year,
    source_document: props.goal.source_document,
    responsible_entity: props.goal.responsible_entity,
    presidential_priority_ids: props.goal.presidential_priorities?.map(p => p.id) || [],
    bond_outcome_ids: props.goal.bond_outcomes?.map(o => o.id) || [],
    nlgas_pillar_ids: props.goal.nlgas_pillars?.map(p => p.id) || [],
})

const submit = () => {
    form.put(route('programs.sectoral-goals.update', props.goal.id))
}
</script>

<template>
    <BeLayout>
        <Head title="Edit Sectoral Goal" />

        <div class="row">
            <div class="col-lg-10">
                <h5 class="mt-4">Edit Sectoral Goal</h5>
                <hr />

                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input v-model="form.code" type="text" class="form-control" placeholder="e.g., SG-001" />
                                    <small class="text-danger">{{ form.errors.code }}</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input v-model="form.title" type="text" class="form-control" placeholder="Goal title" />
                                    <small class="text-danger">{{ form.errors.title }}</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="4" 
                                    placeholder="Detailed description of the goal"></textarea>
                                <small class="text-danger">{{ form.errors.description }}</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Baseline Year</label>
                                    <input v-model="form.baseline_year" type="number" class="form-control" 
                                        placeholder="e.g., 2023" min="2000" max="2100" />
                                    <small class="text-danger">{{ form.errors.baseline_year }}</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Target Year</label>
                                    <input v-model="form.target_year" type="number" class="form-control" 
                                        placeholder="e.g., 2027" min="2000" max="2100" />
                                    <small class="text-danger">{{ form.errors.target_year }}</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Source Document</label>
                                    <input v-model="form.source_document" type="text" class="form-control" 
                                        placeholder="Source document reference" />
                                    <small class="text-danger">{{ form.errors.source_document }}</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Responsible Entity</label>
                                    <input v-model="form.responsible_entity" type="text" class="form-control" 
                                        placeholder="e.g., Ministry of Agriculture" />
                                    <small class="text-danger">{{ form.errors.responsible_entity }}</small>
                                </div>
                            </div>

                            <hr class="my-4" />
                            <h6 class="mb-3">Relationships</h6>

                            <div class="mb-3">
                                <label class="form-label">Presidential Priorities</label>
                                <select v-model="form.presidential_priority_ids" class="form-select" multiple size="5">
                                    <option v-for="priority in props.presidentialPriorities" :key="priority.id" :value="priority.id">
                                        {{ priority.code }} - {{ priority.title }}
                                    </option>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small><br>
                                <small class="text-danger">{{ form.errors.presidential_priority_ids }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Bond Outcomes</label>
                                <select v-model="form.bond_outcome_ids" class="form-select" multiple size="5">
                                    <option v-for="outcome in props.bondOutcomes" :key="outcome.id" :value="outcome.id">
                                        {{ outcome.code }} - {{ outcome.title }}
                                    </option>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small><br>
                                <small class="text-danger">{{ form.errors.bond_outcome_ids }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">NLGAS Pillars</label>
                                <select v-model="form.nlgas_pillar_ids" class="form-select" multiple size="5">
                                    <option v-for="pillar in props.nlgasPillars" :key="pillar.id" :value="pillar.id">
                                        {{ pillar.code }} - {{ pillar.title }}
                                    </option>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small><br>
                                <small class="text-danger">{{ form.errors.nlgas_pillar_ids }}</small>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-check-circle"></i> Update Goal
                                </button>
                                <Link :href="route('programs.sectoral-goals.index')" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.card {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-success {
    background-color: rgb(11, 109, 23);
    border-color: rgb(11, 109, 23);
}

.btn-success:hover {
    background-color: rgb(9, 87, 18);
    border-color: rgb(9, 87, 18);
}
</style>
