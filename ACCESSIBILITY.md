# Accessibility Documentation

## Overview

This Bible application is committed to providing an accessible experience for all users, including those using assistive technologies. We follow WCAG 2.1 Level AA guidelines to ensure our application is usable by everyone.

## Implemented Accessibility Features

### 1. Keyboard Navigation

- **Skip Navigation**: Press `Tab` on any page to reveal a "Skip to main content" link, allowing keyboard users to bypass repetitive navigation
- **Tab Order**: All interactive elements follow a logical tab order
- **Focus Indicators**: Clear visual focus indicators on all interactive elements
- **Keyboard Shortcuts**: All functionality is accessible via keyboard

### 2. Screen Reader Support

- **ARIA Labels**: All interactive elements have appropriate ARIA labels
- **ARIA Roles**: Semantic HTML and ARIA roles for proper content structure
- **Alt Text**: All images include descriptive alt text
- **Screen Reader Only Text**: Important context provided for screen reader users where visual cues are insufficient

### 3. Visual Accessibility

- **Color Contrast**: All text meets WCAG AA contrast requirements (4.5:1 for normal text, 3:1 for large text)
- **Dark Mode**: Full dark mode support with proper contrast ratios
- **Focus Visible**: Clear focus indicators that meet contrast requirements
- **No Color-Only Information**: Information is not conveyed by color alone

### 4. Component-Specific Accessibility

#### Navigation
- Main navigation has `aria-label="Main navigation"`
- Mobile menu button has descriptive `aria-label`
- Current page is indicated with ARIA current attribute

#### Buttons and Links
- All icon-only buttons have `aria-label` attributes
- External links include `rel="noopener noreferrer"`
- Links opening in new tabs are labeled appropriately

#### Forms
- All form inputs have associated labels
- Error messages are properly announced
- Required fields are indicated both visually and semantically

#### Modals and Dialogs
- Focus is trapped within modals
- Focus returns to trigger element on close
- Escape key closes modals
- Proper ARIA roles and properties

#### Feature Toggles
- Feature flag switches have descriptive labels and ARIA attributes
- State changes are announced to screen readers

## Testing Recommendations

### Manual Testing
1. **Keyboard Navigation**: Navigate entire site using only keyboard
2. **Screen Reader**: Test with NVDA (Windows), JAWS (Windows), or VoiceOver (macOS/iOS)
3. **Zoom**: Test at 200% zoom level
4. **Color Contrast**: Use browser DevTools or dedicated tools

### Automated Testing Tools
- **axe DevTools**: Browser extension for accessibility testing
- **Lighthouse**: Built into Chrome DevTools
- **WAVE**: Web accessibility evaluation tool

## Known Issues and Future Improvements

### Planned Enhancements
- [ ] Add live region announcements for dynamic content updates
- [ ] Implement ARIA live regions for notification toasts
- [ ] Add keyboard shortcuts documentation page
- [ ] Improve table accessibility with proper headers

## Reporting Accessibility Issues

If you encounter any accessibility barriers while using this application, please:

1. Open an issue on GitHub with the "accessibility" label
2. Include:
   - Description of the issue
   - Steps to reproduce
   - Your assistive technology (if applicable)
   - Browser and operating system

## Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [WebAIM Resources](https://webaim.org/)

## Compliance Statement

This application strives to meet WCAG 2.1 Level AA standards. We are committed to maintaining and improving accessibility as the application evolves.

Last Updated: December 2024
