import React, { useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { useBible } from '../contexts/BibleContext';
import { useAuth } from '../contexts/AuthContext';
import { COLORS } from '../constants';

const HomeScreen: React.FC<{ navigation: any }> = ({ navigation }) => {
  const { user } = useAuth();
  const { selectedBible, currentBook, currentChapter, isLoading } = useBible();

  const continueReading = () => {
    if (currentBook && currentChapter) {
      navigation.navigate('BibleReader', {
        book: currentBook,
        chapter: currentChapter,
      });
    }
  };

  return (
    <View style={styles.container}>
      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.header}>
          <Text style={styles.greeting}>Hello, {user?.name || 'Reader'}!</Text>
          <Text style={styles.subtitle}>Welcome to Bible App</Text>
        </View>

        {isLoading ? (
          <ActivityIndicator size="large" color={COLORS.primary} />
        ) : (
          <>
            {selectedBible && (
              <View style={styles.card}>
                <Text style={styles.cardTitle}>Current Bible</Text>
                <Text style={styles.bibleName}>{selectedBible.name}</Text>
                <Text style={styles.bibleInfo}>
                  {selectedBible.language} - {selectedBible.abbreviation}
                </Text>
              </View>
            )}

            {currentBook && currentChapter && (
              <TouchableOpacity
                style={styles.continueButton}
                onPress={continueReading}
              >
                <Text style={styles.continueButtonText}>Continue Reading</Text>
                <Text style={styles.continueReference}>
                  {currentBook} {currentChapter}
                </Text>
              </TouchableOpacity>
            )}

            <View style={styles.quickActions}>
              <Text style={styles.sectionTitle}>Quick Actions</Text>

              <TouchableOpacity
                style={styles.actionCard}
                onPress={() => navigation.navigate('BibleReader')}
              >
                <Text style={styles.actionTitle}>📖 Read Bible</Text>
                <Text style={styles.actionDescription}>
                  Browse and read from various translations
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.actionCard}
                onPress={() => navigation.navigate('Lessons')}
              >
                <Text style={styles.actionTitle}>📚 Study Lessons</Text>
                <Text style={styles.actionDescription}>
                  Explore Bible study lessons and series
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.actionCard}
                onPress={() => navigation.navigate('Settings')}
              >
                <Text style={styles.actionTitle}>⚙️ Settings</Text>
                <Text style={styles.actionDescription}>
                  Customize your reading experience
                </Text>
              </TouchableOpacity>
            </View>
          </>
        )}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  content: {
    padding: 20,
  },
  header: {
    marginBottom: 30,
    marginTop: 20,
  },
  greeting: {
    fontSize: 28,
    fontWeight: 'bold',
    color: COLORS.text,
    marginBottom: 5,
  },
  subtitle: {
    fontSize: 16,
    color: '#6b7280',
  },
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 20,
    marginBottom: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  cardTitle: {
    fontSize: 14,
    color: '#6b7280',
    marginBottom: 10,
  },
  bibleName: {
    fontSize: 20,
    fontWeight: 'bold',
    color: COLORS.text,
    marginBottom: 5,
  },
  bibleInfo: {
    fontSize: 14,
    color: '#6b7280',
  },
  continueButton: {
    backgroundColor: COLORS.primary,
    borderRadius: 12,
    padding: 20,
    marginBottom: 30,
    alignItems: 'center',
  },
  continueButtonText: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 5,
  },
  continueReference: {
    color: '#ffffff',
    fontSize: 14,
    opacity: 0.9,
  },
  quickActions: {
    marginTop: 10,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: COLORS.text,
    marginBottom: 15,
  },
  actionCard: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 20,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  actionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 5,
  },
  actionDescription: {
    fontSize: 14,
    color: '#6b7280',
  },
});

export default HomeScreen;
