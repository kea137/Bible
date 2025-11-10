import { BibleReference } from '../types';

/**
 * Format a Bible verse reference
 * @param book - Book name
 * @param chapter - Chapter number
 * @param verse - Verse number (optional)
 * @returns Formatted reference string (e.g., "John 3:16" or "John 3")
 */
export const formatVerseReference = (
  book: string,
  chapter: number,
  verse?: number
): string => {
  if (verse) {
    return `${book} ${chapter}:${verse}`;
  }
  return `${book} ${chapter}`;
};

/**
 * Parse a Bible verse reference string
 * @param reference - Reference string (e.g., "John 3:16")
 * @returns Parsed reference object
 */
export const parseVerseReference = (reference: string): BibleReference | null => {
  const match = reference.match(/^(\w+(?:\s+\w+)?)\s+(\d+)(?::(\d+))?$/);
  if (!match) return null;

  return {
    book: match[1],
    chapter: parseInt(match[2], 10),
    verse: match[3] ? parseInt(match[3], 10) : undefined,
  };
};

/**
 * Truncate text to specified length
 * @param text - Text to truncate
 * @param maxLength - Maximum length
 * @returns Truncated text with ellipsis if needed
 */
export const truncateText = (text: string, maxLength: number): string => {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength - 3) + '...';
};

/**
 * Format a date string to readable format
 * @param dateString - ISO date string
 * @returns Formatted date (e.g., "Jan 15, 2025")
 */
export const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

/**
 * Format a relative time string
 * @param dateString - ISO date string
 * @returns Relative time (e.g., "2 hours ago")
 */
export const formatRelativeTime = (dateString: string): string => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

  if (diffInSeconds < 60) return 'just now';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
  
  return formatDate(dateString);
};

/**
 * Validate email format
 * @param email - Email string to validate
 * @returns True if valid email format
 */
export const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

/**
 * Generate a random color from highlight colors
 * @param colors - Array of color objects
 * @returns Random color value
 */
export const getRandomColor = (colors: Array<{ name: string; value: string }>): string => {
  const randomIndex = Math.floor(Math.random() * colors.length);
  return colors[randomIndex].value;
};

/**
 * Debounce a function
 * @param func - Function to debounce
 * @param wait - Wait time in milliseconds
 * @returns Debounced function
 */
export const debounce = <T extends (...args: any[]) => any>(
  func: T,
  wait: number
): ((...args: Parameters<T>) => void) => {
  let timeout: NodeJS.Timeout | null = null;

  return (...args: Parameters<T>) => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

/**
 * Convert hex color to RGBA
 * @param hex - Hex color string
 * @param alpha - Alpha value (0-1)
 * @returns RGBA color string
 */
export const hexToRgba = (hex: string, alpha: number = 1): string => {
  const r = parseInt(hex.slice(1, 3), 16);
  const g = parseInt(hex.slice(3, 5), 16);
  const b = parseInt(hex.slice(5, 7), 16);
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

/**
 * Sort an array of objects by a key
 * @param array - Array to sort
 * @param key - Key to sort by
 * @param order - Sort order ('asc' or 'desc')
 * @returns Sorted array
 */
export const sortByKey = <T>(
  array: T[],
  key: keyof T,
  order: 'asc' | 'desc' = 'asc'
): T[] => {
  return [...array].sort((a, b) => {
    if (a[key] < b[key]) return order === 'asc' ? -1 : 1;
    if (a[key] > b[key]) return order === 'asc' ? 1 : -1;
    return 0;
  });
};

/**
 * Group an array of objects by a key
 * @param array - Array to group
 * @param key - Key to group by
 * @returns Object with grouped items
 */
export const groupByKey = <T>(
  array: T[],
  key: keyof T
): Record<string, T[]> => {
  return array.reduce((result, item) => {
    const groupKey = String(item[key]);
    if (!result[groupKey]) {
      result[groupKey] = [];
    }
    result[groupKey].push(item);
    return result;
  }, {} as Record<string, T[]>);
};

/**
 * Calculate reading progress percentage
 * @param totalChapters - Total number of chapters
 * @param readChapters - Number of chapters read
 * @returns Percentage (0-100)
 */
export const calculateProgress = (
  totalChapters: number,
  readChapters: number
): number => {
  if (totalChapters === 0) return 0;
  return Math.round((readChapters / totalChapters) * 100);
};

/**
 * Get initials from a name
 * @param name - Full name
 * @returns Initials (e.g., "John Doe" -> "JD")
 */
export const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};
