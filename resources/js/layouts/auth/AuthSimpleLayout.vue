<script setup lang="ts">
import SkipNavigation from '@/components/SkipNavigation.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';
import Logo from '/resources/images/logo-small.png';

const imageUrl = Logo;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <SkipNavigation />
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                        aria-label="Go to home page"
                    >
                        <div
                            class="mb-1 flex h-9 w-9 items-center justify-center rounded-md"
                        >
                            <img
                                :src="imageUrl"
                                alt="Jesus"
                                class="mx-auto max-h-full max-w-full rounded-lg shadow-lg"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <main id="main-content" tabindex="-1">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
