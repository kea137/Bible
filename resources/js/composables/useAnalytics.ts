/**
 * Privacy-safe analytics composable
 *
 * This composable provides analytics tracking without collecting personally
 * identifiable information (PII). It respects user privacy and can be
 * configured to be opt-in.
 */

import { ref } from 'vue';

export interface AnalyticsEvent {
    category: string;
    action: string;
    label?: string;
    value?: number;
}

const analyticsEnabled = ref(true);
const events: AnalyticsEvent[] = [];

/**
 * Initialize analytics system
 * Checks user preferences and localStorage for analytics consent
 */
export function initializeAnalytics(): void {
    // Check if user has opted out of analytics
    const consent = localStorage.getItem('analytics_consent');
    if (consent === 'false') {
        analyticsEnabled.value = false;
    }

    // Log initialization status
    if (import.meta.env.DEV) {
        console.log(
            '[Analytics] Initialized:',
            analyticsEnabled.value ? 'enabled' : 'disabled',
        );
    }
}

/**
 * Track an analytics event
 * Only tracks if analytics is enabled and user has consented
 */
export function trackEvent(event: AnalyticsEvent): void {
    if (!analyticsEnabled.value) {
        return;
    }

    // Store event locally (in production, this would send to analytics service)
    const timestamp = new Date().toISOString();
    const eventWithTimestamp = { ...event, timestamp };

    events.push(eventWithTimestamp);

    // In development, log the event
    if (import.meta.env.DEV) {
        console.log('[Analytics] Event:', eventWithTimestamp);
    }

    // In production, you would send this to your analytics backend
    // Example: sendToAnalyticsService(eventWithTimestamp);
}

/**
 * Track page view
 */
export function trackPageView(pageName: string): void {
    trackEvent({
        category: 'Navigation',
        action: 'Page View',
        label: pageName,
    });
}

/**
 * Track Bible reading activity
 */
export function trackBibleReading(
    translation: string,
    book: string,
    chapter: number,
): void {
    trackEvent({
        category: 'Bible Reading',
        action: 'Chapter View',
        label: `${translation} - ${book} ${chapter}`,
    });
}

/**
 * Track verse sharing
 */
export function trackVerseShare(translation: string, shareMethod: string): void {
    trackEvent({
        category: 'Verse Sharing',
        action: 'Share',
        label: `${translation} - ${shareMethod}`,
    });
}

/**
 * Track lesson progress
 */
export function trackLessonProgress(
    lessonId: number,
    action: 'start' | 'complete',
): void {
    trackEvent({
        category: 'Lessons',
        action: action === 'start' ? 'Lesson Started' : 'Lesson Completed',
        label: `Lesson ${lessonId}`,
        value: lessonId,
    });
}

/**
 * Track search usage
 */
export function trackSearch(query: string, resultsCount: number): void {
    // Don't store the actual query for privacy
    trackEvent({
        category: 'Search',
        action: 'Search Query',
        label: resultsCount > 0 ? 'Results Found' : 'No Results',
        value: resultsCount,
    });
}

/**
 * Set analytics consent
 */
export function setAnalyticsConsent(enabled: boolean): void {
    analyticsEnabled.value = enabled;
    localStorage.setItem('analytics_consent', enabled.toString());

    if (import.meta.env.DEV) {
        console.log(
            '[Analytics] Consent updated:',
            enabled ? 'enabled' : 'disabled',
        );
    }
}

/**
 * Get analytics consent status
 */
export function getAnalyticsConsent(): boolean {
    return analyticsEnabled.value;
}

/**
 * Get all tracked events (for debugging)
 */
export function getTrackedEvents(): AnalyticsEvent[] {
    return [...events];
}

export function useAnalytics() {
    return {
        initializeAnalytics,
        trackEvent,
        trackPageView,
        trackBibleReading,
        trackVerseShare,
        trackLessonProgress,
        trackSearch,
        setAnalyticsConsent,
        getAnalyticsConsent,
        getTrackedEvents,
    };
}
