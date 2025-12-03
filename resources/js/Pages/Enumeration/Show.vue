<script setup>
import { Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'
import { computed } from 'vue'

const props = defineProps({
  record: Object
})

const formatDate = (dt) => {
  if (!dt) return 'N/A'
  const d = new Date(dt)
  const pad = (n) => String(n).padStart(2, '0')
  const day = pad(d.getDate())
  const month = pad(d.getMonth() + 1)
  const year = d.getFullYear()
  let hours = d.getHours()
  const mins = pad(d.getMinutes())
  const ampm = hours >= 12 ? 'pm' : 'am'
  hours = hours % 12 || 12
  return `${day}-${month}-${year} - ${hours}:${mins} ${ampm}`
}

const formTypeLabel = computed(() => {
  const labels = {
    'household': 'Household Survey',
    'market': 'Market Survey',
    'commercial_farm': 'Commercial Farm Survey'
  }
  return labels[props.record.form_type] || props.record.form_type
})

const syncStatusClass = computed(() => {
  const classes = {
    'pending': 'bg-warning text-dark',
    'synced': 'bg-success',
    'failed': 'bg-danger'
  }
  return classes[props.record.sync_status] || 'bg-secondary'
})

const payload = computed(() => props.record.payload || {})

const getFieldLabel = (key) => {
  const labels = {
    // Geographic
    'geopolitical_zone': 'Geopolitical Zone',
    'state': 'State',
    'lga': 'LGA',
    'gps_coordinates': 'GPS Coordinates',
    'location': 'Location',
    
    // Household Demographics
    'household_head_age': 'Household Head Age',
    'household_head_gender': 'Household Head Gender',
    'marital_status': 'Marital Status',
    'education_level': 'Education Level',
    'household_total': 'Household Size',
    'dependents': 'Number of Dependents',
    'owns_livestock': 'Owns Livestock',
    'primary_livelihood': 'Primary Livelihood',
    'livestock_income': 'Livestock Income',
    'income_share': 'Income Share from Livestock',
    'income_stability': 'Income Stability',
    'economic_dependency': 'Economic Dependency',
    
    // Market
    'marketName': 'Market Name',
    'respondentName': 'Respondent Name',
    'rolePosition': 'Role/Position',
    'enumerator_name': 'Enumerator Name',
    'interview_date': 'Interview Date',
    'start_time': 'Start Time',
    'end_time': 'End Time',
    
    // Commercial Farm Management
    'farm_name': 'Farm Name / Registration Number',
    'manager_age': 'Manager Age',
    'manager_gender': 'Manager Gender',
    'experience_years': 'Years of Experience',
    'business_type': 'Business Type',
    'business_structure': 'Business Structure',
    'specialization': 'Specialization',
    'annual_revenue': 'Annual Revenue',
    'employees': 'Number of Employees',
    'financing_source': 'Financing Source',
    'business_focus': 'Business Focus',
    'growth_pattern': 'Growth Pattern',
    'technology_level': 'Technology Level',
    
    // Livestock Counts
    'livestock_types': 'Livestock Types',
    'cattle_count': 'Cattle Count',
    'beef_cattle': 'Beef Cattle',
    'dairy_cattle': 'Dairy Cattle',
    'sheep_count': 'Sheep Count',
    'sheep': 'Sheep',
    'goats_count': 'Goats Count',
    'goats': 'Goats',
    'pigs_count': 'Pigs Count',
    'pigs': 'Pigs',
    'chickens_count': 'Chickens Count',
    'layers': 'Layers (Egg-Laying Chickens)',
    'broilers': 'Broilers',
    'other_poultry_count': 'Other Poultry Count',
    'other_poultry': 'Other Poultry',
    'other_livestock': 'Other Livestock',
    'other_species': 'Other Species',
    
    // Production Systems
    'keeping_type': 'Livestock Keeping Type',
    'management_pattern': 'Management Pattern',
    'primary_owner': 'Primary Owner',
    'production_system': 'Production System',
    'production_orientation': 'Production Orientation',
    'mobility_pattern': 'Mobility Pattern',
    'feeding_strategy': 'Feeding Strategy',
    'feed_source': 'Feed Source',
    'feed_sources': 'Feed Sources',
    'feed_formulation': 'Feed Formulation',
    'housing_type': 'Housing Type',
    'storage_facilities': 'Storage Facilities',
    'primary_caretaker': 'Primary Caretaker',
    'infrastructure': 'Infrastructure',
    'labor_management': 'Labor Management',
    'own_feed_mill': 'Owns Feed Mill',
    
    // Productivity & Performance
    'cattle_milk_yield': 'Cattle Milk Yield (L/day)',
    'goats_milk_yield': 'Goats Milk Yield (L/day)',
    'milk_yield_per_cow': 'Milk Yield per Cow',
    'chickens_egg_production': 'Egg Production per Hen',
    'eggs_per_hen': 'Eggs per Hen',
    'cattle_live_weight': 'Cattle Live Weight (kg)',
    'sheep_live_weight': 'Sheep Live Weight (kg)',
    'goats_live_weight': 'Goats Live Weight (kg)',
    'pigs_live_weight': 'Pigs Live Weight (kg)',
    'beef_daily_gain': 'Beef Cattle Daily Gain',
    'pigs_daily_gain': 'Pigs Daily Gain',
    'feed_conversion_ratio': 'Feed Conversion Ratio',
    'reproduction_rate': 'Reproduction Rate',
    'cattle_lactating_percent': 'Cattle Lactating (%)',
    'goats_lactating_percent': 'Goats Lactating (%)',
    'chickens_laying_percent': 'Chickens Laying (%)',
    
    // Mortality
    'cattle_mortality': 'Cattle Mortality Rate (%)',
    'sheep_mortality': 'Sheep Mortality Rate (%)',
    'goats_mortality': 'Goats Mortality Rate (%)',
    'pigs_mortality': 'Pigs Mortality Rate (%)',
    'chickens_mortality': 'Chickens Mortality Rate (%)',
    'small_ruminants_mortality': 'Small Ruminants Mortality (%)',
    'mortality_causes': 'Mortality Causes',
    
    // Health & Services
    'veterinary_access': 'Veterinary Access',
    
    // Marketing & Economics
    'sells_products': 'Sells Livestock Products',
    'products_sold': 'Products Sold',
    'products_marketed': 'Products Marketed',
    'sales_frequency': 'Sales Frequency',
    'sales_channels': 'Sales Channels',
    'market_distance': 'Distance to Market (km)',
    'market_reach': 'Market Reach',
    'transport_method': 'Transport Method',
    'transportation': 'Transportation',
    'market_challenges': 'Market Challenges',
    'business_challenges': 'Business Challenges',
    'abattoir_access': 'Abattoir Access',
    'processing_facilities': 'Processing Facilities',
    'revenue_share': 'Revenue Share'
  }
  return labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatValue = (value, key = '') => {
  if (value === null || value === undefined || value === '') return 'N/A'
  if (typeof value === 'boolean') return value ? 'Yes' : 'No'
  if (value === '1') return 'Yes'
  if (value === '2') return 'No'
  if (Array.isArray(value)) return value.length > 0 ? value.join(', ') : 'None'
  if (typeof value === 'object') return 'Complex data'
  
  // Age mappings
  if (key.includes('age') && typeof value === 'string') {
    const ageMap = {
      '1': '18-30 years',
      '2': '31-45 years',
      '3': '46-60 years',
      '4': 'Above 60 years'
    }
    return ageMap[value] || value
  }
  
  // Gender mappings
  if (key.includes('gender') && typeof value === 'string') {
    const genderMap = { '1': 'Male', '2': 'Female' }
    return genderMap[value] || value
  }
  
  // Education mappings
  if (key === 'education_level' && typeof value === 'string') {
    const eduMap = {
      '1': 'No formal education',
      '2': 'Primary',
      '3': 'Secondary',
      '4': 'Tertiary',
      '5': 'Adult literacy'
    }
    return eduMap[value] || value
  }
  
  // Marital status mappings
  if (key === 'marital_status' && typeof value === 'string') {
    const maritalMap = {
      '1': 'Single',
      '2': 'Married',
      '3': 'Divorced/Separated',
      '4': 'Widowed'
    }
    return maritalMap[value] || value
  }
  
  return value
}

const geographicFields = computed(() => {
  const fields = []
  // Prefer relational names when available
  if (props.record.zone) {
    fields.push({ key: 'geopolitical_zone', value: props.record.zone.name })
  } else if (payload.value['geopolitical_zone']) {
    fields.push({ key: 'geopolitical_zone', value: payload.value['geopolitical_zone'] })
  }

  if (props.record.state) {
    fields.push({ key: 'state', value: props.record.state.name })
  } else if (payload.value['state']) {
    fields.push({ key: 'state', value: payload.value['state'] })
  }

  if (props.record.lga) {
    fields.push({ key: 'lga', value: props.record.lga.name })
  } else if (payload.value['lga']) {
    fields.push({ key: 'lga', value: payload.value['lga'] })
  }

  if (payload.value['gps_coordinates']) {
    fields.push({ key: 'gps_coordinates', value: payload.value['gps_coordinates'] })
  }

  return fields
})

const respondentFields = computed(() => {
  if (props.record.form_type === 'market') {
    const fields = ['marketName', 'location', 'respondentName', 'rolePosition']
    return fields.filter(f => payload.value[f]).map(f => ({ key: f, value: payload.value[f] }))
  }
  return []
})

const demographicFields = computed(() => {
  let fields = []
  if (props.record.form_type === 'household') {
    fields = ['household_head_age', 'household_head_gender', 'marital_status', 'education_level', 'household_total', 'dependents', 'owns_livestock']
  } else if (props.record.form_type === 'commercial_farm') {
    fields = ['farm_name', 'manager_age', 'manager_gender', 'education_level', 'experience_years']
  }
  return fields.filter(f => payload.value[f] !== undefined && payload.value[f] !== null && payload.value[f] !== '')
    .map(f => ({ key: f, value: payload.value[f] }))
})

const livestockFields = computed(() => {
  const fields = Object.keys(payload.value).filter(k => {
    const val = payload.value[k]
    return (k.includes('_count') || k.includes('cattle') || k.includes('sheep') || 
           k.includes('goats') || k.includes('pigs') || k.includes('chickens') ||
           k.includes('layers') || k.includes('broilers') || k === 'livestock_types' ||
           k.includes('other_poultry') || k.includes('other_species') || k.includes('other_livestock')) &&
           val !== null && val !== undefined && val !== '' && val !== 0
  })
  return fields.map(f => ({ key: f, value: payload.value[f] }))
})

const productionFields = computed(() => {
  const fields = Object.keys(payload.value).filter(k => {
    const val = payload.value[k]
    return (k.includes('keeping_type') || k.includes('management') || k.includes('production') ||
           k.includes('feeding') || k.includes('feed_') || k.includes('housing') || 
           k.includes('storage') || k.includes('infrastructure') || k.includes('labor') ||
           k.includes('mobility') || k.includes('orientation') || k.includes('system') ||
           k.includes('primary_owner') || k.includes('primary_caretaker') || k.includes('own_feed')) &&
           val !== null && val !== undefined && val !== ''
  })
  return fields.map(f => ({ key: f, value: payload.value[f] }))
})

const performanceFields = computed(() => {
  const fields = Object.keys(payload.value).filter(k => {
    const val = payload.value[k]
    return (k.includes('yield') || k.includes('production') || k.includes('weight') || 
           k.includes('gain') || k.includes('conversion') || k.includes('reproduction') ||
           k.includes('lactating') || k.includes('laying') || k.includes('egg')) &&
           !k.includes('_count') && val !== null && val !== undefined && val !== '' && val !== 0
  })
  return fields.map(f => ({ key: f, value: payload.value[f] }))
})

const healthFields = computed(() => {
  const fields = Object.keys(payload.value).filter(k => {
    const val = payload.value[k]
    return (k.includes('mortality') || k.includes('veterinary') || k.includes('health')) &&
           val !== null && val !== undefined && val !== ''
  })
  return fields.map(f => ({ key: f, value: payload.value[f] }))
})

const marketingFields = computed(() => {
  const fields = Object.keys(payload.value).filter(k => {
    const val = payload.value[k]
    return (k.includes('sells') || k.includes('products_sold') || k.includes('products_marketed') ||
           k.includes('sales') || k.includes('market') || k.includes('transport') || 
           k.includes('abattoir') || k.includes('processing') || k.includes('channels') ||
           k.includes('revenue') || k.includes('income')) &&
           !k.includes('_meta') && val !== null && val !== undefined && val !== ''
  })
  return fields.map(f => ({ key: f, value: payload.value[f] }))
})

const businessFields = computed(() => {
  if (props.record.form_type !== 'commercial_farm') return []
  const fields = Object.keys(payload.value).filter(k => {
    const val = payload.value[k]
    return (k.includes('business') || k.includes('annual_revenue') || k.includes('employees') ||
           k.includes('financing') || k.includes('growth') || k.includes('technology') ||
           k.includes('specialization')) &&
           val !== null && val !== undefined && val !== ''
  })
  return fields.map(f => ({ key: f, value: payload.value[f] }))
})

const economicFields = computed(() => {
  if (props.record.form_type !== 'household') return []
  const fields = ['primary_livelihood', 'livestock_income', 'income_share', 'income_stability', 'economic_dependency']
  return fields.filter(f => payload.value[f]).map(f => ({ key: f, value: payload.value[f] }))
})
</script>

<template>
  <BeLayout>
    <Head :title="`Record #${record.id}`" />
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-400">Enumeration Record #{{ record.id }}</h5>
      <Link :href="route('enumerations.index')" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </Link>
    </div>
    <hr />
    
    <!-- Record Metadata -->
    <div class="card mb-3">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Record Information</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="info-item">
              <label>Form Type</label>
              <div><span class="badge bg-primary">{{ formTypeLabel }}</span></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <label>Sync Status</label>
              <div><span class="badge" :class="syncStatusClass">{{ record.sync_status }}</span></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <label>Enumerator</label>
              <div>{{ record.enumerator ? record.enumerator.full_name : record.enumerator_name }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item">
              <label>Submitted At</label>
              <div>{{ formatDate(record.submitted_at || record.created_at) }}</div>
            </div>
          </div>
          <div class="col-md-6" v-if="record.latitude && record.longitude">
            <div class="info-item">
              <label>Coordinates</label>
              <div>{{ record.latitude }}, {{ record.longitude }}</div>
            </div>
          </div>
          <div class="col-md-6" v-if="record.device_id">
            <div class="info-item">
              <label>Device ID</label>
              <div><code>{{ record.device_id }}</code></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Geographic Information -->
    <div class="card mb-3" v-if="geographicFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Geographic Information</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in geographicFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Respondent Information (Market) -->
    <div class="card mb-3" v-if="respondentFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-person-badge"></i> Respondent Information</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in respondentFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Demographic Information -->
    <div class="card mb-3" v-if="demographicFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-people"></i> Demographic Information</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in demographicFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Livestock Information -->
    <div class="card mb-3" v-if="livestockFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-piggy-bank"></i> Livestock Inventory</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4" v-for="field in livestockFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Production Systems -->
    <div class="card mb-3" v-if="productionFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-gear"></i> Production Systems</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in productionFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance & Productivity -->
    <div class="card mb-3" v-if="performanceFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Performance & Productivity</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in performanceFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Health & Veterinary -->
    <div class="card mb-3" v-if="healthFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-heart-pulse"></i> Health & Veterinary</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in healthFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Marketing & Sales -->
    <div class="card mb-3" v-if="marketingFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-shop"></i> Marketing & Sales</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in marketingFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Business Information (Commercial Farm) -->
    <div class="card mb-3" v-if="businessFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-building"></i> Business Information</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in businessFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Economic Information (Household) -->
    <div class="card mb-3" v-if="economicFields.length > 0">
      <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-cash-stack"></i> Economic & Livelihoods</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6" v-for="field in economicFields" :key="field.key">
            <div class="info-item">
              <label>{{ getFieldLabel(field.key) }}</label>
              <div>{{ formatValue(field.value, field.key) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BeLayout>
</template>

<style scoped>
.fw-400 { font-weight: 400; }
.info-item {
  padding: 8px 0;
}
.info-item label {
  font-size: 0.85rem;
  color: #6c757d;
  margin-bottom: 4px;
  display: block;
  font-weight: 500;
}
.info-item > div {
  font-size: 0.95rem;
  color: #212529;
  font-weight: 400;
}
.card-header h6 {
  font-weight: 600;
}
code {
  background: #f8f9fa;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 0.9em;
}
</style>
