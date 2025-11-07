<script setup>
import { Link, Head, useForm } from '@inertiajs/vue3'
import BeLayout from '../../Layouts/BeLayout.vue'
import { route } from 'ziggy-js'

const props = defineProps({
  supervisors: { type: Array, required: true },
  enumerators: { type: Array, required: true },
})

const form = useForm({
  supervisor_id: '',
  enumerator_id: '',
})

function submit() {
  form.post(route('supervisor-enumerators.assign'), {
    onSuccess: () => {
      form.reset();
      window.location = route('supervisor-enumerators.index');
    }
  });
}
</script>

<template>
  <BeLayout>
    <Head title="Create Supervisor-Enumerator Assignment" />

    <div class="row">
      <div class="col-8">
        <h5 class="mt-4 fw-400">Create Supervisor-Enumerator Assignment</h5>
      </div>
      <div class="col-4 text-end mt-3">
        <Link :href="route('supervisor-enumerators.index')" class="btn btn-sm btn-secondary">
          Back to List
        </Link>
      </div>
      <div class="col-12">
        <hr />
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="submit">
              <div class="mb-3">
                <label class="form-label">Supervisor</label>
                <select v-model="form.supervisor_id" class="form-select">
                  <option value="">Select a Supervisor</option>
                  <option v-for="supervisor in supervisors" :key="supervisor.id" :value="supervisor.id">
                    {{ supervisor.full_name }}
                  </option>
                </select>
                <div v-if="form.errors.supervisor_id" class="text-danger small mt-1">{{ form.errors.supervisor_id }}</div>
              </div>

              <div class="mb-3">
                <label class="form-label">Enumerator</label>
                <select v-model="form.enumerator_id" class="form-select">
                  <option value="">Select an Enumerator</option>
                  <option v-for="enumerator in enumerators" :key="enumerator.id" :value="enumerator.id">
                    {{ enumerator.full_name }}
                  </option>
                </select>
                <div v-if="form.errors.enumerator_id" class="text-danger small mt-1">{{ form.errors.enumerator_id }}</div>
              </div>

              <div class="text-end">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">Create Assignment</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </BeLayout>
</template>

