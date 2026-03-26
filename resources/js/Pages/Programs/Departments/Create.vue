<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    parentOptions: Array,
})

const form = useForm({
    name:         '',
    is_technical: false,
    is_agency:    false,
    parent_id:    '',
})

const submit = () => form.post(route('programs.departments.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create Department" />

        <h5 class="mt-4">Create Department</h5>
        <hr />

        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header card-header-green text-white">
                        <h6 class="mb-0"><i class="bi bi-building me-2"></i>New Department</h6>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">

                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input v-model="form.name" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.name }"
                                    placeholder="e.g., Veterinary Services Department" />
                                <div class="invalid-feedback">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Parent Department</label>
                                <select v-model="form.parent_id" class="form-select"
                                    :class="{ 'is-invalid': form.errors.parent_id }">
                                    <option value="">— None (root department) —</option>
                                    <option v-for="d in parentOptions" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                                <div class="invalid-feedback">{{ form.errors.parent_id }}</div>
                                <div class="form-text">Leave blank if this is a top-level department.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block">Classification</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check form-switch">
                                        <input v-model="form.is_technical" class="form-check-input" type="checkbox"
                                            id="is_technical" role="switch" />
                                        <label class="form-check-label" for="is_technical">
                                            <i class="bi bi-gear me-1"></i>Technical Department
                                        </label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input v-model="form.is_agency" class="form-check-input" type="checkbox"
                                            id="is_agency" role="switch" />
                                        <label class="form-check-label" for="is_agency">
                                            <i class="bi bi-bank me-1"></i>Agency
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <Link :href="route('programs.departments.index')" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancel
                                </Link>
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-save me-2"></i>Create Department
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.card-header-green { background-color: rgb(11,109,23); }
.btn-success { background-color: rgb(11,109,23); border-color: rgb(11,109,23); }
.btn-success:hover { background-color: rgb(9,87,18); border-color: rgb(9,87,18); }
</style>
