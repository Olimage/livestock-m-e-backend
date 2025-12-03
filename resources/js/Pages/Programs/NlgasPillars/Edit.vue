<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    pillar: Object,
    tiers: Array,
})

const form = useForm({
    code: props.pillar.code,
    title: props.pillar.title,
    description: props.pillar.description,
    tier_ids: props.pillar.tiers?.map(t => t.id) || [],
})

const submit = () => {
    form.put(route('programs.nlgas-pillars.update', props.pillar.id))
}
</script>

<template>
    <BeLayout>
        <Head title="Edit NLGAS Pillar" />

        <div class="row">
            <div class="col-lg-10">
                <h5 class="mt-4">Edit NLGAS Pillar</h5>
                <hr />

                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input v-model="form.code" type="text" class="form-control" placeholder="e.g., NP-001" />
                                    <small class="text-danger">{{ form.errors.code }}</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input v-model="form.title" type="text" class="form-control" placeholder="Pillar title" />
                                    <small class="text-danger">{{ form.errors.title }}</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="4" 
                                    placeholder="Detailed description of the NLGAS pillar"></textarea>
                                <small class="text-danger">{{ form.errors.description }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tiers</label>
                                <select v-model="form.tier_ids" class="form-select" multiple size="5">
                                    <option v-for="tier in props.tiers" :key="tier.id" :value="tier.id">
                                        {{ tier.tier }} - {{ tier.level }}
                                    </option>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small><br>
                                <small class="text-danger">{{ form.errors.tier_ids }}</small>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-check-circle"></i> Update Pillar
                                </button>
                                <Link :href="route('programs.nlgas-pillars.index')" class="btn btn-outline-secondary">
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
