<script setup>
import { computed } from 'vue'
import { useForm, usePage, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    indicatorTier: Object,
})

const flash = computed(() => usePage().props.flash ?? {})

const form = useForm({
    name:   props.indicatorTier.name,
    prefix: props.indicatorTier.prefix,
})

const submit = () => form.put(route('programs.indicator-tiers.update', props.indicatorTier.id))
</script>

<template>
    <BeLayout>
        <Head title="Edit Indicator Tier" />

        <h5 class="mt-4">
            Edit Indicator Tier
            <span class="badge bg-success ms-2">{{ indicatorTier.prefix }}</span>
        </h5>
        <hr />

        <div v-if="flash.success" class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header card-header-green text-white">
                        <h6 class="mb-0"><i class="bi bi-layers me-2"></i>Edit Indicator Tier</h6>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input v-model="form.name" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.name }"
                                    placeholder="e.g., Output" />
                                <div class="invalid-feedback">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Prefix <span class="text-danger">*</span></label>
                                <input v-model="form.prefix" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.prefix }"
                                    placeholder="e.g., OUT" maxlength="20" style="text-transform:uppercase" />
                                <div class="invalid-feedback">{{ form.errors.prefix }}</div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <Link :href="route('programs.indicator-tiers.index')" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </Link>
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-save me-2"></i>Update Tier
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
