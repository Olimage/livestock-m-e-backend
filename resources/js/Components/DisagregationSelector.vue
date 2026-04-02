<script setup>
import { ref, computed, reactive } from 'vue'

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

// ── local state ─────────────────────────────────────────────────
const search          = ref('')
const localCategories = reactive(props.categories.map(c => ({ ...c, items: [...c.items] })))
const expanded        = reactive({})      // { [categoryId]: bool }
const addingItem      = reactive({})      // { [categoryId]: bool } — shows per-cat input
const newItemName     = reactive({})      // { [categoryId]: string }
const newCatMode      = ref(false)
const newCatName      = ref('')
const newCatItemName  = ref('')
const saving          = reactive({})      // { [categoryId]: bool }
const savingCat       = ref(false)
const errors          = reactive({})      // { [categoryId]: string }
const catError        = ref('')

// Start all categories expanded
props.categories.forEach(c => { expanded[c.id] = true })

// ── computed ─────────────────────────────────────────────────────
const filteredCategories = computed(() => {
    const q = search.value.toLowerCase().trim()
    if (!q) return localCategories
    return localCategories
        .map(c => ({
            ...c,
            items: c.items.filter(i => i.name.toLowerCase().includes(q)),
        }))
        .filter(c => c.name.toLowerCase().includes(q) || c.items.length > 0)
})

const selectedCount = computed(() => props.modelValue.length)

// ── selection helpers ────────────────────────────────────────────
const toggle = (id) => {
    const cur = [...props.modelValue]
    const idx = cur.indexOf(id)
    idx === -1 ? cur.push(id) : cur.splice(idx, 1)
    emit('update:modelValue', cur)
}

const isCatAllSelected = (cat) => cat.items.length > 0 && cat.items.every(i => props.modelValue.includes(i.id))
const isCatPartial     = (cat) => cat.items.some(i => props.modelValue.includes(i.id)) && !isCatAllSelected(cat)

const toggleCategory = (cat) => {
    const cur = [...props.modelValue]
    if (isCatAllSelected(cat)) {
        cat.items.forEach(i => { const idx = cur.indexOf(i.id); if (idx > -1) cur.splice(idx, 1) })
    } else {
        cat.items.forEach(i => { if (!cur.includes(i.id)) cur.push(i.id) })
    }
    emit('update:modelValue', cur)
}

// ── quick-add item to existing category ─────────────────────────
const startAddItem = (catId) => {
    addingItem[catId] = true
    newItemName[catId] = ''
    errors[catId] = ''
}

const saveItem = async (catId) => {
    const name = (newItemName[catId] || '').trim()
    if (!name) { errors[catId] = 'Name required'; return }
    saving[catId] = true
    errors[catId] = ''
    try {
        const res = await window.axios.post(route('result-chain.disagregation-items.quick-add'), {
            category_id: catId,
            item_name: name,
        })
        const cat = localCategories.find(c => c.id === catId)
        if (cat) cat.items.push(res.data.item)
        addingItem[catId] = false
        newItemName[catId] = ''
    } catch (e) {
        errors[catId] = e.response?.data?.message || 'Error saving item'
    } finally {
        saving[catId] = false
    }
}

// ── quick-add new category + first item ─────────────────────────
const saveNewCategory = async () => {
    const catName  = newCatName.value.trim()
    const itemName = newCatItemName.value.trim()
    if (!catName) { catError.value = 'Category name required'; return }
    savingCat.value = true
    catError.value = ''
    try {
        const res = await window.axios.post(route('result-chain.disagregation-items.quick-add'), {
            category_name: catName,
            item_name: itemName || catName,
        })
        const existingCat = localCategories.find(c => c.id === res.data.category.id)
        if (existingCat) {
            existingCat.items.push(res.data.item)
        } else {
            localCategories.push({ ...res.data.category, items: [res.data.item] })
            expanded[res.data.category.id] = true
        }
        newCatMode.value = false
        newCatName.value = ''
        newCatItemName.value = ''
    } catch (e) {
        catError.value = e.response?.data?.message || 'Error saving category'
    } finally {
        savingCat.value = false
    }
}
</script>

