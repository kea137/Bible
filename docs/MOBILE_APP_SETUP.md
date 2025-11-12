# Mobile App Setup Guide

This guide helps you set up the React Native mobile app (kea137/Bible-App) to work with the Bible API.

## Prerequisites

- The Bible web application running (this repository)
- Laravel Sanctum configured
- React Native development environment set up

## Backend Setup (Laravel)

### 1. Configure Sanctum for Mobile

Sanctum is already installed in this project. For mobile apps, you'll use token-based authentication.

#### Update CORS Configuration

Edit `config/cors.php` to allow requests from your mobile app:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register'],

'allowed_origins' => ['*'], // Or specify your mobile app's domain for production

'supports_credentials' => true,
```

### 2. Create Login/Register Endpoints

The existing authentication routes in `routes/web.php` can be used, but you may want to create API-specific versions:

```php
// routes/api.php
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
```

### 3. Create Authentication Controller

```php
// app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'language' => 'en',
            'onboarding_completed' => false,
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke old tokens (optional - for single device login)
        // $user->tokens()->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
```

## Mobile App Setup (React Native)

### 1. Install Dependencies

```bash
npm install @react-native-async-storage/async-storage axios
# or
yarn add @react-native-async-storage/async-storage axios
```

### 2. Create API Service

```javascript
// services/api.js
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';

const API_BASE_URL = 'https://your-domain.com/api/mobile';
const AUTH_BASE_URL = 'https://your-domain.com/api/auth';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Handle 401 errors (unauthorized)
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await AsyncStorage.removeItem('authToken');
      // Navigate to login screen
      // navigation.navigate('Login');
    }
    return Promise.reject(error);
  }
);

export const authApi = {
  register: async (name, email, password, passwordConfirmation) => {
    const response = await axios.post(`${AUTH_BASE_URL}/register`, {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    });
    if (response.data.token) {
      await AsyncStorage.setItem('authToken', response.data.token);
    }
    return response.data;
  },

  login: async (email, password) => {
    const response = await axios.post(`${AUTH_BASE_URL}/login`, {
      email,
      password,
    });
    if (response.data.token) {
      await AsyncStorage.setItem('authToken', response.data.token);
    }
    return response.data;
  },

  logout: async () => {
    try {
      await api.post(`${AUTH_BASE_URL}/logout`);
    } finally {
      await AsyncStorage.removeItem('authToken');
    }
  },
};

export const bibleApi = {
  // Dashboard
  getDashboard: () => api.get('/dashboard'),

  // Onboarding
  getOnboarding: () => api.get('/onboarding'),
  completeOnboarding: (data) => api.post('/onboarding', data),

  // Bibles
  getBibles: () => api.get('/bibles'),
  getBible: (id, params) => api.get(`/bibles/${id}`, { params }),
  getChapter: (chapterId) => api.get(`/bibles/chapters/${chapterId}`),
  getParallelBibles: () => api.get('/bibles/parallel'),

  // User Preferences
  updateLocale: (locale) => api.post('/update-locale', { locale }),
  updateTheme: (theme) => api.post('/update-theme', { theme }),
  updateTranslations: (translations) => 
    api.post('/update-translations', { preferred_translations: translations }),

  // Verse Highlights
  createHighlight: (verseId, color, note) => 
    api.post('/verse-highlights', { verse_id: verseId, color, note }),
  deleteHighlight: (verseId) => api.delete(`/verse-highlights/${verseId}`),
  getHighlights: () => api.get('/verse-highlights'),
  getChapterHighlights: (chapterId) => 
    api.get('/verse-highlights/chapter', { params: { chapter_id: chapterId } }),

  // Notes
  getNotes: () => api.get('/notes'),
  createNote: (verseId, title, content) => 
    api.post('/notes', { verse_id: verseId, title, content }),
  updateNote: (noteId, title, content) => 
    api.put(`/notes/${noteId}`, { title, content }),
  deleteNote: (noteId) => api.delete(`/notes/${noteId}`),
  getNote: (noteId) => api.get(`/notes/${noteId}`),

  // Reading Progress
  getReadingPlan: (bibleId) => api.get('/reading-plan', { params: { bible_id: bibleId } }),
  toggleChapterProgress: (chapterId, bibleId) => 
    api.post('/reading-progress/toggle', { chapter_id: chapterId, bible_id: bibleId }),
  getBibleProgress: (bibleId) => 
    api.get('/reading-progress/bible', { params: { bible_id: bibleId } }),
  getReadingStatistics: () => api.get('/reading-progress/statistics'),

  // Lessons
  getLessons: () => api.get('/lessons'),
  getLesson: (lessonId) => api.get(`/lessons/${lessonId}`),
  toggleLessonProgress: (lessonId) => 
    api.post('/lesson-progress/toggle', { lesson_id: lessonId }),

  // Share
  getShareData: (params) => api.get('/share', { params }),

  // Verse Study
  getVerseReferences: (verseId) => api.get(`/verses/${verseId}/references`),
  getVerseStudy: (verseId) => api.get(`/verses/${verseId}/study`),
};

