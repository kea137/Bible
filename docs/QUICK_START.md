# Bible Referencing System - Quick Start

## 🚀 Quick Start

### For Users

#### 1. Upload Reference Data (Admin)
1. Navigate to `/create/{bible_id}/references`
2. Select your Bible
3. Upload JSON file with references
4. Format: `{"1":{"v":"GEN 1 1","r":{"2063":"EXO 20 11",...}}}`

#### 2. Read with References
1. Go to any Bible (`/bibles/{id}`)
2. **Hover** over verse numbers → See reference preview
3. **Right sidebar** shows all references
4. **Click** reference → See full verse text

#### 3. Highlight Verses
1. **Right-click** any verse
2. Choose highlight color (Yellow or Green)
3. View highlights on your Dashboard

#### 4. Study Verses
1. **Right-click** a verse
2. Select "Study this Verse"
3. See cross-references and other translations

### For Developers

#### Setup
```bash
# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Build assets
npm run build
```

#### Reference Upload API
```php
POST /references/store
Content-Type: multipart/form-data

bible_id: 1
file: references.json
```

#### Get Verse References
```php
GET /api/verses/{verse_id}/references

Response:
{
  "verse": {...},
  "references": [
    {
      "id": "2063",
      "reference": "EXO 20 11",
      "verse": {...}
    }
  ]
}
```

#### Highlight Management
```php
// Create/Update
POST /api/verse-highlights
{ verse_id: 123, color: "yellow", note: "..." }

// Get all
GET /api/verse-highlights

// Get for chapter
GET /api/verse-highlights/chapter?chapter_id=1

// Delete
DELETE /api/verse-highlights/{verse_id}
```

## 📁 Key Files

```
app/
├── Utils/BookShorthand.php           # Book name mappings
├── Services/ReferenceService.php     # Reference logic
├── Models/VerseHighlight.php         # Highlights model
└── Http/Controllers/
    ├── ReferenceController.php       # Reference endpoints
    └── VerseHighlightController.php  # Highlight endpoints

resources/js/pages/
├── Bible.vue                         # Reading with refs
├── Create References.vue             # Upload interface
├── Dashboard.vue                     # Highlights display
└── Verse Study.vue                   # Study page

docs/
├── REFERENCING_SYSTEM.md             # User guide
├── IMPLEMENTATION_SUMMARY.md         # Tech details
└── ARCHITECTURE.md                   # System diagrams
```

## 🔧 Book Shorthands

### Old Testament (1-39)
```
1-5:    GEN, EXO, LEV, NUM, DEU
6-10:   JOS, JDG, RUT, 1SA, 2SA
11-15:  1KI, 2KI, 1CH, 2CH, EZR
16-20:  NEH, EST, JOB, PSA, PRO
21-25:  ECC, SNG, ISA, JER, LAM
26-30:  EZK, DAN, HOS, JOL, AMO
31-35:  OBA, JON, MIC, NAM, HAB
36-39:  ZEP, HAG, ZEC, MAL
```

### New Testament (40-66)
```
40-44:  MAT, MAR, LUK, JOH, ACT
45-49:  ROM, 1CO, 2CO, GAL, EPH
50-54:  PHP, COL, 1TH, 2TH, 1TI
55-59:  2TI, TIT, PHM, HEB, JAM
60-65:  1PE, 2PE, 1JO, 2JO, 3JO, JUD
66:     REV
```

## 🎨 UI Components

### Bible Reading Layout
```
┌────────────────────────────────┬──────────────┐
│ Book Chapter Verse             │              │
│ Genesis 1                      │  References  │
│                                │  ──────────  │
│ 1. In the beginning God...     │  • EXO 20:11 │
│ 2. And the earth was...        │  • PSA 33:6  │
│ 3. And God said...             │  • HEB 11:3  │
│                                │              │
│ [Hover verse numbers]          │  Selected:   │
│ [Right-click for menu]         │  EXO 20:11   │
│                                │  Full text...│
└────────────────────────────────┴──────────────┘
```

### Context Menu
```
┌─────────────────────┐
│ ⬜ Highlight - Yellow│
│ ⬜ Highlight - Green │
│ 📖 Study this Verse  │
└─────────────────────┘
```

### Dashboard Highlights
```
┌────────────────────────────────┐
│ Your Highlighted Verses        │
│ ────────────────────────────── │
│ │ Genesis 1:1                  │
│ │ In the beginning God...      │
│ │ (Yellow highlight)           │
│                                │
│ │ John 3:16                    │
│ │ For God so loved...          │
│ │ (Green highlight)            │
└────────────────────────────────┘
```

## ⚡ Features at a Glance

✅ **Reference Upload** - JSON file with cross-references
✅ **Hover Cards** - Preview references on verse numbers
✅ **Split Layout** - Persistent reference sidebar
✅ **Highlighting** - Yellow and Green colors
✅ **Study Page** - Deep dive into verses
✅ **Dashboard** - View all highlights
✅ **Multi-Bible** - Compare translations
✅ **Responsive** - Works on all devices

## 🔍 Example Reference JSON

```json
{
  "1": {
    "v": "GEN 1 1",
    "r": {
      "2063": "EXO 20 11",
      "2439": "EXO 31 18",
      "14373": "PSA 33 6",
      "30000": "HEB 3 4"
    }
  },
  "2": {
    "v": "GEN 1 2",
    "r": {
      "13481": "JOB 26 13",
      "14016": "PSA 8 3"
    }
  }
}
```

## 📖 Documentation

- **User Guide**: `docs/REFERENCING_SYSTEM.md`
- **Tech Details**: `docs/IMPLEMENTATION_SUMMARY.md`
- **Architecture**: `docs/ARCHITECTURE.md`

## 🐛 Testing

```bash
# Run all tests
php artisan test

# Run specific tests
php artisan test --filter=DashboardTest

# Build frontend
npm run build

# Format code
npm run format
```

## 🎯 Common Tasks

### Add New Highlight Color
1. Update `verse_highlights` migration (add to enum)
2. Update `VerseHighlightController` validation
3. Add color to context menu in `Bible.vue`
4. Add color class in `getVerseHighlightClass()`

### Add New Book Shorthand
1. Update `BookShorthand::BOOK_SHORTHANDS`
2. Add English name to `BookShorthand::ENGLISH_NAMES`
3. Add Swahili name to `BookShorthand::SWAHILI_NAMES`

### Debug Reference Loading
1. Check browser console for API errors
2. Verify JSON format matches schema
3. Check database `references` table
4. Test API endpoint: `/api/verses/{id}/references`

## ⚙️ Configuration

### Database Tables
- `references` - Verse cross-references (JSON)
- `verse_highlights` - User verse highlights
- `bibles`, `books`, `chapters`, `verses` - Core data

### Environment
- No special environment variables needed
- Uses default Laravel database configuration
- Frontend built with Vite

## 🚨 Troubleshooting

**References not showing?**
- Check if reference JSON was uploaded
- Verify verse IDs match database
- Check browser console for errors

**Highlights not saving?**
- Ensure user is authenticated
- Check CSRF token
- Verify API endpoint is accessible

**Study page not loading?**
- Check verse exists in database
- Verify route is defined
- Check for JavaScript errors

## 📞 Support

See documentation in `docs/` directory for detailed information.

For issues, check:
1. Browser console for frontend errors
2. Laravel logs for backend errors
3. Database for data integrity
4. API responses for validation errors
