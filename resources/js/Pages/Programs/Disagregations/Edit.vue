<script setup>
import { ref } from 'vue'
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    category: Object
})

const categoryForm = useForm({
    name: props.category.name
})

const submitCategory = () => {
    categoryForm.put(`/programs/disagregations/${props.category.id}`)
}

// Add item
const addForm = useForm({ name: '' })

const submitAddItem = () => {
    addForm.post(`/programs/disagregations/${props.category.id}/items`, {
        onSuccess: () => addForm.reset()
    })
}

// Inline edit
const editingItemId = ref(null)
const editingName = ref('')

const startEdit = (item) => {
    editingItemId.value = item.id
    editingName.value = item.name
}

const cancelEdit = () => {
    editingItemId.value = null
    editingName.value = ''
}

const saveItem = (item) => {
    router.put(`/programs/disagregations/${props.category.id}/items/${item.id}`, {
        name: editingName.value
    }, {
        preserveScroll: true,
        onSuccess: () => { editingItemId.value = null }
    })
}

const deleteItem = (itemId) => {
    if (confirm('Are you sure you want to delete this item?')) {
        router.delete(`/programs/disagregations/${props.category.id}/items/${itemId}`, {
            preserveScroll: true
        })
    }
}
</script>

<template>
    <BeLayout>
        <Head :title="`Edit: ${category.name}`" />

        <div class="container-fluid mt-4">
            <div class="row justify-content-center">
                <div class="col-md-9">

                    <!-- Category Name -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header card-header-green text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Edit Category</h5>
                            <Link href="/programs/disagregations" class="btn btn-sm btn-light">
                                <i class="bi bi-arrow-left me-1"></i>Back to Categories
                            </Link>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submitCategory">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-9">
                                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                        <input
                                            v-model="categoryForm.name"
                                            type="text"
                                            id="name"
                                            class="form-control"
                                            :class="{ 'is-invalid': categoryForm.errors.name }"
                                            required
                                        />
                                        <div v-if="categoryForm.errors.name" class="invalid-feedback">
                                            {{ categoryForm.errors.name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-success w-100" :disabled="categoryForm.processing">
                                            <i class="bi bi-save me-1"></i>Save Name
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Items Management -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-list-ul me-2"></i>Items
                                <span class="badge bg-success ms-2">{{ category.items.length }}</span>
                            </h6>
                        </div>
                        <div class="card-body">

                            <!-- Add Item -->
                            <form @submit.prevent="submitAddItem" class="mb-3">
                                <div class="input-group">
                                    <input
                                        v-model="addForm.name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': addForm.errors.name }"
                                        placeholder="New item name..."
                                        required
                                    />
                                    <button type="submit" class="btn btn-success" :disabled="addForm.processing">
                                        <i class="bi bi-plus-circle me-1"></i>Add Item
                                    </button>
                                    <div v-if="addForm.errors.name" class="invalid-feedback">
                                        {{ addForm.errors.name }}
                                    </div>
                                </div>
                            </form>

                            <!-- Empty state -->
                            <div v-if="category.items.length === 0" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No items yet. Add the first one above.
                            </div>

                            <!-- Items table -->
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Item Name</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in category.items" :key="item.id">
                                            <td class="text-muted small">{{ index + 1 }}</td>
                                            <td>
                                                <div v-if="editingItemId === item.id" class="input-group input-group-sm">
                                                    <input
                                                        v-model="editingName"
                                                        type="text"
                                                        class="form-control"
                                                        @keydown.enter.prevent="saveItem(item)"
                                                        @keydown.esc="cancelEdit"
                                                    />
                                                    <button class="btn btn-success btn-sm" type="button" @click="saveItem(item)">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary btn-sm" type="button" @click="cancelEdit">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                                <span v-else>{{ item.name }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div v-if="editingItemId !== item.id" class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" type="button" @click="startEdit(item)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" type="button" @click="deleteItem(item.id)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

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

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.table-responsive {
    min-height: 200px;
}
</style>
