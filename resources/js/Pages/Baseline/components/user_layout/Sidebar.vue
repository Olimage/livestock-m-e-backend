<template>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">

            <!-- <img src="/images/fmld.png" alt="Logo" class="img-fluid" style="max-height: 50px;"> -->

            <h5 class="offcanvas-title" id="offcanvasExampleLabel">FMLD Baseline</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div>

            </div>
            <!-- Vertical menu -->
            <nav class="nav flex-column sidebar-menu">
                <router-link class="nav-link" to="/" >Dashboard <i class="bi bi-speedometer"></i> </router-link>
                <router-link class="nav-link" to="/" >New Record <i class="bi bi-plus-square"></i> </router-link>
            </nav>
        </div>
    </div>
</template>

<style scoped>

.nav-link{
    color: #198754; /* Bootstrap green */
    font-weight: 600;
    text-transform: uppercase;
    border-bottom: #19875453 0.3px solid;
}
.sidebar-menu .nav-link {
    transition: background 0.2s;
}
.sidebar-menu .nav-link:hover {
    background-color: #198754; /* Bootstrap green */
    color: #fff;
}
.sidebar-separator {
    border: none;
    border-bottom: 0.3px solid #198754;
    margin-top: 1rem;
}
</style>