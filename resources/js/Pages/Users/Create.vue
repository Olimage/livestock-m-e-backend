<script setup>
import { computed, watch, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'

const props = defineProps({
    roles: Array,
    departments: Array,
})

const form = useForm({
    full_name: '',
    email: '',
    role: props.roles?.[0]?.slug ?? (props.roles?.[0]?.name ?? ''),
    // array of selected department ids starting from top-level
    department_ids: [],
})

// levels will be an array of option arrays. level 0 = top-level departments
const levels = ref([])

// initialize first level with top-level departments passed from server
levels.value = [props.departments || []]

// helper to fetch children for a department id
async function fetchChildren(id) {
    if (!id) return []
    const res = await fetch(`/departments/${id}/children`, { credentials: 'same-origin' })
    if (!res.ok) return []
    return res.json()
}

// when a level selection changes, fetch next-level children and trim deeper levels
async function onLevelChange(levelIndex, selectedId) {
    // ensure department_ids array matches selections
    form.department_ids = form.department_ids.slice(0, levelIndex)
    if (selectedId) form.department_ids[levelIndex] = selectedId

    // remove deeper levels
    levels.value = levels.value.slice(0, levelIndex + 1)

    // attempt to load children for newly selected id
    const children = await fetchChildren(selectedId)
    if (children && children.length > 0) {
        // append new level options
        levels.value.push(children)
    }
}

function submit() {
    // require at least one department selected
    if (!form.department_ids || form.department_ids.length === 0) {
        form.setError('department_ids', 'Please select a department')
        return
    }

    form.post('/users')
}
</script>

<template>
    <BeLayout>
        <Head title="Create User" />

        <div class="row">
            <div class="col-lg-8">
                <h5 class="mt-4">Create User</h5>
                <hr />

                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">Full name</label>
                                <input v-model="form.full_name" type="text" class="form-control" />
                                <small class="text-danger">{{ form.errors.full_name }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input v-model="form.email" type="email" class="form-control" />
                                <small class="text-danger">{{ form.errors.email }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select v-model="form.role" class="form-select">
                                    <option v-for="r in props.roles" :key="r.id" :value="r.slug ?? r.name">{{ r.name }}</option>
                                </select>
                                <small class="text-danger">{{ form.errors.role }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Department (choose path)</label>
                                <div v-for="(options, idx) in levels" :key="idx" class="mb-2">
                                    <select
                                        class="form-select"
                                        v-model="form.department_ids[idx]"
                                        @change="onLevelChange(idx, Number(form.department_ids[idx]) || null)"
                                    >
                                        <option :value="null">-- Select --</option>
                                        <option v-for="opt in options" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                                    </select>
                                </div>
                                <small class="text-danger">{{ form.errors.department_ids }}</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success" :disabled="form.processing">Create User</button>
                                <Link href="/users" class="btn btn-outline-secondary">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.card { border: none; }
</style>
