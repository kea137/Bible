// Core Types for Bible App

export interface Bible {
  id: number;
  name: string;
  abbreviation: string;
  language: string;
  type: string;
  direction: 'ltr' | 'rtl';
  books?: Book[];
}

export interface Book {
  id: number;
  name: string;
  abbreviation: string;
  order: number;
  chapters: number;
  testament: 'Old' | 'New';
}

export interface Verse {
  id: number;
  book: string;
  chapter: number;
  verse: number;
  text: string;
  bible_id: number;
  bible_name?: string;
  bible_abbreviation?: string;
}

export interface CrossReference {
  id: number;
  from_book: string;
  from_chapter: number;
  from_verse: number;
  to_book: string;
  to_chapter: number;
  to_verse: number;
}

export interface Highlight {
  id: number;
  user_id: number;
  bible_id: number;
  book: string;
  chapter: number;
  verse: number;
  color: string;
  created_at: string;
}

export interface Note {
  id: number;
  user_id: number;
  bible_id: number;
  book: string;
  chapter: number;
  verse: number;
  content: string;
  created_at: string;
  updated_at: string;
}

export interface Lesson {
  id: number;
  title: string;
  description: string;
  scripture_reference: string;
  content: string;
  category?: string;
  is_series: boolean;
  series_order?: number;
  parent_lesson_id?: number;
  created_by: number;
  created_at: string;
  updated_at: string;
}

export interface LessonProgress {
  id: number;
  user_id: number;
  lesson_id: number;
  completed: boolean;
  progress_percentage: number;
  last_accessed: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  two_factor_enabled: boolean;
  profile_photo_url?: string;
  created_at: string;
}

export interface ReadingPlan {
  id: number;
  user_id: number;
  bible_id: number;
  book: string;
  chapter: number;
  read_at: string;
}

export interface ShareOptions {
  verse: Verse;
  backgroundType: 'gradient' | 'photo';
  backgroundValue: string;
  fontSize: number;
  fontFamily: string;
  textColor: string;
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
  status: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface AuthCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterData extends AuthCredentials {
  name: string;
  password_confirmation: string;
}

export interface BibleReference {
  book: string;
  chapter: number;
  verse?: number;
}
