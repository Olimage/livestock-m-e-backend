<script setup>
import { Head, Link } from '@inertiajs/vue3'

const newSurvey = 
    {
        title: 'New Survey',
        icon: 'bi bi-plus-square',
        route: '/baseline/new'
    }

</script>
<template>
    <div class="clearfix"></div>
    <section class=" main-content">





        <div class="row">



            <div class=" col-sm-12 col-md-12 col-lg-12  ">
                <div class="  card text-bg-light">
                    <div class="card-header"> Statistics

                        <Link :href="newSurvey.route" class="btn btn-success float-end">
                        New Input <i :class="newSurvey.icon"></i>
                        </Link>
                       
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-4">
                                <div class=" card text-bg-success mb-3" >
                                    <div class="card-header">Records Saved</div>
                                    <div class="card-body">
                                        <h4 class="mont-font fs-3 fw-800  mb-8 text-start">
                                            1,200
                                        </h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                            <div class="col-md-4">
                                <div class=" card text-bg-success mb-3" >
                                    <div class="card-header">Records Synced</div>
                                    <div class="card-body">
                                        <h4 class="mont-font fs-3 fw-800  mb-8 text-start">
                                            1,200
                                        </h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                            <div class="col-md-4">
                                <div class=" card text-bg-success  mb-3" >
                                    <div class="card-header">Records Pending Sync</div>
                                    <div class="card-body">
                                        <h4 class="mont-font fs-3 fw-800  mb-8 text-start">
                                            1,200
                                        </h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>



                        </div>
                    </div>
                </div>
            </div>

        </div>






    </section>
</template>

<style scoped>
.stats {
    padding: 50px 0 10em 0;

}

p {
    font-size: 18;
    line-height: 1.8;
    font-weight: 400;
}

h5 {
    font-size: 1rem;
    line-height: 1.2;
    font-weight: 500;
}

/* Sync Status Bar */
.sync-status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.sync-offline {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.sync-progress {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #fed7aa;
}

.sync-pending {
    background: #dbeafe;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}

.sync-synced {
    background: #d1fae5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.sync-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sync-button {
    padding: 4px 12px;
    border: none;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
}

.sync-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.sync-stats {
    font-size: 12px;
    opacity: 0.8;
}
</style>