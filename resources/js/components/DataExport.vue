<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Download } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const isExporting = ref(false);

const handleExport = () => {
    isExporting.value = true;
    // Direct download via window.location
    window.location.href = '/settings/export-data';
    // Reset after a delay
    setTimeout(() => {
        isExporting.value = false;
    }, 2000);
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall
            :title="t('Export your data')"
            :description="t('Download all your personal data')"
        />
        <div
            class="space-y-4 rounded-lg border border-blue-100 bg-blue-50 p-4 dark:border-blue-200/10 dark:bg-blue-700/10"
        >
            <div class="relative space-y-0.5 text-blue-600 dark:text-blue-100">
                <p class="font-medium">{{ t('Data Export') }}</p>
                <p class="text-sm">
                    {{
                        t(
                            'Download a ZIP file containing all your personal data in JSON format.',
                        )
                    }}
                </p>
            </div>

            <Dialog>
                <DialogTrigger as-child>
                    <Button
                        variant="default"
                        data-test="export-data-button"
                        class="gap-2"
                    >
                        <Download class="h-4 w-4" />
                        {{ t('Export my data') }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader class="space-y-3">
                        <DialogTitle>{{ t('Export your data') }}</DialogTitle>
                        <DialogDescription>
                            {{
                                t(
                                    'This will download a ZIP file containing all your personal data including:',
                                )
                            }}
                        </DialogDescription>
                    </DialogHeader>

                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>{{ t('Profile information') }}</li>
                        <li>{{ t('Notes on Bible verses') }}</li>
                        <li>{{ t('Verse highlights') }}</li>
                        <li>{{ t('Created lessons') }}</li>
                        <li>{{ t('Lesson progress') }}</li>
                        <li>{{ t('Reading progress') }}</li>
                        <li>{{ t('Verse link canvases') }}</li>
                    </ul>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary">
                                {{ t('Cancel') }}
                            </Button>
                        </DialogClose>

                        <Button
                            @click="handleExport"
                            :disabled="isExporting"
                            data-test="confirm-export-button"
                            class="gap-2"
                        >
                            <Download class="h-4 w-4" />
                            {{ isExporting ? t('Exporting...') : t('Export') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
