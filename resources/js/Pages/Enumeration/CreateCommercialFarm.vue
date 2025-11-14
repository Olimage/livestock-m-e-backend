<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'
import GetLocationButton from '../../Components/GetLocationButton.vue'
import { computed, ref, watch, onMounted } from 'vue'

const form = useForm({
  latitude: null,
  longitude: null,
  device_id: null,
  payload: {
    farm_name: '',
    gps_coordinates: '',
    state: '',
    lga: '',
    geopolitical_zone: '',
    manager_age: '',
    manager_gender: '',
    education_level: '',
    experience_years: null,
    livestock_types: [],
    beef_cattle: 0,
    beef_cattle_breeding: null,
    beef_cattle_production: null,
    dairy_cattle: 0,
    dairy_cattle_breeding: null,
    dairy_milk_capacity: null,
    sheep: 0,
    sheep_breeding: null,
    sheep_production: null,
    goats: 0,
    goats_breeding: null,
    goats_production: null,
    pigs: 0,
    pigs_breeding: null,
    pigs_production: null,
    layers: 0,
    layers_egg_capacity: null,
    broilers: 0,
    broilers_production: null,
    other_poultry: 0,
    other_poultry_production: null,
    other_species: 0,
    other_species_production: null,
    business_type: '',
    business_structure: '',
    specialization: '',
    production_system: '',
    production_orientation: '',
    management_pattern: '',
    feeding_strategy: '',
    feed_sources: '',
    feed_formulation: '',
    own_feed_mill: '',
    infrastructure: '',
    storage_facilities: '',
    labor_management: '',
    milk_yield_per_cow: null,
    eggs_per_hen: null,
    beef_daily_gain: null,
    pigs_daily_gain: null,
    feed_conversion_ratio: null,
    reproduction_rate: null,
    cattle_mortality: null,
    pigs_mortality: null,
    chickens_mortality: null,
    small_ruminants_mortality: null,
    mortality_causes: [],
    veterinary_access: '',
    products_marketed: [],
    sales_frequency: '',
    sales_channels: '',
    market_reach: '',
    transportation: '',
    market_challenges: [],
    processing_facilities: '',
    business_focus: '',
    annual_revenue: '',
    revenue_share: '',
    growth_pattern: '',
    technology_level: '',
    employees: '',
    financing_source: '',
    business_challenges: [],
    _meta: { type: 'commercial_farm', version: 2 }
  }
})

const lt = computed(() => form.payload.livestock_types)

const applyLocation = (loc) => {
  form.latitude = loc.latitude
  form.longitude = loc.longitude
  form.payload.gps_coordinates = `${loc.latitude}, ${loc.longitude}`
}

// Dynamic selectors
const zones = ref([])
const states = ref([])
const lgas = ref([])
const selectedZone = ref('')
const selectedState = ref('')
const selectedLga = ref('')

onMounted(async () => {
  const res = await fetch(route('location.zones'))
  zones.value = await res.json()
})

watch(selectedZone, async (zoneId) => {
  if (!zoneId) { states.value = []; selectedState.value = ''; lgas.value = []; selectedLga.value = ''; return }
  const res = await fetch(route('location.states', { zone_id: zoneId }))
  states.value = await res.json()
  selectedState.value = ''
  lgas.value = []
  selectedLga.value = ''
})

watch(selectedState, async (stateId) => {
  if (!stateId) { lgas.value = []; selectedLga.value = ''; return }
  const res = await fetch(route('location.lgas', { state_id: stateId }))
  lgas.value = await res.json()
  selectedLga.value = ''
})

watch(selectedZone, (zoneId) => {
  form.payload.geopolitical_zone = zones.value.find(z => z.id == zoneId)?.name || ''
})
watch(selectedState, (stateId) => {
  form.payload.state = states.value.find(s => s.id == stateId)?.name || ''
})
watch(selectedLga, (lgaId) => {
  form.payload.lga = lgas.value.find(l => l.id == lgaId)?.name || ''
})

const submit = () => form.post(route('enumerations.store', 'commercial_farm'))
</script>

