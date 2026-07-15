<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({ lga: Object, states: Array })
const form = useForm({ name: props.lga.name, state_id: props.lga.state_id })
const submit = () => form.put(route('lgas.update', props.lga.id))
</script>

<template>
    <BeLayout>
        <Head title="Edit LGA" />
        <h5 class="mt-4">Edit LGA — {{ lga.name }}</h5>
        <hr />
        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input v-model="form.name" type="text" class="form-control" />
                            <small class="text-danger">{{ form.errors.name }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select v-model="form.state_id" class="form-select">
                                <option value="" disabled>— select a state —</option>
                                <option v-for="s in states" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <small class="text-danger">{{ form.errors.state_id }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" :disabled="form.processing"><i class="bi bi-check-circle"></i> Update LGA</button>
                        <Link :href="route('lgas.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
