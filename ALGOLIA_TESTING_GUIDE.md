# Algolia Integration Testing Guide

This guide explains how to test the Algolia verse search integration in the Bible application.

## Overview

The application uses Laravel Scout with support for multiple search drivers:
- **Collection Driver** (Default): Database-based search for development
- **Algolia**: Cloud-hosted search for production
- **Meilisearch**: Self-hosted alternative search engine

## Test Suites

### 1. VerseSearchTest.php (10 tests)
Tests the search API endpoint (`/api/verses/search`) functionality.

**Tests include:**
- Authentication requirements
- Search query validation
- Result structure validation
- Empty query handling
- Limit parameter validation
- Book and chapter information in results

**Run with:**
```bash
php artisan test --filter=VerseSearchTest
```

### 2. VerseScoutImportTest.php (14 tests)
Tests the Laravel Scout integration and import functionality.

**Tests include:**
- Searchable trait verification
- Searchable array structure
- Scout import/flush commands
- Search functionality with Scout
- Automatic index updates on create/update
- Eager loading relationships
- Driver configuration

**Run with:**
```bash
php artisan test --filter=VerseScoutImportTest
```

### 3. AlgoliaIntegrationTest.php (9 tests, 2 skipped with collection driver)
End-to-end integration tests for the complete search workflow.

**Tests include:**
- Complete import and search workflow via API
- Bible filtering
- Metadata accuracy
- Special character handling
- Search performance
- Concurrent search handling
- Index clearing and reimporting

**Run with:**
```bash
php artisan test --filter=AlgoliaIntegrationTest
```

## Running All Verse Search Tests

```bash
php artisan test tests/Feature/VerseScoutImportTest.php tests/Feature/VerseSearchTest.php tests/Feature/AlgoliaIntegrationTest.php
```

**Expected output:**
- 31 tests passed
- 2 skipped (with collection driver)
- 138+ assertions

## Testing with Different Scout Drivers

### Collection Driver (Default)

No additional setup required. This is the default configuration.

```bash
# In .env
SCOUT_DRIVER=collection
```

All tests will run, with 2 tests skipped (status and reimport commands not fully supported).

### Algolia Driver

1. Sign up for an Algolia account at https://www.algolia.com
2. Get your Application ID and Admin API Key
3. Update your `.env` file:

```env
SCOUT_DRIVER=algolia
ALGOLIA_APP_ID=your_app_id
ALGOLIA_SECRET=your_admin_api_key
```

4. Import verses to Algolia:

```bash
php artisan scout:import "App\Models\Verse"
```

5. Run tests:

```bash
php artisan test --filter=AlgoliaIntegrationTest
```

All 9 tests should pass (none skipped).

### Meilisearch Driver

1. Install Meilisearch: https://www.meilisearch.com
2. Start Meilisearch server (default: http://127.0.0.1:7700)
3. Update your `.env` file:

```env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=your_master_key
```

4. Import verses to Meilisearch:

```bash
php artisan scout:import "App\Models\Verse"
```

5. Run tests:

```bash
php artisan test --filter=AlgoliaIntegrationTest
```

## Manual Testing

### 1. Import Verses to Search Index

```bash
# Import all verses
php artisan scout:import "App\Models\Verse"

# Check import status (Algolia/Meilisearch only)
php artisan scout:status "App\Models\Verse"
```

### 2. Test Search via API

```bash
# Using curl (replace with actual auth token)
curl -X GET "http://localhost/api/verses/search?query=God&limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Test Search via Application

1. Log in to the application
2. Navigate to the Bibles page (`/bibles`)
3. Click "Search Bibles" button
4. Type a search query (e.g., "faith", "love", "God created")
5. Click on a verse result to navigate to the verse study page

## Scout Commands Reference

### Import verses to search index
```bash
php artisan scout:import "App\Models\Verse"
```

### Clear search index
```bash
php artisan scout:flush "App\Models\Verse"
```

### Reimport (clear and import)
```bash
php artisan scout:reimport "App\Models\Verse"
```

### Check index status (Algolia/Meilisearch only)
```bash
php artisan scout:status "App\Models\Verse"
```

### Delete all indexes
```bash
php artisan scout:delete-all-indexes
```

## Searchable Fields

The `Verse` model indexes the following fields:

- **id**: Verse ID
- **text**: Verse text (primary searchable field)
- **verse_number**: Verse number
- **bible_id**: Bible ID (for filtering)
- **book_id**: Book ID (for filtering)
- **chapter_id**: Chapter ID (for filtering)

These are defined in `App\Models\Verse::toSearchableArray()`.

## Performance Considerations

### Collection Driver
- Uses database queries with `LIKE` statements
- Good for development and small datasets
- May be slower with large datasets (10,000+ verses)

### Algolia
- Fast, cloud-hosted search
- Scales well with large datasets
- Requires internet connection
- Has usage limits based on plan

### Meilisearch
- Fast, self-hosted search
- Good for privacy-sensitive deployments
- Requires server resources
- No external dependencies

## Troubleshooting

### Tests failing with "Impossible to connect to Algolia"

This occurs when using Algolia driver without valid credentials. Solutions:

1. **Switch to collection driver** (recommended for testing):
   ```env
   SCOUT_DRIVER=collection
   ```

2. **Provide valid Algolia credentials** in `.env`:
   ```env
   ALGOLIA_APP_ID=your_app_id
   ALGOLIA_SECRET=your_admin_api_key
   ```

### Search returns empty results

1. Verify verses are imported:
   ```bash
   php artisan scout:import "App\Models\Verse"
   ```

2. Check if verses exist in database:
   ```bash
   php artisan tinker
   >>> App\Models\Verse::count()
   ```

3. Test search directly:
   ```bash
   php artisan tinker
   >>> App\Models\Verse::search('God')->get()
   ```

### Slow search performance

1. For collection driver, ensure database has proper indexes
2. Consider upgrading to Algolia or Meilisearch for production
3. Use pagination and limit parameters to reduce result size

## CI/CD Integration

For automated testing in CI/CD pipelines, use the collection driver:

```yaml
# .github/workflows/test.yml
env:
  SCOUT_DRIVER: collection
```

This avoids the need for external service credentials during testing.

## Additional Resources

- [Laravel Scout Documentation](https://laravel.com/docs/scout)
- [Algolia Documentation](https://www.algolia.com/doc/)
- [Meilisearch Documentation](https://www.meilisearch.com/docs)
- [VERSE_SEARCH_DOCUMENTATION.md](../VERSE_SEARCH_DOCUMENTATION.md) - Feature implementation details
