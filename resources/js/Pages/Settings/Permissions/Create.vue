<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    modules: Array,
})

const form = useForm({
    permission: '',
    label: '',
    module_id: '',
    description: '',
})

const submit = () => form.post(route('permissions.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create Permission" />
        <h5 class="mt-4">Create Permission</h5>
        <hr />
        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Key <span class="text-danger">*</span></label>
                            <input v-model="form.permission" type="text" class="form-control" placeholder="e.g. manage-widgets" />
                            <small class="text-danger">{{ form.errors.permission }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Label</label>
                            <input v-model="form.label" type="text" class="form-control" placeholder="e.g. Manage Widgets" />
                            <small class="text-danger">{{ form.errors.label }}</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Module</label>
                        <select v-model="form.module_id" class="form-select">
                            <option value="">— none —</option>
                            <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-check-circle"></i> Create Permission
                        </button>
                        <Link :href="route('permissions.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
