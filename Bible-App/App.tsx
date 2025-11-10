import React from 'react';
import { StatusBar } from 'expo-status-bar';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { AuthProvider } from './src/contexts/AuthContext';
import { BibleProvider } from './src/contexts/BibleContext';
import AppNavigator from './src/navigation/AppNavigator';

export default function App() {
  return (
    <SafeAreaProvider>
      <AuthProvider>
        <BibleProvider>
          <AppNavigator />
          <StatusBar style="auto" />
        </BibleProvider>
      </AuthProvider>
    </SafeAreaProvider>
  );
}
