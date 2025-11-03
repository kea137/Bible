# Lesson Feature Documentation

## Overview
The Lesson Feature allows users to create, manage, and track their progress through Bible study lessons. The system supports scripture references, lesson series, and progress tracking.

## Features

### 1. Scripture References
Lessons can include two types of scripture references:

#### Short References (Clickable)
Use single quotes to add clickable scripture references:
- Format: `'BOOK CHAPTER:VERSE'`
- Examples: `'GEN 1:1'`, `'2KI 2:2'`, `'JHN 3:16'`
- Supports both English codes (GEN, EXO, PSA) and localized codes (MWA, KUT, ZAB for Swahili)
- These appear as clickable references that show the verse text in a hover card or sidebar

#### Full Verses (Embedded)
Use triple quotes to embed the full verse text in your lesson:
- Format: `'''BOOK CHAPTER:VERSE'''`
- Examples: `'''JHN 3:16'''`, `'''PSA 23:1'''`, `'''MWA 1:1'''` (Swahili for Genesis)
- Supports both English and localized book codes
- The system automatically fetches and displays the full verse text from your preferred Bible translation

### 2. Lesson Series
Lessons can be organized into series with episodes:
- Create a series with a title, description, and language
- Assign lessons to the series with episode numbers
- Track progress through entire series
- View all lessons in a series from the lesson page

### 3. Progress Tracking
Users can track their lesson completion:
- Mark lessons as completed with a single click
- View completed lessons in the Reading Plan page
- See statistics: total lessons completed, lessons completed today
- Progress is saved per user

### 4. Mobile Footer
A static footer for mobile viewers provides easy navigation:
- Fixed at the bottom on mobile devices (screens < 768px)
- Quick access to: Dashboard, Bibles, Parallel Bibles, Lessons, Reading Plan, Highlights, and Notes
- Active page is highlighted
- Automatically hidden on desktop/tablet views

## Creating a Lesson

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

## Viewing a Lesson

When viewing a lesson:
- The main content shows your lesson text with embedded verses
- Short references appear as clickable links
- Hover over paragraph numbers to see scripture references
- Click on a reference to view the full verse in the sidebar
- Mark the lesson as completed using the "Mark as Read" button
- If part of a series, see other episodes in the series

## Progress Tracking

View your lesson progress in the Reading Plan page:
- See total lessons completed
- View lessons completed today
- Browse recently completed lessons
- See which series you've been studying

## API Endpoints

### Toggle Lesson Progress
`POST /api/lessons/{lesson}/progress`
- Marks a lesson as completed/uncompleted for the authenticated user
- Returns the updated progress status

## Database Schema

### lesson_series
- id: Primary key
- title: Series title
- description: Series description
- language: Language code
- user_id: Creator of the series
- timestamps

### lessons (updated)
- Added: series_id (nullable foreign key)
- Added: episode_number (nullable integer)

### lesson_progress
- id: Primary key
- user_id: Foreign key to users
- lesson_id: Foreign key to lessons
- completed: Boolean
- completed_at: Timestamp
- Unique constraint on (user_id, lesson_id)

## Technical Implementation

### ScriptureReferenceService
Handles parsing and fetching scripture references:
- `parseReferences(text)`: Extracts all references from text
- `fetchVerse(bookCode, chapter, verse, bibleId)`: Retrieves verse data
- `replaceReferences(text, bibleId)`: Replaces full verse markers with actual text
- Supports both English book codes (GEN, EXO, PSA, etc.) and localized codes (MWA, KUT, ZAB for Swahili, etc.)
- Uses book_number (1-66) to query the database instead of non-existent code column

### Frontend Components
- **Lesson.vue**: Main lesson display with scripture reference integration
- **MobileFooter.vue**: Static footer for mobile navigation
- **Create/Edit Lesson**: Forms with scripture reference guidance
- **Lessons.vue**: Creative card-based lesson listing
- **Reading Plan.vue**: Includes lesson progress tracking

## Best Practices

1. **Scripture References**: Use book codes consistently
   - English codes: GEN for Genesis, EXO for Exodus, JHN for John, etc.
   - Localized codes: Users can use their language's book codes (e.g., MWA for Genesis in Swahili)
   - Both English and localized codes are supported
2. **Lesson Structure**: Break lessons into logical paragraphs for better readability
3. **Series Organization**: Use meaningful episode numbers for series
4. **Content Quality**: Write clear, engaging lesson content that flows naturally
5. **Reference Placement**: Place references where they enhance understanding, not distract from it

## Future Enhancements

Potential additions to consider:
- Lesson comments and discussions
- Lesson sharing between users
- Advanced filtering and search
- Lesson templates
- Quiz/assessment integration
- Audio/video lesson support
