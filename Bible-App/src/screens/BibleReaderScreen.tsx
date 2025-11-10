import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { useBible } from '../contexts/BibleContext';
import { COLORS, HIGHLIGHT_COLORS } from '../constants';
import { Verse } from '../types';

const BibleReaderScreen: React.FC<{ navigation: any; route: any }> = ({
  navigation,
  route,
}) => {
  const {
    selectedBible,
    verses,
    highlights,
    notes,
    isLoading,
    loadChapter,
    addHighlight,
    addNote,
  } = useBible();

  const [selectedVerse, setSelectedVerse] = useState<Verse | null>(null);
  const [showActions, setShowActions] = useState(false);

  useEffect(() => {
    if (route.params?.book && route.params?.chapter) {
      loadChapter(route.params.book, route.params.chapter);
    } else if (selectedBible) {
      // Load Genesis 1 by default
      loadChapter('Genesis', 1);
    }
  }, [route.params, selectedBible]);

  const getVerseHighlight = (verse: Verse) => {
    return highlights.find(
      h =>
        h.book === verse.book &&
        h.chapter === verse.chapter &&
        h.verse === verse.verse
    );
  };

  const getVerseNotes = (verse: Verse) => {
    return notes.filter(
      n =>
        n.book === verse.book &&
        n.chapter === verse.chapter &&
        n.verse === verse.verse
    );
  };

  const handleVerseLongPress = (verse: Verse) => {
    setSelectedVerse(verse);
    setShowActions(true);
  };

  const handleHighlight = async (color: string) => {
    if (selectedVerse) {
      try {
        await addHighlight(selectedVerse, color);
        setShowActions(false);
        setSelectedVerse(null);
      } catch (error) {
        Alert.alert('Error', 'Failed to add highlight');
      }
    }
  };

  const handleAddNote = () => {
    if (selectedVerse) {
      Alert.prompt(
        'Add Note',
        'Enter your note for this verse:',
        async (text) => {
          if (text) {
            try {
              await addNote(selectedVerse, text);
              setShowActions(false);
              setSelectedVerse(null);
            } catch (error) {
              Alert.alert('Error', 'Failed to add note');
            }
          }
        }
      );
    }
  };

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.bibleTitle}>{selectedBible?.name || 'Bible'}</Text>
        <Text style={styles.chapterTitle}>
          {verses[0]?.book || 'Book'} {verses[0]?.chapter || '1'}
        </Text>
      </View>

      <ScrollView style={styles.content}>
        {verses.map((verse) => {
          const highlight = getVerseHighlight(verse);
          const verseNotes = getVerseNotes(verse);

          return (
            <TouchableOpacity
              key={verse.id}
              style={[
                styles.verseContainer,
                highlight && { backgroundColor: highlight.color },
              ]}
              onLongPress={() => handleVerseLongPress(verse)}
            >
              <Text style={styles.verseNumber}>{verse.verse}</Text>
              <Text style={styles.verseText}>{verse.text}</Text>
              {verseNotes.length > 0 && (
                <View style={styles.noteBadge}>
                  <Text style={styles.noteBadgeText}>📝 {verseNotes.length}</Text>
                </View>
              )}
            </TouchableOpacity>
          );
        })}
      </ScrollView>

      {showActions && selectedVerse && (
        <View style={styles.actionsModal}>
          <View style={styles.actionsContent}>
            <Text style={styles.actionsTitle}>
              {selectedVerse.book} {selectedVerse.chapter}:{selectedVerse.verse}
            </Text>

            <Text style={styles.actionsSubtitle}>Highlight Color</Text>
            <View style={styles.colorPicker}>
              {HIGHLIGHT_COLORS.map((color) => (
                <TouchableOpacity
                  key={color.name}
                  style={[
                    styles.colorOption,
                    { backgroundColor: color.value },
                  ]}
                  onPress={() => handleHighlight(color.value)}
                />
              ))}
            </View>

            <TouchableOpacity
              style={styles.actionButton}
              onPress={handleAddNote}
            >
              <Text style={styles.actionButtonText}>Add Note</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.cancelButton}
              onPress={() => {
                setShowActions(false);
                setSelectedVerse(null);
              }}
            >
              <Text style={styles.cancelButtonText}>Cancel</Text>
            </TouchableOpacity>
          </View>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    padding: 20,
    paddingTop: 60,
    backgroundColor: COLORS.primary,
  },
  bibleTitle: {
    fontSize: 16,
    color: '#ffffff',
    marginBottom: 5,
  },
  chapterTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  content: {
    flex: 1,
    padding: 20,
  },
  verseContainer: {
    flexDirection: 'row',
    marginBottom: 15,
    padding: 10,
    borderRadius: 8,
  },
  verseNumber: {
    fontSize: 14,
    fontWeight: 'bold',
    color: COLORS.primary,
    marginRight: 10,
    minWidth: 25,
  },
  verseText: {
    flex: 1,
    fontSize: 16,
    lineHeight: 24,
    color: COLORS.text,
  },
  noteBadge: {
    marginLeft: 10,
  },
  noteBadgeText: {
    fontSize: 12,
  },
  actionsModal: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  actionsContent: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 20,
    width: '85%',
  },
  actionsTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: COLORS.text,
    marginBottom: 15,
  },
  actionsSubtitle: {
    fontSize: 14,
    color: '#6b7280',
    marginBottom: 10,
  },
  colorPicker: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 20,
  },
  colorOption: {
    width: 40,
    height: 40,
    borderRadius: 20,
    margin: 5,
    borderWidth: 2,
    borderColor: '#e5e7eb',
  },
  actionButton: {
    backgroundColor: COLORS.primary,
    borderRadius: 8,
    padding: 15,
    alignItems: 'center',
    marginBottom: 10,
  },
  actionButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  cancelButton: {
    borderRadius: 8,
    padding: 15,
    alignItems: 'center',
  },
  cancelButtonText: {
    color: COLORS.text,
    fontSize: 16,
  },
});

export default BibleReaderScreen;
