# Bible App - Visual Architecture Guide

## App Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         App.tsx (Root)                          │
│                                                                 │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │              SafeAreaProvider                             │ │
│  │  ┌─────────────────────────────────────────────────────┐  │ │
│  │  │           AuthProvider                              │  │ │
│  │  │  ┌───────────────────────────────────────────────┐  │  │ │
│  │  │  │        BibleProvider                          │  │  │ │
│  │  │  │  ┌─────────────────────────────────────────┐  │  │  │ │
│  │  │  │  │      AppNavigator                       │  │  │  │ │
│  │  │  │  │                                         │  │  │  │ │
│  │  │  │  │  ┌──────────────┬──────────────────┐   │  │  │  │ │
│  │  │  │  │  │              │                  │   │  │  │  │ │
│  │  │  │  │  │  AuthStack   │    MainTabs     │   │  │  │  │ │
│  │  │  │  │  │              │                  │   │  │  │  │ │
│  │  │  │  │  └──────────────┴──────────────────┘   │  │  │  │ │
│  │  │  │  └─────────────────────────────────────────┘  │  │  │ │
│  │  │  └───────────────────────────────────────────────┘  │  │ │
│  │  └─────────────────────────────────────────────────────┘  │ │
│  └───────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

## Navigation Structure

### Not Authenticated (AuthStack)
```
AuthStack (Stack Navigator)
├── LoginScreen
│   ├── Email input
│   ├── Password input
│   └── Link to RegisterScreen
└── RegisterScreen
    ├── Name input
    ├── Email input
    ├── Password input
    ├── Confirm password input
    └── Link to LoginScreen
```

### Authenticated (MainTabs)
```
MainTabs (Bottom Tab Navigator)
├── Home Tab
│   └── HomeScreen
│       ├── Welcome message
│       ├── Current Bible card
│       ├── Continue reading button
│       └── Quick actions
│
├── Read Tab
│   └── BibleReaderScreen
│       ├── Bible/Chapter header
│       ├── Verse list (ScrollView)
│       │   └── VerseCard (x many)
│       │       ├── Verse number
│       │       ├── Verse text
│       │       ├── Highlight (if any)
│       │       └── Note badge (if any)
│       └── Actions modal (on long-press)
│           ├── Color picker
│           ├── Add note button
│           └── Cancel button
│
├── Lessons Tab
│   └── LessonsScreen
│       ├── Header
│       └── Lesson list (FlatList)
│           └── LessonCard (x many)
│               ├── Title
│               ├── Description
│               ├── Series badge (if series)
│               └── Scripture reference
│
└── Settings Tab
    └── SettingsScreen
        ├── Profile section
        ├── Bible settings
        ├── Reading preferences
        ├── About section
        └── Logout button
```

## Component Hierarchy

### Screen Components
```
HomeScreen
├── View (container)
├── ScrollView
│   ├── Header section
│   │   ├── Greeting text
│   │   └── Subtitle text
│   ├── Bible card
│   │   ├── Card title
│   │   ├── Bible name
│   │   └── Bible info
│   ├── Continue button (if applicable)
│   └── Quick actions
│       └── ActionCard (x3)
└── LoadingSpinner (if loading)

BibleReaderScreen
├── View (container)
├── Header
│   ├── Bible title
│   └── Chapter title
├── ScrollView
│   └── VerseCard (x many)
│       ├── Verse number
│       ├── Verse text
│       ├── Highlight background
│       └── Note badge
└── ActionsModal (if active)
    ├── Title
    ├── Color picker
    ├── Add note button
    └── Cancel button

LessonsScreen
├── View (container)
├── Header
│   ├── Title
│   └── Subtitle
└── FlatList
    └── LessonCard (x many)
        ├── Header row
        │   ├── Title
        │   └── Series badge
        ├── Description
        └── Scripture reference

SettingsScreen
├── View (container)
├── Header
└── ScrollView
    ├── Profile section
    │   └── ProfileCard
    ├── Bible section
    │   └── SettingItem
    ├── Preferences section
    │   ├── SettingItem (Font size)
    │   └── SettingItem (Theme)
    ├── About section
    │   ├── SettingItem (Version)
    │   ├── SettingItem (Privacy)
    │   └── SettingItem (Terms)
    └── Logout button
```

## State Management Flow

### Authentication Flow
```
User Action → LoginScreen/RegisterScreen
    ↓
API Service (api.ts)
    ↓
Backend API (/api/login or /api/register)
    ↓
Response (token + user data)
    ↓
AuthContext.login()
    ↓
AsyncStorage (save token + user)
    ↓
Update state (setUser)
    ↓
AppNavigator (re-render)
    ↓
Navigate to MainTabs
```

### Bible Reading Flow
```
User selects Bible/Chapter → BibleReaderScreen
    ↓
BibleContext.loadChapter(book, chapter)
    ↓
API Service (api.ts)
    ↓
Backend API (/api/verses?bible_id=X&book=Y&chapter=Z)
    ↓
Response (array of verses)
    ↓
Update state (setVerses)
    ↓
BibleReaderScreen re-renders
    ↓
Display VerseCard components
```

### Highlight Flow
```
User long-presses verse → Show actions modal
    ↓
User selects color → handleHighlight()
    ↓
BibleContext.addHighlight(verse, color)
    ↓
API Service (api.ts)
    ↓
Backend API (POST /api/highlights)
    ↓
Response (highlight object)
    ↓
Update state (add to highlights array)
    ↓
BibleReaderScreen re-renders
    ↓
Verse displays with highlight color
```

