<script setup lang="ts">
/**
 * Skip Navigation Link Component
 *
 * Provides a skip navigation link for keyboard users to bypass repetitive navigation.
 * This is an important accessibility feature for WCAG AA compliance.
 */
</script>

<template>
    <a
        href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-primary focus:px-4 focus:py-2 focus:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
        aria-label="Skip to main content"
    >
        Skip to main content
    </a>
</template>

<style scoped>
/* Screen-reader only class - visible only when focused */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

.focus\:not-sr-only:focus {
    position: absolute;
    width: auto;
    height: auto;
    padding: 0.5rem 1rem;
    margin: 0;
    overflow: visible;
    clip: auto;
    white-space: normal;
}
</style>
