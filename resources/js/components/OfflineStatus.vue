<script setup lang="ts">
import { useOffline } from '@/composables/useOffline';
import { useOfflineData } from '@/composables/useOfflineData';
import { WifiOffIcon, WifiIcon, CloudUploadIcon } from 'lucide-vue-next';
import { computed } from 'vue';

const { isOnline } = useOffline();
const { queuedMutations, isSyncing } = useOfflineData();

const hasPendingMutations = computed(() => queuedMutations.value.length > 0);
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50">
        <!-- Offline indicator -->
        <div
            v-if="!isOnline"
            class="flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-medium text-white shadow-lg"
        >
            <WifiOffIcon class="h-4 w-4" />
            <span>Offline Mode</span>
        </div>

        <!-- Syncing indicator -->
        <div
            v-else-if="isSyncing"
            class="flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white shadow-lg"
        >
            <CloudUploadIcon class="h-4 w-4 animate-pulse" />
            <span>Syncing...</span>
        </div>

        <!-- Pending mutations indicator -->
        <div
            v-else-if="hasPendingMutations"
            class="flex items-center gap-2 rounded-lg bg-yellow-500 px-4 py-2 text-sm font-medium text-white shadow-lg"
        >
            <CloudUploadIcon class="h-4 w-4" />
            <span>{{ queuedMutations.length }} pending</span>
        </div>
    </div>
</template>
