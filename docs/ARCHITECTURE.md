# Bible Referencing System - Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         BIBLE REFERENCING SYSTEM                         │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                           USER INTERFACES                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐      │
│  │  Create Refs     │  │   Bible Reading  │  │   Verse Study    │      │
│  │  ─────────────   │  │  ──────────────  │  │  ──────────────  │      │
│  │  Upload JSON     │  │  ┌─────────────┐ │  │  Full Verse      │      │
│  │  with references │  │  │ 2/3 Main    │ │  │  Cross-Refs      │      │
│  │                  │  │  │ Content     │ │  │  Other Versions  │      │
│  │  Select Bible    │  │  │             │ │  │                  │      │
│  │  Choose File     │  │  │ Hover cards │ │  │  Navigation      │      │
│  │  Upload          │  │  │ Context menu│ │  │                  │      │
│  │                  │  │  └─────────────┘ │  │                  │      │
│  │                  │  │  ┌─────────────┐ │  │                  │      │
│  │                  │  │  │ 1/3 Sidebar │ │  │                  │      │
│  │                  │  │  │ References  │ │  │                  │      │
│  │                  │  │  │ Upper: List │ │  │                  │      │
│  │                  │  │  │ Lower: Full │ │  │                  │      │
│  │                  │  │  └─────────────┘ │  │                  │      │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘      │
│                                                                           │
│  ┌──────────────────┐                                                    │
│  │   Dashboard      │                                                    │
│  │  ──────────────  │                                                    │
│  │  Stats Cards     │                                                    │
│  │  Highlighted     │                                                    │
│  │  Verses          │                                                    │
│  │  (Yellow/Green)  │                                                    │
│  └──────────────────┘                                                    │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                              API LAYER                                   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  POST /references/store              ← Upload reference JSON            │
│  GET  /api/verses/{id}/references    ← Get verse cross-references       │
│  GET  /verses/{id}/study             ← Verse study page                 │
│                                                                           │
│  POST   /api/verse-highlights        ← Create/update highlight          │
│  GET    /api/verse-highlights        ← Get user highlights              │
│  DELETE /api/verse-highlights/{id}   ← Remove highlight                 │
│  GET    /api/verse-highlights/chapter ← Get chapter highlights          │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                          BUSINESS LOGIC                                  │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌────────────────────────────────────────────────────────────────┐     │
│  │  ReferenceController                                            │     │
│  │  • create() - Show upload form                                 │     │
│  │  • store() - Process uploaded JSON                             │     │
│  │  • getVerseReferences() - API endpoint for references          │     │
│  │  • studyVerse() - Render study page                            │     │
│  └────────────────────────────────────────────────────────────────┘     │
│                                                                           │
│  ┌────────────────────────────────────────────────────────────────┐     │
│  │  VerseHighlightController                                       │     │
│  │  • store() - Save/update highlight                              │     │
│  │  • destroy() - Remove highlight                                 │     │
│  │  • index() - Get all user highlights                            │     │
│  │  • getForChapter() - Get chapter highlights                     │     │
│  └────────────────────────────────────────────────────────────────┘     │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                            SERVICES                                      │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌────────────────────────────────────────────────────────────────┐     │
│  │  ReferenceService                                               │     │
│  │  • loadFromJson() - Parse and store JSON references            │     │
│  │  • getReferencesForVerse() - Retrieve verse references         │     │
│  │  • findVerseByReference() - Locate verse by book/ch/v          │     │
│  │  • studyVerse() - Prepare study page data                      │     │
│  └────────────────────────────────────────────────────────────────┘     │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                            UTILITIES                                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌────────────────────────────────────────────────────────────────┐     │
│  │  BookShorthand                                                  │     │
│  │  • BOOK_SHORTHANDS - 66 books (1=GEN ... 66=REV)              │     │
│  │  • ENGLISH_NAMES - Full English names                          │     │
│  │  • SWAHILI_NAMES - Full Swahili names                          │     │
│  │  • getShorthand() - Book number → shorthand                    │     │
│  │  • getName() - Shorthand → full name (EN/SW)                   │     │
│  │  • parseReference() - "GEN 1 1" → {book, ch, v}                │     │
│  │  • getBookNumber() - Shorthand → book number                   │     │
│  └────────────────────────────────────────────────────────────────┘     │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                          DATABASE SCHEMA                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  ┌────────────────────┐        ┌─────────────────────┐                  │
│  │  references        │        │  verse_highlights   │                  │
│  ├────────────────────┤        ├─────────────────────┤                  │
│  │  id                │        │  id                 │                  │
│  │  bible_id      FK  │        │  user_id        FK  │                  │
│  │  book_id       FK  │        │  verse_id       FK  │                  │
│  │  chapter_id    FK  │        │  color              │                  │
│  │  verse_id      FK  │        │  note (nullable)    │                  │
│  │  verse_reference   │        │  created_at         │                  │
│  │     (JSON)         │        │  updated_at         │                  │
│  │  note (nullable)   │        │                     │                  │
│  │  created_at        │        │  UNIQUE(user, verse)│                  │
│  │  updated_at        │        └─────────────────────┘                  │
│  └────────────────────┘                                                  │
│                                                                           │
│  Existing Tables Used:                                                   │
│  • bibles, books, chapters, verses (existing models)                    │
│  • users (for highlights)                                                │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                           DATA FLOW                                      │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  1. UPLOAD REFERENCES                                                    │
│     JSON File → ReferenceController → ReferenceService →                │
│     BookShorthand → Database (references table)                          │
│                                                                           │
│  2. VIEW REFERENCES                                                      │
│     Hover Verse → API Call → ReferenceService →                         │
│     Find Referenced Verses → Return with Full Text → Display            │
│                                                                           │
│  3. HIGHLIGHT VERSE                                                      │
│     Right-Click → Choose Color → VerseHighlightController →             │
│     Database (verse_highlights) → Update UI                              │
│                                                                           │
│  4. STUDY VERSE                                                          │
│     Click "Study" → ReferenceController.studyVerse() →                  │
│     Get References + Other Versions → Render Study Page                 │
│                                                                           │
│  5. DASHBOARD                                                            │
│     Load Dashboard → Fetch User Highlights →                            │
│     Display with Color Coding → Navigate on Click                       │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                       REFERENCE JSON FORMAT                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                           │
│  {                                                                        │
│    "1": {                          ← Verse ID                           │
│      "v": "GEN 1 1",               ← Verse reference (Book Ch Vs)        │
│      "r": {                        ← Related verses                      │
│        "2063": "EXO 20 11",        ← ID: Reference                       │
│        "2439": "EXO 31 18",                                              │
│        "10077": "2KI 19 15",                                             │
│        ...                                                                │
│      }                                                                    │
│    },                                                                     │
│    "2": { ... }                                                           │
│  }                                                                        │
│                                                                           │
│  Book Codes: GEN, EXO, LEV, NUM, DEU, JOS ... MAT, MAR ... REV          │
│  (66 total, defined in BookShorthand utility)                            │
│                                                                           │
└─────────────────────────────────────────────────────────────────────────┘
```

## Key Design Decisions

### 1. **Split Layout**
- 2/3 for reading comfort
- 1/3 for persistent reference access
- Responsive on smaller screens

### 2. **Hover + Click Pattern**
- Hover: Quick preview (non-intrusive)
- Click: Full reference (when interested)
- Efficient for power users

### 3. **Context Menu**
- Natural right-click interaction
- Groups related actions
- Familiar UI pattern

### 4. **Two-Color Highlighting**
- Simple but effective
- Visual distinction
- Room for future expansion

### 5. **JSON Storage**
- Flexible reference format
- Easy to parse/update
- Supports variable reference counts

### 6. **Book Shorthands**
- Standard abbreviations
- Language-agnostic codes
- Easy to remember (GEN, EXO, etc.)

## Performance Considerations

- References loaded on-demand (not bulk)
- JSON parsing cached in service
- Database indexes on foreign keys
- Lazy loading of other Bible versions
- Efficient queries with eager loading
