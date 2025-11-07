<script setup lang="ts">
import { Button } from '@/components/ui/button';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { router, useForm, Head } from '@inertiajs/vue3';
import { BookOpen, Check, Languages, Palette } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps<{
    bibles: Record<string, Array<{
        id: number;
        name: string;
        abbreviation: string;
        language: string;
        version: string;
    }>>;
    currentLanguage: string;
}>();

const currentStep = ref(1);
const totalSteps = 3;

const form = useForm({
    language: props.currentLanguage || 'en',
    preferred_translations: [] as number[],
    appearance_preferences: {
        theme: 'system' as 'light' | 'dark' | 'system',
    },
});

const languages = [
    { value: 'en', label: 'English' },
    { value: 'sw', label: 'Swahili' },
    { value: 'fr', label: 'Français' },
    { value: 'es', label: 'Español' },
    { value: 'de', label: 'Deutsch' },
    { value: 'it', label: 'Italiano' },
    { value: 'ru', label: 'Русский' },
    { value: 'zh', label: '中文' },
    { value: 'ja', label: '日本語' },
    { value: 'ar', label: 'العربية' },
    { value: 'hi', label: 'हिन्दी' },
    { value: 'ko', label: '한국어' },
] as const;

const themes = [
    { value: 'light', label: t('Light'), icon: '☀️' },
    { value: 'dark', label: t('Dark'), icon: '🌙' },
    { value: 'system', label: t('System'), icon: '💻' },
] as const;

// Get bibles for selected language
const biblesForSelectedLanguage = computed(() => {
    const languageMap: Record<string, string> = {
        'en': 'English',
        'sw': 'Swahili',
        'fr': 'French',
        'es': 'Spanish',
        'de': 'German',
        'it': 'Italian',
        'ru': 'Russian',
        'zh': 'Chinese',
        'ja': 'Japanese',
        'ar': 'Arabic',
        'hi': 'Hindi',
        'ko': 'Korean',
    };
    
    const languageName = languageMap[form.language] || 'English';
    return props.bibles[languageName] || [];
});

const canProceed = computed(() => {
    if (currentStep.value === 1) return true;
    if (currentStep.value === 2) return form.preferred_translations.length > 0;
    if (currentStep.value === 3) return true;
    return false;
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canProceed.value) {
        currentStep.value++;
    }
};

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const toggleTranslation = (id: number) => {
    const index = form.preferred_translations.indexOf(id);
    if (index > -1) {
        form.preferred_translations.splice(index, 1);
    } else {
        form.preferred_translations.push(id);
    }
};

const completeOnboarding = () => {
    form.post(route('onboarding.store'), {
        preserveScroll: true,
    });
};

const skipOnboarding = () => {
    router.post(route('onboarding.skip'));
};

const selectLanguage = (lang: string) => {
    form.language = lang;
    // Update locale immediately for better UX
    locale.value = lang;
};

