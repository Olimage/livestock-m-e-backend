<script setup>
defineProps({
    approvals: { type: Array, default: () => [] },
});

const icon = (action) => ({
    submitted: 'bi-send', resubmitted: 'bi-arrow-repeat', approved: 'bi-check-circle',
    declined: 'bi-x-circle', returned: 'bi-reply', published: 'bi-broadcast',
}[action] ?? 'bi-dot');

const color = (action) => ({
    approved: 'text-success', published: 'text-success',
    declined: 'text-danger', returned: 'text-danger',
    submitted: 'text-primary', resubmitted: 'text-primary',
}[action] ?? 'text-secondary');
</script>

<template>
    <ul class="list-unstyled mb-0">
        <li v-if="approvals.length === 0" class="text-muted">No activity yet.</li>
        <li v-for="a in approvals" :key="a.id" class="d-flex mb-3">
            <div class="me-3"><i class="bi" :class="[icon(a.action), color(a.action)]" style="font-size:1.25rem"></i></div>
            <div>
                <div class="fw-semibold text-capitalize">
                    {{ a.action }}
                    <span v-if="a.stage" class="text-muted fw-normal">· {{ a.stage.name }}</span>
                </div>
                <div class="small text-muted">
                    {{ a.actor?.full_name ?? 'System' }} · {{ new Date(a.acted_at).toLocaleString() }}
                </div>
                <div v-if="a.reason" class="small text-danger">Reason: {{ a.reason }}</div>
            </div>
        </li>
    </ul>
</template>
