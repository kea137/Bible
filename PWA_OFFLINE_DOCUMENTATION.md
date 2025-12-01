# PWA Offline Reading Implementation

This document describes the Progressive Web App (PWA) implementation for offline Bible reading.

## Overview

The Bible application now supports offline reading through PWA technologies, allowing users to:
- Install the app on their devices
- Cache selected Bible chapters for offline reading
- Create notes and highlights while offline
- Automatically sync changes when back online

## Architecture

### Core Components

1. **PWA Manifest** (`/public/manifest.webmanifest`)
   - Defines app metadata, icons, and shortcuts
   - Enables "Add to Home Screen" functionality
   - Configures standalone display mode

2. **Service Worker** (`/public/sw.js`)
   - Handles offline asset caching
   - Implements network-first strategy for API calls
   - Supports background sync for queued mutations
   - Cache versioning for updates

3. **IndexedDB Storage** (`resources/js/lib/offlineDB.ts`)
   - Stores cached Bible chapters
   - Queues offline mutations (notes/highlights)
   - Database schema:
     - `chapters` store: Cached chapter data
     - `mutations` store: Pending changes to sync

4. **Composables**
   - `useOffline.ts`: Manages PWA installation and service worker state
   - `useOfflineData.ts`: Handles chapter caching and mutation queue

### UI Components

- **OfflineStatus**: Floating indicator showing connection status and pending syncs
- **Bible Reading Page**: Cache/uncache buttons for each chapter
- **Offline Settings**: Complete management interface for cached data

## Features

### 1. App Installation
- Users can install the app via browser prompt or settings page
- Detects if app is already installed
- Shows service worker status

### 2. Offline Chapter Caching
- Cache individual chapters from the Bible reading page
- View all cached chapters in settings
- Remove chapters individually or clear all
- Storage size estimation

### 3. Offline Mutations Queue
- Notes and highlights created offline are queued
- Automatic sync when connection restored
- Retry logic with max 3 attempts
- Manual sync option in settings

### 4. Background Sync
- Uses Background Sync API when available
- Triggers automatic sync on reconnection
- Falls back to manual sync if API unavailable

## User Flow

### Caching a Chapter
1. Navigate to any Bible chapter
2. Click the "Cache Offline" button
3. Chapter is stored in IndexedDB
4. Button changes to "Cached" with remove icon

### Working Offline
1. App detects offline status
2. Shows orange "Offline Mode" indicator
3. Cached chapters load from IndexedDB
4. Notes/highlights are queued for later sync

### Syncing Changes
1. App detects online status
2. Automatically attempts to sync queued mutations
3. Shows blue "Syncing..." indicator during sync
4. Removes successfully synced mutations from queue

## Technical Details

### Storage Strategy
- **Static Assets**: Cache-first with fallback to network
- **API Requests**: Network-first with cache fallback
- **Chapter Data**: Stored in IndexedDB (not service worker cache)

### Cache Management
- Service worker uses versioned caches
- Old caches automatically cleaned on activation
- IndexedDB independent of service worker lifecycle

### Type Safety
- Strongly typed interfaces for all data structures
- `ChapterData`, `NoteMutationData`, `HighlightMutationData`
- Comprehensive TypeScript coverage

## Configuration

### Constants
- `MAX_RETRIES = 3`: Maximum sync attempts for failed mutations
- `ESTIMATED_CHAPTER_SIZE_KB = 10`: Size estimation for storage display
- `DB_VERSION = 1`: IndexedDB schema version

### Service Worker Caches
- `bible-static-v1`: Static assets (manifest, icons)
- `bible-dynamic-v1`: Dynamic API responses
- `bible-chapters-v1`: Reserved for future use

## Browser Compatibility

### Required Features
- Service Workers
- IndexedDB
- Fetch API
- Promise

### Optional Features
- Background Sync API (gracefully degrades)
- Web App Manifest
- `crypto.randomUUID()` (falls back to timestamp)

## Security Considerations

- Service worker only serves same-origin requests
- CSRF tokens validated for all mutations
- No sensitive data cached (only public Bible content)
- User-specific data (notes/highlights) requires authentication

## Future Enhancements

Potential improvements for future development:
- Background download of entire books/Bibles
- Offline search within cached chapters
- Periodic background sync for updates
- Push notifications for sync completion
- Storage quota management and warnings
- Compression for cached data
- Differential sync for bandwidth optimization

## Troubleshooting

### Service Worker Not Registering
- Check browser console for errors
- Ensure HTTPS or localhost
- Verify `/sw.js` is accessible
- Check service worker scope

### Chapters Not Caching
- Verify IndexedDB is enabled in browser
- Check storage quota
- Ensure user is authenticated for auth-required features
- Check browser console for errors

### Sync Not Working
- Verify online status
- Check pending mutations in settings
- Look for API errors in network tab
- Ensure valid CSRF token

## Development

### Testing Offline Functionality
1. Open DevTools
2. Go to Application > Service Workers
3. Check "Offline" checkbox
4. Test app functionality

### Debugging IndexedDB
1. Open DevTools
2. Go to Application > IndexedDB
3. Expand `BibleOfflineDB`
4. Inspect `chapters` and `mutations` stores

### Updating Service Worker
1. Modify `/public/sw.js`
2. Increment `CACHE_VERSION`
3. Build and deploy
4. Service worker auto-updates on next page load
