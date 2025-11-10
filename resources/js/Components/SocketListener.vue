<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
  userId: {
    type: [String, Number],
    default: null
  }
});

const messages = ref([]);

onMounted(() => {
  if (window.Echo) {
    // Dashboard stats channel
    try {
      window.Echo.channel('dashboard-stats')
        .listen('.StatsUpdated', (e) => {
          console.debug('DashboardStatsUpdated:', e);
          window.dispatchEvent(new CustomEvent('dashboard:stats-updated', { 
            detail: { data: e.data }
          }));
        });
    } catch (e) {
      console.warn('Echo dashboard-stats channel error:', e);
    }

    // public live-data channel
    try {
      window.Echo.channel('live-data')
        .listen('.LiveDataUpdated', (e) => {
          messages.value.push({ type: 'live', payload: e.data });
          console.debug('LiveDataUpdated', e);
          // also emit a global notification event for UI to consume
          try {
            window.dispatchEvent(new CustomEvent('app:notify', { detail: { message: 'Live data received', payload: e.data, variant: 'info' } }));
          } catch (err) {}
        });
    } catch (e) {
      console.warn('Echo live-data channel error', e);
    }

    // private user notifications
    if (props.userId) {
      try {
        console.debug('Attempting to subscribe to private channel for user:', props.userId);
        const channel = window.Echo.private(`App.Models.User.${props.userId}`);
        
        // Add error handler for subscription
        channel.error((error) => {
          console.error('Private channel subscription error:', error);
        });

        channel
          .listen('.UserNotification', (e) => {
            console.debug('Received UserNotification:', e);
            messages.value.push({ type: 'notify', message: e.message });
            
            // dispatch a global app:notify event for the Toaster
            try {
              window.dispatchEvent(new CustomEvent('app:notify', { 
                detail: { 
                  message: e.message || (e.data && e.data.message) || 'Notification', 
                  variant: 'success' 
                } 
              }));
            } catch (err) {
              console.error('Error dispatching notification event:', err);
            }
          });
      } catch (e) {
        console.error('Echo private channel setup error:', e);
      }
    }
  }
});
</script>

<template>
  <div class="socket-listener">
    <div v-if="messages.length">
      <h6>Live messages</h6>
      <ul class="list-unstyled">
        <li v-for="(m, i) in messages.slice().reverse()" :key="i">
          <small v-if="m.type==='live'">[Live]</small>
          <small v-else>[Notify]</small>
          <span v-if="m.payload">{{ JSON.stringify(m.payload) }}</span>
          <span v-else>{{ m.message }}</span>
        </li>
      </ul>
    </div>
    <div v-else>
      <small class="text-muted">No live messages yet</small>
    </div>
  </div>
</template>

<style scoped>
.socket-listener { padding: .5rem; }
</style>
