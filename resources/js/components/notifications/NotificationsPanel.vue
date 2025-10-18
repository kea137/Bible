<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { CheckCheck, Trash2 } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    data: any;
    read: boolean;
    created_at: string;
}

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'notification-read': [];
}>();

const notifications = ref<Notification[]>([]);
const loading = ref(true);

async function fetchNotifications() {
    try {
        const response = await fetch('/api/notifications');
        const data = await response.json();
        notifications.value = data.notifications;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    } finally {
        loading.value = false;
    }
}

async function markAsRead(notificationId: number) {
    try {
        await fetch(`/api/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        const notification = notifications.value.find(n => n.id === notificationId);
        if (notification) {
            notification.read = true;
        }
        
        emit('notification-read');
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

async function markAllAsRead() {
    try {
        await fetch('/api/notifications/read-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        notifications.value.forEach(n => n.read = true);
        emit('notification-read');
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
}

async function deleteNotification(notificationId: number) {
    try {
        await fetch(`/api/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        notifications.value = notifications.value.filter(n => n.id !== notificationId);
        emit('notification-read');
    } catch (error) {
        console.error('Error deleting notification:', error);
    }
}

const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read).length;
});

function getNotificationIcon(type: string) {
    if (type.includes('success')) {
        return '✅';
    } else if (type.includes('failed')) {
        return '❌';
    }
    return '📋';
}

function formatDate(dateString: string) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString();
}

let intervalId: NodeJS.Timeout | null = null;

watch(() => props.open, (newValue) => {
    if (newValue) {
        fetchNotifications();
        if (!intervalId) {
            intervalId = setInterval(fetchNotifications, 5000);
        }
    } else {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }
});

onMounted(() => {
    fetchNotifications();
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent side="right" class="w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle class="flex items-center justify-between">
                    <span>Notifications</span>
                    <Badge v-if="unreadCount > 0" variant="destructive" class="ml-2">
                        {{ unreadCount }}
                    </Badge>
                </SheetTitle>
                <SheetDescription>
                    View your completed tasks and notifications
                </SheetDescription>
            </SheetHeader>

            <div class="mt-4 flex items-center justify-between">
                <Button
                    v-if="unreadCount > 0"
                    size="sm"
                    variant="outline"
                    @click="markAllAsRead"
                >
                    <CheckCheck class="mr-2 h-4 w-4" />
                    Mark all as read
                </Button>
            </div>

            <ScrollArea class="mt-4 h-[calc(100vh-200px)]">
                <div v-if="loading" class="flex items-center justify-center py-8">
                    <div class="text-muted-foreground">Loading...</div>
                </div>

                <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-8">
                    <div class="text-muted-foreground">No notifications yet</div>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        :class="[
                            'rounded-lg border p-4 transition-colors',
                            notification.read ? 'bg-background' : 'bg-accent/50',
                        ]"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">{{ getNotificationIcon(notification.type) }}</span>
                                    <h4 class="font-semibold">{{ notification.title }}</h4>
                                </div>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ notification.message }}
                                </p>
                                <p class="mt-2 text-xs text-muted-foreground">
                                    {{ formatDate(notification.created_at) }}
                                </p>
                            </div>
                            <div class="flex gap-1">
                                <Button
                                    v-if="!notification.read"
                                    size="icon"
                                    variant="ghost"
                                    @click="markAsRead(notification.id)"
                                    title="Mark as read"
                                >
                                    <CheckCheck class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="deleteNotification(notification.id)"
                                    title="Delete"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollArea>
        </SheetContent>
    </Sheet>
</template>
