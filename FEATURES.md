# Bible Application Features Documentation

This document provides comprehensive documentation for all major features of the Bible application.

## Table of Contents

1. [Unified Search](#unified-search)
2. [Lesson Feature](#lesson-feature)

---

## Unified Search

### Overview

The Bible application includes a powerful unified search system built with Laravel Scout and Meilisearch. This feature allows users to search across verses, notes, and lessons with advanced filtering capabilities.

### Search Capabilities

- **Full-text search** across verses, notes, and lessons
- **Faceted filtering** by:
  - Bible version/translation
  - Book
  - Language
  - Series (for lessons)
  - Date ranges (for notes and lessons)
- **Real-time search** with instant results
- **Pagination** support for large result sets
- **User-scoped search** for notes (users only see their own notes)

### Indexed Content

#### Verses
- Text content
- Book name
- Chapter and verse numbers
- Bible version/translation
- Language

#### Notes
- Title
- Content
- Associated verse
- Creation and update timestamps
- User ownership (private to each user)

#### Lessons
- Title
- Description
- Language
- Series information
- Episode number
- Creation timestamp

### Setup Instructions

#### 1. Install Meilisearch

##### Using Docker (Recommended)
```bash
docker run -d \
  --name meilisearch \
  -p 7700:7700 \
  -e MEILI_ENV='development' \
  -v $(pwd)/meili_data:/meili_data \
  getmeili/meilisearch:latest
```

##### Using Homebrew (macOS)
```bash
brew install meilisearch
brew services start meilisearch
```

##### Using Binary (Linux/Windows)
Download from [Meilisearch releases](https://github.com/meilisearch/meilisearch/releases) and run:
```bash
./meilisearch
```

#### 2. Configure Environment

Update your `.env` file with Meilisearch configuration:

```env
# Set Scout driver to meilisearch
SCOUT_DRIVER=meilisearch

# Meilisearch connection
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=
```

For production, generate a master key:
```env
MEILISEARCH_KEY=your-secure-master-key-here
```

#### 3. Index Your Data

Run the reindexing command to populate the search indexes:

```bash
# Index all models (verses, notes, lessons)
php artisan search:reindex

# Index specific model only
php artisan search:reindex verses
php artisan search:reindex notes
php artisan search:reindex lessons
```

**Note**: The initial indexing may take several minutes depending on the amount of data.

### API Usage

#### Search Endpoint

**URL**: `GET /api/search`

**Parameters**:
- `query` (required): Search query string
- `types` (optional): JSON array of types to search (default: `["verses", "notes", "lessons"]`)
- `filters` (optional): JSON object with filter criteria
- `per_page` (optional): Results per page (default: 10, max: 100)

**Filter Options**:
```json
{
  "bible_id": 1,
  "book_id": 2,
  "version": "KJV",
  "language": "English",
  "series_id": 3,
  "date_from": "2024-01-01",
  "date_to": "2024-12-31"
}
```

**Example Request**:
```bash
curl "http://localhost/api/search?query=love&types=[\"verses\"]&filters={\"version\":\"KJV\"}"
```

**Example Response**:
```json
{
  "query": "love",
  "filters": {
    "version": "KJV"
  },
  "verses": {
    "data": [
      {
        "id": 123,
        "text": "For God so loved the world...",
        "verse_number": 16,
        "book": "John",
        "chapter": 3,
        "version": "KJV",
        "language": "English"
      }
    ],
    "total": 150,
    "current_page": 1,
    "per_page": 10
  },
  "notes": [],
  "lessons": []
}
```

#### Filter Options Endpoint

**URL**: `GET /api/search/filters`

Returns available filter options:
```json
{
  "bibles": [...],
  "books": [...],
  "series": [...],
  "languages": [...]
}
```

### Frontend Integration

#### Using the Search Component

```vue
<template>
  <UnifiedSearch />
</template>

<script setup>
import UnifiedSearch from '@/components/UnifiedSearch.vue';
</script>
```

The `UnifiedSearch` component provides:
- Search input with real-time query
- Filter panel with checkboxes and dropdowns
- Results display for all content types
- Pagination controls
- Loading states

### Reindexing

#### When to Reindex

Reindex your search data when:
- Setting up the search feature for the first time
- After bulk data imports
- If search results seem out of sync with your database
- After changing index settings in `config/scout.php`

#### Automatic Indexing

Scout automatically keeps indexes in sync when you:
- Create new records (verses, notes, lessons)
- Update existing records
- Delete records

#### Manual Reindexing

```bash
# Full reindex
php artisan search:reindex

# Specific model
php artisan search:reindex verses

# Clear and rebuild indexes
php artisan scout:flush "App\Models\Verse"
php artisan scout:import "App\Models\Verse"
```

### Performance Optimization

#### Index Settings

Index settings are configured in `config/scout.php`:

```php
'meilisearch' => [
    'index-settings' => [
        'verses' => [
            'filterableAttributes' => ['bible_id', 'book_id', 'version', 'language'],
            'sortableAttributes' => ['book_id', 'chapter_id', 'verse_number'],
            'searchableAttributes' => ['text', 'book_name', 'version'],
        ],
        // ... other models
    ],
],
```

#### Queue Processing

For large datasets, enable queue processing in `.env`:

```env
SCOUT_QUEUE=true
QUEUE_CONNECTION=redis
```

This will index changes asynchronously for better performance.

### Troubleshooting

#### Search Not Working

1. **Check Meilisearch is running**:
   ```bash
   curl http://localhost:7700/health
   ```

2. **Verify indexes exist**:
   ```bash
   curl http://localhost:7700/indexes
   ```

3. **Check Scout configuration**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

#### Empty Results

1. **Reindex the data**:
   ```bash
   php artisan search:reindex
   ```

2. **Check index settings** in `config/scout.php`

3. **Verify model has `Searchable` trait**

#### Performance Issues

1. **Enable queue processing** for indexing
2. **Increase Meilisearch memory** if handling large datasets
3. **Optimize index settings** to only include necessary attributes

### Security Considerations

#### Production Setup

1. **Set a master key** for Meilisearch:
   ```env
   MEILISEARCH_KEY=your-long-secure-random-key
   ```

2. **Use API keys** for different access levels:
   - Search-only keys for frontend
   - Admin keys for backend operations

3. **Firewall rules**: Restrict direct access to Meilisearch port (7700)

#### User Privacy

- Notes are automatically filtered by `user_id`
- Users can only search their own notes
- Public data (verses, lessons) is accessible to all users

### Advanced Usage

#### Custom Search Queries

You can extend the search functionality by modifying the `SearchController`:

```php
// Add custom filters
$versesQuery->where('custom_field', $value);

// Customize result transformation
->through(function ($verse) {
    return [
        'id' => $verse->id,
        'custom_field' => $verse->custom_field,
        // ... more fields
    ];
});
```

#### Model-Specific Search

```php
// Search only verses
$verses = Verse::search('love')->get();

// Search with filters
$verses = Verse::search('love')
    ->where('version', 'KJV')
    ->where('book_id', 43)
    ->paginate(20);
```

### Technical Implementation

The unified search feature was implemented with the following components:

#### Dependencies
- **Added**: `meilisearch/meilisearch-php` (v1.16.1) for Meilisearch PHP SDK
- **Already present**: `laravel/scout` (v10.21.0) for search abstraction layer
- **Already present**: `http-interop/http-factory-guzzle` for HTTP factory

#### Models Updated

- **Verse Model**: Enhanced `toSearchableArray()` to include book name, Bible version, and language
- **Note Model**: Added `Searchable` trait with user scoping
- **Lesson Model**: Enhanced `toSearchableArray()` with all relevant fields

#### API Controllers

- **SearchController**: Provides unified search and filter options endpoints
- **ReindexSearchCommand**: Artisan command for manual reindexing

---

## Lesson Feature

### Overview

The Lesson Feature allows users to create, manage, and track their progress through Bible study lessons. The system supports scripture references, lesson series, and progress tracking.

### Features

#### 1. Scripture References

Lessons can include two types of scripture references:

##### Short References (Clickable)
Use single quotes to add clickable scripture references:
- Format: `'BOOK CHAPTER:VERSE'`
- Examples: `'GEN 1:1'`, `'2KI 2:2'`, `'JHN 3:16'`
- Supports both English codes (GEN, EXO, PSA) and localized codes (MWA, KUT, ZAB for Swahili)
- These appear as clickable references that show the verse text in a hover card or sidebar

##### Full Verses (Embedded)
Use triple quotes to embed the full verse text in your lesson:
- Format: `'''BOOK CHAPTER:VERSE'''`
- Examples: `'''JHN 3:16'''`, `'''PSA 23:1'''`, `'''MWA 1:1'''` (Swahili for Genesis)
- Supports both English and localized book codes
- The system automatically fetches and displays the full verse text from your preferred Bible translation

#### 2. Lesson Series

Lessons can be organized into series with episodes:
- Create a series with a title, description, and language
- Assign lessons to the series with episode numbers
- Track progress through entire series
- View all lessons in a series from the lesson page

#### 3. Progress Tracking

Users can track their lesson completion:
- Mark lessons as completed with a single click
- View completed lessons in the Reading Plan page
- See statistics: total lessons completed, lessons completed today
- Progress is saved per user

#### 4. Mobile Footer

A static footer for mobile viewers provides easy navigation:
- Fixed at the bottom on mobile devices (screens < 768px)
- Quick access to: Dashboard, Bibles, Parallel Bibles, Lessons, Reading Plan, Highlights, and Notes
- Active page is highlighted
- Automatically hidden on desktop/tablet views

### Creating a Lesson

1. Navigate to "Lessons Management" (admin/editor only)
2. Click "Create New Lesson"
3. Fill in the lesson details:
   - Title: A descriptive name for your lesson
   - Language: The language of the lesson content
   - Readable: Whether the lesson is publicly visible
   - Number of Paragraphs: How many paragraphs your lesson will have
   - Description: A brief summary of the lesson
4. Write your lesson paragraphs:
   - Use plain text for regular content
   - Add scripture references using the special syntax
   - Example: "God created the heavens and the earth '''GEN 1:1'''. This shows His power."
5. Click "Create Lesson" to save

### Viewing a Lesson

When viewing a lesson:
- The main content shows your lesson text with embedded verses
- Short references appear as clickable links
- Hover over paragraph numbers to see scripture references
- Click on a reference to view the full verse in the sidebar
- Mark the lesson as completed using the "Mark as Read" button
- If part of a series, see other episodes in the series

### Progress Tracking

View your lesson progress in the Reading Plan page:
- See total lessons completed
- View lessons completed today
- Browse recently completed lessons
- See which series you've been studying

### API Endpoints

#### Toggle Lesson Progress
`POST /api/lessons/{lesson}/progress`
- Marks a lesson as completed/uncompleted for the authenticated user
- Returns the updated progress status

### Database Schema

#### lesson_series
- id: Primary key
- title: Series title
- description: Series description
- language: Language code
- user_id: Creator of the series
- timestamps

#### lessons (updated)
- Added: series_id (nullable foreign key)
- Added: episode_number (nullable integer)

#### lesson_progress
- id: Primary key
- user_id: Foreign key to users
- lesson_id: Foreign key to lessons
- completed: Boolean
- completed_at: Timestamp
- Unique constraint on (user_id, lesson_id)

### Technical Implementation

#### ScriptureReferenceService
Handles parsing and fetching scripture references:
- `parseReferences(text)`: Extracts all references from text
- `fetchVerse(bookCode, chapter, verse, bibleId)`: Retrieves verse data
- `replaceReferences(text, bibleId)`: Replaces full verse markers with actual text
- Supports both English book codes (GEN, EXO, PSA, etc.) and localized codes (MWA, KUT, ZAB for Swahili, etc.)
- Uses book_number (1-66) to query the database instead of non-existent code column

#### Frontend Components
- **Lesson.vue**: Main lesson display with scripture reference integration
- **MobileFooter.vue**: Static footer for mobile navigation
- **Create/Edit Lesson**: Forms with scripture reference guidance
- **Lessons.vue**: Creative card-based lesson listing
- **Reading Plan.vue**: Includes lesson progress tracking

### Best Practices

1. **Scripture References**: Use book codes consistently
   - English codes: GEN for Genesis, EXO for Exodus, JHN for John, etc.
   - Localized codes: Users can use their language's book codes (e.g., MWA for Genesis in Swahili)
   - Both English and localized codes are supported
2. **Lesson Structure**: Break lessons into logical paragraphs for better readability
3. **Series Organization**: Use meaningful episode numbers for series
4. **Content Quality**: Write clear, engaging lesson content that flows naturally
5. **Reference Placement**: Place references where they enhance understanding, not distract from it

### Future Enhancements

Potential additions to consider:
- Lesson comments and discussions
- Lesson sharing between users
- Advanced filtering and search
- Lesson templates
- Quiz/assessment integration
- Audio/video lesson support

---

## Additional Resources

- [Laravel Scout Documentation](https://laravel.com/docs/scout)
- [Meilisearch Documentation](https://www.meilisearch.com/docs)
- [Meilisearch API Reference](https://www.meilisearch.com/docs/reference/api)

## Support

For issues or questions:
1. Check this documentation
2. Review the troubleshooting sections
3. Open an issue on [GitHub](https://github.com/kea137/Bible/issues)
