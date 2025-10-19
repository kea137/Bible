# Bible Application Documentation

Welcome to the Bible Application documentation. This guide covers all features and functionality of the application.

## Table of Contents

1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [Features](#features)
4. [Bible Reading](#bible-reading)
5. [Cross-Reference System](#cross-reference-system)
6. [Highlighting & Notes](#highlighting--notes)
7. [Reading Plan](#reading-plan)
8. [Parallel Bibles](#parallel-bibles)
9. [User Management](#user-management)
10. [Configuration](#configuration)
11. [Developer Guide](#developer-guide)

---

## Overview

The Bible Application is a modern Bible reading and study platform built with Laravel, Vue.js, and Inertia.js. It provides a comprehensive set of tools for reading, studying, and managing Bible translations with advanced features like cross-references, highlighting, notes, and reading progress tracking.

### Key Features

- **Multiple Bible Translations**: Browse and read different Bible versions
- **Cross-Reference System**: Navigate related verses with ease
- **Verse Highlighting**: Mark important verses with color codes
- **Personal Notes**: Add private notes to verses
- **Reading Plan**: Track your Bible reading progress
- **Parallel View**: Compare multiple translations side-by-side
- **User Authentication**: Secure account with optional two-factor authentication
- **Dark Mode**: Choose your preferred theme
- **Responsive Design**: Works on desktop, tablet, and mobile devices

---

## Getting Started

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/kea137/Bible.git
   cd Bible
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env` file with your database credentials and other settings.

4. **Run migrations**
   ```bash
   php artisan migrate
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start the application**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` in your browser.

### First Steps

1. **Create an account** or log in if you already have one
2. **Browse available Bibles** from the Bibles page
3. **Select a Bible translation** to start reading
4. **Explore features** like highlighting, notes, and cross-references

---

## Features

### Dashboard

Your personal dashboard provides:
- **Quick stats**: Reading progress overview
- **Recent highlights**: Last 5 highlighted verses
- **Quick access**: Jump to your favorite sections

### Bible Library

Browse all available Bible translations:
- Filter by language
- Search by name
- Quick access to continue reading where you left off

---

## Bible Reading

### Reading Interface

The Bible reading page features:

**Main Content (2/3 screen)**
- Book, chapter, and verse navigation
- Clean, readable text
- Verse numbers are interactive
- Right-click context menu on verses

**References Sidebar (1/3 screen)**
- Upper section: List of cross-references for hovered/selected verse
- Lower section: Full text of clicked reference
- Collapsible for full-screen reading

### Navigation

- **Book selector**: Choose from all 66 books
- **Chapter selector**: Quick chapter navigation
- **Previous/Next buttons**: Move between chapters
- **Keyboard shortcuts**: Arrow keys for navigation

### Hover Features

**Hover over verse numbers** to:
- Preview cross-references
- See reference count
- Quick peek at related verses

---

## Cross-Reference System

### Overview

The cross-reference system connects related Bible verses, enabling deep scripture study.

### Features

#### Reference Preview
- **Hover** over verse numbers to see preview
- Shows first referenced verse
- Indicates total number of references

#### Reference Sidebar
- **Upper panel**: List of all cross-references
- **Lower panel**: Full verse text when clicked
- Navigate to any reference with a single click

#### Verse Study Page
Access via right-click menu on any verse:
- **Full verse context**
- **All cross-references** with complete text
- **Same verse in other translations**
- **Easy navigation** to related verses

### Book Shorthands

The system uses standard book abbreviations:

**Old Testament (1-39)**
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

**New Testament (40-66)**
```
40-44:  MAT, MAR, LUK, JOH, ACT
45-49:  ROM, 1CO, 2CO, GAL, EPH
50-54:  PHP, COL, 1TH, 2TH, 1TI
55-59:  2TI, TIT, PHM, HEB, JAM
60-65:  1PE, 2PE, 1JO, 2JO, 3JO, JUD
66:     REV
```

### For Administrators: Uploading References

**Location**: Configure References page

**Format**: JSON file
```json
{
  "1": {
    "v": "GEN 1 1",
    "r": {
      "2063": "EXO 20 11",
      "2439": "EXO 31 18",
      "14373": "PSA 33 6"
    }
  }
}
```

Where:
- Key (`"1"`) is the verse ID
- `"v"` is the verse reference (Book Chapter Verse)
- `"r"` contains referenced verses as ID:Reference pairs

---

## Highlighting & Notes

### Verse Highlighting

**How to Highlight:**
1. **Right-click** on any verse
2. Choose highlight color:
   - **Yellow**: General highlights
   - **Green**: Important verses
3. Highlight saves automatically

**Viewing Highlights:**
- See all highlights on Dashboard
- Filter by color
- Quick navigation to highlighted verses
- Highlights persist across sessions

### Personal Notes

**Creating Notes:**
1. Navigate to Notes page
2. Click "New Note"
3. Select verse or enter reference
4. Write your note
5. Save

**Managing Notes:**
- View all notes in one place
- Edit existing notes
- Search notes by content or reference
- Delete notes when no longer needed

**Note Features:**
- Rich text editor
- Private to your account
- Searchable
- Exportable

---

## Reading Plan

### Overview

Track your Bible reading progress through all 66 books.

### Features

**Progress Tracking:**
- Mark chapters as complete
- Visual progress bars
- Statistics dashboard
- Reading streaks

**Reading Statistics:**
- Total chapters read
- Percentage complete
- Books completed
- Reading history

**How to Use:**
1. Go to Reading Plan page
2. Select a Bible translation
3. Click chapters to mark as read/unread
4. View progress in real-time

---

## Parallel Bibles

### Overview

Compare multiple Bible translations side-by-side.

### Features

**Parallel View:**
- Select up to 3 translations
- Synchronized scrolling
- Same chapter in all versions
- Easy comparison

**Use Cases:**
- **Translation comparison**: See how different versions translate
- **Study**: Get deeper understanding
- **Language learning**: Compare languages
- **Research**: Academic and personal study

**How to Use:**
1. Go to Parallel Bibles page
2. Select Bible translations (2-3)
3. Choose book and chapter
4. Read side-by-side
5. Scroll to compare verses

---

## User Management

### Account Features

**Profile Settings:**
- Update personal information
- Change email address
- Update password
- Delete account

**Security:**
- **Two-Factor Authentication**: Optional 2FA for enhanced security
- **Session management**: View and revoke active sessions
- **Password requirements**: Strong password policy

**Preferences:**
- **Theme**: Light or Dark mode
- **Language**: Interface language selection
- **Default Bible**: Set your preferred translation

### Roles & Permissions

**User Roles:**
1. **Admin**: Full system access
   - Manage all Bibles
   - Upload references
   - Manage users
   - Configure system

2. **Editor**: Content management
   - Upload Bibles
   - Upload references
   - Configure Bibles

3. **User**: Standard access
   - Read Bibles
   - Personal notes
   - Highlights
   - Reading progress

---

## Configuration

### For Administrators

#### Bible Management

**Configure Bibles Page:**
- View all installed Bibles
- Upload new translations
- Edit Bible information
- Delete Bibles
- Boot up all Bibles from resources

**Uploading Bibles:**
1. Go to Configure Bibles
2. Click "Upload Bible"
3. Fill in Bible details:
   - Name
   - Language
   - Abbreviation
4. Upload JSON file
5. Submit

**Supported Bible JSON Formats:**

**Format 1: Swahili Format**
```json
{
  "BIBLEBOOK": [
    {
      "book_number": 1,
      "book_name": "Genesis",
      "CHAPTER": [
        {
          "chapter_number": 1,
          "VERSES": [
            {
              "verse_number": 1,
              "verse_text": "In the beginning..."
            }
          ]
        }
      ]
    }
  ]
}
```

**Format 2: Flat Verses Format**
```json
{
  "verses": [
    {
      "book": "Genesis",
      "chapter": 1,
      "verse": 1,
      "text": "In the beginning..."
    }
  ]
}
```

**Format 3: Nested Books Format**
```json
{
  "books": [
    {
      "name": "Genesis",
      "chapters": [
        {
          "number": 1,
          "verses": [...]
        }
      ]
    }
  ]
}
```

#### Reference Management

**Configure References Page:**
- Upload cross-reference data
- View installed references
- Delete reference data

**Boot Up Feature:**

For administrators to quickly populate the database:

1. Navigate to "Configure Bibles"
2. Click "Boot Up All Bibles"
3. Confirm the action
4. System queues background jobs to:
   - Install all Bibles from `resources/Bibles/` directory
   - Install references from `resources/References/` directory
   - Process asynchronously

**Requirements:**
- Queue worker must be running: `php artisan queue:work`
- JSON files in correct directories
- Proper file formats

#### Role Management

**Managing User Roles:**
1. Go to Role Management (admin only)
2. View all users
3. Assign/remove roles
4. Delete users if necessary

---

## Developer Guide

### Architecture

**Tech Stack:**
- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 + Inertia.js
- **Styling**: Tailwind CSS
- **UI Components**: Reka UI (Radix Vue)
- **Build Tool**: Vite
- **Database**: MySQL/PostgreSQL

**Project Structure:**
```
/
├── app/                    # Laravel application
│   ├── Http/Controllers/   # Request handlers
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic
│   └── Utils/              # Utility classes
├── resources/
│   ├── js/                 # Vue.js application
│   │   ├── components/     # Vue components
│   │   ├── pages/          # Inertia pages
│   │   └── layouts/        # Layout components
│   ├── Bibles/             # Bible JSON files
│   └── References/         # Reference JSON files
├── routes/                 # Route definitions
├── database/               # Migrations & seeders
└── docs/                   # Documentation
```

### API Endpoints

**Bible Endpoints:**
```
GET  /api/bibles                           # List all Bibles
GET  /api/bibles/books/chapters/{chapter}  # Get chapter data
```

**Reference Endpoints:**
```
GET  /api/verses/{verse}/references        # Get verse cross-references
GET  /verses/{verse}/study                 # Verse study page
```

**Highlight Endpoints:**
```
POST   /api/verse-highlights               # Create/update highlight
GET    /api/verse-highlights               # Get user highlights
DELETE /api/verse-highlights/{verse}       # Delete highlight
GET    /api/verse-highlights/chapter       # Get chapter highlights
```

**Note Endpoints:**
```
GET    /api/notes          # List user notes
POST   /api/notes          # Create note
GET    /api/notes/{note}   # Get note
PUT    /api/notes/{note}   # Update note
DELETE /api/notes/{note}   # Delete note
```

**Reading Progress Endpoints:**
```
POST /api/reading-progress/toggle       # Toggle chapter read status
GET  /api/reading-progress/bible        # Get Bible progress
GET  /api/reading-progress/statistics   # Get reading stats
```

### Database Schema

**Key Tables:**
- `bibles`: Bible translations
- `books`: Books of the Bible
- `chapters`: Bible chapters
- `verses`: Individual verses
- `references`: Cross-reference data (JSON)
- `verse_highlights`: User verse highlights
- `notes`: User notes
- `reading_progress`: Chapter completion tracking
- `users`: User accounts
- `roles`: User roles

### Development Workflow

**Setup Development Environment:**
```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development servers
php artisan serve          # Backend (port 8000)
npm run dev               # Frontend (Vite HMR)
```

**Development Commands:**
```bash
npm run dev        # Development with hot reload
npm run build      # Production build
npm run lint       # Lint code
npm run format     # Format code with Prettier
php artisan test   # Run tests
```

**Queue Worker (for background jobs):**
```bash
php artisan queue:work      # Production
php artisan queue:listen    # Development (auto-reload)
```

### Testing

**Running Tests:**
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BibleTest.php

# Run with coverage
php artisan test --coverage
```

**Test Categories:**
- Feature tests: API and page tests
- Unit tests: Service and utility tests
- Browser tests: E2E tests with Dusk (if configured)

### Code Style

**PHP:**
- PSR-12 coding standard
- Laravel conventions
- Use Laravel Pint for formatting:
  ```bash
  ./vendor/bin/pint
  ```

**JavaScript/Vue:**
- ESLint configuration provided
- Prettier for formatting
- Vue 3 Composition API preferred

### Performance Optimization

**Database:**
- Indexes on frequently queried columns
- Eager loading to prevent N+1 queries
- Query result caching where appropriate

**Frontend:**
- Code splitting with Vite
- Lazy loading for heavy components
- Image optimization

**Caching:**
```bash
php artisan config:cache  # Cache configuration
php artisan route:cache   # Cache routes
php artisan view:cache    # Cache views
```

### Deployment

See `PRODUCTION_DEPLOYMENT.md` for comprehensive deployment guide.

**Quick Deployment Steps:**
1. Set up server (Apache/Nginx)
2. Configure SSL certificate
3. Set up database
4. Configure Redis for caching
5. Run migrations
6. Build assets
7. Set up queue workers
8. Configure backups

---

## Troubleshooting

### Common Issues

**References not showing?**
- Verify reference JSON was uploaded
- Check verse IDs match database
- Inspect browser console for errors

**Highlights not saving?**
- Ensure user is authenticated
- Check CSRF token
- Verify API endpoint accessibility

**Build errors?**
- Clear npm cache: `npm cache clean --force`
- Delete `node_modules` and reinstall
- Check Node.js version compatibility

**Database issues?**
- Verify `.env` database credentials
- Check database exists
- Run migrations: `php artisan migrate`

**Queue jobs not running?**
- Start queue worker: `php artisan queue:work`
- Check `jobs` table for pending jobs
- Review `failed_jobs` table for errors

### Getting Help

- **Documentation**: Check this guide and docs in `/docs` folder
- **Issues**: Report bugs on GitHub
- **Community**: Join discussions on GitHub
- **Logs**: Check `storage/logs/laravel.log` for errors

---

## Credits & Acknowledgments

This project utilizes resources from:

- **Bible Translations**: [jadenzaleski/BibleTranslations](https://github.com/jadenzaleski/BibleTranslations)
  - Provides Bible translation JSON files in `resources/Bibles`

- **Cross-References**: [josephilipraja/bible-cross-reference-json](https://github.com/josephilipraja/bible-cross-reference-json)
  - Provides cross-reference data in `resources/References`

We are grateful to these projects for making their resources available to the community.

---

## License

MIT License - See LICENSE file for details.

Copyright (c) 2025 Kea Rajabu Baruan

---

## Contact

For questions, issues, or contributions:
- **GitHub**: [https://github.com/kea137/Bible](https://github.com/kea137/Bible)
- **Issues**: Report bugs or request features on GitHub

---

**Last Updated**: 2025
**Version**: 1.0
