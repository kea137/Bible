import { ref, onMounted, onUnmounted } from 'vue';

export interface OfflineState {
    isOnline: boolean;
    isInstalled: boolean;
    canInstall: boolean;
    serviceWorkerReady: boolean;
}

const isOnline = ref(true);
const isInstalled = ref(false);
const canInstall = ref(false);
const serviceWorkerReady = ref(false);
const deferredPrompt = ref<any>(null);

let registration: ServiceWorkerRegistration | null = null;

export function useOffline() {
    const updateOnlineStatus = () => {
        isOnline.value = navigator.onLine;
    };

    const handleBeforeInstallPrompt = (e: Event) => {
        e.preventDefault();
        deferredPrompt.value = e;
        canInstall.value = true;
    };

    const handleAppInstalled = () => {
        deferredPrompt.value = null;
        canInstall.value = false;
        isInstalled.value = true;
    };

    const registerServiceWorker = async () => {
        if ('serviceWorker' in navigator) {
            try {
                registration = await navigator.serviceWorker.register('/sw.js', {
                    scope: '/',
                });

                console.log('[App] Service Worker registered:', registration);

                // Check if service worker is ready
                if (registration.active) {
                    serviceWorkerReady.value = true;
                }

                registration.addEventListener('updatefound', () => {
                    const newWorker = registration?.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'activated') {
                                serviceWorkerReady.value = true;
                            }
                        });
                    }
                });

                // Listen for messages from service worker
                navigator.serviceWorker.addEventListener('message', (event) => {
                    if (event.data && event.data.type === 'SYNC_MUTATIONS') {
                        // Trigger sync in the app
                        window.dispatchEvent(new CustomEvent('sync-mutations'));
                    }
                });
            } catch (error) {
                console.error('[App] Service Worker registration failed:', error);
            }
        }
    };

    const installApp = async () => {
        if (!deferredPrompt.value) {
            return false;
        }

        deferredPrompt.value.prompt();
        const { outcome } = await deferredPrompt.value.userChoice;

        if (outcome === 'accepted') {
            console.log('[App] User accepted install prompt');
            deferredPrompt.value = null;
            canInstall.value = false;
            isInstalled.value = true;
            return true;
        } else {
            console.log('[App] User dismissed install prompt');
            return false;
        }
    };

    const skipWaiting = () => {
        if (registration?.waiting) {
            registration.waiting.postMessage({ type: 'SKIP_WAITING' });
        }
    };

    const clearCache = async () => {
        if ('serviceWorker' in navigator && registration) {
            registration.active?.postMessage({ type: 'CLEAR_CACHE' });
        }
    };

    onMounted(() => {
        updateOnlineStatus();
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        window.addEventListener('appinstalled', handleAppInstalled);

        // Check if app is already installed
        if (window.matchMedia('(display-mode: standalone)').matches) {
            isInstalled.value = true;
        }

        // Register service worker
        registerServiceWorker();
    });

    onUnmounted(() => {
        window.removeEventListener('online', updateOnlineStatus);
        window.removeEventListener('offline', updateOnlineStatus);
        window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        window.removeEventListener('appinstalled', handleAppInstalled);
    });

    return {
        isOnline,
        isInstalled,
        canInstall,
        serviceWorkerReady,
        installApp,
        skipWaiting,
        clearCache,
    };
}
