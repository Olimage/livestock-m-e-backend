<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const props = defineProps({
  indicator: Object,
  presidentialPriorities: Array,
  sectoralGoals: Array,
  bondOutcomes: Array,
  nlgasPillars: Array,
  departments: Array
})

const form = useForm({
  code: props.indicator.code,
  title: props.indicator.title,
  description: props.indicator.description || '',
  presidential_priority_id: props.indicator.presidential_priority_id,
  sectoral_goal_id: props.indicator.sectoral_goal_id,
  bond_outcome_id: props.indicator.bond_outcome_id,
  nlgas_pillar_id: props.indicator.nlgas_pillar_id,
  department_id: props.indicator.department_id,
  indicator_type: props.indicator.indicator_type,
  measurement_unit: props.indicator.measurement_unit || '',
  baseline_value: props.indicator.baseline_value,
  baseline_year: props.indicator.baseline_year,
  target_value: props.indicator.target_value,
  target_year: props.indicator.target_year,
  data_source: props.indicator.data_source || '',
  collection_frequency: props.indicator.collection_frequency || '',
  responsible_entity: props.indicator.responsible_entity || '',
  tier_level: props.indicator.tier_level,
})

const submit = () => {
  form.put(route('programs.indicators.update', props.indicator.id))
}
</script>

<template>
  <BeLayout>
    <Head title="Edit Indicator" />
    <h5 class="mt-4">Edit Indicator</h5>
    <hr />
    <div class="card">
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label">Code *</label>
              <input v-model="form.code" type="text" class="form-control" />
              <small class="text-danger">{{ form.errors.code }}</small>
            </div>
            <div class="col-md-9 mb-3">
              <label class="form-label">Title *</label>
              <input v-model="form.title" type="text" class="form-control" />
              <small class="text-danger">{{ form.errors.title }}</small>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea v-model="form.description" class="form-control" rows="3" />
            <small class="text-danger">{{ form.errors.description }}</small>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Presidential Priority</label>
              <select v-model="form.presidential_priority_id" class="form-select">
                <option :value="null">-- Select Priority --</option>
                <option v-for="p in props.presidentialPriorities" :key="p.id" :value="p.id">{{ p.code }} - {{ p.title }}</option>
              </select>
              <small class="text-danger">{{ form.errors.presidential_priority_id }}</small>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Sectoral Goal</label>
              <select v-model="form.sectoral_goal_id" class="form-select">
                <option :value="null">-- Select Goal --</option>
                <option v-for="g in props.sectoralGoals" :key="g.id" :value="g.id">{{ g.code }} - {{ g.title }}</option>
              </select>
              <small class="text-danger">{{ form.errors.sectoral_goal_id }}</small>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Bond Outcome</label>
              <select v-model="form.bond_outcome_id" class="form-select">
                <option :value="null">-- Select Outcome --</option>
                <option v-for="b in props.bondOutcomes" :key="b.id" :value="b.id">{{ b.code }} - {{ b.title }}</option>
              </select>
              <small class="text-danger">{{ form.errors.bond_outcome_id }}</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Pillar</label>
              <select v-model="form.nlgas_pillar_id" class="form-select">
                <option :value="null">-- Select Pillar --</option>
                <option v-for="p in props.nlgasPillars" :key="p.id" :value="p.id">{{ p.code }} - {{ p.title }}</option>
              </select>
              <small class="text-danger">{{ form.errors.nlgas_pillar_id }}</small>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Department</label>
              <select v-model="form.department_id" class="form-select">
                <option :value="null">-- Select Department --</option>
                <option v-for="d in props.departments" :key="d.id" :value="d.id">{{ d.name }}</option>
              </select>
              <small class="text-danger">{{ form.errors.department_id }}</small>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Indicator Type</label>
              <select v-model="form.indicator_type" class="form-select">
                <option :value="null">-- Select Type --</option>
                <option value="output">Output</option>
                <option value="outcome">Outcome</option>
                <option value="impact">Impact</option>
              </select>
              <small class="text-danger">{{ form.errors.indicator_type }}</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label">Measurement Unit</label>
              <input v-model="form.measurement_unit" type="text" class="form-control" />
              <small class="text-danger">{{ form.errors.measurement_unit }}</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Baseline Value</label>
              <input v-model="form.baseline_value" type="number" step="0.01" class="form-control" />
              <small class="text-danger">{{ form.errors.baseline_value }}</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Baseline Year</label>
              <input v-model="form.baseline_year" type="number" class="form-control" />
              <small class="text-danger">{{ form.errors.baseline_year }}</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Target Value</label>
              <input v-model="form.target_value" type="number" step="0.01" class="form-control" />
              <small class="text-danger">{{ form.errors.target_value }}</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label">Target Year</label>
              <input v-model="form.target_year" type="number" class="form-control" />
              <small class="text-danger">{{ form.errors.target_year }}</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Data Source</label>
              <input v-model="form.data_source" type="text" class="form-control" />
              <small class="text-danger">{{ form.errors.data_source }}</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Collection Frequency</label>
              <input v-model="form.collection_frequency" type="text" class="form-control" placeholder="e.g. Quarterly" />
              <small class="text-danger">{{ form.errors.collection_frequency }}</small>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Tier Level</label>
              <input v-model="form.tier_level" type="number" class="form-control" />
              <small class="text-danger">{{ form.errors.tier_level }}</small>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Responsible Entity</label>
            <input v-model="form.responsible_entity" type="text" class="form-control" />
            <small class="text-danger">{{ form.errors.responsible_entity }}</small>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-success" :disabled="form.processing">
              <i class="bi bi-check-circle"></i> Update
            </button>
            <Link :href="route('programs.indicators.index')" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left"></i> Back
            </Link>
          </div>
        </form>
      </div>
    </div>
  </BeLayout>
</template>