<template>
    <div class="disagregation-selector">
        <!-- toolbar -->
        <div class="d-flex gap-2 mb-2 align-items-center">
            <div class="input-group input-group-sm flex-grow-1">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input v-model="search" type="text" class="form-control" placeholder="Search categories or items..." />
            </div>
            <span class="text-muted small text-nowrap">{{ selectedCount }} selected</span>
        </div>

        <!-- category list -->
        <div class="disagg-list border rounded">
            <div v-if="!filteredCategories.length" class="text-muted small p-3 text-center">
                No disaggregation items found.
            </div>

            <div v-for="cat in filteredCategories" :key="cat.id" class="disagg-category">
                <!-- category header row -->
                <div class="disagg-cat-header d-flex align-items-center px-3 py-2"
                    :class="{ 'border-bottom': expanded[cat.id] }">
                    <!-- tri-state checkbox for whole category -->
                    <input
                        class="form-check-input me-2 flex-shrink-0"
                        type="checkbox"
                        :id="`cat-${cat.id}`"
                        :checked="isCatAllSelected(cat)"
                        :indeterminate="isCatPartial(cat)"
                        @change="toggleCategory(cat)"
                    />
                    <label :for="`cat-${cat.id}`" class="fw-semibold small flex-grow-1 mb-0 user-select-none" style="cursor:pointer">
                        {{ cat.name }}
                        <span class="badge bg-light text-secondary ms-1">{{ cat.items.length }}</span>
                    </label>
                    <!-- toggle expand -->
                    <button type="button" class="btn btn-sm btn-link p-0 ms-2 text-muted"
                        @click="expanded[cat.id] = !expanded[cat.id]">
                        <i :class="expanded[cat.id] ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                    </button>
                </div>

                <!-- items -->
                <div v-show="expanded[cat.id]" class="disagg-items">
                    <div v-if="!cat.items.length" class="px-4 py-2 text-muted small fst-italic">No items yet.</div>

                    <div v-for="item in cat.items" :key="item.id"
                        class="form-check px-4 py-1 disagg-item"
                        :class="{ selected: modelValue.includes(item.id) }"
                        @click="toggle(item.id)">
                        <input class="form-check-input" type="checkbox"
                            :id="`di-${item.id}`" :value="item.id"
                            :checked="modelValue.includes(item.id)"
                            @change.stop="toggle(item.id)" />
                        <label class="form-check-label w-100 small" :for="`di-${item.id}`" style="cursor:pointer">
                            {{ item.name }}
                        </label>
                    </div>

                    <!-- inline add item -->
                    <div v-if="addingItem[cat.id]" class="px-4 py-2 border-top">
                        <div class="input-group input-group-sm">
                            <input v-model="newItemName[cat.id]" type="text" class="form-control"
                                placeholder="New item name..."
                                @keyup.enter="saveItem(cat.id)"
                                @keyup.escape="addingItem[cat.id] = false"
                                autofocus />
                            <button type="button" class="btn btn-sm btn-success"
                                :disabled="saving[cat.id]" @click="saveItem(cat.id)">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                @click="addingItem[cat.id] = false">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <small v-if="errors[cat.id]" class="text-danger">{{ errors[cat.id] }}</small>
                    </div>
                    <div v-else class="px-4 py-1">
                        <button type="button" class="btn btn-link btn-sm p-0 text-success"
                            @click="startAddItem(cat.id)">
                            <i class="bi bi-plus-circle me-1"></i>Add item
                        </button>
                    </div>
                </div>
            </div>

            <!-- add new category row -->
            <div class="disagg-category border-top">
                <div v-if="!newCatMode" class="px-3 py-2">
                    <button type="button" class="btn btn-link btn-sm p-0 text-success fw-semibold"
                        @click="newCatMode = true">
                        <i class="bi bi-plus-circle me-1"></i>New category
                    </button>
                </div>
                <div v-else class="px-3 py-2">
                    <div class="mb-2">
                        <input v-model="newCatName" type="text" class="form-control form-control-sm"
                            placeholder="Category name (e.g. Gender)"
                            @keyup.escape="newCatMode = false" />
                    </div>
                    <div class="mb-2">
                        <input v-model="newCatItemName" type="text" class="form-control form-control-sm"
                            placeholder="First item name (optional)"
                            @keyup.enter="saveNewCategory"
                            @keyup.escape="newCatMode = false" />
                    </div>
                    <small v-if="catError" class="text-danger d-block mb-1">{{ catError }}</small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" :disabled="savingCat" @click="saveNewCategory">
                            <i class="bi bi-check-lg me-1"></i>Save
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="newCatMode = false; catError = ''">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.disagg-list { max-height: 340px; overflow-y: auto; }
.disagg-cat-header { background-color: #f8f9fa; cursor: default; }
.disagg-cat-header:hover { background-color: #f0f0f0; }
.disagg-item { cursor: pointer; border-radius: 0; transition: background .1s; }
.disagg-item:hover { background-color: rgba(11,109,23,.06); }
.disagg-item.selected { background-color: rgba(11,109,23,.12); }
.btn-success { background-color: rgb(11,109,23); border-color: rgb(11,109,23); }
.btn-success:hover { background-color: rgb(9,87,18); border-color: rgb(9,87,18); }
.btn-link.text-success { color: rgb(11,109,23) !important; }
</style>
