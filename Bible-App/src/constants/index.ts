// Constants for Bible App

// API Configuration
export const API_BASE_URL = __DEV__ 
  ? 'http://localhost:8000' 
  : 'https://your-production-url.com';

export const API_ENDPOINTS = {
  // Auth endpoints
  LOGIN: '/api/login',
  REGISTER: '/api/register',
  LOGOUT: '/api/logout',
  USER: '/api/user',
  
  // Bible endpoints
  BIBLES: '/api/bibles',
  BOOKS: '/api/books',
  VERSES: '/api/verses',
  PUBLIC_VERSE: '/api/:language/:version/:references/:book/:chapter/:verse?',
  
  // Cross-references
  REFERENCES: '/api/references',
  
  // Highlights and Notes
  HIGHLIGHTS: '/api/highlights',
  NOTES: '/api/notes',
  
  // Lessons
  LESSONS: '/api/lessons',
  LESSON_PROGRESS: '/api/lesson-progress',
  
  // Reading Plan
  READING_PLAN: '/api/reading-plan',
  
  // Share
  SHARE: '/api/share',
};

// Storage Keys
export const STORAGE_KEYS = {
  AUTH_TOKEN: 'auth_token',
  USER_DATA: 'user_data',
  SELECTED_BIBLE: 'selected_bible',
  THEME: 'theme',
  FONT_SIZE: 'font_size',
  LAST_READ: 'last_read',
};

// Theme Colors
export const COLORS = {
  primary: '#3b82f6',
  secondary: '#8b5cf6',
  accent: '#10b981',
  background: '#ffffff',
  backgroundDark: '#1f2937',
  text: '#1f2937',
  textDark: '#f9fafb',
  border: '#e5e7eb',
  borderDark: '#374151',
  error: '#ef4444',
  success: '#10b981',
  warning: '#f59e0b',
  info: '#3b82f6',
};

// Highlight Colors
export const HIGHLIGHT_COLORS = [
  { name: 'Yellow', value: '#fef3c7' },
  { name: 'Green', value: '#d1fae5' },
  { name: 'Blue', value: '#dbeafe' },
  { name: 'Pink', value: '#fce7f3' },
  { name: 'Purple', value: '#e9d5ff' },
  { name: 'Orange', value: '#fed7aa' },
  { name: 'Red', value: '#fee2e2' },
];

// Font Sizes
export const FONT_SIZES = {
  small: 14,
  medium: 16,
  large: 18,
  xlarge: 20,
  xxlarge: 24,
};

// Gradient Backgrounds for Verse Sharing
export const GRADIENT_BACKGROUNDS = [
  { name: 'Sunset', colors: ['#ff6b6b', '#feca57'] },
  { name: 'Ocean', colors: ['#4facfe', '#00f2fe'] },
  { name: 'Purple Dream', colors: ['#a8edea', '#fed6e3'] },
  { name: 'Forest', colors: ['#0f2027', '#203a43', '#2c5364'] },
  { name: 'Fire', colors: ['#f12711', '#f5af19'] },
  { name: 'Sky', colors: ['#2196f3', '#f44336'] },
  { name: 'Mint', colors: ['#00d2ff', '#3a7bd5'] },
  { name: 'Rose', colors: ['#eb3349', '#f45c43'] },
  { name: 'Lavender', colors: ['#834d9b', '#d04ed6'] },
  { name: 'Gold', colors: ['#f7b733', '#fc4a1a'] },
];

// Screen Names
export const SCREENS = {
  // Auth
  LOGIN: 'Login',
  REGISTER: 'Register',
  FORGOT_PASSWORD: 'ForgotPassword',
  
  // Main
  HOME: 'Home',
  BIBLE_READER: 'BibleReader',
  LESSONS: 'Lessons',
  LESSON_DETAIL: 'LessonDetail',
  SETTINGS: 'Settings',
  
  // Features
  PARALLEL_VIEW: 'ParallelView',
  SEARCH: 'Search',
  HIGHLIGHTS: 'Highlights',
  NOTES: 'Notes',
  READING_PLAN: 'ReadingPlan',
  SHARE: 'Share',
  
  // Profile
  PROFILE: 'Profile',
  EDIT_PROFILE: 'EditProfile',
};

// Testaments
export const TESTAMENTS = {
  OLD: 'Old',
  NEW: 'New',
};

// Default Settings
export const DEFAULT_SETTINGS = {
  fontSize: FONT_SIZES.medium,
  theme: 'light',
  autoScrollEnabled: true,
  parallelViewColumns: 2,
};
