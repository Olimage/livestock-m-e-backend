<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'

const props = defineProps({
    user: Object,
    selectedRoleIds: Array,
    selectedPermissionIds: Array,
    selectedDepartmentIds: Array,
    roles: Array,
    permissions: Array,
    departments: Array,
})

const form = useForm({
    full_name: props.user.full_name,
    email: props.user.email,
    is_admin: !!props.user.is_admin,
    role_ids: [...(props.selectedRoleIds || [])],
    permission_ids: [...(props.selectedPermissionIds || [])],
    department_ids: [...(props.selectedDepartmentIds || [])],
})

const submit = () => form.put(route('users.update', props.user.id))
</script>

<template>
    <BeLayout>
        <Head title="Edit User" />
        <h5 class="mt-4">Edit User — {{ user.full_name }}</h5>
        <hr />
        <div class="card">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input v-model="form.full_name" type="text" class="form-control" />
                            <small class="text-danger">{{ form.errors.full_name }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input v-model="form.email" type="email" class="form-control" />
                            <small class="text-danger">{{ form.errors.email }}</small>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_admin" v-model="form.is_admin" />
                        <label class="form-check-label" for="is_admin">Administrator (full access)</label>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Roles</label>
                            <div class="border rounded p-2" style="max-height:260px;overflow-y:auto">
                                <div class="form-check" v-for="r in roles" :key="r.id">
                                    <input class="form-check-input" type="checkbox" :id="`role-${r.id}`" :value="r.id" v-model="form.role_ids" />
                                    <label class="form-check-label" :for="`role-${r.id}`">{{ r.name }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Direct Permissions</label>
                            <div class="border rounded p-2" style="max-height:260px;overflow-y:auto">
                                <div class="form-check" v-for="p in permissions" :key="p.id">
                                    <input class="form-check-input" type="checkbox" :id="`uperm-${p.id}`" :value="p.id" v-model="form.permission_ids" />
                                    <label class="form-check-label" :for="`uperm-${p.id}`">{{ p.label || p.permission }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Departments</label>
                            <div class="border rounded p-2" style="max-height:260px;overflow-y:auto">
                                <div class="form-check" v-for="d in departments" :key="d.id">
                                    <input class="form-check-input" type="checkbox" :id="`dept-${d.id}`" :value="d.id" v-model="form.department_ids" />
                                    <label class="form-check-label" :for="`dept-${d.id}`">{{ d.name }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-check-circle"></i> Update User
                        </button>
                        <Link :href="route('users.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
