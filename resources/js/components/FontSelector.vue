<script setup lang="ts">
import {
    type FontFamily,
    type FontSize,
    useFontPreferences,
} from '@/composables/useFontPreferences';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { fontPreferences, updateFontPreferences } = useFontPreferences();

const fontFamilyOptions: { value: FontFamily; label: string }[] = [
    { value: 'instrument-sans', label: 'Instrument Sans' },
    { value: 'system', label: t('System Default') },
    { value: 'sans-serif', label: t('Sans Serif') },
    { value: 'serif', label: t('Serif') },
    { value: 'monospace', label: t('Monospace') },
    { value: 'arial', label: 'Arial' },
    { value: 'helvetica', label: 'Helvetica' },
    { value: 'times', label: 'Times New Roman' },
    { value: 'georgia', label: 'Georgia' },
    { value: 'courier', label: 'Courier New' },
];

const fontSizeOptions: { value: FontSize; label: string }[] = [
    { value: 'xs', label: t('Extra Small') },
    { value: 'sm', label: t('Small') },
    { value: 'base', label: t('Medium') },
    { value: 'lg', label: t('Large') },
    { value: 'xl', label: t('Extra Large') },
];
</script>

<template>
    <div class="space-y-4">
        <div>
            <label
                for="font-family"
                class="mb-2 block text-sm font-medium text-foreground"
            >
                {{ t('Font Family') }}
            </label>
            <select
                id="font-family"
                :value="fontPreferences.fontFamily"
                @change="
                    updateFontPreferences({
                        fontFamily: ($event.target as HTMLSelectElement)
                            .value as FontFamily,
                    })
                "
                class="block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                <option
                    v-for="option in fontFamilyOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>

        <div>
            <label
                for="font-size"
                class="mb-2 block text-sm font-medium text-foreground"
            >
                {{ t('Font Size') }}
            </label>
            <select
                id="font-size"
                :value="fontPreferences.fontSize"
                @change="
                    updateFontPreferences({
                        fontSize: ($event.target as HTMLSelectElement)
                            .value as FontSize,
                    })
                "
                class="block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                <option
                    v-for="option in fontSizeOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>
    </div>
</template>
