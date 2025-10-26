<script setup>
import { Head, Link } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'

const surveys = [
    {
        title: 'Comercial Livestock Farm Survey',
        icon: 'bi bi-cash-coin',
        route: '/comercial_survey'
    },
    {
        title: 'Livestock Household Survey',
        icon: 'bi bi-houses-fill',
        route: '/household_survey'
    },
    {
        title: 'Livestock Market Survey',
        icon: 'bi bi-shop',
        route: '/market_survey'
    }
]
</script>

<template>
    <BeLayout>

        <Head title="FMLD :: New Enumeration" />

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4 fw-400">New enumeration</h5>
                <hr />
            </div>
        </div>

        <div class="row">
            <div v-for="survey in surveys" :key="survey.route" class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-image">
                        <i :class="survey.icon"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ survey.title }}</h5>
                        <Link :href="survey.route" class="btn btn-success">
                        Take Survey <i class="bi bi-arrow-right-circle"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.fw-400 {
    font-weight: 400;
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
    height: 100%;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.card-image {
    background-color: rgb(11, 109, 23);
    color: aliceblue;
    font-size: 100px;
    padding: 30px 10px;
    text-align: center;
    border-radius: 5px 5px 0 0;
}

.card-body {
    padding: 1.5rem;
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    min-height: 48px;
    display: flex;
    align-items: center;
}

.btn-success {
    background-color: rgb(11, 109, 23);
    border-color: rgb(11, 109, 23);
    width: 100%;
}

.btn-success:hover {
    background-color: rgb(9, 87, 18);
    border-color: rgb(9, 87, 18);
}
</style>