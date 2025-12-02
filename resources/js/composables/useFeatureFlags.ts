/**
 * Feature flags composable
 *
 * Provides a simple feature flag system for gradual feature rollout.
 * Feature flags can be toggled via configuration or admin interface.
 */

import { reactive, readonly } from 'vue';

export interface FeatureFlags {
    verseSharing: boolean;
    lessonSystem: boolean;
    verseCanvas: boolean;
    offlineMode: boolean;
    advancedSearch: boolean;
    parallelBibles: boolean;
    crossReferences: boolean;
    darkMode: boolean;
    multiLanguage: boolean;
    userNotes: boolean;
}

// Default feature flags - all enabled by default for existing features
const defaultFlags: FeatureFlags = {
    verseSharing: true,
    lessonSystem: true,
    verseCanvas: true,
    offlineMode: true,
    advancedSearch: true,
    parallelBibles: true,
    crossReferences: true,
    darkMode: true,
    multiLanguage: true,
    userNotes: true,
};

// Reactive feature flags state
const flags = reactive<FeatureFlags>({ ...defaultFlags });

// Storage key for feature flags
const STORAGE_KEY = 'app_feature_flags';

/**
 * Initialize feature flags from localStorage
 */
export function initializeFeatureFlags(): void {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            const storedFlags = JSON.parse(stored) as Partial<FeatureFlags>;
            Object.assign(flags, { ...defaultFlags, ...storedFlags });
        }

        if (import.meta.env.DEV) {
            console.log('[Feature Flags] Initialized:', flags);
        }
    } catch (error) {
        console.error('[Feature Flags] Failed to load:', error);
    }
}

/**
 * Check if a feature is enabled
 */
export function isFeatureEnabled(feature: keyof FeatureFlags): boolean {
    return flags[feature] ?? false;
}

/**
 * Toggle a feature flag
 * @param feature - The feature to toggle
 * @param enabled - Optional: Set to specific state. If omitted, toggles current state
 */
export function toggleFeature(
    feature: keyof FeatureFlags,
    enabled?: boolean,
): void {
    const newValue = enabled !== undefined ? enabled : !flags[feature];
    flags[feature] = newValue;

    // Persist to localStorage
    saveFeatureFlags();

    if (import.meta.env.DEV) {
        console.log(`[Feature Flags] ${feature}:`, newValue);
    }
}

/**
 * Set multiple feature flags at once
 */
export function setFeatureFlags(newFlags: Partial<FeatureFlags>): void {
    Object.assign(flags, newFlags);
    saveFeatureFlags();
}

/**
 * Get all feature flags
 */
export function getAllFeatureFlags(): Readonly<FeatureFlags> {
    return readonly(flags);
}

/**
 * Reset all feature flags to defaults
 */
export function resetFeatureFlags(): void {
    Object.assign(flags, defaultFlags);
    saveFeatureFlags();

    if (import.meta.env.DEV) {
        console.log('[Feature Flags] Reset to defaults');
    }
}

/**
 * Save feature flags to localStorage
 */
function saveFeatureFlags(): void {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(flags));
    } catch (error) {
        console.error('[Feature Flags] Failed to save:', error);
    }
}

/**
 * Get feature flag metadata for admin UI
 */
export function getFeatureFlagMetadata(): Array<{
    key: keyof FeatureFlags;
    label: string;
    description: string;
    enabled: boolean;
}> {
    return [
        {
            key: 'verseSharing',
            label: 'Verse Sharing',
            description:
                'Enable verse sharing with beautiful backgrounds (gradients and photos)',
            enabled: flags.verseSharing,
        },
        {
            key: 'lessonSystem',
            label: 'Lesson System',
            description:
                'Enable Bible study lessons and progress tracking features',
            enabled: flags.lessonSystem,
        },
        {
            key: 'verseCanvas',
            label: 'Verse Link Canvas',
            description:
                'Enable visual canvas for creating connections between Bible verses',
            enabled: flags.verseCanvas,
        },
        {
            key: 'offlineMode',
            label: 'Offline Mode',
            description: 'Enable offline reading and PWA capabilities',
            enabled: flags.offlineMode,
        },
        {
            key: 'advancedSearch',
            label: 'Advanced Search',
            description:
                'Enable unified search across verses, books, and content',
            enabled: flags.advancedSearch,
        },
        {
            key: 'parallelBibles',
            label: 'Parallel Bibles',
            description: 'Enable parallel Bible view for comparing translations',
            enabled: flags.parallelBibles,
        },
        {
            key: 'crossReferences',
            label: 'Cross References',
            description: 'Enable cross-reference system for Bible verses',
            enabled: flags.crossReferences,
        },
        {
            key: 'darkMode',
            label: 'Dark Mode',
            description: 'Enable dark mode theme support',
            enabled: flags.darkMode,
        },
        {
            key: 'multiLanguage',
            label: 'Multi-Language',
            description: 'Enable multiple language support and translations',
            enabled: flags.multiLanguage,
        },
        {
            key: 'userNotes',
            label: 'User Notes',
            description: 'Enable verse highlighting and personal notes',
            enabled: flags.userNotes,
        },
    ];
}

export function useFeatureFlags() {
    return {
        initializeFeatureFlags,
        isFeatureEnabled,
        toggleFeature,
        setFeatureFlags,
        getAllFeatureFlags,
        resetFeatureFlags,
        getFeatureFlagMetadata,
    };
}
