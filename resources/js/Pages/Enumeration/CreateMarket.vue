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
    marketName: '',
    location: '',
    respondentName: '',
    rolePosition: '',
    gps_coordinates: '',
    
    // Section A: Livestock Volumes and Species
    A1: '',
    A1a: [],
    A1a_other: '',
    A2: 0, A2a: 0, A2b: 0,
    A3: 0, A3a: 0, A3b: 0,
    A4: 0, A4a: 0, A4b: 0,
    A5: 0, A5a: 0, A5b: 0,
    A6: 0, A6a: 0, A6b: 0,
    A7: 0, A7a: 0, A7b: 0,
    
    // Section B: Livestock Products
    B1: '',
    B2: 0,
    B3: 0,
    
    // Section C: Supply Sources
    C1_households: '',
    C1_commercial: '',
    C1_cooperatives: '',
    C1_intermediaries: '',
    C1_other_rank: '',
    C1_other_specify: '',
    
    // Section D: Livestock Quality and Health
    D1_cattle: 0, D1_goats: 0, D1_sheep: 0, D1_pigs: 0, D1_chickens: 0, D1_other_poultry: 0,
    D2: 0, D2a: 0,
    D3: 0, D4: 0,
    D5: '', D5a: 0, D5b: 0,
    
    // Section E: Pricing and Payments
    E1_cattle: 0, E1_goats: 0, E1_sheep: 0, E1_pigs: 0, E1_chickens: 0, E1_other_poultry: 0,
    E2_cattle: 0, E2_goats: 0, E2_sheep: 0, E2_pigs: 0, E2_chickens: 0, E2_other_poultry: 0,
    E3_milk: 0, E3_eggs: 0, E3_hides: 0, E3_meat: 0, E3_other: 0,
    E4_milk: 0, E4_eggs: 0, E4_hides: 0, E4_meat: 0, E4_other: 0,
    E5_cash: '', E5_credit: '', E5_contract: '', E5_other_rank: '', E5_other_specify: '',
    E6_amount: 0, E6_per: '',
    E7_clients: 0, E7_total: 0,
    
    // Section F: Market Infrastructure
    F1: '', F1a: 0,
    F2: '', F2a: '', F2b: '',
    F3: 0,
    F4: '', F5: '', F6: '', F6a: 0,
    
    // Section G: Logistics and Seasonality
    G1: 0, G2: 0,
    G3: '',
    G4: 0, G5: 0, G6: 0, G7: 0,
    
    // Section H: Security
    H1: 0, H2: [],
    
    // Survey Completion
    enumerator_name: '',
    interview_date: '',
    start_time: '',
    end_time: '',
    
    _meta: { type: 'market', version: 2 }
  }
})

const hasLivestock = computed(() => form.payload.A1 === '1')
const hasProducts = computed(() => form.payload.B1 === '1')
const hasHolding = computed(() => form.payload.F1 === '1')
const hasAbattoir = computed(() => form.payload.F2 === '1')
const hasVetServices = computed(() => form.payload.F6 === '1')
const hasSecurityIncidents = computed(() => form.payload.H1 > 0)

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
  form.post(route('enumerations.store', 'market'))
}
</script>

