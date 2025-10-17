<script setup lang="ts">
import { license } from '@/routes';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="mt-auto border-t border-border bg-background px-4 py-3">
        <div class="flex flex-col items-center justify-between gap-2 text-xs text-muted-foreground sm:flex-row">
            <div class="flex items-center gap-1">
                <span>&copy; 2025 Kea Rajabu Baruan</span>
            </div>
            <div class="flex items-center gap-4">
                <Link :href="license()" class="hover:text-foreground transition-colors">
                    License
                </Link>
                <a
                    href="https://github.com/kea137/Bible"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hover:text-foreground transition-colors"
                >
                    GitHub
                </a>
            </div>
        </div>
    </footer>
</template>
