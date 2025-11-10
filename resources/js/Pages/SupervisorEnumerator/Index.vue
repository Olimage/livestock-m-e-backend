<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BeLayout from '../../Layouts/BeLayout.vue';
import { route } from 'ziggy-js';



    const props = defineProps({
        supervisors: {
            type: Array,
            required: true,
            default: () => []
        },
        availableEnumerators: {
            type: Array,
            required: true,
            default: () => []
        }
    });

    const selectedSupervisor = ref(null);
    const showAssignModal = ref(false);

    const assignForm = useForm({
        supervisor_id: '',
        enumerator_ids: []
    });

    const openAssignModal = (supervisor) => {
        selectedSupervisor.value = supervisor;
        assignForm.supervisor_id = supervisor.id;
        assignForm.enumerator_ids = [];
        showAssignModal.value = true;
    };

    const assignEnumerators = () => {
        assignForm.post(route('supervisor-enumerators.assign'), {
            onSuccess: () => {
                showAssignModal.value = false;
                assignForm.reset();
            }
        });
    };

    const removeEnumerator = (supervisorId, enumeratorId) => {
        if (confirm('Are you sure you want to remove this enumerator?')) {
            useForm().delete(
                route('supervisor-enumerators.remove', { supervisor: supervisorId, enumerator: enumeratorId }),
                {
                    preserveScroll: true,
                }
            );
        }
    };
    </script>

    <template>
        <BeLayout>
            <Head title="Supervisor-Enumerator Management" />

            <div class="row">
                <div class="col-8">
                    <h5 class="mt-4 fw-400">Supervisor-Enumerator Management</h5>
                </div>
                <div class="col-4 text-end mt-3">
                    <Link :href="route('supervisor-enumerators.create')" class="btn btn-sm btn-primary">
                        Creat new Assignment
                        <i class="bi bi-plus-circle"></i>
                    </Link>
                </div>
                <div class="col-12">
                    <hr />
                </div>
            </div>

            <!-- Supervisors List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div v-if="!supervisors || supervisors.length === 0" class="text-center py-4">
                                <p class="text-muted mb-0">No supervisors found</p>
                            </div>

                            <div v-else v-for="supervisor in supervisors" :key="supervisor.id" class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">
                                        {{ supervisor.full_name }}
                                        <small class="text-muted">({{ supervisor.email }})</small>
                                    </h6>
                                    <button 
                                        @click="openAssignModal(supervisor)"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="bi bi-plus-circle"></i> 
                                        Assign Enumerators
                                    </button>
                                </div>

                                <!-- Enumerators List -->
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="!supervisor.enumerators || supervisor.enumerators.length === 0">
                                                <td colspan="3" class="text-center text-muted">
                                                    No enumerators assigned
                                                </td>
                                            </tr>
                                            <tr v-for="enumerator in supervisor.enumerators" 
                                                :key="enumerator.id"
                                            >
                                                <td>{{ enumerator.full_name }}</td>
                                                <td>{{ enumerator.email ?? '—' }}</td>
                                                <td class="text-end">
                                                    <button 
                                                        @click="removeEnumerator(supervisor.id, enumerator.id)"
                                                        class="btn btn-sm btn-outline-danger"
                                                    >
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Modal -->
            <div v-if="showAssignModal" class="modal d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Assign Enumerators to {{ selectedSupervisor?.full_name }}
                            </h5>
                            <button 
                                @click="showAssignModal = false"
                                type="button" 
                                class="btn-close"
                            ></button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="assignEnumerators">
                                <div v-if="!availableEnumerators || availableEnumerators.length === 0" class="text-center py-3">
                                    <p class="text-muted mb-0">No available enumerators to assign</p>
                                </div>
                                <div v-else class="mb-3">
                                    <label class="form-label">Select Enumerators</label>
                                    <div class="form-check" v-for="enumerator in availableEnumerators" :key="enumerator.id">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input"
                                            :id="'enumerator-' + enumerator.id"
                                            :value="enumerator.id"
                                            v-model="assignForm.enumerator_ids"
                                        >
                                        <label class="form-check-label" :for="'enumerator-' + enumerator.id">
                                            {{ enumerator.full_name }}
                                            <small class="text-muted">({{ enumerator.email }})</small>
                                        </label>
                                    </div>
                                    <small class="text-danger" v-if="assignForm.errors.enumerator_ids">{{ assignForm.errors.enumerator_ids }}</small>
                                </div>

                                <div class="text-end">
                                    <button 
                                        type="button" 
                                        class="btn btn-secondary me-2"
                                        @click="showAssignModal = false"
                                    >
                                        Cancel
                                    </button>
                                    <button 
                                        type="submit" 
                                        class="btn btn-primary"
                                        :disabled="assignForm.processing"
                                    >
                                        Assign Selected
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </BeLayout>
    </template>

    <style scoped>
    /* Modal overlay: full-screen, centered dialog and allow clicks inside dialog */
    .modal {
        position: fixed;
        inset: 0; /* top:0; right:0; bottom:0; left:0; */
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1050; /* sit above page content */
    }

    .modal .modal-dialog {
        pointer-events: auto; /* allow clicks inside dialog */
        z-index: 1060;
    }

    /* If an extra backdrop element exists, keep it behind the modal dialog */
    .modal-backdrop {
        z-index: 1040 !important;
    }
    </style>