<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    role: Object,
    selectedPermissionIds: Array,
    permissionGroups: Array,
})

const form = useForm({
    name: props.role.name,
    slug: props.role.slug,
    permission_ids: [...(props.selectedPermissionIds || [])],
})

const submit = () => form.put(route('roles.update', props.role.id))
</script>

<template>
    <BeLayout>
        <Head title="Edit Role" />
        <h5 class="mt-4">Edit Role — {{ role.name }}</h5>
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
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input v-model="form.slug" type="text" class="form-control" />
                            <small class="text-danger">{{ form.errors.slug }}</small>
                        </div>
                    </div>

                    <label class="form-label fw-semibold mt-2">Permissions</label>
                    <div class="border rounded p-3 mb-3">
                        <div v-for="grp in permissionGroups" :key="grp.group" class="mb-3">
                            <h6 class="fw-semibold text-uppercase small text-muted">{{ grp.group }}</h6>
                            <div class="row">
                                <div class="col-md-6" v-for="p in grp.permissions" :key="p.id">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" :id="`perm-${p.id}`"
                                            :value="p.id" v-model="form.permission_ids" />
                                        <label class="form-check-label" :for="`perm-${p.id}`">
                                            {{ p.label }} <code class="small text-muted">{{ p.permission }}</code>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!permissionGroups?.length" class="text-muted small">No permissions defined yet.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" :disabled="form.processing">
                            <i class="bi bi-check-circle"></i> Update Role
                        </button>
                        <Link :href="route('roles.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
