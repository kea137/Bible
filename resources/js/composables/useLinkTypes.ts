/**
 * Link types configuration for Verse Link Canvas
 */
export const linkTypes = [
    { value: 'general', label: 'General', color: '#3b82f6' },
    { value: 'support', label: 'Support', color: '#10b981' },
    { value: 'parallel', label: 'Parallel', color: '#8b5cf6' },
    { value: 'prophecy', label: 'Prophecy', color: '#f59e0b' },
    { value: 'typology', label: 'Typology', color: '#ec4899' },
    { value: 'contrast', label: 'Contrast', color: '#ef4444' },
    { value: 'cause-effect', label: 'Cause-Effect', color: '#06b6d4' },
] as const;

/**
 * Get the color for a specific link type
 */
export function getLinkTypeColor(linkType: string): string {
    const type = linkTypes.find((t) => t.value === linkType);
    return type?.color || '#3b82f6';
}

/**
 * Get link type label by value
 */
export function getLinkTypeLabel(linkType: string): string {
    const type = linkTypes.find((t) => t.value === linkType);
    return type?.label || 'General';
}
