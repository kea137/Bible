# Bible App - React Native Mobile Application

A modern, cross-platform Bible reading and study mobile application built with React Native and Expo. This app is the mobile counterpart to the [Bible web application](https://github.com/kea137/Bible), providing users with a native mobile experience for reading, studying, and engaging with Scripture.

## Features

### Core Features
- 📖 **Bible Reading** - Browse and read from multiple Bible translations and languages
- ✨ **Verse Highlighting** - Mark important verses with customizable color highlights
- 📝 **Personal Notes** - Add private notes to any verse
- 🔗 **Cross-References** - Navigate related verses with ease
- 📚 **Bible Lessons** - Access and study Bible lessons and series
- 🎨 **Verse Sharing** - Create beautiful shareable images with Bible verses
- 🔄 **Parallel View** - Compare multiple translations side-by-side
- 📊 **Reading Progress** - Track your Bible reading journey
- 👤 **User Authentication** - Secure account with email/password login
- 🌓 **Dark Mode** - Choose your preferred theme
- 📱 **Offline Support** - Access previously loaded content offline

### Technical Features
- Cross-platform (iOS & Android)
- TypeScript for type safety
- Context API for state management
- Secure API communication with the Laravel backend
- Responsive design optimized for all screen sizes
- Native performance with React Native

## Prerequisites

- Node.js 18+ and npm/yarn
- Expo CLI (`npm install -g expo-cli`)
- iOS Simulator (macOS only) or Android Studio with emulator
- Expo Go app on your physical device (optional)

## Backend Setup

This mobile app requires the Bible web application backend to be running. Please follow the setup instructions in the [main Bible repository](https://github.com/kea137/Bible) first.

## Installation

1. **Navigate to the Bible-App directory**
   ```bash
   cd Bible-App
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Configure API endpoint**
   
   Update the API base URL in `src/constants/index.ts`:
   ```typescript
   export const API_BASE_URL = __DEV__ 
     ? 'http://localhost:8000'  // Your local backend URL
     : 'https://your-production-url.com';
   ```

   > **Note for iOS Simulator**: If testing on iOS simulator, use your computer's local IP address instead of `localhost`:
   > ```typescript
   > ? 'http://192.168.1.XXX:8000'
   > ```

4. **Start the development server**
   ```bash
   npm start
   ```

5. **Run on your platform**
   - Press `i` for iOS simulator (macOS only)
   - Press `a` for Android emulator
   - Scan QR code with Expo Go app on your device

## Project Structure

```
Bible-App/
├── src/
│   ├── components/        # Reusable UI components
│   ├── contexts/          # React Context providers (Auth, Bible)
│   ├── navigation/        # React Navigation setup
│   ├── screens/           # Screen components
│   │   ├── auth/          # Authentication screens
│   │   ├── HomeScreen.tsx
│   │   ├── BibleReaderScreen.tsx
│   │   ├── LessonsScreen.tsx
│   │   └── SettingsScreen.tsx
│   ├── services/          # API service layer
│   ├── types/             # TypeScript type definitions
│   ├── constants/         # App constants and configuration
│   └── utils/             # Utility functions
├── assets/                # Images, fonts, and static assets
├── App.tsx                # Root component
├── package.json           # Dependencies and scripts
└── tsconfig.json          # TypeScript configuration
```

## Available Scripts

- `npm start` - Start the Expo development server
- `npm run android` - Run on Android emulator/device
- `npm run ios` - Run on iOS simulator (macOS only)
- `npm run web` - Run in web browser
- `npm test` - Run tests
- `npm run lint` - Lint code with ESLint

## API Integration

The app connects to the Bible web application's backend API. Ensure the backend is running and accessible:

### Required API Endpoints
- `/api/login` - User authentication
- `/api/register` - User registration
- `/api/bibles` - Get available Bible translations
- `/api/verses` - Get Bible verses
- `/api/highlights` - Manage verse highlights
- `/api/notes` - Manage verse notes
- `/api/lessons` - Access Bible lessons
- `/api/:language/:version/:references/:book/:chapter/:verse?` - Public verse API

### Authentication
The app uses token-based authentication. Tokens are securely stored in AsyncStorage and automatically included in API requests.

## State Management

### Context Providers

**AuthContext** - Manages user authentication state
- `user` - Current user data
- `isAuthenticated` - Authentication status
- `login()` - Login method
- `register()` - Registration method
- `logout()` - Logout method

**BibleContext** - Manages Bible reading state
- `bibles` - Available Bible translations
- `selectedBible` - Currently selected Bible
- `verses` - Current chapter verses
- `highlights` - User's verse highlights
- `notes` - User's verse notes
- `loadChapter()` - Load a specific chapter
- `addHighlight()` - Add verse highlight
- `addNote()` - Add verse note

## Key Technologies

- **React Native** - Mobile app framework
- **Expo** - Development platform and toolchain
- **TypeScript** - Type-safe JavaScript
- **React Navigation** - Navigation library
- **Axios** - HTTP client for API requests
- **AsyncStorage** - Local data persistence
- **React Context API** - State management

## Features in Development

- [ ] Offline Bible text storage
- [ ] Search functionality
- [ ] Audio Bible playback
- [ ] Reading plans
- [ ] Social sharing to social media platforms
- [ ] Push notifications for daily verses
- [ ] Bookmarks and favorites
- [ ] Advanced study tools (commentaries, concordance)

## Building for Production

### Android
```bash
# Build APK
expo build:android -t apk

# Build App Bundle (for Google Play)
expo build:android -t app-bundle
```

### iOS
```bash
# Build for iOS (requires Apple Developer account)
expo build:ios
```

## Troubleshooting

### Common Issues

**Cannot connect to backend**
- Ensure backend is running on the specified URL
- Check that your device/emulator can reach the backend IP
- For iOS simulator, use your computer's local IP instead of localhost

**Metro bundler errors**
```bash
# Clear cache and restart
expo start -c
```

**Module resolution errors**
```bash
# Clear watchman cache
watchman watch-del-all

# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules && npm install
```

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Credits & Acknowledgments

This mobile app is built to work with:
- **Bible Web Application**: [kea137/Bible](https://github.com/kea137/Bible) - Laravel + Vue.js backend and web frontend
- **Bible Translations**: [jadenzaleski/BibleTranslations](https://github.com/jadenzaleski/BibleTranslations)
- **Cross References**: [josephilipraja/bible-cross-reference-json](https://github.com/josephilipraja/bible-cross-reference-json)

## License

MIT License - Copyright (c) 2025 Kea Rajabu Baruan

See the main [Bible repository LICENSE](../LICENSE) for full details.

## Support

For issues, questions, or feature requests:
- Open an issue on GitHub
- Contact the maintainers
- Check the [main repository documentation](https://github.com/kea137/Bible)

## Disclaimer

This software is provided "AS IS" without warranty of any kind. The authors and contributors are NOT liable for any damages, claims, or issues arising from the use of this software. By using this application, you acknowledge and accept these terms.
