# Mobile API Documentation

This document describes the REST API endpoints available for the React Native mobile app (kea137/Bible-App).

## Base URL

```
https://your-domain.com/api/mobile
```

## Authentication

Most endpoints require authentication using Laravel Sanctum. Include the bearer token in the `Authorization` header:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Getting a Token

Use Laravel Sanctum's token generation:

```php
// After successful login
$token = $user->createToken('mobile-app')->plainTextToken;
```

## Endpoints

### Public Endpoints

#### Home
Get basic API information.

```http
GET /api/mobile/home
```

**Response:**
```json
{
  "success": true,
  "message": "Welcome to Bible API",
  "version": "1.0.0"
}
```

---

### Authenticated Endpoints

All endpoints below require authentication.

#### Dashboard

Get user dashboard data including verse of the day, reading stats, and last reading.

```http
GET /api/mobile/dashboard
```

**Response:**
```json
{
  "success": true,
  "data": {
    "verseOfTheDay": {
      "id": 1,
      "text": "For God so loved the world...",
      "verse_number": 16,
      "bible": { "id": 1, "name": "King James Version" },
      "book": { "id": 43, "title": "John" },
      "chapter": { "id": 123, "chapter_number": 3 }
    },
    "lastReading": {
      "bible_id": 1,
      "bible_name": "King James Version",
      "book_title": "Genesis",
      "chapter_number": 1
    },
    "readingStats": {
      "total_bibles": 10,
      "verses_read_today": 25,
      "chapters_completed": 100
    },
    "userName": "John Doe"
  }
}
```

---

#### Onboarding

##### Get Onboarding Data
```http
GET /api/mobile/onboarding
```

**Response:**
```json
{
  "success": true,
  "data": {
    "bibles": {
      "English": [
        { "id": 1, "name": "KJV", "abbreviation": "KJV", "language": "English", "version": "1.0" }
      ]
    },
    "currentLanguage": "en",
    "onboarding_completed": false
  }
}
```

##### Complete Onboarding
```http
POST /api/mobile/onboarding
Content-Type: application/json

{
  "language": "en",
  "preferred_translations": [1, 2, 3],
  "appearance_preferences": {
    "theme": "dark"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Onboarding completed successfully",
  "data": {
    "user": { ... }
  }
}
```

---

#### User Preferences

##### Update Locale
```http
POST /api/mobile/update-locale
Content-Type: application/json

{
  "locale": "en"
}
```

##### Update Theme
```http
POST /api/mobile/update-theme
Content-Type: application/json

{
  "theme": "dark"
}
```
Valid themes: `light`, `dark`, `system`

##### Update Translations
```http
POST /api/mobile/update-translations
Content-Type: application/json

{
  "preferred_translations": [1, 2, 3]
}
```

---

#### Bibles

##### Get All Bibles
```http
GET /api/mobile/bibles
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "King James Version",
      "abbreviation": "KJV",
      "language": "English",
      "version": "1.0",
      "description": "..."
    }
  ]
}
```

##### Get Specific Bible
```http
GET /api/mobile/bibles/{bible_id}
```

Query parameters:
- `book` (optional): Book ID to load
- `chapter` (optional): Chapter ID to load

**Response:**
```json
{
  "success": true,
  "data": {
    "bible": { ... },
    "initialChapter": { ... }
  }
}
```

##### Get Parallel Bibles
```http
GET /api/mobile/bibles/parallel
```

Returns user's preferred translations with books and chapters loaded.

##### Get Chapter
```http
GET /api/mobile/bibles/chapters/{chapter_id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "chapter_number": 1,
    "verses": [ ... ],
    "book": { ... }
  }
}
```

##### Get All Bibles (Simple)
```http
GET /api/mobile/api-bibles
```

Returns all bibles without filtering.

---

#### Verses

##### Get Verse References
```http
GET /api/mobile/verses/{verse_id}/references
```

##### Get Verse Study Data
```http
GET /api/mobile/verses/{verse_id}/study
```

---

#### Verse Highlights

##### Create/Update Highlight
```http
POST /api/mobile/verse-highlights
Content-Type: application/json

{
  "verse_id": 123,
  "color": "yellow",
  "note": "Important verse"
}
```
Valid colors: `yellow`, `green`

##### Delete Highlight
```http
DELETE /api/mobile/verse-highlights/{verse_id}
```

