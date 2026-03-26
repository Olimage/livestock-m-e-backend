<script setup>
import { ref, computed } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
    indicators: Array,
})

const form = useForm({
    code:          '',
    deliverable:   '',
    indicator_ids: [],
})

const indicatorSearch = ref('')
const filteredIndicators = computed(() => {
    const q = indicatorSearch.value.toLowerCase()
    return q
        ? props.indicators.filter(i =>
            i.code.toLowerCase().includes(q) || i.title.toLowerCase().includes(q))
        : props.indicators
})

const submit = () => form.post(route('programs.bond-deliverables.store'))
</script>

<template>
    <BeLayout>
        <Head title="Create Bond Deliverable" />

        <h5 class="mt-4">Create Bond Deliverable</h5>
        <hr />

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header card-header-green text-white">
                        <h6 class="mb-0"><i class="bi bi-bookmark-check me-2"></i>New Bond Deliverable</h6>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input v-model="form.code" type="text" class="form-control"
                                        :class="{ 'is-invalid': form.errors.code }"
                                        placeholder="e.g., BD-1" />
                                    <div class="invalid-feedback">{{ form.errors.code }}</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Deliverable <span class="text-danger">*</span></label>
                                <textarea v-model="form.deliverable" class="form-control" rows="3"
                                    :class="{ 'is-invalid': form.errors.deliverable }"
                                    placeholder="Describe the bond deliverable..." />
                                <div class="invalid-feedback">{{ form.errors.deliverable }}</div>
                            </div>

                            <!-- Linked Indicators -->
                            <div class="mb-4">
                                <label class="form-label">Linked Indicators</label>
                                <div class="mb-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input v-model="indicatorSearch" type="text" class="form-control"
                                            placeholder="Filter indicators..." />
                                    </div>
                                </div>
                                <div class="indicator-list border rounded p-2">
                                    <div v-if="filteredIndicators.length === 0" class="text-muted small p-2">
                                        No indicators match your search.
                                    </div>
                                    <div v-for="ind in filteredIndicators" :key="ind.id"
                                        class="form-check py-1 px-3 indicator-item"
                                        :class="{ 'selected': form.indicator_ids.includes(ind.id) }">
                                        <input class="form-check-input" type="checkbox"
                                            :id="`ind-${ind.id}`" :value="ind.id"
                                            v-model="form.indicator_ids" />
                                        <label class="form-check-label w-100" :for="`ind-${ind.id}`">
                                            <span class="badge bg-success me-2">{{ ind.code }}</span>
                                            <span class="small">{{ ind.title }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    {{ form.indicator_ids.length }} indicator(s) selected
                                </div>
                                <small class="text-danger">{{ form.errors.indicator_ids }}</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <Link :href="route('programs.bond-deliverables.index')" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancel
                                </Link>
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-save me-2"></i>Create Deliverable
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
.indicator-list { max-height: 350px; overflow-y: auto; }
.indicator-item { border-radius: 4px; cursor: pointer; }
.indicator-item:hover { background-color: rgba(11,109,23,.06); }
.indicator-item.selected { background-color: rgba(11,109,23,.12); }
</style>
