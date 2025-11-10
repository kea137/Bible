import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
} from 'react-native';
import { COLORS } from '../../constants';
import { Verse, Highlight, Note } from '../../types';

interface VerseCardProps {
  verse: Verse;
  highlight?: Highlight;
  notes?: Note[];
  onLongPress?: (verse: Verse) => void;
  onPress?: (verse: Verse) => void;
  showBook?: boolean;
}

const VerseCard: React.FC<VerseCardProps> = ({
  verse,
  highlight,
  notes = [],
  onLongPress,
  onPress,
  showBook = false,
}) => {
  const handlePress = () => {
    if (onPress) {
      onPress(verse);
    }
  };

  const handleLongPress = () => {
    if (onLongPress) {
      onLongPress(verse);
    }
  };

  return (
    <TouchableOpacity
      style={[
        styles.container,
        highlight && { backgroundColor: highlight.color },
      ]}
      onPress={handlePress}
      onLongPress={handleLongPress}
      delayLongPress={300}
    >
      <View style={styles.content}>
        <Text style={styles.verseNumber}>{verse.verse}</Text>
        <View style={styles.textContainer}>
          {showBook && (
            <Text style={styles.bookReference}>
              {verse.book} {verse.chapter}:{verse.verse}
            </Text>
          )}
          <Text style={styles.verseText}>{verse.text}</Text>
        </View>
      </View>
      {notes.length > 0 && (
        <View style={styles.noteBadge}>
          <Text style={styles.noteBadgeText}>📝 {notes.length}</Text>
        </View>
      )}
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  container: {
    marginBottom: 12,
    padding: 12,
    borderRadius: 8,
  },
  content: {
    flexDirection: 'row',
  },
  verseNumber: {
    fontSize: 14,
    fontWeight: 'bold',
    color: COLORS.primary,
    marginRight: 12,
    minWidth: 25,
    paddingTop: 2,
  },
  textContainer: {
    flex: 1,
  },
  bookReference: {
    fontSize: 12,
    color: COLORS.primary,
    fontWeight: '600',
    marginBottom: 4,
  },
  verseText: {
    fontSize: 16,
    lineHeight: 24,
    color: COLORS.text,
  },
  noteBadge: {
    marginTop: 8,
    alignSelf: 'flex-start',
    backgroundColor: '#ffffff',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  noteBadgeText: {
    fontSize: 12,
    color: COLORS.text,
  },
});

export default VerseCard;
