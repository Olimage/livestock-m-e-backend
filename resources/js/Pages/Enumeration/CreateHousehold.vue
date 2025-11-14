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
    gps_coordinates: '',
    state: '',
    lga: '',
    geopolitical_zone: '',
    household_head_age: '',
    household_head_gender: '',
    marital_status: '',
    education_level: '',
    household_total: null,
    dependents: null,
    owns_livestock: '',
    cattle_count: 0,
    cattle_lactating_percent: null,
    sheep_count: 0,
    goats_count: 0,
    goats_lactating_percent: null,
    pigs_count: 0,
    chickens_count: 0,
    chickens_laying_percent: null,
    other_poultry_count: 0,
    other_livestock: '',
    keeping_type: '',
    management_pattern: '',
    primary_owner: '',
    production_system: '',
    production_orientation: '',
    mobility_pattern: '',
    feeding_strategy: '',
    feed_source: '',
    feed_formulation: '',
    housing_type: '',
    storage_facilities: '',
    primary_caretaker: '',
    cattle_milk_yield: null,
    goats_milk_yield: null,
    chickens_egg_production: null,
    cattle_live_weight: null,
    sheep_live_weight: null,
    goats_live_weight: null,
    pigs_live_weight: null,
    cattle_mortality: null,
    sheep_mortality: null,
    goats_mortality: null,
    pigs_mortality: null,
    chickens_mortality: null,
    mortality_causes: [],
    veterinary_access: '',
    sells_products: '',
    products_sold: [],
    sales_frequency: '',
    sales_channels: '',
    market_distance: null,
    transport_method: '',
    market_challenges: [],
    abattoir_access: '',
    primary_livelihood: '',
    livestock_income: '',
    income_share: '',
    income_stability: '',
    economic_dependency: '',
    _meta: { type: 'household', version: 2 }
  }
})

const ownsLivestock = computed(() => form.payload.owns_livestock === '1')

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

const submit = () => {
  form.post(route('enumerations.store', 'household'))
}
</script>

