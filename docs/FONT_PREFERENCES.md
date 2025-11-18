# Font Styling Preferences Feature

## Overview
This feature allows users to customize the font family and font size across the entire Bible application according to their personal preferences.

## User Guide

### Accessing Font Preferences
1. Navigate to the Settings page (click on your profile icon)
2. Select "Preferences" or "Appearance"
3. Scroll to the "Font Preferences" section

### Font Family Options
Choose from 9 different font families:
- **System Default**: Uses your device's default font
- **Sans Serif**: Clean, modern font without decorative strokes
- **Serif**: Traditional font with decorative strokes (good for reading)
- **Monospace**: Fixed-width font (each character takes same space)
- **Arial**: Classic sans-serif font
- **Helvetica**: Popular sans-serif font
- **Times New Roman**: Classic serif font
- **Georgia**: Elegant serif font designed for screen reading
- **Courier New**: Traditional monospace typewriter-style font

### Font Size Options
Choose from 5 different font sizes:
- **Extra Small** (12px): Compact text for maximizing content
- **Small** (14px): Slightly reduced size
- **Medium** (16px): Default comfortable reading size
- **Large** (18px): Easier to read, reduced eye strain
- **Extra Large** (20px): Maximum readability

### How It Works
1. Select your preferred font family from the dropdown
2. Select your preferred font size from the dropdown
3. Your changes are automatically saved to your account
4. The page will reload to apply the new font settings
5. Your preferences persist across all pages and sessions

## Technical Details

### Storage
- Font preferences are stored in the `appearance_preferences` JSON column of the `users` table
- Preferences are persisted per user account
- Structure: `{ "font_family": "serif", "font_size": "lg" }`

### Implementation
- Frontend composable: `useFontPreferences.ts`
- Component: `FontSelector.vue`
- Backend controller: `DashboardController::updateFontPreferences()`
- API endpoint: `POST /api/user/font-preferences`

### Browser Support
The feature uses CSS custom properties and is supported in all modern browsers:
- Chrome/Edge 49+
- Firefox 31+
- Safari 9.1+
- Opera 36+

## Troubleshooting

### Changes Not Applying
- Ensure you're logged in
- Try refreshing the page manually
- Clear your browser cache if needed

### Font Looks Different Than Expected
- Some fonts may render differently on different operating systems
- "System Default" uses your OS/browser's default font
- Font rendering can vary between Windows, macOS, and Linux

### Accessibility
- Larger font sizes (Large, Extra Large) improve readability for users with visual impairments
- Serif fonts are often easier to read in print, while sans-serif fonts work well on screens
- Choose the combination that works best for your needs
