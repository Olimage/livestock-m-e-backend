<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({ state: Object, zones: Array })
const form = useForm({ name: props.state.name, zone_id: props.state.zone_id })
const submit = () => form.put(route('states.update', props.state.id))
</script>

<template>
    <BeLayout>
        <Head title="Edit State" />
        <h5 class="mt-4">Edit State — {{ state.name }}</h5>
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
                            <label class="form-label">Zone <span class="text-danger">*</span></label>
                            <select v-model="form.zone_id" class="form-select">
                                <option value="" disabled>— select a zone —</option>
                                <option v-for="z in zones" :key="z.id" :value="z.id">{{ z.name }}</option>
                            </select>
                            <small class="text-danger">{{ form.errors.zone_id }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" :disabled="form.processing"><i class="bi bi-check-circle"></i> Update State</button>
                        <Link :href="route('states.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
