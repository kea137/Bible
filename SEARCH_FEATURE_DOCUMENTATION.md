# Unified Search Feature Documentation

## Overview

The Bible application now includes a powerful unified search system built with Laravel Scout and Meilisearch. This feature allows users to search across verses, notes, and lessons with advanced filtering capabilities.

## Features

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

## Setup Instructions

### 1. Install Meilisearch

#### Using Docker (Recommended)
```bash
docker run -d \
  --name meilisearch \
  -p 7700:7700 \
  -e MEILI_ENV='development' \
  -v $(pwd)/meili_data:/meili_data \
  getmeili/meilisearch:latest
```

#### Using Homebrew (macOS)
```bash
brew install meilisearch
brew services start meilisearch
```

#### Using Binary (Linux/Windows)
Download from [Meilisearch releases](https://github.com/meilisearch/meilisearch/releases) and run:
```bash
./meilisearch
```

### 2. Configure Environment

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

### 3. Index Your Data

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

## API Usage

### Search Endpoint

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

### Filter Options Endpoint

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

## Frontend Integration

### Using the Search Component

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

## Reindexing

### When to Reindex

Reindex your search data when:
- Setting up the search feature for the first time
- After bulk data imports
- If search results seem out of sync with your database
- After changing index settings in `config/scout.php`

### Automatic Indexing

Scout automatically keeps indexes in sync when you:
- Create new records (verses, notes, lessons)
- Update existing records
- Delete records

### Manual Reindexing

```bash
# Full reindex
php artisan search:reindex

# Specific model
php artisan search:reindex verses

# Clear and rebuild indexes
php artisan scout:flush "App\Models\Verse"
php artisan scout:import "App\Models\Verse"
```

## Performance Optimization

### Index Settings

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

### Queue Processing

For large datasets, enable queue processing in `.env`:

```env
SCOUT_QUEUE=true
QUEUE_CONNECTION=redis
```

This will index changes asynchronously for better performance.

## Troubleshooting

### Search Not Working

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

### Empty Results

1. **Reindex the data**:
   ```bash
   php artisan search:reindex
   ```

2. **Check index settings** in `config/scout.php`

3. **Verify model has `Searchable` trait**

### Performance Issues

1. **Enable queue processing** for indexing
2. **Increase Meilisearch memory** if handling large datasets
3. **Optimize index settings** to only include necessary attributes

## Security Considerations

### Production Setup

1. **Set a master key** for Meilisearch:
   ```env
   MEILISEARCH_KEY=your-long-secure-random-key
   ```

2. **Use API keys** for different access levels:
   - Search-only keys for frontend
   - Admin keys for backend operations

3. **Firewall rules**: Restrict direct access to Meilisearch port (7700)

### User Privacy

- Notes are automatically filtered by `user_id`
- Users can only search their own notes
- Public data (verses, lessons) is accessible to all users

## Advanced Usage

### Custom Search Queries

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

### Model-Specific Search

```php
// Search only verses
$verses = Verse::search('love')->get();

// Search with filters
$verses = Verse::search('love')
    ->where('version', 'KJV')
    ->where('book_id', 43)
    ->paginate(20);
```

## Additional Resources

- [Laravel Scout Documentation](https://laravel.com/docs/scout)
- [Meilisearch Documentation](https://www.meilisearch.com/docs)
- [Meilisearch API Reference](https://www.meilisearch.com/docs/reference/api)

## Support

For issues or questions:
1. Check this documentation
2. Review the [troubleshooting section](#troubleshooting)
3. Open an issue on [GitHub](https://github.com/kea137/Bible/issues)
