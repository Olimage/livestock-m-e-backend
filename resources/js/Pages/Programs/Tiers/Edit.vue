<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    tier: Object
})

const form = useForm({
    tier: props.tier.tier,
    level: props.tier.level,
    measurement_frequency: props.tier.measurement_frequency,
    attribution: props.tier.attribution
})

const submit = () => {
    form.put(`/programs/tiers/${props.tier.id}`)
}
</script>

<template>
    <BeLayout>
        <Head title="Edit Tier" />

        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Edit Tier</h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <div class="mb-3">
                                    <label for="tier" class="form-label">Tier <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.tier"
                                        type="text"
                                        id="tier"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.tier }"
                                        required
                                    />
                                    <div v-if="form.errors.tier" class="invalid-feedback">
                                        {{ form.errors.tier }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.level"
                                        type="text"
                                        id="level"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.level }"
                                        required
                                    />
                                    <div v-if="form.errors.level" class="invalid-feedback">
                                        {{ form.errors.level }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="measurement_frequency" class="form-label">Measurement Frequency <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.measurement_frequency"
                                        type="text"
                                        id="measurement_frequency"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.measurement_frequency }"
                                        required
                                    />
                                    <div v-if="form.errors.measurement_frequency" class="invalid-feedback">
                                        {{ form.errors.measurement_frequency }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="attribution" class="form-label">Attribution <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.attribution"
                                        type="text"
                                        id="attribution"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.attribution }"
                                        required
                                    />
                                    <div v-if="form.errors.attribution" class="invalid-feedback">
                                        {{ form.errors.attribution }}
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <Link href="/programs/tiers" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Cancel
                                    </Link>
                                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                        <i class="bi bi-save me-2"></i>Update Tier
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>
