<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({ states: Array })
const form = useForm({ name: '', state_id: '' })
const submit = () => form.post(route('lgas.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create LGA" />
        <h5 class="mt-4">Create LGA</h5>
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
                        <button type="submit" class="btn btn-success" :disabled="form.processing"><i class="bi bi-check-circle"></i> Create LGA</button>
                        <Link :href="route('lgas.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
