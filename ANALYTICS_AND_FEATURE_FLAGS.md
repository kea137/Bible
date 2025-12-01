# Analytics & Feature Flags Documentation

## Analytics

### Overview

The Bible application implements privacy-safe analytics to understand how users interact with the application without collecting personally identifiable information (PII).

### Privacy-First Design

Our analytics system is designed with privacy as the top priority:

- **No PII Collection**: We do not collect names, emails, IP addresses, or any personally identifiable information
- **Opt-In/Opt-Out**: Users can enable or disable analytics at any time
- **Local Storage**: Analytics preferences are stored locally in the browser
- **Transparent**: Clear documentation of what we track

### What We Track

The analytics system tracks the following anonymous usage data:

1. **Page Views**: Which pages users visit (without user identity)
2. **Bible Reading**: Translation and chapter viewed (not specific verses or content)
3. **Feature Usage**: Use of features like verse sharing, lessons, search
4. **Search Activity**: Number of search queries and results (not the actual queries)
5. **Lesson Progress**: Lesson starts and completions (not user identity)

### What We DO NOT Track

- User names or email addresses
- IP addresses or location data
- Specific verse content or personal notes
- Cross-site tracking
- Third-party advertising data

### Usage

#### Initialization

Analytics is automatically initialized when the application loads:

```typescript
import { initializeAnalytics } from '@/composables/useAnalytics';

initializeAnalytics();
```

#### Tracking Events

```typescript
import { useAnalytics } from '@/composables/useAnalytics';

const { trackEvent, trackPageView, trackBibleReading } = useAnalytics();

// Track a page view
trackPageView('Bible Reading');

// Track Bible reading
trackBibleReading('KJV', 'John', 3);

// Track custom event
trackEvent({
    category: 'User Interaction',
    action: 'Button Click',
    label: 'Share Verse',
});
```

#### Managing Consent

```typescript
import { setAnalyticsConsent, getAnalyticsConsent } from '@/composables/useAnalytics';

// Check current consent status
const isEnabled = getAnalyticsConsent();

// Update consent
setAnalyticsConsent(true); // Enable
setAnalyticsConsent(false); // Disable
```

### User Controls

Users can manage analytics preferences in the application settings:

1. Navigate to Settings
2. Find "Privacy & Analytics" section
3. Toggle analytics on or off
4. View detailed information about what is tracked

---

## Feature Flags

### Overview

Feature flags allow gradual rollout of new features and quick disabling of problematic features without code changes.

### Available Feature Flags

The following features can be toggled:

| Feature Flag | Description | Default |
|--------------|-------------|---------|
| `verseSharing` | Verse sharing with backgrounds | Enabled |
| `lessonSystem` | Bible study lessons | Enabled |
| `verseCanvas` | Verse link canvas | Enabled |
| `offlineMode` | Offline reading capabilities | Enabled |
| `advancedSearch` | Unified search | Enabled |
| `parallelBibles` | Parallel Bible view | Enabled |
| `crossReferences` | Cross-reference system | Enabled |
| `darkMode` | Dark mode theme | Enabled |
| `multiLanguage` | Multi-language support | Enabled |
| `userNotes` | Verse notes and highlighting | Enabled |

### Usage

#### Initialization

Feature flags are automatically initialized when the application loads:

```typescript
import { initializeFeatureFlags } from '@/composables/useFeatureFlags';

initializeFeatureFlags();
```

#### Checking Feature Status

```typescript
import { isFeatureEnabled } from '@/composables/useFeatureFlags';

// Check if a feature is enabled
if (isFeatureEnabled('verseSharing')) {
    // Show verse sharing UI
}
```

#### Toggling Features

```typescript
import { toggleFeature } from '@/composables/useFeatureFlags';

// Toggle a feature
toggleFeature('darkMode');

// Set to specific state
toggleFeature('darkMode', true); // Enable
toggleFeature('darkMode', false); // Disable
```

#### Using in Components

```vue
<script setup lang="ts">
import { isFeatureEnabled } from '@/composables/useFeatureFlags';

const showVerseSharing = isFeatureEnabled('verseSharing');
</script>

<template>
    <div v-if="showVerseSharing">
        <!-- Verse sharing UI -->
    </div>
</template>
```

### Managing Feature Flags

Users with appropriate permissions can manage feature flags:

1. Navigate to Settings
2. Find "Feature Flags" section
3. Toggle features on or off
4. View descriptions of each feature

### Storage

- Feature flags are stored in browser localStorage
- Flags persist across sessions
- Can be reset to defaults at any time

### Development

In development mode, feature flag changes are logged to the console:

```
[Feature Flags] Initialized: { verseSharing: true, ... }
[Feature Flags] verseSharing: true
[Feature Flags] Reset to defaults
```

### Best Practices

1. **Gradual Rollout**: Enable new features for small user groups first
2. **Quick Disable**: Use flags to quickly disable problematic features
3. **Testing**: Test features in isolation before full rollout
4. **Communication**: Document feature flag changes in release notes

### API Reference

#### `initializeFeatureFlags()`
Initializes feature flags from localStorage.

#### `isFeatureEnabled(feature: keyof FeatureFlags): boolean`
Returns whether a feature is currently enabled.

#### `toggleFeature(feature: keyof FeatureFlags, enabled?: boolean): void`
Toggles a feature flag or sets it to a specific state.

#### `setFeatureFlags(flags: Partial<FeatureFlags>): void`
Sets multiple feature flags at once.

#### `getAllFeatureFlags(): Readonly<FeatureFlags>`
Returns all feature flags as a readonly object.

#### `resetFeatureFlags(): void`
Resets all feature flags to their default values.

#### `getFeatureFlagMetadata()`
Returns metadata about all feature flags for admin UI.

---

## Integration Example

Here's how analytics and feature flags work together:

```typescript
import { useAnalytics } from '@/composables/useAnalytics';
import { isFeatureEnabled } from '@/composables/useFeatureFlags';

const { trackEvent } = useAnalytics();

// Only show and track feature if enabled
if (isFeatureEnabled('verseSharing')) {
    // Show UI
    showVerseShareButton.value = true;
    
    // Track usage
    function shareVerse() {
        trackEvent({
            category: 'Verse Sharing',
            action: 'Share',
            label: 'Social Media',
        });
        // ... sharing logic
    }
}
```

## Configuration

Both analytics and feature flags can be configured via environment variables in production:

```env
# Analytics
VITE_ANALYTICS_ENABLED=true
VITE_ANALYTICS_ENDPOINT=https://your-analytics-service.com

# Feature Flags (for server-side management)
VITE_FEATURE_FLAGS_ENDPOINT=https://your-feature-flags-service.com
```

## Security & Privacy

- Analytics data is anonymized and aggregated
- No sensitive user data is transmitted
- All analytics calls respect user consent
- Feature flags do not expose sensitive information
- GDPR and privacy law compliant

---

**Last Updated**: December 2024