<template>
  <BeLayout>
    <Head title="New Market Enumeration" />
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-400">New Market Enumeration</h5>
      <Link :href="route('enumerations.index')" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</Link>
    </div>
    <hr />
    <form @submit.prevent="submit" class="card">
      <div class="card-body">
        <h6 class="section-title">Respondent Information</h6>
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
            <label class="form-label">Market Name</label>
            <input v-model="form.payload.marketName" type="text" class="form-control" required />
          </div>
          <div class="col-md-3">
            <label class="form-label">Location</label>
            <input v-model="form.payload.location" type="text" class="form-control" required />
          </div>
          <div class="col-md-3">
            <label class="form-label">Respondent Name</label>
            <input v-model="form.payload.respondentName" type="text" class="form-control" required />
          </div>
          <div class="col-md-3">
            <label class="form-label">Role / Position</label>
            <input v-model="form.payload.rolePosition" type="text" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">GPS Coordinates</label>
            <input v-model="form.payload.gps_coordinates" type="text" class="form-control" placeholder="lat, lng" />
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <GetLocationButton @updated="applyLocation" />
          </div>
        </div>

        <h6 class="section-title">Section A: Livestock Volumes and Species</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">A1. Are livestock sold in this market?</label>
            <select v-model="form.payload.A1" class="form-select" required>
              <option value="">Select</option>
              <option value="1">Yes</option>
              <option value="2">No</option>
            </select>
          </div>
        </div>

        <div v-if="hasLivestock">
          <div class="row g-3 mb-2">
            <div class="col-12">
              <label class="form-label">A1a. Which livestock species are sold? (comma-separated)</label>
              <input v-model="form.payload.A1a_other" type="text" class="form-control" 
                     @change="form.payload.A1a = $event.target.value.split(',').map(s => s.trim()).filter(Boolean)" 
                     placeholder="e.g., Cattle, Goats, Sheep, Pigs, Chickens, Other Poultry" />
            </div>
          </div>

          <div class="livestock-volumes">
            <h6 class="mt-3 mb-2">A2. Cattle</h6>
            <div class="row g-3 mb-2">
              <div class="col-md-4">
                <label class="form-label">Weekly volume</label>
                <input v-model.number="form.payload.A2" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Male</label>
                <input v-model.number="form.payload.A2a" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Female</label>
                <input v-model.number="form.payload.A2b" type="number" min="0" class="form-control" />
              </div>
            </div>

            <h6 class="mt-3 mb-2">A3. Goats</h6>
            <div class="row g-3 mb-2">
              <div class="col-md-4">
                <label class="form-label">Weekly volume</label>
                <input v-model.number="form.payload.A3" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Male</label>
                <input v-model.number="form.payload.A3a" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Female</label>
                <input v-model.number="form.payload.A3b" type="number" min="0" class="form-control" />
              </div>
            </div>

            <h6 class="mt-3 mb-2">A4. Sheep</h6>
            <div class="row g-3 mb-2">
              <div class="col-md-4">
                <label class="form-label">Weekly volume</label>
                <input v-model.number="form.payload.A4" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Male</label>
                <input v-model.number="form.payload.A4a" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Female</label>
                <input v-model.number="form.payload.A4b" type="number" min="0" class="form-control" />
              </div>
            </div>

            <h6 class="mt-3 mb-2">A5. Pigs</h6>
            <div class="row g-3 mb-2">
              <div class="col-md-4">
                <label class="form-label">Weekly volume</label>
                <input v-model.number="form.payload.A5" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Male</label>
                <input v-model.number="form.payload.A5a" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Female</label>
                <input v-model.number="form.payload.A5b" type="number" min="0" class="form-control" />
              </div>
            </div>

            <h6 class="mt-3 mb-2">A6. Chickens</h6>
            <div class="row g-3 mb-2">
              <div class="col-md-4">
                <label class="form-label">Weekly volume</label>
                <input v-model.number="form.payload.A6" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Male</label>
                <input v-model.number="form.payload.A6a" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Female</label>
                <input v-model.number="form.payload.A6b" type="number" min="0" class="form-control" />
              </div>
            </div>

            <h6 class="mt-3 mb-2">A7. Other Poultry</h6>
            <div class="row g-3 mb-2">
              <div class="col-md-4">
                <label class="form-label">Weekly volume</label>
                <input v-model.number="form.payload.A7" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Male</label>
                <input v-model.number="form.payload.A7a" type="number" min="0" class="form-control" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Female</label>
                <input v-model.number="form.payload.A7b" type="number" min="0" class="form-control" />
              </div>
            </div>
          </div>
        </div>

        <h6 class="section-title">Section B: Livestock Products</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">B1. Are livestock products sold?</label>
            <select v-model="form.payload.B1" class="form-select" required>
              <option value="">Select</option>
              <option value="1">Yes</option>
              <option value="2">No</option>
            </select>
          </div>
        </div>
        <div v-if="hasProducts" class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">B2. Milk volume (liters/week)</label>
            <input v-model.number="form.payload.B2" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-6">
            <label class="form-label">B3. Eggs volume (crates/week)</label>
            <input v-model.number="form.payload.B3" type="number" min="0" class="form-control" />
          </div>
        </div>

        <h6 class="section-title">Section C: Supply Sources</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-12">
            <label class="form-label">C1. Rank supply sources (1=primary, 5=least)</label>
          </div>
          <div class="col-md-6">
            <label class="form-label">Smallholder households</label>
            <select v-model="form.payload.C1_households" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Commercial farms</label>
            <select v-model="form.payload.C1_commercial" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Cooperatives</label>
            <select v-model="form.payload.C1_cooperatives" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Intermediaries/traders</label>
            <select v-model="form.payload.C1_intermediaries" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Other (rank)</label>
            <select v-model="form.payload.C1_other_rank" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Other (specify)</label>
            <input v-model="form.payload.C1_other_specify" type="text" class="form-control" />
          </div>
        </div>

        <h6 class="section-title">Section D: Livestock Quality and Health</h6>
        <div class="row g-3 mb-2">
          <div class="col-12"><label class="form-label fw-bold">D1. Average weight at sale (kg)</label></div>
          <div class="col-md-2">
            <label class="form-label">Cattle</label>
            <input v-model.number="form.payload.D1_cattle" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Goats</label>
            <input v-model.number="form.payload.D1_goats" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Sheep</label>
            <input v-model.number="form.payload.D1_sheep" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Pigs</label>
            <input v-model.number="form.payload.D1_pigs" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Chickens</label>
            <input v-model.number="form.payload.D1_chickens" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Other Poultry</label>
            <input v-model.number="form.payload.D1_other_poultry" type="number" min="0" class="form-control" />
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">D2. Rejected animals/week</label>
            <input v-model.number="form.payload.D2" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-4" v-if="form.payload.D2 > 0">
            <label class="form-label">D2a. Due to disease/health</label>
            <input v-model.number="form.payload.D2a" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">D3. Disease outbreaks/year</label>
            <input v-model.number="form.payload.D3" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">D4. Animals quarantined/week</label>
            <input v-model.number="form.payload.D4" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">D5. Health certificate required?</label>
            <select v-model="form.payload.D5" class="form-select">
              <option value="">Select</option>
              <option value="1">Yes</option>
              <option value="2">No</option>
            </select>
          </div>
          <div class="col-md-4" v-if="form.payload.D5 === '1'">
            <label class="form-label">D5a. Compliance %</label>
            <input v-model.number="form.payload.D5a" type="number" min="0" max="100" class="form-control" />
          </div>
          <div class="col-md-4" v-if="form.payload.D5 === '1'">
            <label class="form-label">D5b. Certificate cost (NGN)</label>
            <input v-model.number="form.payload.D5b" type="number" min="0" class="form-control" />
          </div>
        </div>

        <h6 class="section-title">Section E: Pricing and Payments</h6>
        <div class="row g-3 mb-2">
          <div class="col-12"><label class="form-label fw-bold">E1. Average price per head (NGN)</label></div>
          <div class="col-md-2">
            <label class="form-label">Cattle</label>
            <input v-model.number="form.payload.E1_cattle" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Goats</label>
            <input v-model.number="form.payload.E1_goats" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Sheep</label>
            <input v-model.number="form.payload.E1_sheep" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Pigs</label>
            <input v-model.number="form.payload.E1_pigs" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Chickens</label>
            <input v-model.number="form.payload.E1_chickens" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Other Poultry</label>
            <input v-model.number="form.payload.E1_other_poultry" type="number" min="0" class="form-control" />
          </div>
        </div>
        <div class="row g-3 mb-2">
          <div class="col-12"><label class="form-label fw-bold">E2. Maximum price per head (NGN)</label></div>
          <div class="col-md-2">
            <label class="form-label">Cattle</label>
            <input v-model.number="form.payload.E2_cattle" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Goats</label>
            <input v-model.number="form.payload.E2_goats" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Sheep</label>
            <input v-model.number="form.payload.E2_sheep" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Pigs</label>
            <input v-model.number="form.payload.E2_pigs" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Chickens</label>
            <input v-model.number="form.payload.E2_chickens" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-2">
            <label class="form-label">Other Poultry</label>
            <input v-model.number="form.payload.E2_other_poultry" type="number" min="0" class="form-control" />
          </div>
        </div>
        <div v-if="hasProducts" class="row g-3 mb-2">
          <div class="col-12"><label class="form-label fw-bold">E3. Average product price (NGN)</label></div>
          <div class="col-md-3">
            <label class="form-label">Milk (per liter)</label>
            <input v-model.number="form.payload.E3_milk" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Eggs (per crate)</label>
            <input v-model.number="form.payload.E3_eggs" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Hides/skins (per piece)</label>
            <input v-model.number="form.payload.E3_hides" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Meat (per kg)</label>
            <input v-model.number="form.payload.E3_meat" type="number" min="0" class="form-control" />
          </div>
        </div>
        <div v-if="hasProducts" class="row g-3 mb-2">
          <div class="col-12"><label class="form-label fw-bold">E4. Maximum product price (NGN)</label></div>
          <div class="col-md-3">
            <label class="form-label">Milk (per liter)</label>
            <input v-model.number="form.payload.E4_milk" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Eggs (per crate)</label>
            <input v-model.number="form.payload.E4_eggs" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Hides/skins (per piece)</label>
            <input v-model.number="form.payload.E4_hides" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Meat (per kg)</label>
            <input v-model.number="form.payload.E4_meat" type="number" min="0" class="form-control" />
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-12"><label class="form-label fw-bold">E5. Payment methods (rank 1-5)</label></div>
          <div class="col-md-3">
            <label class="form-label">Cash</label>
            <select v-model="form.payload.E5_cash" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Credit</label>
            <select v-model="form.payload.E5_credit" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Contract</label>
            <select v-model="form.payload.E5_contract" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Other (rank)</label>
            <select v-model="form.payload.E5_other_rank" class="form-select">
              <option value="">Select rank</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Other (specify)</label>
            <input v-model="form.payload.E5_other_specify" type="text" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">E6. Market fee amount (NGN)</label>
            <input v-model.number="form.payload.E6_amount" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-4">
            <label class="form-label">E6. Fee charged per</label>
            <select v-model="form.payload.E6_per" class="form-select">
              <option value="">Select</option>
              <option value="animal">Per animal</option>
              <option value="day">Per day</option>
              <option value="transaction">Per transaction</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">E7. Clients with credit/week</label>
            <input v-model.number="form.payload.E7_clients" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-6">
            <label class="form-label">E7. Total clients/week</label>
            <input v-model.number="form.payload.E7_total" type="number" min="0" class="form-control" />
          </div>
        </div>

        <h6 class="section-title">Section F: Market Infrastructure</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">F1. Holding/quarantine facility?</label>
            <select v-model="form.payload.F1" class="form-select">
              <option value="">Select</option>
              <option value="1">Yes</option>
              <option value="2">No</option>
            </select>
          </div>
          <div class="col-md-3" v-if="hasHolding">
            <label class="form-label">F1a. Capacity (animals)</label>
            <input v-model.number="form.payload.F1a" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">F2. Abattoir/slaughterhouse?</label>
            <select v-model="form.payload.F2" class="form-select">
              <option value="">Select</option>
              <option value="1">Yes</option>
              <option value="2">No</option>
            </select>
          </div>
          <div class="col-md-3" v-if="hasAbattoir">
            <label class="form-label">F2a. Type</label>
            <select v-model="form.payload.F2a" class="form-select">
              <option value="">Select</option>
              <option value="modern">Modern</option>
              <option value="traditional">Traditional</option>
              <option value="mobile">Mobile</option>
            </select>
          </div>
          <div class="col-md-6" v-if="hasAbattoir">
            <label class="form-label">F2b. Capacity (animals/day)</label>
            <input v-model="form.payload.F2b" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">F3. Traders operating</label>
            <input v-model.number="form.payload.F3" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">F4. Market days</label>
            <select v-model="form.payload.F4" class="form-select">
              <option value="">Select</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="bi-weekly">Bi-weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">F5. Market access type</label>
            <select v-model="form.payload.F5" class="form-select">
              <option value="">Select</option>
              <option value="paved">Paved</option>
              <option value="unpaved">Unpaved</option>
              <option value="seasonal">Seasonal</option>
              <option value="limited">Limited</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">F6. Veterinary services?</label>
            <select v-model="form.payload.F6" class="form-select">
              <option value="">Select</option>
              <option value="1">Yes</option>
              <option value="2">No</option>
            </select>
          </div>
          <div class="col-md-6" v-if="hasVetServices">
            <label class="form-label">F6a. Average service cost (NGN)</label>
            <input v-model.number="form.payload.F6a" type="number" min="0" class="form-control" />
          </div>
        </div>

        <h6 class="section-title">Section G: Logistics and Seasonality</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">G1. Transport cost (NGN/km)</label>
            <input v-model.number="form.payload.G1" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">G2. Average distance (km)</label>
            <input v-model.number="form.payload.G2" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-6">
            <label class="form-label">G3. Main transport mode</label>
            <select v-model="form.payload.G3" class="form-select">
              <option value="">Select</option>
              <option value="truck">Truck</option>
              <option value="foot">On foot</option>
              <option value="motorcycle">Motorcycle</option>
              <option value="cart">Cart</option>
              <option value="mixed">Mixed</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">G4. Peak season volume</label>
            <input v-model.number="form.payload.G4" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">G5. Low season volume</label>
            <input v-model.number="form.payload.G5" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">G6. Peak season price (NGN)</label>
            <input v-model.number="form.payload.G6" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">G7. Low season price (NGN)</label>
            <input v-model.number="form.payload.G7" type="number" min="0" class="form-control" />
          </div>
        </div>

        <h6 class="section-title">Section H: Security</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">H1. Security incidents/year</label>
            <input v-model.number="form.payload.H1" type="number" min="0" class="form-control" />
          </div>
          <div class="col-md-8" v-if="hasSecurityIncidents">
            <label class="form-label">H2. Incident types (comma-separated)</label>
            <input v-model="form.payload.H2" type="text" class="form-control" 
                   @change="form.payload.H2 = $event.target.value.split(',').map(s => s.trim()).filter(Boolean)" 
                   placeholder="e.g., Theft, Conflict, Fraud" />
          </div>
        </div>

        <h6 class="section-title">Survey Completion</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Enumerator Name</label>
            <input v-model="form.payload.enumerator_name" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Interview Date</label>
            <input v-model="form.payload.interview_date" type="date" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Start Time</label>
            <input v-model="form.payload.start_time" type="time" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">End Time</label>
            <input v-model="form.payload.end_time" type="time" class="form-control" />
          </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-success" :disabled="form.processing">
            <i class="bi bi-save"></i>
            <span v-if="!form.processing"> Save Market Survey</span>
            <span v-else> Saving...</span>
          </button>
        </div>
      </div>
    </form>
  </BeLayout>
</template>

<style scoped>
.fw-400 { font-weight: 400; }
.section-title { font-weight: 600; margin-top: 20px; margin-bottom: 10px; }
.livestock-volumes h6 { font-weight: 500; color: #495057; }
</style>