const selectTheme = (theme: 'light' | 'dark' | 'system') => {
    form.appearance_preferences.theme = theme;
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 p-4">
        <Head :title="t('Welcome! Let\'s get you started')" />
        
        <Card class="w-full max-w-2xl shadow-xl">
            <CardHeader class="text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                    <BookOpen class="h-8 w-8 text-primary" />
                </div>
                <CardTitle class="text-2xl">
                    {{ t('Welcome! Let\'s get you started') }}
                </CardTitle>
                <CardDescription>
                    {{ t('Let\'s personalize your Bible experience') }}
                </CardDescription>
                <div class="mt-4 text-sm text-muted-foreground">
                    {{ t('Step') }} {{ currentStep }} {{ t('of') }} {{ totalSteps }}
                </div>
            </CardHeader>

            <CardContent class="space-y-6">
                <!-- Step 1: Language Selection -->
                <div v-if="currentStep === 1" class="space-y-4">
                    <div class="flex items-center gap-2 text-primary">
                        <Languages class="h-5 w-5" />
                        <h3 class="text-lg font-semibold">
                            {{ t('Choose your preferred language') }}
                        </h3>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('Select the language you want to use for the app interface') }}
                    </p>
                    
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <button
                            v-for="lang in languages"
                            :key="lang.value"
                            @click="selectLanguage(lang.value)"
                            :class="[
                                'flex items-center justify-center rounded-lg border-2 p-4 transition-all hover:border-primary hover:bg-primary/5',
                                form.language === lang.value
                                    ? 'border-primary bg-primary/10 font-semibold'
                                    : 'border-gray-200 dark:border-gray-700',
                            ]"
                        >
                            <span>{{ lang.label }}</span>
                            <Check
                                v-if="form.language === lang.value"
                                class="ml-2 h-4 w-4 text-primary"
                            />
                        </button>
                    </div>
                </div>

                <!-- Step 2: Bible Translations Selection -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <div class="flex items-center gap-2 text-primary">
                        <BookOpen class="h-5 w-5" />
                        <h3 class="text-lg font-semibold">
                            {{ t('Select Your Preferred Bible Translations') }}
                        </h3>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('Choose the Bible translations you\'d like to use for parallel reading and study') }}
                    </p>
                    <p class="text-xs text-muted-foreground italic">
                        {{ t('You can select multiple translations') }}
                    </p>

                    <ScrollArea class="h-[300px] rounded-md border p-4">
                        <div v-if="biblesForSelectedLanguage.length > 0" class="space-y-3">
                            <div
                                v-for="bible in biblesForSelectedLanguage"
                                :key="bible.id"
                                class="flex items-start space-x-3 rounded-lg border p-3 hover:bg-accent transition-colors"
                            >
                                <Checkbox
                                    :id="`bible-${bible.id}`"
                                    :checked="form.preferred_translations.includes(bible.id)"
                                    @update:checked="() => toggleTranslation(bible.id)"
                                />
                                <div class="flex-1">
                                    <Label
                                        :for="`bible-${bible.id}`"
                                        class="cursor-pointer font-medium"
                                    >
                                        {{ bible.name }}
                                    </Label>
                                    <p class="text-xs text-muted-foreground">
                                        {{ bible.abbreviation }} - {{ bible.version }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex h-full items-center justify-center text-muted-foreground">
                            {{ t('No translations available') }}
                        </div>
                    </ScrollArea>

                    <div v-if="!canProceed" class="text-sm text-destructive">
                        {{ t('Select at least one Bible translation') }}
                    </div>
                </div>

                <!-- Step 3: Appearance Preferences -->
                <div v-if="currentStep === 3" class="space-y-4">
                    <div class="flex items-center gap-2 text-primary">
                        <Palette class="h-5 w-5" />
                        <h3 class="text-lg font-semibold">
                            {{ t('Customize Your Reading Experience') }}
                        </h3>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ t('Choose your preferred theme for reading') }}
                    </p>

                    <div class="grid grid-cols-3 gap-4">
                        <button
                            v-for="theme in themes"
                            :key="theme.value"
                            @click="selectTheme(theme.value)"
                            :class="[
                                'flex flex-col items-center justify-center rounded-lg border-2 p-6 transition-all hover:border-primary hover:bg-primary/5',
                                form.appearance_preferences.theme === theme.value
                                    ? 'border-primary bg-primary/10 font-semibold'
                                    : 'border-gray-200 dark:border-gray-700',
                            ]"
                        >
                            <span class="text-3xl mb-2">{{ theme.icon }}</span>
                            <span>{{ theme.label }}</span>
                            <Check
                                v-if="form.appearance_preferences.theme === theme.value"
                                class="mt-2 h-4 w-4 text-primary"
                            />
                        </button>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-between gap-4 pt-6 border-t">
                    <Button
                        v-if="currentStep > 1"
                        variant="outline"
                        @click="previousStep"
                    >
                        {{ t('Previous') }}
                    </Button>
                    <Button
                        v-else
                        variant="ghost"
                        @click="skipOnboarding"
                    >
                        {{ t('Skip for now') }}
                    </Button>

                    <Button
                        v-if="currentStep < totalSteps"
                        @click="nextStep"
                        :disabled="!canProceed"
                    >
                        {{ t('Next') }}
                    </Button>
                    <Button
                        v-else
                        @click="completeOnboarding"
                        :disabled="form.processing || !canProceed"
                    >
                        {{ t('Complete Setup') }}
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
