<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    permission: Object,
    modules: Array,
})

const form = useForm({
    permission: props.permission.permission,
    label: props.permission.label,
    module_id: props.permission.module_id || '',
    description: props.permission.description,
})

const submit = () => form.put(route('permissions.update', props.permission.id))
</script>

<template>
    <BeLayout>
        <Head title="Edit Permission" />
        <h5 class="mt-4">Edit Permission</h5>
        <hr />
        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Key <span class="text-danger">*</span></label>
                            <input v-model="form.permission" type="text" class="form-control" />
                            <small class="text-danger">{{ form.errors.permission }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Label</label>
                            <input v-model="form.label" type="text" class="form-control" />
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
                            <i class="bi bi-check-circle"></i> Update Permission
                        </button>
                        <Link :href="route('permissions.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
