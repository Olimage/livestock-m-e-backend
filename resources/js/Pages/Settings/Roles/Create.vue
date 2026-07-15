<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    permissionGroups: Array,
})

const form = useForm({
    name: '',
    slug: '',
    permission_ids: [],
})

const submit = () => form.post(route('roles.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create Role" />
        <h5 class="mt-4">Create Role</h5>
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
                            <input v-model="form.slug" type="text" class="form-control" placeholder="e.g. director" />
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
                            <i class="bi bi-check-circle"></i> Create Role
                        </button>
                        <Link :href="route('roles.index')" class="btn btn-outline-secondary">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
