<script setup lang="ts">
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

const user = computed(() => page.props.auth?.user);
const appearancePreferences = computed(
    () => user.value?.appearance_preferences || {},
);

const reminderTime = ref(
    appearancePreferences.value.memory_verse_reminder_time || '08:00',
);

const timeOptions = [
    { value: '06:00', label: '6:00 AM' },
    { value: '07:00', label: '7:00 AM' },
    { value: '08:00', label: '8:00 AM' },
    { value: '09:00', label: '9:00 AM' },
    { value: '10:00', label: '10:00 AM' },
    { value: '11:00', label: '11:00 AM' },
    { value: '12:00', label: '12:00 PM' },
    { value: '13:00', label: '1:00 PM' },
    { value: '14:00', label: '2:00 PM' },
    { value: '15:00', label: '3:00 PM' },
    { value: '16:00', label: '4:00 PM' },
    { value: '17:00', label: '5:00 PM' },
    { value: '18:00', label: '6:00 PM' },
    { value: '19:00', label: '7:00 PM' },
    { value: '20:00', label: '8:00 PM' },
    { value: '21:00', label: '9:00 PM' },
];

function updateReminderTime(value: string) {
    reminderTime.value = value;

    const updatedPreferences = {
        ...appearancePreferences.value,
        memory_verse_reminder_time: value,
    };

    router.post(
        '/settings/appearance',
        {
            appearance_preferences: updatedPreferences,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}
</script>

<template>
    <div class="space-y-2">
        <Label for="reminder-time">{{ t('Memory Verse Reminder Time') }}</Label>
        <Select
            :model-value="reminderTime"
            @update:model-value="updateReminderTime"
        >
            <SelectTrigger id="reminder-time">
                <SelectValue :placeholder="t('Select reminder time')" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in timeOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <p class="text-xs text-muted-foreground">
            {{
                t(
                    'Choose when you want to receive daily memory verse reminders',
                )
            }}
        </p>
    </div>
</template>
