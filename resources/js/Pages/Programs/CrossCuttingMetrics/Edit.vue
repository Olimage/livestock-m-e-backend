<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    metric: Object
})

const form = useForm({
    code: props.metric.code,
    area: props.metric.area,
    key_metric: props.metric.key_metric,
    purpose: props.metric.purpose
})

const submit = () => {
    form.put(`/programs/cross-cutting-metrics/${props.metric.id}`)
}
</script>

<template>
    <BeLayout>
        <Head title="Edit Cross-Cutting Metric" />

        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Edit Cross-Cutting Metric</h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.code"
                                        type="text"
                                        id="code"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.code }"
                                        required
                                    />
                                    <div v-if="form.errors.code" class="invalid-feedback">
                                        {{ form.errors.code }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="area" class="form-label">Area <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.area"
                                        type="text"
                                        id="area"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.area }"
                                        required
                                    />
                                    <div v-if="form.errors.area" class="invalid-feedback">
                                        {{ form.errors.area }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="key_metric" class="form-label">Key Metric <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.key_metric"
                                        type="text"
                                        id="key_metric"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.key_metric }"
                                        required
                                    />
                                    <div v-if="form.errors.key_metric" class="invalid-feedback">
                                        {{ form.errors.key_metric }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                                    <textarea
                                        v-model="form.purpose"
                                        id="purpose"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.purpose }"
                                        rows="4"
                                        required
                                    ></textarea>
                                    <div v-if="form.errors.purpose" class="invalid-feedback">
                                        {{ form.errors.purpose }}
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <Link href="/programs/cross-cutting-metrics" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Cancel
                                    </Link>
                                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                        <i class="bi bi-save me-2"></i>Update Metric
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
