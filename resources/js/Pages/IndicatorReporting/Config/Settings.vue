<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import BeLayout from '../../../Layouts/BeLayout.vue';

const props = defineProps({ settings: { type: Object, default: () => ({}) } });

const KEY = 'reporting.allow_supporting_department_reporting';
const form = useForm({ settings: { [KEY]: !!props.settings[KEY] } });

const save = () => form.put(route('indicator-reporting.settings.update'), { preserveScroll: true });
</script>

<template>
    <BeLayout>
        <Head title="Reporting Settings" />
        <div class="row mt-4"><div class="col"><h5 class="fw-400">Reporting Settings</h5><hr /></div></div>

        <div class="card">
            <div class="card-body">
                <form @submit.prevent="save">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="supporting" v-model="form.settings[KEY]" />
                        <label class="form-check-label" for="supporting">
                            Allow supporting-department reporting
                            <div class="small text-muted">When on, users in an indicator's supporting departments may also report on it.</div>
                        </label>
                    </div>
                    <button class="btn btn-primary" :disabled="form.processing">Save settings</button>
                </form>
            </div>
        </div>
    </BeLayout>
</template>
