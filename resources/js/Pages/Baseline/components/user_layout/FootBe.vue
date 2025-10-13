<template>
  <footer class="footer mt-auto py-3 bg-light">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-md-6">
          <span class="text-muted">&copy; 2025 Olimage Company. All rights reserved.</span>
        </div>
        <div class="col-md-6 text-md-end">
          <span class="text-muted">Version 1.0.0</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'Footer'
}
</script>

<style scoped>
.footer {
  position: absolute;
  bottom: 0;
  width: 100%;
  border-top: 1px solid #dee2e6;
  margin-top: 2rem;
}
</style>