<template>
  <BeLayout>
    <Head title="New Commercial Farm Enumeration" />
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-400">New Commercial Farm Enumeration</h5>
      <Link :href="route('enumerations.index')" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</Link>
    </div>
    <hr />
    <form @submit.prevent="submit" class="card">
      <div class="card-body">
        <h6 class="section-title">Section A: Identification & Location</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Geopolitical Zone</label>
            <select v-model="selectedZone" class="form-select" required>
              <option value="">Select zone</option>
              <option v-for="z in zones" :key="z.id" :value="z.id">{{ z.name }}</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">State</label>
            <select v-model="selectedState" class="form-select" :disabled="!selectedZone" required>
              <option value="">Select state</option>
              <option v-for="s in states" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">LGA</label>
            <select v-model="selectedLga" class="form-select" :disabled="!selectedState" required>
              <option value="">Select LGA</option>
              <option v-for="l in lgas" :key="l.id" :value="l.id">{{ l.name }}</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Farm Name / Reg No</label>
            <input v-model="form.payload.farm_name" type="text" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">GPS Coordinates</label>
            <input v-model="form.payload.gps_coordinates" type="text" class="form-control" placeholder="lat, lng" />
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <GetLocationButton @updated="applyLocation" />
          </div>
        </div>

        <h6 class="section-title">Section B: Management & Demographics</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Manager Age</label>
            <select v-model="form.payload.manager_age" class="form-select" required>
              <option value="">Select</option>
              <option value="under_30">Under 30</option>
              <option value="30_45">30 - 45</option>
              <option value="46_60">46 - 60</option>
              <option value="60_plus">60+</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Gender</label>
            <select v-model="form.payload.manager_gender" class="form-select" required>
              <option value="">Select</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Education Level</label>
            <select v-model="form.payload.education_level" class="form-select" required>
              <option value="">Select</option>
              <option value="none">None</option>
              <option value="primary">Primary</option>
              <option value="secondary">Secondary</option>
              <option value="tertiary">Tertiary</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Years Experience</label>
            <input v-model.number="form.payload.experience_years" type="number" min="0" class="form-control" required />
          </div>
        </div>

        <h6 class="section-title">Section C: Livestock Populations</h6>
        <div class="mb-2">
          <div class="d-flex flex-wrap gap-3">
            <label><input type="checkbox" value="beef_cattle" v-model="form.payload.livestock_types" /> Beef</label>
            <label><input type="checkbox" value="dairy_cattle" v-model="form.payload.livestock_types" /> Dairy</label>
            <label><input type="checkbox" value="sheep" v-model="form.payload.livestock_types" /> Sheep</label>
            <label><input type="checkbox" value="goats" v-model="form.payload.livestock_types" /> Goats</label>
            <label><input type="checkbox" value="pigs" v-model="form.payload.livestock_types" /> Pigs</label>
            <label><input type="checkbox" value="layers" v-model="form.payload.livestock_types" /> Layers</label>
            <label><input type="checkbox" value="broilers" v-model="form.payload.livestock_types" /> Broilers</label>
            <label><input type="checkbox" value="other_poultry" v-model="form.payload.livestock_types" /> Other Poultry</label>
            <label><input type="checkbox" value="other_species" v-model="form.payload.livestock_types" /> Other Species</label>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-2" v-if="lt.includes('beef_cattle')">
            <label class="form-label">Beef Cattle</label>
            <input v-model.number="form.payload.beef_cattle" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2" v-if="lt.includes('dairy_cattle')">
            <label class="form-label">Dairy Cattle</label>
            <input v-model.number="form.payload.dairy_cattle" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2" v-if="lt.includes('sheep')">
            <label class="form-label">Sheep</label>
            <input v-model.number="form.payload.sheep" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2" v-if="lt.includes('goats')">
            <label class="form-label">Goats</label>
            <input v-model.number="form.payload.goats" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2" v-if="lt.includes('pigs')">
            <label class="form-label">Pigs</label>
            <input v-model.number="form.payload.pigs" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2" v-if="lt.includes('layers')">
            <label class="form-label">Layers</label>
            <input v-model.number="form.payload.layers" type="number" class="form-control" />
          </div>
          <div class="col-md-2" v-if="lt.includes('broilers')">
            <label class="form-label">Broilers</label>
            <input v-model.number="form.payload.broilers" type="number" min="0" class="form-control" />
          </div>
        </div>

        <h6 class="section-title mt-4">Section D: Production Systems</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Production System</label>
            <select v-model="form.payload.production_system" class="form-select">
              <option value="">Select</option>
              <option value="intensive">Intensive</option>
              <option value="semi_intensive">Semi-Intensive</option>
              <option value="extensive">Extensive</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Orientation</label>
            <select v-model="form.payload.production_orientation" class="form-select">
              <option value="">Select</option>
              <option value="commercial">Commercial</option>
              <option value="subsistence">Subsistence</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Management Pattern</label>
            <input v-model="form.payload.management_pattern" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Feeding Strategy</label>
            <input v-model="form.payload.feeding_strategy" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Feed Sources</label>
            <input v-model="form.payload.feed_sources" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Feed Formulation</label>
            <input v-model="form.payload.feed_formulation" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Own Feed Mill</label>
            <select v-model="form.payload.own_feed_mill" class="form-select">
              <option value="">Select</option>
              <option value="yes">Yes</option>
              <option value="no">No</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Storage Facilities</label>
            <input v-model="form.payload.storage_facilities" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Labor Management</label>
            <input v-model="form.payload.labor_management" type="text" class="form-control" />
          </div>
        </div>

        <h6 class="section-title">Section E: Productivity Performance</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3" v-if="form.payload.dairy_cattle > 0">
            <label class="form-label">Milk Yield/Cow</label>
            <input v-model.number="form.payload.milk_yield_per_cow" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3" v-if="form.payload.layers > 0">
            <label class="form-label">Eggs/Hen</label>
            <input v-model.number="form.payload.eggs_per_hen" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3" v-if="form.payload.beef_cattle > 0">
            <label class="form-label">Beef Daily Gain (g)</label>
            <input v-model.number="form.payload.beef_daily_gain" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3" v-if="form.payload.pigs > 0">
            <label class="form-label">Pigs Daily Gain (g)</label>
            <input v-model.number="form.payload.pigs_daily_gain" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3" v-if="form.payload.broilers > 0">
            <label class="form-label">Feed Conversion Ratio</label>
            <input v-model.number="form.payload.feed_conversion_ratio" type="number" min="0" step="0.01" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Reproduction Rate</label>
            <input v-model.number="form.payload.reproduction_rate" type="number" min="0" class="form-control" />
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-3" v-if="(form.payload.beef_cattle > 0) || (form.payload.dairy_cattle > 0)">
            <label class="form-label">Cattle Mortality %</label>
            <input v-model.number="form.payload.cattle_mortality" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3" v-if="form.payload.pigs > 0">
            <label class="form-label">Pigs Mortality %</label>
            <input v-model.number="form.payload.pigs_mortality" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3" v-if="(form.payload.layers > 0) || (form.payload.broilers > 0)">
            <label class="form-label">Chickens Mortality %</label>
            <input v-model.number="form.payload.chickens_mortality" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3" v-if="(form.payload.sheep > 0) || (form.payload.goats > 0)">
            <label class="form-label">Small Ruminants Mortality %</label>
            <input v-model.number="form.payload.small_ruminants_mortality" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Mortality Causes (comma separated)</label>
            <input type="text" class="form-control" @change="e => form.payload.mortality_causes = e.target.value.split(',').map(x=>x.trim()).filter(Boolean)" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Veterinary Access</label>
            <input v-model="form.payload.veterinary_access" type="text" class="form-control" />
          </div>
        </div>

        <h6 class="section-title">Section F: Market & Operations</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Sales Frequency</label>
            <input v-model="form.payload.sales_frequency" type="text" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Sales Channels</label>
            <input v-model="form.payload.sales_channels" type="text" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Market Reach</label>
            <input v-model="form.payload.market_reach" type="text" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Transportation</label>
            <input v-model="form.payload.transportation" type="text" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Processing Facilities</label>
            <input v-model="form.payload.processing_facilities" type="text" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Products Marketed (comma separated)</label>
            <input type="text" class="form-control" @change="e => form.payload.products_marketed = e.target.value.split(',').map(x=>x.trim()).filter(Boolean)" />
          </div>
          <div class="col-md-12">
            <label class="form-label">Market Challenges (comma separated)</label>
            <input type="text" class="form-control" @change="e => form.payload.market_challenges = e.target.value.split(',').map(x=>x.trim()).filter(Boolean)" />
          </div>
        </div>

        <h6 class="section-title">Section G: Socio-Economic & Business</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Business Focus</label>
            <input v-model="form.payload.business_focus" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Annual Revenue</label>
            <input v-model="form.payload.annual_revenue" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Revenue Share</label>
            <input v-model="form.payload.revenue_share" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Growth Pattern</label>
            <input v-model="form.payload.growth_pattern" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Technology Level</label>
            <input v-model="form.payload.technology_level" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Employees</label>
            <input v-model="form.payload.employees" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Financing Source</label>
            <input v-model="form.payload.financing_source" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Business Challenges (comma separated)</label>
            <input type="text" class="form-control" @change="e => form.payload.business_challenges = e.target.value.split(',').map(x=>x.trim()).filter(Boolean)" />
          </div>
        </div>

        <div class="d-flex justify-content-end mt-2">
          <button class="btn btn-success" :disabled="form.processing">
            <i class="bi bi-save"></i>
            <span v-if="!form.processing"> Save Record</span>
            <span v-else> Saving...</span>
          </button>
        </div>
      </div>
    </form>
  </BeLayout>
</template>

<style scoped>
.fw-400 { font-weight: 400; }
.section-title { font-weight:600; margin-top:10px; margin-bottom:8px; }
</style>
