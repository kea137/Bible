# Bible App - Quick Start Guide

This guide will help you set up and run the Bible React Native mobile app.

## Prerequisites

Before you begin, ensure you have the following installed:

### Required
- **Node.js** (v18 or higher) - [Download](https://nodejs.org/)
- **npm** or **yarn** - Comes with Node.js
- **Expo CLI** - Install globally: `npm install -g expo-cli`

### For Development
- **iOS**: macOS with Xcode (for iOS Simulator)
- **Android**: Android Studio with Android SDK and emulator
- **Physical Device**: Expo Go app from App Store or Google Play

## Backend Setup

⚠️ **Important**: The mobile app requires the Bible web application backend to be running.

1. Navigate to the main repository root (parent directory)
2. Follow the installation instructions in the main README.md
3. Ensure the Laravel backend is running on `http://localhost:8000`

## Mobile App Installation

### Step 1: Navigate to Bible-App Directory

```bash
cd Bible-App
```

### Step 2: Install Dependencies

```bash
npm install
```

This will install all required packages including:
- React Native core libraries
- React Navigation
- Axios for API calls
- AsyncStorage for local data
- And more...

### Step 3: Configure API Endpoint

Open `src/constants/index.ts` and update the API URL:

```typescript
export const API_BASE_URL = __DEV__ 
  ? 'http://localhost:8000'  // Change this if needed
  : 'https://your-production-url.com';
```

**Important Notes:**
- **iOS Simulator**: Use your computer's IP address instead of localhost
  - Find your IP: `ifconfig | grep "inet " | grep -v 127.0.0.1`
  - Example: `'http://192.168.1.100:8000'`
- **Android Emulator**: Use `10.0.2.2` instead of localhost
  - Example: `'http://10.0.2.2:8000'`
- **Physical Device**: Use your computer's IP address on the same network

### Step 4: Start the Development Server

```bash
npm start
```

This will start the Expo development server and display a QR code.

## Running the App

### Option 1: iOS Simulator (macOS only)

Press `i` in the terminal or run:
```bash
npm run ios
```

### Option 2: Android Emulator

Press `a` in the terminal or run:
```bash
npm run android
```

Make sure you have an Android emulator running in Android Studio.

### Option 3: Physical Device (Easiest)

1. Install **Expo Go** from App Store (iOS) or Google Play (Android)
2. Scan the QR code shown in the terminal
3. The app will load on your device

## First Run

When you first run the app:

1. **Register an Account**
   - Click "Sign Up" on the login screen
   - Enter your name, email, and password
   - Or use an existing account from the web app

2. **Select a Bible**
   - The app will automatically load available Bibles
   - You can change the Bible in Settings

3. **Start Reading**
   - Browse to any book and chapter
   - Long-press a verse to highlight or add notes

## Troubleshooting

### Cannot Connect to Backend

**Error**: Network request failed or timeout

**Solutions**:
1. Ensure backend is running: `php artisan serve` in main repo
2. Check API_BASE_URL in `src/constants/index.ts`
3. For iOS simulator, use IP address instead of localhost
4. For Android emulator, use `10.0.2.2` instead of localhost
5. Check firewall settings

### Metro Bundler Errors

**Error**: Metro bundler fails to start

**Solutions**:
```bash
# Clear cache
npm start -- --reset-cache

# Or
expo start -c
```

### Module Not Found Errors

**Error**: Cannot find module 'X'

**Solutions**:
```bash
# Delete node_modules and reinstall
rm -rf node_modules
npm install

# Clear watchman (macOS/Linux)
watchman watch-del-all
```

### iOS Build Errors (CocoaPods)

**Error**: CocoaPods errors

**Solutions**:
```bash
cd ios
pod install
cd ..
```

### Android Build Errors

**Error**: Gradle build failed

**Solutions**:
```bash
cd android
./gradlew clean
cd ..
```

### App Crashes on Startup

**Solutions**:
1. Check console for error messages
2. Ensure all dependencies are installed
3. Clear cache and rebuild:
   ```bash
   expo start -c
   ```

## Development Tips

### Hot Reload

The app supports hot reload. Changes to code will automatically refresh the app.

### Debugging

- **Shake Device**: Opens developer menu
- **iOS Simulator**: Cmd + D
- **Android Emulator**: Cmd + M (Mac) or Ctrl + M (Windows/Linux)

Enable Remote JS Debugging for:
- Chrome DevTools debugging
- Network request inspection
- Console logs

### Viewing Logs

**Option 1: Expo DevTools**
```bash
npm start
# Click on "Open DevTools" in terminal
```

**Option 2: Terminal Logs**
Logs will appear in the terminal where you ran `npm start`

### Making Changes

1. Edit files in `src/` directory
2. Save the file
3. App will automatically reload
4. Check for errors in terminal or on device

## Common Tasks

### Adding a New Screen

1. Create file in `src/screens/NewScreen.tsx`
2. Add to navigation in `src/navigation/AppNavigator.tsx`
3. Add screen name to `src/constants/index.ts` SCREENS object

### Adding a New API Endpoint

1. Add endpoint to `src/constants/index.ts` API_ENDPOINTS
2. Add method to `src/services/api.ts`
3. Use in components

### Testing on Multiple Devices

You can run the app on multiple devices simultaneously:
1. Start with `npm start`
2. Scan QR code on each device
3. All devices will receive updates

## Next Steps

Once the app is running:

1. **Explore Features**
   - Read Bible verses
   - Add highlights and notes
   - Browse lessons
   - Customize settings

2. **Review Documentation**
   - [README.md](README.md) - Full documentation
   - [DEVELOPMENT.md](DEVELOPMENT.md) - Development guide

3. **Start Development**
   - Make changes to the codebase
   - Test new features
   - Submit pull requests

## Need Help?

- Check the [main README](README.md)
- Review [DEVELOPMENT.md](DEVELOPMENT.md)
- Open an issue on GitHub
- Contact the development team

## Quick Reference

| Command | Description |
|---------|-------------|
| `npm start` | Start development server |
| `npm run ios` | Run on iOS simulator |
| `npm run android` | Run on Android emulator |
| `npm run web` | Run in web browser |
| `npm test` | Run tests |
| `npm run lint` | Lint code |

## Success Checklist

- [ ] Node.js and npm installed
- [ ] Expo CLI installed globally
- [ ] Backend running on http://localhost:8000
- [ ] Dependencies installed (`npm install`)
- [ ] API endpoint configured correctly
- [ ] Development server started (`npm start`)
- [ ] App running on device/simulator
- [ ] Can login/register successfully
- [ ] Can read Bible verses

If all items are checked, you're ready to start using and developing the app! 🎉
