import {
    offlineDB,
    type CachedChapter,
    type ChapterData,
    type HighlightMutationData,
    type NoteMutationData,
    type QueuedMutation,
} from '@/lib/offlineDB';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const cachedChapters = ref<CachedChapter[]>([]);
const queuedMutations = ref<QueuedMutation[]>([]);
const isSyncing = ref(false);

// Configuration constants
const MAX_RETRIES = 3;

export function useOfflineData() {
    // Load cached chapters from IndexedDB
    const loadCachedChapters = async () => {
        try {
            cachedChapters.value = await offlineDB.getAllCachedChapters();
        } catch (error) {
            console.error(
                '[OfflineData] Error loading cached chapters:',
                error,
            );
        }
    };

    // Load queued mutations from IndexedDB
    const loadQueuedMutations = async () => {
        try {
            queuedMutations.value = await offlineDB.getAllMutations();
        } catch (error) {
            console.error(
                '[OfflineData] Error loading queued mutations:',
                error,
            );
        }
    };

    // Cache a chapter for offline reading
    const cacheChapter = async (
        bibleId: number,
        bookId: number,
        chapterNumber: number,
        data: ChapterData,
    ): Promise<void> => {
        const id = `bible_${bibleId}_book_${bookId}_chapter_${chapterNumber}`;

        const chapter: CachedChapter = {
            id,
            bibleId,
            bookId,
            chapterNumber,
            data,
            timestamp: Date.now(),
        };

        try {
            await offlineDB.cacheChapter(chapter);
            await loadCachedChapters();
            console.log('[OfflineData] Chapter cached:', id);
        } catch (error) {
            console.error('[OfflineData] Error caching chapter:', error);
            throw error;
        }
    };

    // Remove a cached chapter
    const removeCachedChapter = async (id: string): Promise<void> => {
        try {
            await offlineDB.removeCachedChapter(id);
            await loadCachedChapters();
            console.log('[OfflineData] Chapter removed:', id);
        } catch (error) {
            console.error('[OfflineData] Error removing chapter:', error);
            throw error;
        }
    };

    // Clear all cached chapters
    const clearAllChapters = async (): Promise<void> => {
        try {
            await offlineDB.clearAllChapters();
            cachedChapters.value = [];
            console.log('[OfflineData] All chapters cleared');
        } catch (error) {
            console.error('[OfflineData] Error clearing chapters:', error);
            throw error;
        }
    };

    // Check if a chapter is cached
    const isChapterCached = (
        bibleId: number,
        bookId: number,
        chapterNumber: number,
    ): boolean => {
        const id = `bible_${bibleId}_book_${bookId}_chapter_${chapterNumber}`;
        return cachedChapters.value.some((c) => c.id === id);
    };

    // Get a cached chapter
    const getCachedChapter = async (
        bibleId: number,
        bookId: number,
        chapterNumber: number,
    ): Promise<CachedChapter | null> => {
        const id = `bible_${bibleId}_book_${bookId}_chapter_${chapterNumber}`;
        try {
            return await offlineDB.getCachedChapter(id);
        } catch (error) {
            console.error('[OfflineData] Error getting cached chapter:', error);
            return null;
        }
    };

    // Queue a mutation for later sync
    const queueMutation = async (
        type: 'note' | 'highlight',
        action: 'create' | 'update' | 'delete',
        data: NoteMutationData | HighlightMutationData,
    ): Promise<void> => {
        // Use crypto.randomUUID if available, fallback to timestamp + random
        const generateId = () => {
            if (typeof crypto !== 'undefined' && crypto.randomUUID) {
                return crypto.randomUUID();
            }
            return `${type}_${action}_${Date.now()}_${Math.random().toString(36).substring(2, 15)}`;
        };

        const mutation: QueuedMutation = {
            id: generateId(),
            type,
            action,
            data,
            timestamp: Date.now(),
            retries: 0,
        };

        try {
            await offlineDB.queueMutation(mutation);
            await loadQueuedMutations();
            console.log('[OfflineData] Mutation queued:', mutation.id);

            // Try to register background sync
            if (
                'serviceWorker' in navigator &&
                'sync' in ServiceWorkerRegistration.prototype
            ) {
                const registration = await navigator.serviceWorker.ready;
                await registration.sync.register('sync-mutations');
            }
        } catch (error) {
            console.error('[OfflineData] Error queueing mutation:', error);
            throw error;
        }
    };

    // Sync queued mutations with server
    const syncMutations = async (): Promise<void> => {
        if (isSyncing.value) {
            console.log('[OfflineData] Sync already in progress');
            return;
        }

        if (!navigator.onLine) {
            console.log('[OfflineData] Cannot sync while offline');
            return;
        }

        isSyncing.value = true;
        const mutations = await offlineDB.getAllMutations();

        console.log('[OfflineData] Syncing', mutations.length, 'mutations');

        for (const mutation of mutations) {
            try {
                // Build the appropriate API request based on mutation type
                let endpoint = '';
                let method = 'POST';

                if (mutation.type === 'note') {
                    if (mutation.action === 'create') {
                        endpoint = '/notes';
                        method = 'POST';
                    } else if (mutation.action === 'update') {
                        endpoint = `/notes/${mutation.data.id}`;
                        method = 'PUT';
                    } else if (mutation.action === 'delete') {
                        endpoint = `/notes/${mutation.data.id}`;
                        method = 'DELETE';
                    }
                } else if (mutation.type === 'highlight') {
                    if (mutation.action === 'create') {
                        endpoint = '/verse-highlights';
                        method = 'POST';
                    } else if (mutation.action === 'update') {
                        endpoint = `/verse-highlights/${mutation.data.id}`;
                        method = 'PUT';
                    } else if (mutation.action === 'delete') {
                        endpoint = `/verse-highlights/${mutation.data.id}`;
                        method = 'DELETE';
                    }
                }

                // Validate endpoint is set
                if (!endpoint) {
                    console.error(
                        '[OfflineData] Unknown mutation type/action:',
                        mutation.type,
                        mutation.action,
                    );
                    await offlineDB.removeMutation(mutation.id);
                    continue;
                }

                // Use Inertia router for the request
                if (method === 'POST') {
                    await new Promise<void>((resolve, reject) => {
                        router.post(endpoint, mutation.data, {
                            preserveState: true,
                            preserveScroll: true,
                            onSuccess: () => resolve(),
                            onError: () => reject(new Error('Request failed')),
                        });
                    });
                } else if (method === 'PUT') {
                    await new Promise<void>((resolve, reject) => {
                        router.put(endpoint, mutation.data, {
                            preserveState: true,
                            preserveScroll: true,
                            onSuccess: () => resolve(),
                            onError: () => reject(new Error('Request failed')),
                        });
                    });
                } else if (method === 'DELETE') {
                    await new Promise<void>((resolve, reject) => {
                        router.delete(endpoint, {
                            preserveState: true,
                            preserveScroll: true,
                            onSuccess: () => resolve(),
                            onError: () => reject(new Error('Request failed')),
                        });
                    });
                }

                // Remove mutation from queue after successful sync
                await offlineDB.removeMutation(mutation.id);
                console.log('[OfflineData] Mutation synced:', mutation.id);
            } catch (error) {
                console.error(
                    '[OfflineData] Error syncing mutation:',
                    mutation.id,
                    error,
                );

                // Increment retry count
                mutation.retries++;
                if (mutation.retries < MAX_RETRIES) {
                    await offlineDB.queueMutation(mutation);
                } else {
                    console.error(
                        `[OfflineData] Mutation failed after ${MAX_RETRIES} retries, removing:`,
                        mutation.id,
                    );
                    await offlineDB.removeMutation(mutation.id);
                }
            }
        }

        await loadQueuedMutations();
        isSyncing.value = false;
        console.log('[OfflineData] Sync complete');
    };

    // Clear all mutations
    const clearAllMutations = async (): Promise<void> => {
        try {
            await offlineDB.clearAllMutations();
            queuedMutations.value = [];
            console.log('[OfflineData] All mutations cleared');
        } catch (error) {
            console.error('[OfflineData] Error clearing mutations:', error);
            throw error;
        }
    };

    // Initialize on mount
    onMounted(async () => {
        await offlineDB.init();
        await loadCachedChapters();
        await loadQueuedMutations();

        // Listen for sync events
        window.addEventListener('sync-mutations', () => {
            syncMutations();
        });

        // Listen for online event to auto-sync
        window.addEventListener('online', () => {
            console.log('[OfflineData] Back online, syncing mutations...');
            syncMutations();
        });
    });

    return {
        // Chapters
        cachedChapters: computed(() => cachedChapters.value),
        cacheChapter,
        removeCachedChapter,
        clearAllChapters,
        isChapterCached,
        getCachedChapter,

        // Mutations
        queuedMutations: computed(() => queuedMutations.value),
        queueMutation,
        syncMutations,
        clearAllMutations,
        isSyncing: computed(() => isSyncing.value),
    };
}
