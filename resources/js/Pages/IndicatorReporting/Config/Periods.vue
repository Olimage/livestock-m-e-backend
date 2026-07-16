<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import BeLayout from '../../../Layouts/BeLayout.vue';

defineProps({ periods: { type: Array, default: () => [] } });

const form = useForm({ name: '', type: 'quarter', year: new Date().getFullYear(), period_number: 1, is_open: true });

const create = () => form.post(route('indicator-reporting.periods.store'), { preserveScroll: true, onSuccess: () => form.reset() });
const toggleOpen = (p) => router.put(route('indicator-reporting.periods.update', p.id), { ...p, is_open: !p.is_open }, { preserveScroll: true });
const remove = (p) => { if (confirm(`Delete "${p.name}"?`)) router.delete(route('indicator-reporting.periods.destroy', p.id), { preserveScroll: true }); };
</script>

<template>
    <BeLayout>
        <Head title="Reporting Periods" />
        <div class="row mt-4"><div class="col"><h5 class="fw-400">Reporting Periods</h5><hr /></div></div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-sm align-middle">
                            <thead class="table-light"><tr><th>Name</th><th>Type</th><th>Year</th><th>Open</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                <tr v-if="periods.length === 0"><td colspan="5" class="text-center text-muted py-3">No periods</td></tr>
                                <tr v-for="p in periods" :key="p.id">
                                    <td>{{ p.name }}</td><td>{{ p.type }}</td><td>{{ p.year }}</td>
                                    <td>
                                        <button class="btn btn-sm" :class="p.is_open ? 'btn-success' : 'btn-outline-secondary'" @click="toggleOpen(p)">
                                            {{ p.is_open ? 'Open' : 'Closed' }}
                                        </button>
                                    </td>
                                    <td class="text-end"><button class="btn btn-sm btn-outline-danger" @click="remove(p)"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Add period</div>
                    <div class="card-body">
                        <form @submit.prevent="create">
                            <div class="mb-2"><label class="form-label">Name</label><input v-model="form.name" class="form-control form-control-sm" /><small class="text-danger" v-if="form.errors.name">{{ form.errors.name }}</small></div>
                            <div class="mb-2"><label class="form-label">Type</label>
                                <select v-model="form.type" class="form-select form-select-sm"><option value="month">Month</option><option value="quarter">Quarter</option><option value="year">Year</option></select>
                            </div>
                            <div class="mb-2"><label class="form-label">Year</label><input v-model="form.year" type="number" class="form-control form-control-sm" /></div>
                            <div class="mb-2"><label class="form-label">Period number</label><input v-model="form.period_number" type="number" class="form-control form-control-sm" /></div>
                            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="p-open" v-model="form.is_open" /><label class="form-check-label" for="p-open">Open</label></div>
                            <button class="btn btn-sm btn-primary w-100" :disabled="form.processing">Add period</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
