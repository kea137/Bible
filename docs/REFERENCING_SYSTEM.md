# Bible Referencing System - User Guide

## Overview
This system enables cross-referencing between Bible verses, highlighting important verses, and studying individual verses in depth.

## Features

### 1. Upload Reference Data
**Location:** `/create/{bible_id}/references`

Upload a JSON file containing verse cross-references in the following format:
```json
{
  "1": {
    "v": "GEN 1 1",
    "r": {
      "2063": "EXO 20 11",
      "2439": "EXO 31 18",
      "10077": "2KI 19 15"
    }
  }
}
```

Where:
- Key (`"1"`) is the verse ID
- `"v"` is the verse reference (Book Chapter Verse)
- `"r"` contains referenced verses as ID: Reference pairs

**Book Shorthands:**
- Old Testament: GEN, EXO, LEV, NUM, DEU, JOS, JDG, RUT, 1SA, 2SA, 1KI, 2KI, etc.
- New Testament: MAT, MAR, LUK, JOH, ACT, ROM, 1CO, 2CO, GAL, EPH, etc.
- See `app/Utils/BookShorthand.php` for complete list

**Supported Languages:**
- English names (default)
- Swahili names (using same English shorthands)

### 2. Reading with Cross-References
**Location:** `/bibles/{bible_id}`

When reading the Bible:
- **Hover** over verse numbers to see a preview of cross-references
- View **full list of references** in the right sidebar (1/3 of screen)
- **Click** on a reference to see its full verse text in the bottom panel

### 3. Highlighting Verses
**How to Highlight:**
1. **Right-click** on any verse text
2. Choose highlight color:
   - Yellow
   - Green
3. Highlight is saved automatically

**Viewing Your Highlights:**
- See all highlights on your Dashboard
- Highlights persist across sessions
- Each user has their own highlights

### 4. Verse Study Page
**Access:** Right-click a verse → "Study this Verse"

The study page shows:
- **Full verse text** with reference
- **All cross-references** with complete verse texts
- **Same verse in other Bible versions** for comparison
- Click any reference to navigate to it

### 5. Dashboard
**Location:** `/dashboard`

Your dashboard displays:
- **Recent highlighted verses** (up to 5)
- Color-coded by highlight type
- Quick access to return to verses

## API Endpoints

### Get Verse References
```
GET /api/verses/{verse_id}/references
```
Returns all cross-references for a specific verse.

### Manage Highlights
```
POST /api/verse-highlights
Body: { verse_id, color, note? }
```
Create or update a verse highlight.

```
GET /api/verse-highlights
```
Get all highlights for authenticated user.

```
DELETE /api/verse-highlights/{verse_id}
```
Remove a highlight.

```
GET /api/verse-highlights/chapter?chapter_id={chapter_id}
```
Get all highlights for a specific chapter.

## Database Schema

### References Table
```sql
- id
- bible_id (foreign key)
- book_id (foreign key)
- chapter_id (foreign key)
- verse_id (foreign key)
- verse_reference (JSON)
- note (nullable text)
- timestamps
```

### Verse Highlights Table
```sql
- id
- user_id (foreign key)
- verse_id (foreign key)
- color (yellow|green)
- note (nullable text)
- timestamps
- unique(user_id, verse_id)
```

## Technical Notes

### Book Number Mapping
The system uses standardized book numbers (1-66) mapped to shorthands:
- 1 = GEN (Genesis/Mwanzo)
- 40 = MAT (Matthew/Mathayo)
- 66 = REV (Revelation/Ufunuo)

### Reference Format
References use space-separated format: `BOOK CHAPTER VERSE`
- Example: `GEN 1 1`, `JOH 3 16`, `REV 22 21`

### Language Support
The `BookShorthand` utility supports:
- English book names (default)
- Swahili book names (accessed via language parameter)
- All using standardized English shorthands for consistency

## Tips
1. Upload reference data before using cross-reference features
2. Use highlights to mark important verses for later study
3. Right-click context menu provides quick access to all features
4. Study page is perfect for deep-dive verse analysis
5. Dashboard keeps your highlights organized and accessible