##### Get All Highlights
```http
GET /api/mobile/verse-highlights
```

##### Get Chapter Highlights
```http
GET /api/mobile/verse-highlights/chapter?chapter_id={chapter_id}
```

##### Get Highlighted Verses Page
```http
GET /api/mobile/highlighted-verses
```

---

#### Notes

##### Get All Notes
```http
GET /api/mobile/notes
# or
GET /api/mobile/notes/index
```

##### Create Note
```http
POST /api/mobile/notes
Content-Type: application/json

{
  "verse_id": 123,
  "title": "My Note",
  "content": "This is my note content"
}
```

##### Get Single Note
```http
GET /api/mobile/notes/{note_id}
```

##### Update Note
```http
PUT /api/mobile/notes/{note_id}
Content-Type: application/json

{
  "title": "Updated Title",
  "content": "Updated Content"
}
```

##### Delete Note
```http
DELETE /api/mobile/notes/{note_id}
```

---

#### Reading Progress

##### Get Reading Plan
```http
GET /api/mobile/reading-plan?bible_id={bible_id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "totalChapters": 1189,
    "completedChapters": 100,
    "progressPercentage": 8.4,
    "chaptersReadToday": 5,
    "selectedBible": { ... },
    "allBibles": [ ... ],
    "completedLessons": [ ... ],
    "lessonsReadToday": 2
  }
}
```

##### Toggle Chapter Progress
```http
POST /api/mobile/reading-progress/toggle
Content-Type: application/json

{
  "chapter_id": 123,
  "bible_id": 1
}
```

##### Get Bible Progress
```http
GET /api/mobile/reading-progress/bible?bible_id={bible_id}
```

##### Get Reading Statistics
```http
GET /api/mobile/reading-progress/statistics
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_chapters_completed": 100,
    "chapters_read_today": 5,
    "verses_read_today": 125,
    "last_reading": {
      "bible_id": 1,
      "bible_name": "KJV",
      "book_title": "Genesis",
      "chapter_number": 1
    }
  }
}
```

---

#### Lessons

##### Get All Lessons
```http
GET /api/mobile/lessons
```

##### Get Specific Lesson
```http
GET /api/mobile/lessons/{lesson_id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "lesson": {
      "id": 1,
      "title": "Introduction to Bible Study",
      "description": "...",
      "paragraphs": [ ... ]
    },
    "userProgress": { ... },
    "seriesLessons": [ ... ]
  }
}
```

##### Toggle Lesson Progress
```http
POST /api/mobile/lesson-progress/toggle
Content-Type: application/json

{
  "lesson_id": 123
}
```

---

#### Share

##### Get Share Data
```http
GET /api/mobile/share?verseId={verse_id}
# or
GET /api/mobile/share?reference={reference}&text={text}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "verseReference": "John 3:16",
    "verseText": "For God so loved the world...",
    "verseId": 123,
    "backgroundImages": [ ... ],
    "bible": 1,
    "book": 43,
    "chapter": 123
  }
}
```

---

#### Sitemap

##### Get Sitemap Data
```http
GET /api/mobile/sitemap
```

**Response:**
```json
{
  "success": true,
  "data": {
    "bibles": [ ... ]
  }
}
```

---

## Error Responses

All endpoints return errors in the following format:

```json
{
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (access denied)
- `404` - Not found
- `422` - Validation error
- `500` - Server error

---

## Rate Limiting

API requests are rate-limited. The public Bible API endpoints use throttling (30 requests per minute). Mobile API endpoints use standard Laravel Sanctum rate limiting.

---

## Notes

1. All timestamps are in UTC
2. All responses are in JSON format
3. Use `application/json` Content-Type for POST/PUT requests
4. IDs are integers
5. Boolean values are represented as `true`/`false`

---

## Example Usage

### JavaScript (React Native)

```javascript
// Login and get token
const login = async (email, password) => {
  const response = await fetch('https://your-domain.com/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  return data.token; // Store this token
};

// Make authenticated request
const getDashboard = async (token) => {
  const response = await fetch('https://your-domain.com/api/mobile/dashboard', {
    headers: { 
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  return await response.json();
};

// Create a note
const createNote = async (token, verseId, title, content) => {
  const response = await fetch('https://your-domain.com/api/mobile/notes', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ verse_id: verseId, title, content })
  });
  return await response.json();
};
```

---

## Support

For issues or questions, please open an issue on the GitHub repository.
