# Bible App - React Native Development Guide

## Overview

This document provides detailed information about the React Native Bible App architecture, development practices, and guidelines for contributors.

## Architecture

### Application Structure

The app follows a modular architecture with clear separation of concerns:

```
src/
├── components/     # Reusable UI components
├── contexts/       # React Context for global state
├── navigation/     # Navigation configuration
├── screens/        # Screen components
├── services/       # External service integrations (API)
├── types/          # TypeScript type definitions
├── constants/      # App constants and configuration
└── utils/          # Helper functions and utilities
```

### State Management

The app uses React Context API for state management with two main contexts:

1. **AuthContext** - User authentication and profile management
2. **BibleContext** - Bible reading state, verses, highlights, and notes

### Navigation

Navigation is implemented using React Navigation v7:
- **Stack Navigator** - For authentication flow
- **Bottom Tab Navigator** - For main app navigation
- **Drawer Navigator** - (Future) For additional navigation options

### API Communication

The app communicates with a Laravel backend via RESTful API:
- **Base Service**: `src/services/api.ts`
- **Authentication**: Token-based with AsyncStorage
- **Interceptors**: Automatic token injection and error handling

## Core Features Implementation

### 1. Bible Reading

**Components:**
- `BibleReaderScreen.tsx` - Main reading interface
- Displays verses with highlighting and notes
- Long-press to add highlights or notes

**Key Functions:**
- `loadChapter(book, chapter)` - Loads chapter verses
- `getVerseHighlight(verse)` - Retrieves highlight for a verse
- `getVerseNotes(verse)` - Retrieves notes for a verse

### 2. Verse Highlighting

**Implementation:**
- Color selection from predefined palette
- Stored in backend and synced across devices
- Visual feedback on highlighted verses

### 3. Notes System

**Features:**
- Per-verse note creation
- Edit and delete functionality
- Note count badge on verses

### 4. Lessons

**Components:**
- `LessonsScreen.tsx` - List of available lessons
- Paginated lesson loading
- Series identification

### 5. User Authentication

**Flow:**
1. User enters credentials
2. App sends request to `/api/login`
3. Backend returns JWT token and user data
4. Token stored in AsyncStorage
5. Token auto-injected in subsequent requests

## Component Guidelines

### Creating New Screens

```typescript
import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { COLORS } from '../constants';

const NewScreen: React.FC<{ navigation: any }> = ({ navigation }) => {
  return (
    <View style={styles.container}>
      <Text>New Screen</Text>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
});

export default NewScreen;
```

### TypeScript Guidelines

- Always define prop interfaces
- Use strict type checking
- Avoid `any` type when possible
- Leverage union types for flexibility

Example:
```typescript
interface VerseProps {
  verse: Verse;
  onPress?: (verse: Verse) => void;
  highlighted?: boolean;
}

const VerseComponent: React.FC<VerseProps> = ({ 
  verse, 
  onPress, 
  highlighted = false 
}) => {
  // Implementation
};
```

### Styling Guidelines

- Use StyleSheet.create for all styles
- Follow consistent naming conventions
- Use constants for colors and sizes
- Implement responsive designs

Example:
```typescript
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: COLORS.text,
  },
});
```

## API Integration

### Adding New API Endpoints

1. Define the endpoint in `constants/index.ts`:
```typescript
export const API_ENDPOINTS = {
  // ... existing endpoints
  NEW_FEATURE: '/api/new-feature',
};
```

2. Add method to `services/api.ts`:
```typescript
async getNewFeature(): Promise<FeatureData> {
  const response = await this.api.get<FeatureData>(
    API_ENDPOINTS.NEW_FEATURE
  );
  return response.data;
}
```

3. Use in components:
```typescript
import apiService from '../services/api';

const data = await apiService.getNewFeature();
```

## Testing

### Unit Tests

```typescript
// Example test for a utility function
import { formatVerseReference } from '../utils';

describe('formatVerseReference', () => {
  it('should format verse reference correctly', () => {
    const result = formatVerseReference('John', 3, 16);
    expect(result).toBe('John 3:16');
  });
});
```

