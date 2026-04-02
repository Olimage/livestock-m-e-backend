<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    program: Object,
})

const form = useForm({
    code: props.program.code,
    title: props.program.title
})

const submit = () => {
    form.put(`/programs/programs/${props.program.id}`)
}
</script>

<template>
    <BeLayout>
        <Head title="Edit Program" />

        <h5 class="mt-4">Edit Program</h5>
        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header card-header-green text-white">
                        <h6 class="mb-0"><i class="bi bi-folder me-2"></i>Edit Program</h6>
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
                                <div v-if="form.errors.code" class="invalid-feedback">{{ form.errors.code }}</div>
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
                                <div v-if="form.errors.title" class="invalid-feedback">{{ form.errors.title }}</div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <Link href="/programs/programs" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancel
                                </Link>
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-save me-2"></i>Update Program
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
.btn-success:hover { background-color: rgb(9,87,18); border-color: rgb(9,87,18); }
</style>
