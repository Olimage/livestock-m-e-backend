<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    priority: Object,
    tiers: Array,
})

const form = useForm({
    code: props.priority.code,
    title: props.priority.title,
    description: props.priority.description,
    tier_ids: props.priority.tiers?.map(t => t.id) || [],
})

const submit = () => {
    form.put(route('programs.presidential-priorities.update', props.priority.id))
}
</script>

<template>
    <BeLayout>
        <Head title="Edit Presidential Priority" />

        <div class="row">
            <div class="col-lg-10">
                <h5 class="mt-4">Edit Presidential Priority</h5>
                <hr />

                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input v-model="form.code" type="text" class="form-control" placeholder="e.g., PP-001" />
                                    <small class="text-danger">{{ form.errors.code }}</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input v-model="form.title" type="text" class="form-control" placeholder="Priority title" />
                                    <small class="text-danger">{{ form.errors.title }}</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="4" 
                                    placeholder="Detailed description of the priority"></textarea>
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
                                    <i class="bi bi-check-circle"></i> Update Priority
                                </button>
                                <Link :href="route('programs.presidential-priorities.index')" class="btn btn-outline-secondary">
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