### Component Tests

```typescript
import { render, fireEvent } from '@testing-library/react-native';
import VerseComponent from '../components/VerseComponent';

describe('VerseComponent', () => {
  it('should render verse text', () => {
    const verse = { /* ... */ };
    const { getByText } = render(<VerseComponent verse={verse} />);
    expect(getByText(verse.text)).toBeTruthy();
  });
});
```

## Performance Optimization

### Best Practices

1. **List Rendering**
   - Use `FlatList` for long lists
   - Implement `keyExtractor` properly
   - Use `getItemLayout` when possible

2. **Image Optimization**
   - Use appropriate image formats
   - Implement lazy loading
   - Cache images locally

3. **State Updates**
   - Minimize re-renders with `useMemo` and `useCallback`
   - Use local state when possible
   - Batch state updates

4. **API Calls**
   - Implement caching strategies
   - Use pagination for large datasets
   - Handle loading and error states

## Debugging

### Common Issues and Solutions

**Issue: Cannot connect to backend**
```typescript
// Solution: Check API_BASE_URL in constants/index.ts
// For iOS simulator, use IP address instead of localhost
export const API_BASE_URL = 'http://192.168.1.XXX:8000';
```

**Issue: Navigation not working**
```typescript
// Solution: Ensure screen is registered in navigation
<Stack.Screen name="ScreenName" component={ScreenComponent} />
```

**Issue: AsyncStorage errors**
```typescript
// Solution: Always handle AsyncStorage errors
try {
  const value = await AsyncStorage.getItem(key);
} catch (error) {
  console.error('AsyncStorage error:', error);
}
```

### Debugging Tools

- **React Native Debugger** - Full-featured debugging
- **Flipper** - Native debugging tool
- **console.log** - Basic debugging (avoid in production)
- **Reactotron** - Advanced React Native debugging

## Build Process

### Development Build

```bash
npm start
```

### Production Build

**Android:**
```bash
expo build:android -t app-bundle
```

**iOS:**
```bash
expo build:ios
```

### Environment Variables

Create `.env` files for different environments:

```
# .env.development
API_BASE_URL=http://localhost:8000

# .env.production
API_BASE_URL=https://api.yourdomain.com
```

## Deployment

### App Store Deployment (iOS)

1. Ensure all assets are correct
2. Update version in `app.json`
3. Build with `expo build:ios`
4. Submit to App Store Connect

### Google Play Deployment (Android)

1. Update version in `app.json`
2. Build with `expo build:android -t app-bundle`
3. Upload to Google Play Console
4. Complete store listing

## Future Enhancements

### Planned Features

- [ ] Offline Bible text storage with SQLite
- [ ] Full-text search across translations
- [ ] Audio Bible integration
- [ ] Daily verse notifications
- [ ] Reading plans and progress tracking
- [ ] Social features (share, discuss)
- [ ] Advanced study tools
- [ ] Widgets for home screen

### Technical Improvements

- [ ] Implement Redux for complex state management
- [ ] Add comprehensive test coverage
- [ ] Implement CI/CD pipeline
- [ ] Performance monitoring with Sentry
- [ ] Analytics integration
- [ ] A/B testing framework

## Resources

### Documentation
- [React Native Docs](https://reactnative.dev/docs/getting-started)
- [Expo Docs](https://docs.expo.dev/)
- [React Navigation](https://reactnavigation.org/docs/getting-started)

### Community
- [React Native Community](https://github.com/react-native-community)
- [Expo Forums](https://forums.expo.dev/)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/react-native)

## Contributing

Please refer to the main [CONTRIBUTING.md](../CONTRIBUTING.md) for contribution guidelines.

## Support

For technical questions or issues:
1. Check this documentation
2. Search existing GitHub issues
3. Open a new issue with detailed information
4. Contact the development team

---

Last updated: 2025-11-10
