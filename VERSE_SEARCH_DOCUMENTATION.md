# Verse Search and Lesson Progress Feature Documentation

This document describes the implementation of the verse search feature using Algolia (Laravel Scout) and the mark as read functionality for lessons.

## Features Implemented

### 1. Verse Search Functionality

The verse search feature allows users to search for verses across all Bibles using Laravel Scout, which supports multiple search drivers including Algolia, Meilisearch, and a collection-based driver for local development.

#### Backend Implementation

**File: `app/Http/Controllers/VerseController.php`**

Added a new `search()` method that:
- Accepts a search query and optional limit parameter
- Uses Laravel Scout to search verses
- Returns verse results with book and chapter information
- Includes bible_id in the response for filtering

**File: `routes/web.php`**

Added a new route:
```php
Route::get('/api/verses/search', [VerseController::class, 'search'])->name('verses_search')->middleware('auth');
```

**File: `app/Models/Verse.php`**

Added `toSearchableArray()` method to customize what fields are indexed:
- `id`: Verse ID
- `text`: Verse text (primary searchable field)
- `verse_number`: Verse number
- `bible_id`: Bible ID for filtering
- `book_id`: Book ID
- `chapter_id`: Chapter ID

#### Frontend Implementation

**File: `resources/js/pages/Bibles.vue`**

Updated the search functionality to:
- Call the `/api/verses/search` endpoint instead of Meilisearch client
- Display verse search results in the command dialog
- Make verses clickable to navigate to verse study page
- Filter and display both verses and bibles based on search query

**File: `resources/js/pages/Bible.vue`**

Updated the search functionality to:
- Call the `/api/verses/search` endpoint
- Filter results by the current Bible ID
- Navigate to verse study page when clicking on a search result

#### Usage

1. On the Bibles page (`/bibles`), click the "Search Bibles" button
2. Type a search query (e.g., "faith", "love", "God created")
3. Results will show:
   - Matching verses with book, chapter, and verse number
   - Highlighted verses that match the query
   - Bibles that match by name, language, or version

4. Click on a verse to navigate to the verse study page (`/verses/{id}/study`)

### 2. Mark as Read for Lessons

The mark as read functionality allows users to track their progress through lessons.

#### Backend Implementation

**File: `app/Http/Controllers/LessonController.php`**

The `toggleProgress()` method already existed and:
- Toggles the completion status of a lesson for the current user
- Creates a new LessonProgress record if none exists
- Updates the `completed` and `completed_at` fields

**File: `app/Models/LessonProgress.php`**

Tracks:
- `user_id`: User who completed the lesson
- `lesson_id`: Lesson that was completed
- `completed`: Boolean indicating completion status
- `completed_at`: Timestamp of completion

#### Frontend Implementation

**File: `resources/js/pages/Lesson.vue`**

Fixed the `toggleLessonCompletion()` function:
- Removed debug alert that was displaying CSRF token
- Properly handles the API response
- Updates the UI to reflect completion status

The "Mark as Read" button:
- Shows "Completed" when the lesson is marked as complete
- Shows "Mark as Read" when the lesson is not complete
- Changes visual appearance based on completion status
- Is only visible to authenticated users

#### Usage

1. Navigate to a lesson page (`/lessons/show/{id}`)
2. Click the "Mark as Read" button
3. The button will change to "Completed" and the status will be saved
4. Click again to toggle back to incomplete

## Configuration

### Scout Driver Configuration

The search functionality uses Laravel Scout, which supports multiple drivers:

1. **Collection Driver** (Default for development)
   - No additional setup required
   - Uses database queries with basic pattern matching
   - Good for development and testing

2. **Algolia** (Recommended for production)
   - Sign up for Algolia account at https://www.algolia.com
   - Add credentials to `.env`:
     ```
     SCOUT_DRIVER=algolia
     ALGOLIA_APP_ID=your_app_id
     ALGOLIA_SECRET=your_admin_api_key
     ```
   - Import existing verses to Algolia:
     ```bash
     php artisan scout:import "App\Models\Verse"
     ```

3. **Meilisearch** (Alternative open-source option)
   - Install Meilisearch: https://www.meilisearch.com
   - Add credentials to `.env`:
     ```
     SCOUT_DRIVER=meilisearch
     MEILISEARCH_HOST=http://127.0.0.1:7700
     MEILISEARCH_KEY=your_master_key
     ```
   - Import existing verses:
     ```bash
     php artisan scout:import "App\Models\Verse"
     ```

### Customizing Search Results

To customize what fields are searchable in Algolia, update the `config/scout.php` file:

```php
'algolia' => [
    'id' => env('ALGOLIA_APP_ID', ''),
    'secret' => env('ALGOLIA_SECRET', ''),
    'index-settings' => [
        'verses' => [
            'searchableAttributes' => ['text', 'verse_number'],
            'attributesForFaceting' => ['filterOnly(bible_id)', 'filterOnly(book_id)'],
        ],
    ],
],
```

## Testing

Two comprehensive test suites have been created:

### Verse Search Tests

**File: `tests/Feature/VerseSearchTest.php`**

Tests:
- Authenticated access to search endpoint
- Unauthenticated users are redirected to login
- Search results match query
- Empty results for non-matching queries
- Empty query returns no results
- Limit parameter is respected
- Results include book and chapter information

Run tests:
```bash
php artisan test --filter=VerseSearchTest
```

### Lesson Progress Tests

**File: `tests/Feature/LessonProgressTest.php`**

Tests:
- Authenticated access to progress endpoint
- Unauthenticated users are rejected
- Users can mark lessons as read
- Users can toggle completion status
- Multiple users can track progress independently
- Validation of lesson_id parameter

Run tests:
```bash
php artisan test --filter=LessonProgressTest
```

## API Endpoints

### Verse Search

```
GET /api/verses/search
```

**Parameters:**
- `query` (string, required): Search query
- `limit` (integer, optional): Maximum number of results (default: 10)

**Response:**
```json
{
  "verses": [
    {
      "id": 1,
      "text": "In the beginning God created the heavens and the earth.",
      "verse_number": 1,
      "bible_id": 1,
      "book": {
        "id": 1,
        "title": "Genesis"
      },
      "chapter": {
        "id": 1,
        "chapter_number": 1
      }
    }
  ],
  "total": 1
}
```

### Lesson Progress Toggle

```
POST /api/lesson/progress
```

**Parameters:**
- `lesson_id` (integer, required): ID of the lesson

**Response:**
```json
{
  "success": true,
  "progress": {
    "id": 1,
    "user_id": 1,
    "lesson_id": 1,
    "completed": true,
    "completed_at": "2025-11-04T12:00:00.000000Z"
  }
}
```

## Future Enhancements

Potential improvements for future versions:

1. **Advanced Search Features**
   - Faceted search by book, chapter, or Bible version
   - Boolean operators (AND, OR, NOT)
   - Phrase search with quotes
   - Search within specific books or chapters

2. **Search Analytics**
   - Track popular search queries
   - Suggest related verses
   - Autocomplete suggestions

3. **Lesson Progress Features**
   - Progress tracking across series
   - Completion percentage
   - Reading streaks and achievements
   - Export reading history

4. **Performance Optimizations**
   - Implement search result caching
   - Add pagination for large result sets
   - Optimize database queries with indexes
