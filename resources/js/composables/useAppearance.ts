import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        const mediaQueryList = window.matchMedia(
            '(prefers-color-scheme: dark)',
        );
        const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

        document.documentElement.classList.toggle(
            'dark',
            systemTheme === 'dark',
        );
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const handleSystemThemeChange = (currentAppearance: Appearance) => {
    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize theme from user's database preference passed via Inertia
    const page = usePage();
    const userTheme = (page.props.theme as Appearance) || 'system';
    updateTheme(userTheme);

    // Set up system theme change listener
    if (userTheme === 'system') {
        mediaQuery()?.addEventListener('change', () =>
            handleSystemThemeChange(userTheme),
        );
    }
}

const appearance = ref<Appearance>('system');

export function useAppearance() {
    const page = usePage();
    const userTheme = computed(
        () => (page.props.theme as Appearance) || 'system',
    );

    onMounted(() => {
        appearance.value = userTheme.value;
        updateTheme(userTheme.value);
    });

    async function updateAppearance(value: Appearance) {
        appearance.value = value;
        updateTheme(value);

        // Update database if user is authenticated
        if (!page.props.auth?.user) {
            return;
        }

        try {
            // Get CSRF token
            let csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');
            if (!csrfToken && page.props.csrf_token) {
                csrfToken = String(page.props.csrf_token);
            }
            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            // Post new theme to user's database
            const response = await fetch('/api/user/theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    theme: value,
                }),
            });

            const result = await response.json();
            if (response.ok && result?.success) {
                // Reload page to ensure consistency
                window.location.reload();
            } else {
                console.error(result?.message || 'Failed to update theme.');
            }
        } catch (error) {
            console.error('Failed to update theme.', error);
        }
    }

    return {
        appearance,
        updateAppearance,
    };
}
