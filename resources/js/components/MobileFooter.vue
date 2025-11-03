<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookCopy,
    Highlighter,
    LayoutGrid,
    LibraryBig,
    PencilRuler,
    StickyNote,
    Target,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    bibles,
    bibles_parallel,
    dashboard,
    highlighted_verses_page,
    lessons,
    notes,
    reading_plan,
} from '@/routes';

const { t } = useI18n();
const page = usePage();
const currentRoute = computed(() => page.url);

const navItems = [
    {
        title: t('Dashboard'),
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: t('Bibles'),
        href: bibles(),
        icon: LibraryBig,
    },
    {
        title: t('Parallel'),
        href: bibles_parallel(),
        icon: BookCopy,
    },
    {
        title: t('Lessons'),
        href: lessons(),
        icon: PencilRuler,
    },
    {
        title: t('Plan'),
        href: reading_plan(),
        icon: Target,
    },
    {
        title: t('Highlights'),
        href: highlighted_verses_page(),
        icon: Highlighter,
    },
    {
        title: t('Notes'),
        href: notes(),
        icon: StickyNote,
    },
];

const MOBILE_NAV_ITEMS_LIMIT = 5;

function isActive(href: string): boolean {
    return currentRoute.value.startsWith(href);
}
</script>

<template>
    <!-- Mobile Footer - Only visible on small screens -->
    <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-border bg-background md:hidden">
        <div class="flex items-center justify-around px-2 py-2">
            <Link
                v-for="item in navItems.slice(0, MOBILE_NAV_ITEMS_LIMIT)"
                :key="item.title"
                :href="item.href"
                class="flex flex-col items-center gap-1 rounded-lg px-3 py-2 transition-colors"
                :class="
                    isActive(item.href)
                        ? 'text-primary'
                        : 'text-muted-foreground hover:text-foreground'
                "
            >
                <component :is="item.icon" class="h-5 w-5" />
                <span class="text-[10px] font-medium">{{ item.title }}</span>
            </Link>
        </div>
    </div>
    
    <!-- Spacer to prevent content from being hidden behind the fixed footer -->
    <div class="h-20 md:hidden"></div>
</template>