<template>
  <BeLayout>
    <Head title="New Household Enumeration" />
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-400">New Household Enumeration</h5>
      <Link :href="route('enumerations.index')" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</Link>
    </div>
    <hr />
    <form @submit.prevent="submit" class="card">
      <div class="card-body">
        <h6 class="section-title">Section A: Geographic & Identification</h6>
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
          <div class="col-md-6">
            <label class="form-label">GPS Coordinates</label>
            <input v-model="form.payload.gps_coordinates" type="text" class="form-control" placeholder="lat, lng" />
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <GetLocationButton @updated="applyLocation" />
          </div>
        </div>

        <h6 class="section-title">Section B: Household Demographics</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Head Age</label>
            <select v-model="form.payload.household_head_age" class="form-select" required>
              <option value="">Select</option>
              <option value="1">18-30 years</option>
              <option value="2">31-45 years</option>
              <option value="3">46-60 years</option>
              <option value="4">Above 60 years</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Head Gender</label>
            <select v-model="form.payload.household_head_gender" class="form-select" required>
              <option value="">Select</option>
              <option value="1">Male</option>
              <option value="2">Female</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Marital Status</label>
            <select v-model="form.payload.marital_status" class="form-select" required>
              <option value="">Select</option>
              <option value="1">Single</option>
              <option value="2">Married</option>
              <option value="3">Divorced/Separated</option>
              <option value="4">Widowed</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Education Level</label>
            <select v-model="form.payload.education_level" class="form-select" required>
              <option value="">Select</option>
              <option value="1">No formal education</option>
              <option value="2">Primary</option>
              <option value="3">Secondary</option>
              <option value="4">Tertiary</option>
              <option value="5">Adult literacy</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Household Size</label>
            <input v-model.number="form.payload.household_total" type="number" min="1" class="form-control" required />
          </div>
          <div class="col-md-3">
            <label class="form-label">Dependents</label>
            <input v-model.number="form.payload.dependents" type="number" min="0" class="form-control" required />
          </div>
        </div>

        <h6 class="section-title">Section C: Livestock Ownership</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Owns Livestock?</label>
            <select v-model="form.payload.owns_livestock" class="form-select" required>
              <option value="">Select</option>
              <option value="1">Yes</option>
              <option value="2">No</option>
            </select>
          </div>
        </div>

        <div v-if="ownsLivestock">
          <div class="row g-3 mb-2">
            <div class="col-md-3">
              <label class="form-label">Cattle Count</label>
              <input v-model.number="form.payload.cattle_count" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.cattle_count > 0">
              <label class="form-label">Lactating %</label>
              <input v-model.number="form.payload.cattle_lactating_percent" type="number" min="0" max="100" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Sheep Count</label>
              <input v-model.number="form.payload.sheep_count" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Goats Count</label>
              <input v-model.number="form.payload.goats_count" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.goats_count > 0">
              <label class="form-label">Goats Lactating %</label>
              <input v-model.number="form.payload.goats_lactating_percent" type="number" min="0" max="100" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Pigs Count</label>
              <input v-model.number="form.payload.pigs_count" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Chickens Count</label>
              <input v-model.number="form.payload.chickens_count" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.chickens_count > 0">
              <label class="form-label">Laying %</label>
              <input v-model.number="form.payload.chickens_laying_percent" type="number" min="0" max="100" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Other Poultry</label>
              <input v-model.number="form.payload.other_poultry_count" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Other Livestock</label>
              <input v-model="form.payload.other_livestock" type="text" class="form-control" />
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Keeping Type</label>
              <select v-model="form.payload.keeping_type" class="form-select">
                <option value="">Select</option>
                <option value="own">Own livestock</option>
                <option value="borrowed">Borrowed</option>
                <option value="shared">Shared ownership</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Management Pattern</label>
              <input v-model="form.payload.management_pattern" type="text" class="form-control" />
            </div>
            <div class="col-md-4">
              <label class="form-label">Primary Owner</label>
              <input v-model="form.payload.primary_owner" type="text" class="form-control" />
            </div>
          </div>

          <h6 class="section-title">Section D: Production Systems</h6>
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
              <label class="form-label">Mobility Pattern</label>
              <input v-model="form.payload.mobility_pattern" type="text" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Feeding Strategy</label>
              <input v-model="form.payload.feeding_strategy" type="text" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Feed Source</label>
              <input v-model="form.payload.feed_source" type="text" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Feed Formulation</label>
              <input v-model="form.payload.feed_formulation" type="text" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Housing Type</label>
              <input v-model="form.payload.housing_type" type="text" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Storage Facilities</label>
              <input v-model="form.payload.storage_facilities" type="text" class="form-control" />
            </div>
            <div class="col-md-3">
              <label class="form-label">Primary Caretaker</label>
              <input v-model="form.payload.primary_caretaker" type="text" class="form-control" />
            </div>
          </div>

          <h6 class="section-title">Section E: Productivity Performance</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-3" v-if="form.payload.cattle_count > 0">
              <label class="form-label">Cattle Milk Yield (L/day)</label>
              <input v-model.number="form.payload.cattle_milk_yield" type="number" min="0" step="0.1" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.goats_count > 0">
              <label class="form-label">Goat Milk Yield (L/day)</label>
              <input v-model.number="form.payload.goats_milk_yield" type="number" min="0" step="0.1" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.chickens_count > 0">
              <label class="form-label">Egg Production/hen</label>
              <input v-model.number="form.payload.chickens_egg_production" type="number" min="0" step="0.1" class="form-control" />
            </div>
          </div>
          <div class="row g-3 mb-2">
            <div class="col-md-3" v-if="form.payload.cattle_count > 0">
              <label class="form-label">Cattle Live Weight (kg)</label>
              <input v-model.number="form.payload.cattle_live_weight" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.sheep_count > 0">
              <label class="form-label">Sheep Live Weight (kg)</label>
              <input v-model.number="form.payload.sheep_live_weight" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.goats_count > 0">
              <label class="form-label">Goats Live Weight (kg)</label>
              <input v-model.number="form.payload.goats_live_weight" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.pigs_count > 0">
              <label class="form-label">Pigs Live Weight (kg)</label>
              <input v-model.number="form.payload.pigs_live_weight" type="number" min="0" class="form-control" />
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-3" v-if="form.payload.cattle_count > 0">
              <label class="form-label">Cattle Mortality %</label>
              <input v-model.number="form.payload.cattle_mortality" type="number" min="0" max="100" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.sheep_count > 0">
              <label class="form-label">Sheep Mortality %</label>
              <input v-model.number="form.payload.sheep_mortality" type="number" min="0" max="100" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.goats_count > 0">
              <label class="form-label">Goats Mortality %</label>
              <input v-model.number="form.payload.goats_mortality" type="number" min="0" max="100" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.pigs_count > 0">
              <label class="form-label">Pigs Mortality %</label>
              <input v-model.number="form.payload.pigs_mortality" type="number" min="0" max="100" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.chickens_count > 0">
              <label class="form-label">Chickens Mortality %</label>
              <input v-model.number="form.payload.chickens_mortality" type="number" min="0" max="100" class="form-control" />
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

          <h6 class="section-title">Section F: Marketing & Economics</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Sells Products?</label>
              <select v-model="form.payload.sells_products" class="form-select">
                <option value="">Select</option>
                <option value="1">Yes</option>
                <option value="2">No</option>
              </select>
            </div>
            <div class="col-md-9" v-if="form.payload.sells_products === '1'">
              <label class="form-label">Products Sold (comma separated)</label>
              <input type="text" class="form-control" @change="e => form.payload.products_sold = e.target.value.split(',').map(x=>x.trim()).filter(Boolean)" />
            </div>
            <div class="col-md-3" v-if="form.payload.sells_products === '1'">
              <label class="form-label">Sales Frequency</label>
              <input v-model="form.payload.sales_frequency" type="text" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.sells_products === '1'">
              <label class="form-label">Sales Channels</label>
              <input v-model="form.payload.sales_channels" type="text" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.sells_products === '1'">
              <label class="form-label">Market Distance (km)</label>
              <input v-model.number="form.payload.market_distance" type="number" min="0" class="form-control" />
            </div>
            <div class="col-md-3" v-if="form.payload.sells_products === '1'">
              <label class="form-label">Transport Method</label>
              <input v-model="form.payload.transport_method" type="text" class="form-control" />
            </div>
            <div class="col-md-6" v-if="form.payload.sells_products === '1'">
              <label class="form-label">Market Challenges (comma separated)</label>
              <input type="text" class="form-control" @change="e => form.payload.market_challenges = e.target.value.split(',').map(x=>x.trim()).filter(Boolean)" />
            </div>
            <div class="col-md-6" v-if="form.payload.sells_products === '1'">
              <label class="form-label">Abattoir Access</label>
              <input v-model="form.payload.abattoir_access" type="text" class="form-control" />
            </div>
          </div>
        </div>

        <h6 class="section-title">Section G: Household Economics & Livelihoods</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Primary Livelihood</label>
            <input v-model="form.payload.primary_livelihood" type="text" class="form-control" required />
          </div>
          <div class="col-md-4" v-if="ownsLivestock">
            <label class="form-label">Livestock Income</label>
            <input v-model="form.payload.livestock_income" type="text" class="form-control" />
          </div>
          <div class="col-md-4" v-if="ownsLivestock">
            <label class="form-label">Income Share</label>
            <input v-model="form.payload.income_share" type="text" class="form-control" />
          </div>
          <div class="col-md-4" v-if="ownsLivestock">
            <label class="form-label">Income Stability</label>
            <input v-model="form.payload.income_stability" type="text" class="form-control" />
          </div>
          <div class="col-md-4" v-if="ownsLivestock">
            <label class="form-label">Economic Dependency</label>
            <input v-model="form.payload.economic_dependency" type="text" class="form-control" />
          </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
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
