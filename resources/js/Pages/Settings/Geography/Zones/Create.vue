<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const form = useForm({ name: '', code: '' })
const submit = () => form.post(route('zones.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create Zone" />
        <h5 class="mt-4">Create Zone</h5>
        <hr />
        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input v-model="form.name" type="text" class="form-control" />
                            <small class="text-danger">{{ form.errors.name }}</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Code</label>
                            <input v-model="form.code" type="text" class="form-control" placeholder="e.g. NW" />
                            <small class="text-danger">{{ form.errors.code }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" :disabled="form.processing"><i class="bi bi-check-circle"></i> Create Zone</button>
                        <Link :href="route('zones.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
