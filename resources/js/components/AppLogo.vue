<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Logo from '/resources/images/logo-small.png';

const imageUrl = Logo;
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-secondary text-sidebar-primary-foreground"
    >
    <img :src="imageUrl" alt="Jesus" class="mx-auto max-h-full max-w-full rounded-lg shadow-lg" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >Bible</span
        >
    </div>
</template>
