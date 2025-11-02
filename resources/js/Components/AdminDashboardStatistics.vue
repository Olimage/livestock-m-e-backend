<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { CountUp } from 'countup.js'
import { onMounted, watch, ref } from 'vue' 

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      recordsSaved: 0,
      totalUsers: 0,
      dataPendingSync: 0
    })
  }
})

// Refs for stat elements
const statRefs = {
  recordsSaved: ref(null),
  totalUsers: ref(null),
  dataPendingSync: ref(null)
}

const newSurvey = {
  title: 'New Survey',
  icon: 'bi bi-plus-square',
  route: 'baseline-new'
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num)
}

const statsCards = [
  {
    title: 'Records Saved',
    key: 'recordsSaved', 
    icon: 'bi bi-database-fill-check',
    gradient: 'from-emerald-500 to-teal-600',
    bgColor: 'bg-emerald-50',
    iconColor: 'text-emerald-600'
  },
  {
    title: 'Total Users',
    key: 'totalUsers',
    icon: 'bi bi-people-fill',
    gradient: 'from-blue-500 to-indigo-600',
    bgColor: 'bg-blue-50',
    iconColor: 'text-blue-600'
  },
  {
    title: 'Data Pending Sync',
    key: 'dataPendingSync',
    icon: 'bi bi-cloud-arrow-up-fill',
    gradient: 'from-amber-500 to-orange-600',
    bgColor: 'bg-amber-50',
    iconColor: 'text-amber-600'
  }
]

onMounted(() => {
  statsCards.forEach((card) => {
    const el = statRefs[card.key].value
    if (el) {
      const countUp = new CountUp(el, props.stats[card.key], {
        duration: 2,
        separator: ','
      })
      countUp.start()
    }
  })
})

watch(() => props.stats, (newStats) => {
  statsCards.forEach((card) => {
    const el = statRefs[card.key].value
    if (el) {
      const countUp = new CountUp(el, newStats[card.key], {
        duration: 2,
        separator: ','
      })
      countUp.start()
    }
  })
}, { deep: true })
</script>

<template>
  <div class="clearfix"></div>
  <section class="main-content">
    <div class="row">
      <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="modern-card">
          <div class="card-header-modern">
            <div class="header-content">
              <h5 class="header-title">
                <i class="bi bi-graph-up-arrow me-2"></i>
                Dashboard Statistics
              </h5>
              <p class="header-subtitle">Real-time overview of your data</p>
            </div>
            <Link :href="route(newSurvey.route)" class="btn-modern">
              <i :class="newSurvey.icon" class="me-2"></i>
              New Input
            </Link>
          </div>

          <div class="card-body-modern">
            <div class="stats-grid">
              <div 
                v-for="(card, index) in statsCards" 
                :key="index"
                class="stat-card"
              >
                <div class="stat-icon-wrapper" :class="card.bgColor">
                  <i :class="[card.icon, card.iconColor]" class="stat-icon"></i>
                </div>
                <div class="stat-content">
                  <p class="stat-label">{{ card.title }}</p>
                  <!-- Bind ref dynamically -->
                  <h3 
                    class="stat-value" 
                    :ref="(el) => statRefs[card.key].value = el"
                  >
                    {{ formatNumber(props.stats[card.key]) }}
                  </h3>
                  <div class="stat-badge" :class="`bg-gradient-to-r ${card.gradient}`">
                    <span class="badge-text">Active</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
</template>


<style scoped>
.main-content {
    padding: 20px 0;
}

.modern-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 10px 30px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header-modern {
    padding: 24px 28px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.header-content {
    flex: 1;
    min-width: 200px;
}

.header-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
}

.header-subtitle {
    font-size: 0.875rem;
    color: #64748b;
    margin: 4px 0 0 0;
    font-weight: 400;
}

.btn-modern {
    padding: 10px 24px;
    background: linear-gradient(135deg, rgb(11, 109, 23) 0%, rgb(9, 87, 18) 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px rgba(11, 109, 23, 0.2);
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 12px rgba(11, 109, 23, 0.3);
    color: white;
}

.btn-modern:active {
    transform: translateY(0);
}

.card-body-modern {
    padding: 28px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.stat-card {
    background: white;
    border-radius: 14px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981 0%, #14b8a6 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    transition: transform 0.3s ease;
}

.stat-card:hover .stat-icon-wrapper {
    transform: scale(1.1) rotate(5deg);
}

.stat-icon {
    font-size: 28px;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 8px 0;
}

.stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 12px 0;
    line-height: 1;
}

.stat-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    align-self: flex-start;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.badge-text {
    color: white;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.bg-gradient-to-r {
    background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

.from-emerald-500 {
    --tw-gradient-from: #10b981;
    --tw-gradient-to: rgb(16 185 129 / 0);
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.to-teal-600 {
    --tw-gradient-to: #0d9488;
}

.from-blue-500 {
    --tw-gradient-from: #3b82f6;
    --tw-gradient-to: rgb(59 130 246 / 0);
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.to-indigo-600 {
    --tw-gradient-to: #4f46e5;
}

.from-amber-500 {
    --tw-gradient-from: #f59e0b;
    --tw-gradient-to: rgb(245 158 11 / 0);
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.to-orange-600 {
    --tw-gradient-to: #ea580c;
}

.bg-emerald-50 {
    background-color: #ecfdf5;
}

.text-emerald-600 {
    color: #059669;
}

.bg-blue-50 {
    background-color: #eff6ff;
}

.text-blue-600 {
    color: #2563eb;
}

.bg-amber-50 {
    background-color: #fffbeb;
}

.text-amber-600 {
    color: #d97706;
}

@media (max-width: 768px) {
    .card-header-modern {
        padding: 20px;
    }

    .card-body-modern {
        padding: 20px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .stat-value {
        font-size: 1.75rem;
    }

    .header-title {
        font-size: 1.1rem;
    }
}
</style>