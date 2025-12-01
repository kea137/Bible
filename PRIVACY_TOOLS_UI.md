# Privacy Tools - Frontend UI Implementation

## Overview
This document describes the frontend UI implementation for the privacy tools feature.

## 1. Data Export Component

**Location:** Settings → Profile → Export Your Data

### Features:
- **Export Button**: Blue-themed card with download icon
- **Confirmation Dialog**: Shows detailed list of data being exported:
  - Profile information
  - Notes on Bible verses
  - Verse highlights
  - Created lessons
  - Lesson progress
  - Reading progress
  - Verse link canvases
- **Loading State**: Button shows "Exporting..." during download
- **Direct Download**: Opens browser download automatically

### Visual Design:
```
┌────────────────────────────────────────────┐
│ Export your data                           │
│ Download all your personal data            │
├────────────────────────────────────────────┤
│ Data Export                                │
│ Download a ZIP file containing all your    │
│ personal data in JSON format.              │
│                                            │
│ [📥 Export my data]                        │
└────────────────────────────────────────────┘
```

## 2. Activity Logs Page

**Location:** Admin Sidebar → Activity Logs (Admin-only)

### Features:
- **Filters**:
  - Action type dropdown (account_deletion, data_export, role_update, etc.)
  - Date from/to range pickers
  - Filter button
  - Reset button
- **Results Table**:
  - Action (color-coded badges)
  - User (name and email)
  - Description (with subject user info)
  - IP Address (monospace font)
  - Date (formatted timestamp)
- **Pagination**: Full pagination controls for large datasets
- **Empty State**: Friendly message when no logs found

### Color Coding:
- **Red**: Deletion actions
- **Blue**: Export actions
- **Purple**: Role update actions
- **Gray**: Other actions

### Visual Design:
```
┌──────────────────────────────────────────────────────────┐
│ Activity Logs                                            │
│ View and filter sensitive actions performed by admins    │
├──────────────────────────────────────────────────────────┤
│ [Action ▼] [From Date] [To Date] [🔍 Filter] [↻ Reset] │
├──────────────────────────────────────────────────────────┤
│ Showing 1 to 50 of 100 logs                             │
├──────────────────────────────────────────────────────────┤
│ Action          │ User        │ Description  │ IP       │
├─────────────────┼─────────────┼──────────────┼──────────┤
│ [data_export]   │ John Doe    │ User John... │ 127.0... │
│ [role_update]   │ Admin User  │ Updated ro...│ 192.1... │
└──────────────────────────────────────────────────────────┘
```

## 3. Navigation Integration

### Sidebar (Admin View):
```
Dashboard
Bibles
Parallel Bibles
Lessons
Reading Plan
Highlights
Notes
Verse Link
────────────────
Configure Bibles
Configure References
Role Management
Activity Logs        ← NEW (Admin-only)
Lessons Management
Documentation
License
```

### Profile Settings:
```
Profile Information
[Name input]
[Email input]
[Save]

Export your data     ← NEW
[📥 Export my data]

Delete account
[⚠️ Delete account]
```

## Usage Instructions

### For Users - Exporting Data:
1. Navigate to Settings → Profile
2. Scroll to "Export your data" section
3. Click "Export my data" button
4. Review the data export dialog
5. Click "Export" to download
6. Save the ZIP file when prompted

### For Admins - Viewing Activity Logs:
1. Click "Activity Logs" in the sidebar (only visible to admins)
2. Use filters to narrow down results:
   - Select action type
   - Set date range
   - Click "Filter"
3. View detailed information in the table
4. Use pagination to browse multiple pages
5. Click "Reset" to clear all filters

## Technical Details

### Components Created:
- `resources/js/components/DataExport.vue`
  - Uses Dialog component from UI library
  - Handles direct download via window.location
  - Provides loading feedback

- `resources/js/pages/ActivityLogs.vue`
  - Full-featured data table with filters
  - Pagination using Inertia.js
  - Color-coded badge system
  - Responsive design

### Routes Used:
- `/settings/export-data` - Data export endpoint
- `/activity-logs` - Activity logs page (admin-only)

### Permissions:
- Data export: Available to all authenticated users
- Activity logs: Only users with role number 1 (admin)
