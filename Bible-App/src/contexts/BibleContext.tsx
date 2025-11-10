import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Bible, Verse, Highlight, Note } from '../types';
import { STORAGE_KEYS } from '../constants';
import apiService from '../services/api';

interface BibleContextType {
  bibles: Bible[];
  selectedBible: Bible | null;
  currentBook: string | null;
  currentChapter: number | null;
  verses: Verse[];
  highlights: Highlight[];
  notes: Note[];
  isLoading: boolean;
  selectBible: (bible: Bible) => Promise<void>;
  loadChapter: (book: string, chapter: number) => Promise<void>;
  addHighlight: (verse: Verse, color: string) => Promise<void>;
  removeHighlight: (highlightId: number) => Promise<void>;
  addNote: (verse: Verse, content: string) => Promise<void>;
  updateNote: (noteId: number, content: string) => Promise<void>;
  removeNote: (noteId: number) => Promise<void>;
  refreshBibles: () => Promise<void>;
}

const BibleContext = createContext<BibleContextType | undefined>(undefined);

interface BibleProviderProps {
  children: ReactNode;
}

export const BibleProvider: React.FC<BibleProviderProps> = ({ children }) => {
  const [bibles, setBibles] = useState<Bible[]>([]);
  const [selectedBible, setSelectedBible] = useState<Bible | null>(null);
  const [currentBook, setCurrentBook] = useState<string | null>(null);
  const [currentChapter, setCurrentChapter] = useState<number | null>(null);
  const [verses, setVerses] = useState<Verse[]>([]);
  const [highlights, setHighlights] = useState<Highlight[]>([]);
  const [notes, setNotes] = useState<Note[]>([]);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    initializeBibles();
  }, []);

  useEffect(() => {
    if (selectedBible) {
      loadHighlightsAndNotes();
    }
  }, [selectedBible]);

  const initializeBibles = async () => {
    try {
      setIsLoading(true);
      const biblesData = await apiService.getBibles();
      setBibles(biblesData);

      // Load last selected bible
      const storedBibleId = await AsyncStorage.getItem(STORAGE_KEYS.SELECTED_BIBLE);
      if (storedBibleId) {
        const bible = biblesData.find(b => b.id === parseInt(storedBibleId));
        if (bible) {
          setSelectedBible(bible);
        }
      } else if (biblesData.length > 0) {
        setSelectedBible(biblesData[0]);
      }

      // Load last read position
      const lastRead = await AsyncStorage.getItem(STORAGE_KEYS.LAST_READ);
      if (lastRead) {
        const { book, chapter } = JSON.parse(lastRead);
        setCurrentBook(book);
        setCurrentChapter(chapter);
      }
    } catch (error) {
      console.error('Error initializing bibles:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const selectBible = async (bible: Bible) => {
    setSelectedBible(bible);
    await AsyncStorage.setItem(STORAGE_KEYS.SELECTED_BIBLE, bible.id.toString());
    await loadHighlightsAndNotes();
  };

  const loadChapter = async (book: string, chapter: number) => {
    if (!selectedBible) return;

    try {
      setIsLoading(true);
      const versesData = await apiService.getChapterVerses(
        selectedBible.id,
        book,
        chapter
      );
      setVerses(versesData);
      setCurrentBook(book);
      setCurrentChapter(chapter);

      // Save last read position
      await AsyncStorage.setItem(
        STORAGE_KEYS.LAST_READ,
        JSON.stringify({ book, chapter })
      );
    } catch (error) {
      console.error('Error loading chapter:', error);
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  const loadHighlightsAndNotes = async () => {
    if (!selectedBible) return;

    try {
      const [highlightsData, notesData] = await Promise.all([
        apiService.getHighlights(selectedBible.id),
        apiService.getNotes(selectedBible.id),
      ]);
      setHighlights(highlightsData);
      setNotes(notesData);
    } catch (error) {
      console.error('Error loading highlights and notes:', error);
    }
  };

  const addHighlight = async (verse: Verse, color: string) => {
    if (!selectedBible) return;

    try {
      const highlight = await apiService.createHighlight({
        user_id: 0, // Will be set by backend
        bible_id: selectedBible.id,
        book: verse.book,
        chapter: verse.chapter,
        verse: verse.verse,
        color,
      });
      setHighlights([...highlights, highlight]);
    } catch (error) {
      console.error('Error adding highlight:', error);
      throw error;
    }
  };

  const removeHighlight = async (highlightId: number) => {
    try {
      await apiService.deleteHighlight(highlightId);
      setHighlights(highlights.filter(h => h.id !== highlightId));
    } catch (error) {
      console.error('Error removing highlight:', error);
      throw error;
    }
  };

  const addNote = async (verse: Verse, content: string) => {
    if (!selectedBible) return;

    try {
      const note = await apiService.createNote({
        user_id: 0, // Will be set by backend
        bible_id: selectedBible.id,
        book: verse.book,
        chapter: verse.chapter,
        verse: verse.verse,
        content,
      });
      setNotes([...notes, note]);
    } catch (error) {
      console.error('Error adding note:', error);
      throw error;
    }
  };

  const updateNote = async (noteId: number, content: string) => {
    try {
      const updatedNote = await apiService.updateNote(noteId, content);
      setNotes(notes.map(n => (n.id === noteId ? updatedNote : n)));
    } catch (error) {
      console.error('Error updating note:', error);
      throw error;
    }
  };

  const removeNote = async (noteId: number) => {
    try {
      await apiService.deleteNote(noteId);
      setNotes(notes.filter(n => n.id !== noteId));
    } catch (error) {
      console.error('Error removing note:', error);
      throw error;
    }
  };

  const refreshBibles = async () => {
    await initializeBibles();
  };

  const value: BibleContextType = {
    bibles,
    selectedBible,
    currentBook,
    currentChapter,
    verses,
    highlights,
    notes,
    isLoading,
    selectBible,
    loadChapter,
    addHighlight,
    removeHighlight,
    addNote,
    updateNote,
    removeNote,
    refreshBibles,
  };

  return <BibleContext.Provider value={value}>{children}</BibleContext.Provider>;
};

export const useBible = (): BibleContextType => {
  const context = useContext(BibleContext);
  if (!context) {
    throw new Error('useBible must be used within a BibleProvider');
  }
  return context;
};
