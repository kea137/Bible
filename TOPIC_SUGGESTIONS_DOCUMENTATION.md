# Topic Index and Smart Verse Suggestions

This document describes the Topic Index and Smart Verse Suggestions features implemented in the Bible application.

## Overview

These features enhance Bible study by providing curated topic-based verse collections and intelligent, personalized verse recommendations.

## Topic Index

### Purpose
The Topic Index allows users to explore Bible verses organized by themes and topics, creating structured "verse chains" for topical study.

### Features

#### Topic Browsing
- View all topics organized by categories (e.g., "Faith & Character", "Spiritual Practices", "Comfort & Peace")
- Each topic displays:
  - Title and description
  - Category
  - Number of verses in the verse chain

#### Topic Detail View
- Explore individual topics with their complete verse chains
- Verses are displayed in order with:
  - Full verse text
  - Reference (Book, Chapter, Verse)
  - Optional notes explaining the verse's relevance to the topic
- Click any verse to navigate to the verse study page

#### Navigation
- Access topics through the "Topics" link in the main navigation sidebar
- Route: `/topics`

### Database Schema

#### topics table
- `id`: Primary key
- `title`: Topic name
- `description`: Topic description
- `category`: Topic category for grouping
- `order`: Display order within category
- `is_active`: Whether the topic is visible to users
- `created_at`, `updated_at`: Timestamps

#### topic_verses pivot table
- Links topics to verses with additional metadata
- `topic_id`: Foreign key to topics
- `verse_id`: Foreign key to verses
- `order`: Order of verse in the chain
- `note`: Optional note explaining the verse's relevance

### API Endpoints

- `GET /topics` - List all active topics
- `GET /topics/{topic}` - View a specific topic with its verses
- `POST /api/topics` - Create a new topic (admin)
- `PUT /api/topics/{topic}` - Update a topic (admin)
- `DELETE /api/topics/{topic}` - Delete a topic (admin)
- `POST /api/topics/{topic}/verses` - Add a verse to a topic (admin)
- `DELETE /api/topics/{topic}/verses/{verse}` - Remove a verse from a topic (admin)
- `PUT /api/topics/{topic}/verses/{verse}` - Update verse order/note (admin)

## Smart Verse Suggestions

### Purpose
Provide personalized verse recommendations to users based on their reading history, highlights, and study patterns with explanations for each suggestion.

### How It Works

The suggestion algorithm analyzes:

1. **User's Highlighted Verses**
   - Identifies verses the user has marked as important

2. **User's Notes**
   - Considers verses the user has written notes about

3. **Reading Progress**
   - Tracks which books and chapters the user has read

Based on this data, the system generates suggestions using three strategies:

#### 1. Cross-Reference Suggestions
- Finds verses that cross-reference the user's highlighted verses
- **Reason**: "Related to a verse you highlighted ({source verse})"
- **Score**: 0.8 (high confidence)

#### 2. Contextual Suggestions
- Suggests verses from the same books the user has been reading
- **Reason**: "From the book of {book name} which you've been reading"
- **Score**: 0.5 (medium confidence)

#### 3. Topical Suggestions
- Uses keyword extraction and matching to find thematically similar verses
- Extracts important keywords from highlighted verses (>4 characters, excluding common words)
- **Reason**: "Contains similar themes to verses you've highlighted"
- **Score**: 0.6 (medium confidence)

### Features

#### Suggestion Widget on Dashboard
- Displays up to 5 personalized suggestions
- Each suggestion shows:
  - Verse reference
  - Verse text (truncated)
  - "Why this verse?" explanation (expandable)
  - Icons representing the reason type (🔗 cross-reference, 📖 same book, 🔍 keyword match)

#### User Actions
- **Click**: Navigate to verse study page (automatically tracked)
- **Dismiss**: Hide a suggestion permanently
- **Refresh**: Generate new suggestions manually

#### Explainability
All suggestions include clear explanations:
- Type of suggestion (cross-reference, contextual, topical)
- Specific reason (e.g., which highlighted verse it relates to)
- Visual icons for quick recognition

### Database Schema

#### verse_suggestions table
- `id`: Primary key
- `user_id`: Foreign key to users
- `verse_id`: Foreign key to verses
- `score`: Confidence score (0.0 - 1.0)
- `reasons`: JSON array of reason objects
- `dismissed`: Whether user dismissed the suggestion
- `clicked`: Whether user clicked the suggestion
- `created_at`, `updated_at`: Timestamps

### API Endpoints

- `GET /api/verse-suggestions` - Get active suggestions for the authenticated user
- `POST /api/verse-suggestions/generate` - Generate new suggestions
- `POST /api/verse-suggestions/{suggestion}/click` - Mark a suggestion as clicked
- `POST /api/verse-suggestions/{suggestion}/dismiss` - Dismiss a suggestion

### Privacy & Data Management

- Suggestions are user-specific and private
- Old suggestions (>7 days) are automatically cleaned up
- Users can only access their own suggestions
- Dismissed suggestions are excluded from future results

## Usage Examples

### Viewing Topics
1. Click "Topics" in the sidebar
2. Browse topics by category
3. Click a topic to view its verse chain

### Getting Verse Suggestions
1. Visit the Dashboard
2. Scroll to the "Suggested Verses" card
3. Click the info icon to see why each verse was suggested
4. Click a verse to study it
5. Dismiss suggestions you're not interested in
6. Click the refresh icon to generate new suggestions

### Seeding Sample Topics
```bash
php artisan db:seed --class=TopicSeeder
```

This will create sample topics including:
- Love and Compassion
- Faith and Trust
- Prayer and Worship
- Wisdom and Understanding
- Hope and Encouragement

## Technical Implementation

### Backend
- **Laravel Controllers**: `TopicController`, `VerseSuggestionController`
- **Models**: `Topic`, `VerseSuggestion` with Eloquent relationships
- **Service**: `VerseSuggestionService` handles the suggestion algorithm
- **Migrations**: Three migrations for topics, topic_verses, and verse_suggestions tables

### Frontend
- **Vue Components**:
  - `Topics/Index.vue` - Topic listing page
  - `Topics/Show.vue` - Individual topic detail page
  - `VerseSuggestions.vue` - Suggestion widget component
- **Integration**: Dashboard includes the VerseSuggestions component
- **Navigation**: Topics link added to main sidebar

### Testing
- Comprehensive PHPUnit tests for both features
- Factories for easy test data generation
- Tests cover CRUD operations, access control, and suggestion generation

## Future Enhancements

Potential improvements for future versions:

1. **Topic Management UI**
   - Admin interface for creating and managing topics
   - Drag-and-drop verse ordering

2. **Enhanced Suggestion Algorithm**
   - Machine learning for better relevance
   - Time-based patterns (morning devotionals, evening readings)
   - Collaborative filtering (what similar users read)

3. **User Feedback**
   - Rate suggestions to improve algorithm
   - Report inappropriate suggestions

4. **Topic Sharing**
   - Share topic collections with other users
   - Community-created topics

5. **Suggestion Categories**
   - Devotional suggestions
   - Study suggestions
   - Encouragement suggestions based on recent activity