## Data Models

### User
```typescript
User {
  id: number
  name: string
  email: string
  email_verified_at?: string
  two_factor_enabled: boolean
  profile_photo_url?: string
  created_at: string
}
```

### Bible
```typescript
Bible {
  id: number
  name: string
  abbreviation: string
  language: string
  type: string
  direction: 'ltr' | 'rtl'
  books?: Book[]
}
```

### Verse
```typescript
Verse {
  id: number
  book: string
  chapter: number
  verse: number
  text: string
  bible_id: number
  bible_name?: string
  bible_abbreviation?: string
}
```

### Highlight
```typescript
Highlight {
  id: number
  user_id: number
  bible_id: number
  book: string
  chapter: number
  verse: number
  color: string
  created_at: string
}
```

### Note
```typescript
Note {
  id: number
  user_id: number
  bible_id: number
  book: string
  chapter: number
  verse: number
  content: string
  created_at: string
  updated_at: string
}
```

## API Endpoints Map

```
Authentication
├── POST /api/login              → Login user
├── POST /api/register           → Register user
├── POST /api/logout             → Logout user
└── GET  /api/user               → Get current user

Bibles
├── GET  /api/bibles             → List all Bibles
├── GET  /api/books              → Get books for a Bible
└── GET  /api/:lang/:ver/:refs/:book/:ch/:v → Public verse API

Verses
└── GET  /api/verses             → Get chapter verses

Highlights
├── GET    /api/highlights       → List user highlights
├── POST   /api/highlights       → Create highlight
└── DELETE /api/highlights/:id   → Delete highlight

Notes
├── GET    /api/notes            → List user notes
├── POST   /api/notes            → Create note
├── PUT    /api/notes/:id        → Update note
└── DELETE /api/notes/:id        → Delete note

Lessons
├── GET /api/lessons             → List lessons (paginated)
├── GET /api/lessons/:id         → Get lesson details
└── POST /api/lesson-progress    → Update progress

Reading Plan
├── GET  /api/reading-plan       → Get reading plan
└── POST /api/reading-plan       → Mark chapter as read
```

## File Organization

```
Bible-App/
│
├── App.tsx                      # Root component
├── index.ts                     # Entry point
│
├── src/
│   │
│   ├── components/
│   │   └── common/              # Reusable components
│   │       ├── Button.tsx
│   │       ├── VerseCard.tsx
│   │       ├── LoadingSpinner.tsx
│   │       ├── EmptyState.tsx
│   │       └── index.ts
│   │
│   ├── contexts/                # Global state
│   │   ├── AuthContext.tsx     # Authentication state
│   │   └── BibleContext.tsx    # Bible reading state
│   │
│   ├── navigation/              # App navigation
│   │   └── AppNavigator.tsx
│   │
│   ├── screens/                 # Screen components
│   │   ├── auth/
│   │   │   ├── LoginScreen.tsx
│   │   │   └── RegisterScreen.tsx
│   │   ├── HomeScreen.tsx
│   │   ├── BibleReaderScreen.tsx
│   │   ├── LessonsScreen.tsx
│   │   └── SettingsScreen.tsx
│   │
│   ├── services/                # External services
│   │   └── api.ts              # API service layer
│   │
│   ├── types/                   # TypeScript types
│   │   └── index.ts
│   │
│   ├── constants/               # Constants
│   │   └── index.ts
│   │
│   └── utils/                   # Utility functions
│       └── index.ts
│
├── assets/                      # Static assets
│   ├── icon.png
│   ├── splash-icon.png
│   └── ...
│
└── Documentation/
    ├── README.md
    ├── SETUP.md
    ├── DEVELOPMENT.md
    ├── WEB_VS_MOBILE.md
    └── PROJECT_SUMMARY.md
```

## Color Palette

```
Primary Colors:
├── primary:     #3b82f6 (Blue)
├── secondary:   #8b5cf6 (Purple)
├── accent:      #10b981 (Green)
└── error:       #ef4444 (Red)

Background Colors:
├── background:      #ffffff (White)
├── backgroundDark:  #1f2937 (Dark gray)
├── text:            #1f2937 (Dark gray)
└── textDark:        #f9fafb (Light gray)

Highlight Colors:
├── Yellow:  #fef3c7
├── Green:   #d1fae5
├── Blue:    #dbeafe
├── Pink:    #fce7f3
├── Purple:  #e9d5ff
├── Orange:  #fed7aa
└── Red:     #fee2e2
```

## Development Workflow

```
1. Code Change
   ↓
2. Metro bundler detects change
   ↓
3. Fast Refresh updates app
   ↓
4. Test on device/simulator
   ↓
5. Commit to git
   ↓
6. Push to repository
   ↓
7. Build for production
   ↓
8. Submit to app stores
```

## Testing Strategy

```
Manual Testing
├── Authentication flows
├── Bible reading
├── Highlighting
├── Notes
└── Settings

Integration Testing
├── API communication
├── State management
├── Navigation
└── Data persistence

Unit Testing
├── Utility functions
├── API service
├── Context providers
└── Components
```

This visual guide provides a comprehensive overview of the app's architecture, making it easier for developers to understand and contribute to the project.
