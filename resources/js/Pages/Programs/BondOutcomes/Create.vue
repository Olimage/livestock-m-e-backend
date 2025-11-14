<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    presidentialPriorities: Array,
    sectoralGoals: Array,
    nlgasPillars: Array,
})

const form = useForm({
    code: '',
    title: '',
    description: '',
    baseline_year: null,
    target_year: null,
    source_document: '',
    responsible_entity: '',
    presidential_priority_ids: [],
    sectoral_goal_ids: [],
    nlgas_pillar_ids: [],
})

const submit = () => {
    form.post(route('programs.bond-outcomes.store'))
}
</script>

<template>
    <BeLayout>
        <Head title="Create Bond Outcome" />

        <div class="row">
            <div class="col-lg-10">
                <h5 class="mt-4">Create Bond Outcome</h5>
                <hr />

                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input v-model="form.code" type="text" class="form-control" placeholder="e.g., BO-001" />
                                    <small class="text-danger">{{ form.errors.code }}</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input v-model="form.title" type="text" class="form-control" placeholder="Outcome title" />
                                    <small class="text-danger">{{ form.errors.title }}</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="4" 
                                    placeholder="Detailed description of the bond outcome"></textarea>
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
                                        placeholder="e.g., Ministry of Finance" />
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
                                <label class="form-label">Sectoral Goals</label>
                                <select v-model="form.sectoral_goal_ids" class="form-select" multiple size="5">
                                    <option v-for="goal in props.sectoralGoals" :key="goal.id" :value="goal.id">
                                        {{ goal.code }} - {{ goal.title }}
                                    </option>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small><br>
                                <small class="text-danger">{{ form.errors.sectoral_goal_ids }}</small>
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
                                    <i class="bi bi-check-circle"></i> Create Outcome
                                </button>
                                <Link :href="route('programs.bond-outcomes.index')" class="btn btn-outline-secondary">
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
