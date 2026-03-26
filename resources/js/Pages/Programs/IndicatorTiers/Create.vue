<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const form = useForm({
    name: '',
    prefix: '',
})

const submit = () => form.post(route('programs.indicator-tiers.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create Indicator Tier" />

        <h5 class="mt-4">Create Indicator Tier</h5>
        <hr />

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header card-header-green text-white">
                        <h6 class="mb-0"><i class="bi bi-layers me-2"></i>New Indicator Tier</h6>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input v-model="form.name" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.name }"
                                    placeholder="e.g., Output" />
                                <div class="invalid-feedback">{{ form.errors.name }}</div>
                                <div class="form-text">The display name for this tier (e.g. Output, Outcome, Impact).</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Prefix <span class="text-danger">*</span></label>
                                <input v-model="form.prefix" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.prefix }"
                                    placeholder="e.g., OUT" maxlength="20" style="text-transform:uppercase" />
                                <div class="invalid-feedback">{{ form.errors.prefix }}</div>
                                <div class="form-text">Short uppercase code used in reporting (e.g. OUT, OC, IMP).</div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <Link :href="route('programs.indicator-tiers.index')" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancel
                                </Link>
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-save me-2"></i>Create Tier
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
.btn-success:hover { background-color: rgb(9,87,18); }
</style>
