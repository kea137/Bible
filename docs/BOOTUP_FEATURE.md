# Bible and Reference Bootup Feature

## Overview

This feature provides a "Boot Up" functionality that allows administrators to install all Bible translations and their references in bulk from the resources directory. The installation process runs as a background job to avoid blocking the user interface.

## Components

### Jobs

1. **InstallAllBibles** (`app/Jobs/InstallAllBibles.php`)
   - Scans the `resources/Bibles/` directory for JSON files
   - Installs each Bible translation found
   - Skips Bibles that already exist in the database
   - Logs progress and errors
   - Timeout: 1 hour

2. **InstallReferencesForFirstBible** (`app/Jobs/InstallReferencesForFirstBible.php`)
   - Scans the `resources/References/` directory for JSON files
   - Installs cross-references for the first Bible in the database
   - Uses the ReferenceService to parse and store references
   - Timeout: 1 hour

3. **BootupBiblesAndReferences** (`app/Jobs/BootupBiblesAndReferences.php`)
   - Master job that chains both installation jobs
   - Dispatches InstallAllBibles first, then InstallReferencesForFirstBible
   - Timeout: 2 hours

### Controller

**BibleController::bootup()** (`app/Http/Controllers/BibleController.php`)
- Endpoint: `POST /bibles/bootup`
- Authorization: Requires admin role
- Dispatches the BootupBiblesAndReferences job to the queue
- Returns success message with flash notification

### Frontend

**Configure Bibles Page** (`resources/js/pages/Configure Bibles.vue`)
- Added "Boot Up All Bibles" button
- Shows confirmation dialog before starting the process
- Uses Sonner for toast notifications

**AppShell Component** (`resources/js/components/AppShell.vue`)
- Integrated Sonner toast notifications
- Watches for flash messages (success, error, info) from Inertia responses
- Displays notifications automatically on every page

## Usage

### For Administrators

1. Navigate to the "Configure Bibles" page (`/bibles/configure`)
2. Click the "Boot Up All Bibles" button
3. Confirm the action in the dialog
4. The system will queue the installation job
5. You can continue using the application while the job runs in the background
6. You'll receive a notification when the job starts (success flash message)

### Queue Worker

For the jobs to execute, you need a queue worker running:

```bash
php artisan queue:work
```

Or for development with auto-reload:

```bash
php artisan queue:listen
```

## Testing

Run the bootup tests:

```bash
php artisan test --filter=BibleBootupTest
```

This will test:
- Admin can trigger bootup
- Non-admin cannot trigger bootup
- Guest cannot trigger bootup
- Job instantiation
- Job chaining

## Technical Details

### Queue Configuration

- Default queue connection: `database` (configured in `config/queue.php`)
- Jobs are stored in the `jobs` table
- Failed jobs are stored in the `failed_jobs` table

### Notifications

The application uses Vue Sonner for toast notifications:
- Success messages appear as green toasts
- Error messages appear as red toasts
- Info messages appear as blue toasts
- Notifications automatically appear on all pages
- Position: Top-right corner

### Job Flow

```
User clicks "Boot Up" button
    ↓
POST /bibles/bootup
    ↓
BootupBiblesAndReferences job dispatched to queue
    ↓
InstallAllBibles job executes (synchronously within main job)
    ↓
InstallReferencesForFirstBible job executes (synchronously within main job)
    ↓
Jobs complete (logs available in Laravel logs)
```

## File Formats

### Bible JSON Format

The system supports multiple Bible JSON formats (auto-detected):
- Swahili format (nested structure with BIBLEBOOK, CHAPTER, VERSES)
- Flat verses format (array of verse objects)
- Nested books format (books array with chapters and verses)
- Nested associative format (book names as keys)

### Reference JSON Format

References should be in the format:
```json
{
  "1": {
    "v": "GEN 1 1",
    "r": {
      "2063": "EXO 20 11",
      "2064": "PSA 33 6"
    }
  }
}
```

## Future Enhancements

Potential improvements:
- Add progress tracking for job execution
- Add ability to cancel running jobs
- Add job completion notifications via Sonner
- Add ability to select which Bibles to install
- Add ability to install references for all Bibles, not just the first one
