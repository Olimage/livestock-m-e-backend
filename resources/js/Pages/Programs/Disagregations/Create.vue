<script setup>
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const form = useForm({
    name: '',
    items: []
})

const newItem = ref('')

const addItem = () => {
    const trimmed = newItem.value.trim()
    if (trimmed) {
        form.items.push(trimmed)
        newItem.value = ''
    }
}

const removeItem = (index) => {
    form.items.splice(index, 1)
}

const submit = () => {
    form.post('/programs/disagregations')
}
</script>

<template>
    <BeLayout>
        <Head title="Create Disaggregation Category" />

        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header card-header-green text-white">
                            <h5 class="mb-0">Create New Disaggregation Category</h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        id="name"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.name }"
                                        placeholder="e.g., gender, age_group, region"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="invalid-feedback">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Items <span class="text-muted small">(optional — you can add more later)</span></label>
                                    <div class="input-group mb-2">
                                        <input
                                            v-model="newItem"
                                            type="text"
                                            class="form-control"
                                            placeholder="Type an item name and click Add"
                                            @keydown.enter.prevent="addItem"
                                        />
                                        <button type="button" class="btn btn-outline-secondary" @click="addItem">
                                            <i class="bi bi-plus"></i> Add
                                        </button>
                                    </div>
                                    <div v-if="form.items.length > 0" class="border rounded p-2">
                                        <span
                                            v-for="(item, index) in form.items"
                                            :key="index"
                                            class="badge bg-light text-dark border me-1 mb-1 d-inline-flex align-items-center gap-1"
                                            style="font-size: 0.85rem; padding: 0.4rem 0.6rem;"
                                        >
                                            {{ item }}
                                            <button
                                                type="button"
                                                class="btn-close"
                                                style="font-size: 0.55rem;"
                                                @click="removeItem(index)"
                                            ></button>
                                        </span>
                                    </div>
                                    <div v-else class="text-muted small mt-1">
                                        No items added yet.
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <Link href="/programs/disagregations" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Cancel
                                    </Link>
                                    <button type="submit" class="btn btn-success" :disabled="form.processing">
                                        <i class="bi bi-save me-2"></i>Create Category
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

<style scoped>
.card-header-green {
    background-color: rgb(11, 109, 23);
}

.btn-success {
    background-color: rgb(11, 109, 23);
    border-color: rgb(11, 109, 23);
}

.btn-success:hover {
    background-color: rgb(9, 87, 18);
    border-color: rgb(9, 87, 18);
}
</style>
