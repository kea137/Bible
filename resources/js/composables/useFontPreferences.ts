import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

export type FontFamily =
    | 'instrument-sans'
    | 'system'
    | 'serif'
    | 'sans-serif'
    | 'monospace'
    | 'times'
    | 'georgia'
    | 'arial'
    | 'helvetica'
    | 'courier';

export type FontSize = 'xs' | 'sm' | 'base' | 'lg' | 'xl';

interface FontPreferences {
    fontFamily: FontFamily;
    fontSize: FontSize;
}

const fontFamilyMap: Record<FontFamily, string> = {
    'instrument-sans':
        'Instrument Sans, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
    system:
        'ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
    serif: 'ui-serif, Georgia, Cambria, "Times New Roman", Times, serif',
    'sans-serif':
        'ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji"',
    monospace: 'ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace',
    times: '"Times New Roman", Times, serif',
    georgia: 'Georgia, serif',
    arial: 'Arial, Helvetica, sans-serif',
    helvetica: 'Helvetica, Arial, sans-serif',
    courier: '"Courier New", Courier, monospace',
};

const fontSizeMap: Record<FontSize, string> = {
    xs: '12px',
    sm: '14px',
    base: '16px',
    lg: '18px',
    xl: '20px',
};

export function applyFontPreferences(preferences: FontPreferences) {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    const fontFamily = fontFamilyMap[preferences.fontFamily];
    const fontSize = fontSizeMap[preferences.fontSize];

    // Override Tailwind's --font-sans variable to change font globally
    document.documentElement.style.setProperty('--font-sans', fontFamily);
    document.documentElement.style.setProperty('--font-size', fontSize);
    
    // Also apply directly to body to ensure immediate update
    document.body.style.fontFamily = fontFamily;
    document.body.style.fontSize = fontSize;
}

export function initializeFontPreferences() {
    if (typeof window === 'undefined') {
        return;
    }

    const applyFonts = () => {
        try {
            // Initialize font from user's database preference passed via Inertia
            const page = usePage();
            if (!page || !page.props) {
                console.warn('Page props not available yet for font initialization');
                return;
            }

            const fontFamily =
                (page.props.fontFamily as FontFamily | undefined) || 'system';
            const fontSize = (page.props.fontSize as FontSize | undefined) || 'base';

            console.log('Initializing fonts:', { fontFamily, fontSize });
            applyFontPreferences({ fontFamily, fontSize });
        } catch (error) {
            console.error('Error initializing font preferences:', error);
        }
    };

    // Apply fonts initially
    applyFonts();

    // Reapply fonts after Inertia navigations
    document.addEventListener('inertia:finish', applyFonts);
}

const fontPreferences = ref<FontPreferences>({
    fontFamily: 'system',
    fontSize: 'base',
});

export function useFontPreferences() {
    const page = usePage();

    const userFontFamily = computed(
        () => (page.props.fontFamily as FontFamily | undefined) || 'system',
    );
    const userFontSize = computed(
        () => (page.props.fontSize as FontSize | undefined) || 'base',
    );

    onMounted(() => {
        fontPreferences.value = {
            fontFamily: userFontFamily.value,
            fontSize: userFontSize.value,
        };
        applyFontPreferences(fontPreferences.value);
    });

    async function updateFontPreferences(
        preferences: Partial<FontPreferences>,
    ) {
        const newPreferences = {
            ...fontPreferences.value,
            ...preferences,
        };
        fontPreferences.value = newPreferences;
        applyFontPreferences(newPreferences);

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

            // Post new font preferences to user's database
            const response = await fetch('/api/user/font-preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    font_family: newPreferences.fontFamily,
                    font_size: newPreferences.fontSize,
                }),
            });

            const result = await response.json();
            if (response.ok && result?.success) {
                // Reload page to ensure consistency
                window.location.reload();
            } else {
                console.error(
                    result?.message || 'Failed to update font preferences.',
                );
            }
        } catch (error) {
            console.error('Failed to update font preferences.', error);
        }
    }

    return {
        fontPreferences,
        updateFontPreferences,
    };
}
