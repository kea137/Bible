// IndexedDB wrapper for offline chapter caching and mutation queue

const DB_NAME = 'BibleOfflineDB';
const DB_VERSION = 1;
const CHAPTERS_STORE = 'chapters';
const MUTATIONS_STORE = 'mutations';

export interface CachedChapter {
    id: string; // e.g., "bible_1_book_1_chapter_1"
    bibleId: number;
    bookId: number;
    chapterNumber: number;
    data: any;
    timestamp: number;
}

export interface QueuedMutation {
    id: string;
    type: 'note' | 'highlight';
    action: 'create' | 'update' | 'delete';
    data: any;
    timestamp: number;
    retries: number;
}

class OfflineDB {
    private db: IDBDatabase | null = null;

    async init(): Promise<void> {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => {
                console.error('[IndexedDB] Error opening database:', request.error);
                reject(request.error);
            };

            request.onsuccess = () => {
                this.db = request.result;
                console.log('[IndexedDB] Database opened successfully');
                resolve();
            };

            request.onupgradeneeded = (event) => {
                const db = (event.target as IDBOpenDBRequest).result;

                // Create chapters store
                if (!db.objectStoreNames.contains(CHAPTERS_STORE)) {
                    const chaptersStore = db.createObjectStore(CHAPTERS_STORE, {
                        keyPath: 'id',
                    });
                    chaptersStore.createIndex('bibleId', 'bibleId', { unique: false });
                    chaptersStore.createIndex('timestamp', 'timestamp', { unique: false });
                }

                // Create mutations queue store
                if (!db.objectStoreNames.contains(MUTATIONS_STORE)) {
                    const mutationsStore = db.createObjectStore(MUTATIONS_STORE, {
                        keyPath: 'id',
                    });
                    mutationsStore.createIndex('timestamp', 'timestamp', { unique: false });
                    mutationsStore.createIndex('type', 'type', { unique: false });
                }

                console.log('[IndexedDB] Database upgraded successfully');
            };
        });
    }

    // Chapter caching methods
    async cacheChapter(chapter: CachedChapter): Promise<void> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([CHAPTERS_STORE], 'readwrite');
            const store = transaction.objectStore(CHAPTERS_STORE);
            const request = store.put(chapter);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async getCachedChapter(id: string): Promise<CachedChapter | null> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([CHAPTERS_STORE], 'readonly');
            const store = transaction.objectStore(CHAPTERS_STORE);
            const request = store.get(id);

            request.onsuccess = () => resolve(request.result || null);
            request.onerror = () => reject(request.error);
        });
    }

    async getAllCachedChapters(): Promise<CachedChapter[]> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([CHAPTERS_STORE], 'readonly');
            const store = transaction.objectStore(CHAPTERS_STORE);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    }

    async removeCachedChapter(id: string): Promise<void> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([CHAPTERS_STORE], 'readwrite');
            const store = transaction.objectStore(CHAPTERS_STORE);
            const request = store.delete(id);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async clearAllChapters(): Promise<void> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([CHAPTERS_STORE], 'readwrite');
            const store = transaction.objectStore(CHAPTERS_STORE);
            const request = store.clear();

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    // Mutation queue methods
    async queueMutation(mutation: QueuedMutation): Promise<void> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([MUTATIONS_STORE], 'readwrite');
            const store = transaction.objectStore(MUTATIONS_STORE);
            const request = store.put(mutation);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async getAllMutations(): Promise<QueuedMutation[]> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([MUTATIONS_STORE], 'readonly');
            const store = transaction.objectStore(MUTATIONS_STORE);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    }

    async removeMutation(id: string): Promise<void> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([MUTATIONS_STORE], 'readwrite');
            const store = transaction.objectStore(MUTATIONS_STORE);
            const request = store.delete(id);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async clearAllMutations(): Promise<void> {
        if (!this.db) await this.init();

        return new Promise((resolve, reject) => {
            const transaction = this.db!.transaction([MUTATIONS_STORE], 'readwrite');
            const store = transaction.objectStore(MUTATIONS_STORE);
            const request = store.clear();

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }
}

// Export singleton instance
export const offlineDB = new OfflineDB();
