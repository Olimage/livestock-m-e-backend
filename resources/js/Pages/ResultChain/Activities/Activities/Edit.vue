<script setup>
import { ref, computed } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../../Layouts/BeLayout.vue'

const props = defineProps({
    activity: Object,
    selectedIds: Array,
    programs: Array,
    outputIndicators: Array,
})

const form = useForm({
    program_id: props.activity.program_id,
    code: props.activity.code,
    title: props.activity.title,
    description: props.activity.description ?? '',
    output_indicator_ids: [...props.selectedIds],
})

const indicatorSearch = ref('')
const filteredIndicators = computed(() => {
    const q = indicatorSearch.value.toLowerCase()
    return q
        ? props.outputIndicators.filter(i =>
            i.code.toLowerCase().includes(q) || i.title.toLowerCase().includes(q))
        : props.outputIndicators
})

const submit = () => form.put(route('result-chain.activities.update', props.activity.id))
</script>

<template>
    <BeLayout>
        <Head title="Edit Activity" />

        <h5 class="mt-4">Edit Activity</h5>
        <hr />

        <div class="row">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header card-header-green text-white">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Edit Activity — <span class="opacity-75">{{ activity.code }}</span></h6>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Program <span class="text-danger">*</span></label>
                                    <select v-model="form.program_id" class="form-select"
                                        :class="{ 'is-invalid': form.errors.program_id }" required>
                                        <option value="">Select a program...</option>
                                        <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.code }} — {{ p.title }}</option>
                                    </select>
                                    <div class="invalid-feedback">{{ form.errors.program_id }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input v-model="form.code" type="text" class="form-control"
                                        :class="{ 'is-invalid': form.errors.code }" required />
                                    <div class="invalid-feedback">{{ form.errors.code }}</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input v-model="form.title" type="text" class="form-control"
                                    :class="{ 'is-invalid': form.errors.title }" required />
                                <div class="invalid-feedback">{{ form.errors.title }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="3" />
                            </div>

                            <!-- Output Indicators -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Linked Output Indicators</label>
                                <div class="mb-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input v-model="indicatorSearch" type="text" class="form-control"
                                            placeholder="Filter output indicators..." />
                                    </div>
                                </div>
                                <div class="indicator-list border rounded p-2">
                                    <div v-if="!filteredIndicators.length" class="text-muted small p-2">No indicators match.</div>
                                    <div v-for="ind in filteredIndicators" :key="ind.id"
                                        class="form-check py-1 px-3 indicator-item"
                                        :class="{ selected: form.output_indicator_ids.includes(ind.id) }">
                                        <input class="form-check-input" type="checkbox"
                                            :id="`oi-${ind.id}`" :value="ind.id"
                                            v-model="form.output_indicator_ids" />
                                        <label class="form-check-label w-100" :for="`oi-${ind.id}`">
                                            <span class="badge badge-opt me-2">{{ ind.code }}</span>
                                            <span class="small">{{ ind.title }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    {{ form.output_indicator_ids.length }} indicator(s) selected
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <Link :href="route('result-chain.activities.index')" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancel
                                </Link>
                                <button type="submit" class="btn btn-success" :disabled="form.processing">
                                    <i class="bi bi-save me-2"></i>Update Activity
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
.badge-opt { background-color: rgb(11,109,23); color: #fff; font-size: .75rem; padding: .25em .5em; border-radius: .25rem; }
.indicator-list { max-height: 300px; overflow-y: auto; }
.indicator-item { border-radius: 4px; cursor: pointer; }
.indicator-item:hover { background-color: rgba(11,109,23,.06); }
.indicator-item.selected { background-color: rgba(11,109,23,.12); }
</style>
