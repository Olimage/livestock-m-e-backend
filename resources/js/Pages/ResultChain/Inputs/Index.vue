<script setup>
import { Head } from '@inertiajs/vue3'
import BeLayout from '../../../Layouts/BeLayout.vue'
</script>

<template>
    <BeLayout>
        <Head title="Inputs" />

        <div class="row">
            <div class="col-lg-12">
                <h5 class="mt-4 fw-400">Inputs</h5>
                <hr />
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header card-header-green text-white d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-in-down fs-5"></i>
                        <span class="fw-semibold">Result Chain — Inputs</span>
                    </div>
                    <div class="card-body py-5 text-center text-muted">
                        <i class="bi bi-box-arrow-in-down display-4 text-success mb-3 d-block"></i>
                        <h5 class="text-success">Inputs</h5>
                        <p class="mb-0">
                            This section will capture inputs — the resources, funding, and assets invested<br>
                            to drive activities within the result chain.
                        </p>
                        <p class="mt-3 text-muted small">Content coming soon.</p>
                    </div>
                </div>
            </div>
        </div>
    </BeLayout>
</template>

<style scoped>
.fw-400 { font-weight: 400; }
.card-header-green { background-color: rgb(11,109,23); }
.text-success { color: rgb(11,109,23) !important; }
</style>
