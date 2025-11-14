<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import BeLayout from '../../Layouts/BeLayout.vue'
import GetLocationButton from '../../Components/GetLocationButton.vue'

const props = defineProps({
  formType: String,
  formTypes: Array
})

const rawPayload = ref('{\n  "example_key": "value"\n}')

const form = useForm({
  latitude: null,
  longitude: null,
  device_id: null,
  payload: {}
})

const applyLocation = (loc) => {
  form.latitude = loc.latitude
  form.longitude = loc.longitude
}

const buildPayload = () => {
  let base = {}
  try {
    base = JSON.parse(rawPayload.value || '{}')
  } catch (e) {
    alert('Invalid JSON in raw payload area')
    return null
  }
  // Minimal identity fields per form type (extend as needed)
  if (props.formType === 'household') {
    base._meta = { type: 'household', version: 1 }
  } else if (props.formType === 'market') {
    base._meta = { type: 'market', version: 1 }
  } else if (props.formType === 'commercial_farm') {
    base._meta = { type: 'commercial_farm', version: 1 }
  }
  return base
}

const submit = () => {
  const payload = buildPayload()
  if (!payload) return
  form.payload = payload
  form.post(route('enumerations.store', props.formType))
}
</script>

<template>
  <BeLayout>
    <Head :title="`New ${formType.replace('_',' ')} Enumeration`" />
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-400">New {{ formType.replace('_',' ') }} Enumeration</h5>
      <Link :href="route('enumerations.index')" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</Link>
    </div>
    <hr />

    <div class="alert alert-info small">
      Auth user & enumerator details are injected server-side. Provide survey data below (or paste full JSON from mobile client). Geolocation is optional but recommended.
    </div>

    <form @submit.prevent="submit" class="card">
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Device ID (optional)</label>
          <input v-model="form.device_id" type="text" class="form-control" placeholder="Device identifier" />
        </div>
        <div class="mb-3">
          <label class="form-label">Raw Payload (JSON)</label>
          <textarea v-model="rawPayload" rows="10" class="form-control font-monospace"></textarea>
          <div class="form-text">Paste or edit survey JSON. Minimal _meta will be auto-added.</div>
        </div>
        <div class="mb-3">
          <GetLocationButton @updated="applyLocation" />
          <div class="small text-muted" v-if="form.latitude && form.longitude">
            Will store: {{ form.latitude }}, {{ form.longitude }}
          </div>
        </div>
        <div class="d-flex justify-content-end">
          <button class="btn btn-success" :disabled="form.processing">
            <span v-if="!form.processing"><i class="bi bi-save"></i> Save Record</span>
            <span v-else>Saving...</span>
          </button>
        </div>
      </div>
    </form>
  </BeLayout>
</template>

<style scoped>
textarea { font-size: 13px; }
</style>
