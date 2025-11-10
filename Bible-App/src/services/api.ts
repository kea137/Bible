import axios, { AxiosInstance, AxiosResponse } from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL, API_ENDPOINTS, STORAGE_KEYS } from '../constants';
import {
  ApiResponse,
  AuthCredentials,
  Bible,
  Verse,
  Lesson,
  Highlight,
  Note,
  User,
  RegisterData,
  BibleReference,
  PaginatedResponse,
} from '../types';

class ApiService {
  private api: AxiosInstance;

  constructor() {
    this.api = axios.create({
      baseURL: API_BASE_URL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      timeout: 10000,
    });

    // Add request interceptor to include auth token
    this.api.interceptors.request.use(
      async (config) => {
        const token = await AsyncStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Add response interceptor for error handling
    this.api.interceptors.response.use(
      (response) => response,
      async (error) => {
        if (error.response?.status === 401) {
          // Clear auth data on unauthorized
          await AsyncStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN);
          await AsyncStorage.removeItem(STORAGE_KEYS.USER_DATA);
        }
        return Promise.reject(error);
      }
    );
  }

  // Auth Methods
  async login(credentials: AuthCredentials): Promise<ApiResponse<{ user: User; token: string }>> {
    const response = await this.api.post(API_ENDPOINTS.LOGIN, credentials);
    const { token, user } = response.data;
    await AsyncStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, token);
    await AsyncStorage.setItem(STORAGE_KEYS.USER_DATA, JSON.stringify(user));
    return response.data;
  }

  async register(data: RegisterData): Promise<ApiResponse<{ user: User; token: string }>> {
    const response = await this.api.post(API_ENDPOINTS.REGISTER, data);
    const { token, user } = response.data;
    await AsyncStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, token);
    await AsyncStorage.setItem(STORAGE_KEYS.USER_DATA, JSON.stringify(user));
    return response.data;
  }

  async logout(): Promise<void> {
    try {
      await this.api.post(API_ENDPOINTS.LOGOUT);
    } finally {
      await AsyncStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN);
      await AsyncStorage.removeItem(STORAGE_KEYS.USER_DATA);
    }
  }

  async getCurrentUser(): Promise<User> {
    const response = await this.api.get<User>(API_ENDPOINTS.USER);
    return response.data;
  }

  // Bible Methods
  async getBibles(): Promise<Bible[]> {
    const response = await this.api.get<Bible[]>(API_ENDPOINTS.BIBLES);
    return response.data;
  }

  async getBooks(bibleId: number): Promise<any[]> {
    const response = await this.api.get(`${API_ENDPOINTS.BOOKS}?bible_id=${bibleId}`);
    return response.data;
  }

  async getVerse(
    language: string,
    version: string,
    book: string,
    chapter: number,
    verse?: number,
    includeReferences: boolean = false
  ): Promise<Verse | Verse[]> {
    const endpoint = API_ENDPOINTS.PUBLIC_VERSE
      .replace(':language', language)
      .replace(':version', version)
      .replace(':references', includeReferences.toString())
      .replace(':book', book)
      .replace(':chapter', chapter.toString())
      .replace(':verse?', verse ? verse.toString() : '');
    
    const response = await this.api.get(endpoint);
    return response.data;
  }

  async getChapterVerses(
    bibleId: number,
    book: string,
    chapter: number
  ): Promise<Verse[]> {
    const response = await this.api.get<Verse[]>(
      `${API_ENDPOINTS.VERSES}?bible_id=${bibleId}&book=${book}&chapter=${chapter}`
    );
    return response.data;
  }

  // Highlights Methods
  async getHighlights(bibleId?: number): Promise<Highlight[]> {
    const url = bibleId 
      ? `${API_ENDPOINTS.HIGHLIGHTS}?bible_id=${bibleId}`
      : API_ENDPOINTS.HIGHLIGHTS;
    const response = await this.api.get<Highlight[]>(url);
    return response.data;
  }

  async createHighlight(highlight: Omit<Highlight, 'id' | 'created_at'>): Promise<Highlight> {
    const response = await this.api.post<Highlight>(API_ENDPOINTS.HIGHLIGHTS, highlight);
    return response.data;
  }

  async deleteHighlight(id: number): Promise<void> {
    await this.api.delete(`${API_ENDPOINTS.HIGHLIGHTS}/${id}`);
  }

  // Notes Methods
  async getNotes(bibleId?: number): Promise<Note[]> {
    const url = bibleId 
      ? `${API_ENDPOINTS.NOTES}?bible_id=${bibleId}`
      : API_ENDPOINTS.NOTES;
    const response = await this.api.get<Note[]>(url);
    return response.data;
  }

  async createNote(note: Omit<Note, 'id' | 'created_at' | 'updated_at'>): Promise<Note> {
    const response = await this.api.post<Note>(API_ENDPOINTS.NOTES, note);
    return response.data;
  }

  async updateNote(id: number, content: string): Promise<Note> {
    const response = await this.api.put<Note>(`${API_ENDPOINTS.NOTES}/${id}`, { content });
    return response.data;
  }

  async deleteNote(id: number): Promise<void> {
    await this.api.delete(`${API_ENDPOINTS.NOTES}/${id}`);
  }

  // Lessons Methods
  async getLessons(page: number = 1): Promise<PaginatedResponse<Lesson>> {
    const response = await this.api.get<PaginatedResponse<Lesson>>(
      `${API_ENDPOINTS.LESSONS}?page=${page}`
    );
    return response.data;
  }

  async getLesson(id: number): Promise<Lesson> {
    const response = await this.api.get<Lesson>(`${API_ENDPOINTS.LESSONS}/${id}`);
    return response.data;
  }

  async createLesson(lesson: Partial<Lesson>): Promise<Lesson> {
    const response = await this.api.post<Lesson>(API_ENDPOINTS.LESSONS, lesson);
    return response.data;
  }

  async updateLessonProgress(lessonId: number, progress: number): Promise<void> {
    await this.api.post(API_ENDPOINTS.LESSON_PROGRESS, {
      lesson_id: lessonId,
      progress_percentage: progress,
    });
  }

  // Reading Plan Methods
  async markChapterAsRead(bibleId: number, book: string, chapter: number): Promise<void> {
    await this.api.post(API_ENDPOINTS.READING_PLAN, {
      bible_id: bibleId,
      book,
      chapter,
    });
  }

  async getReadingPlan(bibleId: number): Promise<any[]> {
    const response = await this.api.get(`${API_ENDPOINTS.READING_PLAN}?bible_id=${bibleId}`);
    return response.data;
  }

  // Cross References
  async getCrossReferences(
    book: string,
    chapter: number,
    verse: number
  ): Promise<any[]> {
    const response = await this.api.get(
      `${API_ENDPOINTS.REFERENCES}?book=${book}&chapter=${chapter}&verse=${verse}`
    );
    return response.data;
  }
}

export default new ApiService();
