<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { license } from '@/routes';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'License',
        href: license().url,
    },
];
</script>

<template>
    <Head :title="t('License')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
            <div class="rounded-xl bg-card p-6 shadow-sm">
                <h1 class="mb-6 text-3xl font-semibold text-foreground">
                    License
                </h1>

                <div class="prose prose-sm dark:prose-invert max-w-none">
                    <h2 class="text-xl font-semibold text-foreground">
                        MIT License
                    </h2>

                    <p class="mt-4 text-muted-foreground">
                        Copyright (c) 2025 Kea Rajabu Baruan
                    </p>

                    <p class="mt-4 text-sm leading-relaxed text-foreground">
                        Permission is hereby granted, free of charge, to any
                        person obtaining a copy of this software and associated
                        documentation files (the "Software"), to deal in the
                        Software without restriction, including without
                        limitation the rights to use, copy, modify, merge,
                        publish, distribute, sublicense, and/or sell copies of
                        the Software, and to permit persons to whom the Software
                        is furnished to do so, subject to the following
                        conditions:
                    </p>

                    <p class="mt-4 text-sm leading-relaxed text-foreground">
                        The above copyright notice and this permission notice
                        shall be included in all copies or substantial portions
                        of the Software.
                    </p>

                    <p class="mt-4 text-sm leading-relaxed text-foreground">
                        THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF
                        ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
                        TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
                        PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT
                        SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
                        CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
                        OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
                        IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
                        DEALINGS IN THE SOFTWARE.
                    </p>

                    <div
                        class="mt-8 rounded-lg border border-border bg-muted/50 p-4"
                    >
                        <h3 class="text-lg font-semibold text-foreground">
                            Contact
                        </h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            For questions, issues, or contributions, please
                            visit the
                            <a
                                href="https://github.com/kea137/Bible"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary hover:underline"
                            >
                                GitHub repository
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
