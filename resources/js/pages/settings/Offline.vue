<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { useOfflineData } from '@/composables/useOfflineData';
import { useOffline } from '@/composables/useOffline';
import { Head, Link } from '@inertiajs/vue3';
import {
    Download,
    Trash2,
    WifiOff,
    WifiIcon,
    CloudUpload,
    HardDrive,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('Offline Settings'),
        href: '/settings/offline',
    },
];

const {
    cachedChapters,
    queuedMutations,
    removeCachedChapter,
    clearAllChapters,
    syncMutations,
    clearAllMutations,
    isSyncing,
} = useOfflineData();

const {
    isOnline,
    isInstalled,
    canInstall,
    serviceWorkerReady,
    installApp,
} = useOffline();

const totalSize = computed(() => {
    // Rough estimate: each chapter is about 10KB
    return ((cachedChapters.value.length * 10) / 1024).toFixed(2);
});

async function handleInstallApp() {
    const success = await installApp();
    if (!success) {
        console.log('[Offline Settings] Install failed or was dismissed');
    }
}

async function handleRemoveChapter(id: string) {
    if (confirm(t('Are you sure you want to remove this chapter from offline cache?'))) {
        await removeCachedChapter(id);
    }
}

async function handleClearAll() {
    if (
        confirm(
            t('Are you sure you want to remove all cached chapters? This cannot be undone.'),
        )
    ) {
        await clearAllChapters();
    }
}

async function handleSyncMutations() {
    await syncMutations();
}

async function handleClearMutations() {
    if (
        confirm(
            t('Are you sure you want to clear all pending mutations? Unsaved changes will be lost.'),
        )
    ) {
        await clearAllMutations();
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('Offline Settings')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="t('Offline Settings')"
                    :description="
                        t(
                            'Manage offline reading and app installation',
                        )
                    "
                />

                <!-- PWA Install Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Download class="h-5 w-5" />
                            {{ t('Progressive Web App') }}
                        </CardTitle>
                        <CardDescription>
                            {{ t('Install this app on your device for a better experience') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <p class="text-sm text-muted-foreground">
                                    <span v-if="isInstalled" class="text-green-600">
                                        ✓ {{ t('App is installed') }}
                                    </span>
                                    <span v-else-if="canInstall" class="text-blue-600">
                                        {{ t('Ready to install') }}
                                    </span>
                                    <span v-else class="text-gray-600">
                                        {{ t('Already installed or not available') }}
                                    </span>
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ t('Service Worker: ') }}
                                    <span :class="serviceWorkerReady ? 'text-green-600' : 'text-gray-600'">
                                        {{ serviceWorkerReady ? t('Active') : t('Inactive') }}
                                    </span>
                                </p>
                            </div>
                            <Button
                                v-if="canInstall"
                                @click="handleInstallApp"
                                variant="default"
                            >
                                <Download class="mr-2 h-4 w-4" />
                                {{ t('Install App') }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Connection Status Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <WifiIcon v-if="isOnline" class="h-5 w-5 text-green-600" />
                            <WifiOff v-else class="h-5 w-5 text-orange-600" />
                            {{ t('Connection Status') }}
                        </CardTitle>
                        <CardDescription>
                            {{ isOnline ? t('You are online') : t('You are offline') }}
                        </CardDescription>
                    </CardHeader>
                </Card>

                <!-- Cached Chapters Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <HardDrive class="h-5 w-5" />
                            {{ t('Cached Chapters') }}
                        </CardTitle>
                        <CardDescription>
                            {{ cachedChapters.length }} {{ t('chapters cached') }} ({{ t('approx. ') }}{{ totalSize }} MB)
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="cachedChapters.length === 0" class="text-center py-8 text-muted-foreground">
                            <HardDrive class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>{{ t('No chapters cached yet') }}</p>
                            <p class="text-sm mt-1">
                                {{ t('Cache chapters from the Bible reading page for offline access') }}
                            </p>
                        </div>
                        <div v-else class="space-y-3">
                            <ScrollArea class="h-80">
                                <div class="space-y-2 pr-4">
                                    <div
                                        v-for="chapter in cachedChapters"
                                        :key="chapter.id"
                                        class="flex items-center justify-between rounded-lg border p-3"
                                    >
                                        <div class="flex-1">
                                            <p class="font-medium text-sm">
                                                {{ chapter.data.book?.title }}
                                                {{ t('Chapter') }} {{ chapter.chapterNumber }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ new Date(chapter.timestamp).toLocaleDateString() }}
                                            </p>
                                        </div>
                                        <Button
                                            @click="handleRemoveChapter(chapter.id)"
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </ScrollArea>
                            <div class="flex justify-end pt-2">
                                <Button
                                    @click="handleClearAll"
                                    variant="destructive"
                                    size="sm"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ t('Clear All') }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pending Mutations Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CloudUpload class="h-5 w-5" />
                            {{ t('Pending Changes') }}
                        </CardTitle>
                        <CardDescription>
                            {{ queuedMutations.length }} {{ t('changes waiting to sync') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="queuedMutations.length === 0" class="text-center py-8 text-muted-foreground">
                            <CloudUpload class="h-12 w-12 mx-auto mb-2 opacity-50" />
                            <p>{{ t('No pending changes') }}</p>
                            <p class="text-sm mt-1">
                                {{ t('Notes and highlights made offline will appear here until synced') }}
                            </p>
                        </div>
                        <div v-else class="space-y-3">
                            <ScrollArea class="h-64">
                                <div class="space-y-2 pr-4">
                                    <div
                                        v-for="mutation in queuedMutations"
                                        :key="mutation.id"
                                        class="rounded-lg border p-3"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-sm capitalize">
                                                    {{ mutation.type }} - {{ mutation.action }}
                                                </p>
                                                <p class="text-xs text-muted-foreground">
                                                    {{ new Date(mutation.timestamp).toLocaleString() }}
                                                </p>
                                            </div>
                                            <span
                                                v-if="mutation.retries > 0"
                                                class="text-xs text-orange-600"
                                            >
                                                {{ mutation.retries }} {{ t('retries') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </ScrollArea>
                            <div class="flex justify-end gap-2 pt-2">
                                <Button
                                    @click="handleClearMutations"
                                    variant="outline"
                                    size="sm"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ t('Clear All') }}
                                </Button>
                                <Button
                                    @click="handleSyncMutations"
                                    variant="default"
                                    size="sm"
                                    :disabled="!isOnline || isSyncing"
                                >
                                    <CloudUpload class="mr-2 h-4 w-4" :class="{ 'animate-pulse': isSyncing }" />
                                    {{ isSyncing ? t('Syncing...') : t('Sync Now') }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
