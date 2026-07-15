<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const form = useForm({
    name: '',
    slug: '',
    description: '',
})

const submit = () => form.post(route('modules.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create Module" />
        <h5 class="mt-4">Create Module</h5>
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
                            <label class="form-label">Slug <small class="text-muted">(auto if blank)</small></label>
                            <input v-model="form.slug" type="text" class="form-control" placeholder="e.g. animal-health" />
                            <small class="text-danger">{{ form.errors.slug }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-check-circle"></i> Create Module
                        </button>
                        <Link :href="route('modules.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
