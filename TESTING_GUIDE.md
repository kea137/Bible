# Testing Guide for New Features

## Prerequisites

Before testing, make sure to:

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Build the frontend** (if not already done):
   ```bash
   npm run build
   ```

3. **Start the development server:**
   ```bash
   php artisan serve
   ```

4. **Ensure you have at least one Bible with data loaded** in the database

## Test Scenarios

### 1. Test Highlighted Verses Page Enhancements

**Steps:**
1. Navigate to a Bible chapter
2. Highlight a few verses (click on verse text and choose a highlight color)
3. Go to "Highlights" page from the sidebar
4. On each highlighted verse, click the three-dot menu (⋮)
5. Test each menu option:
   - **Study this Verse** - Should navigate to verse study page
   - **Add/Edit Note** - Should open note dialog
   - **Remove Highlight** - Should remove the highlight and show success message

**Expected Results:**
- Dropdown menu appears on click
- Each action works as expected
- Success/error messages appear
- Highlighting updates correctly

### 2. Test Reading Progress Tracking

**Steps:**
1. Navigate to any Bible
2. Select a book and chapter
3. Read through the chapter
4. Click the "Mark as Read" button (between Previous/Next buttons)
5. Verify button text changes to "Completed"
6. Navigate to another chapter and mark it as read
7. Go back to the first chapter and verify it still shows as completed
8. Click "Completed" button to unmark the chapter

**Expected Results:**
- Button toggles between "Mark as Read" and "Completed"
- State persists when navigating between chapters
- Can mark/unmark chapters multiple times

### 3. Test Reading Plan Page

**Steps:**
1. Mark a few chapters as read (following steps above)
2. Click "Reading Plan" in the sidebar
3. Verify the following displays correctly:
   - Overall progress percentage and bar
   - Today's reading count
   - Total completed chapters
   - Remaining chapters count
   - Four suggested reading plans with calculations

**Expected Results:**
- All statistics are accurate
- Progress bar reflects correct percentage
- Suggested plans show different paces
- Guidelines section is readable and helpful

### 4. Test Dashboard Updates

**Steps:**
1. Mark several chapters as read today
2. Navigate to Dashboard
3. Check the statistics cards

**Expected Results:**
- "Verses Today" shows an estimate based on chapters read
- "Progress" shows total chapters completed
- If you have a last reading, "Continue Reading" card appears with correct info

### 5. Test Cross-References System (Verification)

**Steps:**
1. Ensure you have references loaded in the database
2. Navigate to a Bible chapter with references
3. Hover over a verse number that has references
4. Verify references appear in the hover card
5. Verify references show verses from the CURRENT Bible, not the reference Bible

**Expected Results:**
- References load correctly
- Reference text matches the current Bible version
- Cross-reference panel updates on hover

### 6. Test Navigation Integration

**Steps:**
1. Verify "Reading Plan" appears in the sidebar
2. Click it and verify it navigates correctly
3. Check that the icon (target) displays properly

**Expected Results:**
- Link is visible and correctly positioned
- Navigation works smoothly
- Active state shows when on the page

## Edge Cases to Test

### Empty States
- [ ] Highlights page with no highlights
- [ ] Reading progress with 0 chapters read
- [ ] Cross-references with no references

### Error Handling
- [ ] Try marking chapter as read without authentication (should show login prompt)
- [ ] Try removing a highlight that doesn't exist
- [ ] Network error handling

### Data Consistency
- [ ] Mark chapter as read in Bible A, verify it shows in Reading Plan
- [ ] Mark chapter, go to Dashboard, verify stats update
- [ ] Multiple users should have independent progress

## Performance Checks

- [ ] Page load times are acceptable
- [ ] Navigation is smooth
- [ ] API calls complete quickly
- [ ] No console errors in browser

## Browser Compatibility

Test in:
- [ ] Chrome/Edge
- [ ] Firefox
- [ ] Safari
- [ ] Mobile browsers (responsive design)

## Database Verification

Check the database directly:

```sql
-- Verify reading progress records
SELECT * FROM reading_progress WHERE user_id = YOUR_USER_ID;

-- Check completion counts
SELECT 
    user_id, 
    COUNT(*) as completed_chapters,
    DATE(completed_at) as date
FROM reading_progress 
WHERE completed = 1 
GROUP BY user_id, DATE(completed_at);
```

## API Endpoint Tests

Use a tool like Postman or curl:

```bash
# Get reading statistics
curl -X GET "http://localhost:8000/api/reading-progress/statistics" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Toggle chapter completion
curl -X POST "http://localhost:8000/api/reading-progress/toggle" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"chapter_id": 1, "bible_id": 1}'

# Get Bible progress
curl -X GET "http://localhost:8000/api/reading-progress/bible?bible_id=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Known Limitations

1. Reading progress is per user, not synced across devices (would require additional work)
2. Verse count is estimated at 25 verses/chapter (actual varies)
3. Progress tracking is chapter-level, not verse-level
4. Reading plans assume continuous reading (no breaks)

## Report Issues

If you find any bugs or unexpected behavior:

1. Note the exact steps to reproduce
2. Check browser console for errors
3. Verify database state
4. Check server logs
5. Create an issue with details

## Success Criteria

All features should:
- ✅ Work without errors
- ✅ Display correct data
- ✅ Persist state properly
- ✅ Handle errors gracefully
- ✅ Be intuitive to use
- ✅ Follow existing UI patterns