export default api;
```

### 3. Example Usage in Components

```javascript
// screens/LoginScreen.js
import React, { useState } from 'react';
import { View, TextInput, Button, Alert } from 'react-native';
import { authApi } from '../services/api';

export default function LoginScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    try {
      setLoading(true);
      const data = await authApi.login(email, password);
      // Navigate to home or dashboard
      navigation.navigate('Home');
    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View>
      <TextInput
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        autoCapitalize="none"
      />
      <TextInput
        placeholder="Password"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
      />
      <Button title="Login" onPress={handleLogin} disabled={loading} />
    </View>
  );
}
```

```javascript
// screens/DashboardScreen.js
import React, { useEffect, useState } from 'react';
import { View, Text, ActivityIndicator } from 'react-native';
import { bibleApi } from '../services/api';

export default function DashboardScreen() {
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = async () => {
    try {
      const response = await bibleApi.getDashboard();
      setDashboard(response.data.data);
    } catch (error) {
      console.error('Failed to load dashboard:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <ActivityIndicator />;
  }

  return (
    <View>
      <Text>Welcome, {dashboard?.userName}!</Text>
      {dashboard?.verseOfTheDay && (
        <View>
          <Text>{dashboard.verseOfTheDay.text}</Text>
          <Text>
            {dashboard.verseOfTheDay.book.title}{' '}
            {dashboard.verseOfTheDay.chapter.chapter_number}:
            {dashboard.verseOfTheDay.verse_number}
          </Text>
        </View>
      )}
      <View>
        <Text>Chapters Read: {dashboard?.readingStats.chapters_completed}</Text>
        <Text>Verses Today: {dashboard?.readingStats.verses_read_today}</Text>
      </View>
    </View>
  );
}
```

## Testing

### Using Postman or curl

```bash
# Register
curl -X POST https://your-domain.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'

# Use the token from login response
TOKEN="your_token_here"

# Get Dashboard
curl https://your-domain.com/api/mobile/dashboard \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## Security Considerations

1. **Always use HTTPS in production**
2. **Store tokens securely** using AsyncStorage with encryption if needed
3. **Implement token refresh** if needed for long-lived sessions
4. **Rate limit API requests** on the server side
5. **Validate all user inputs** on both client and server
6. **Keep dependencies updated** for security patches

## Troubleshooting

### CORS Issues
- Ensure `config/cors.php` is properly configured
- Check that `supports_credentials` is set to `true`

### 401 Unauthorized
- Verify the token is being sent correctly
- Check token hasn't expired
- Ensure user exists and is active

### Connection Refused
- Verify API URL is correct
- Check server is running
- Ensure firewall allows connections

## Next Steps

1. Complete the authentication flow in your mobile app
2. Implement the onboarding process
3. Build out the main features (reading, notes, highlights)
4. Test thoroughly on both iOS and Android
5. Set up error logging and analytics

For full API documentation, see [MOBILE_API.md](./MOBILE_API.md)
