<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    pillars: Array
})

const form = useForm({
    nlgas_pillar_id: '',
    code: '',
    title: ''
})

const submit = () => {
    form.post('/programs/programs')
}
</script>

<template>
    <BeLayout>
        <Head title="Create Program" />

        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Create New Program</h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <div class="mb-3">
                                    <label for="nlgas_pillar_id" class="form-label">NLGAS Pillar <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.nlgas_pillar_id"
                                        id="nlgas_pillar_id"
                                        class="form-select"
                                        :class="{ 'is-invalid': form.errors.nlgas_pillar_id }"
                                        required
                                    >
                                        <option value="">Select a pillar...</option>
                                        <option v-for="pillar in pillars" :key="pillar.id" :value="pillar.id">
                                            {{ pillar.code }} - {{ pillar.title }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.nlgas_pillar_id" class="invalid-feedback">
                                        {{ form.errors.nlgas_pillar_id }}
                                    </div>
                                </div>

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
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        id="title"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.title }"
                                        required
                                    />
                                    <div v-if="form.errors.title" class="invalid-feedback">
                                        {{ form.errors.title }}
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <Link href="/programs/programs" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Cancel
                                    </Link>
                                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                        <i class="bi bi-save me-2"></i>Create Program
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
