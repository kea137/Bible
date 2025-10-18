<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import {
    bible_create,
    bibles,
    bibles_parallel,
    dashboard,
    highlighted_verses_page,
    license,
    notes,
    reading_plan,
    references_create,
    role_management,
} from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookCopy,
    BookOpen,
    CogIcon,
    FileText,
    Highlighter,
    LayoutGrid,
    LibraryBig,
    StickyNote,
    Target,
    UserCog2,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';
import AppearanceSideBar from './AppearanceSideBar.vue';
import LanguageSelectorSideBar from './LanguageSelectorSideBar.vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const roleNumbers = computed(() => auth.value?.roleNumbers || []);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Bibles',
        href: bibles(),
        icon: LibraryBig,
    },
    {
        title: 'Parallel Bibles',
        href: bibles_parallel(),
        icon: BookCopy,
    },
    {
        title: 'Reading Plan',
        href: reading_plan(),
        icon: Target,
    },
    {
        title: 'Highlights',
        href: highlighted_verses_page(),
        icon: Highlighter,
    },
    {
        title: 'Notes',
        href: notes(),
        icon: StickyNote,
    },
];

// Filter footer items based on role numbers
const footerNavItems = computed(() => {
    const items: NavItem[] = [];

    // Upload Bibles - only for role numbers 1 & 2 (admin & editor)
    if (roleNumbers.value.includes(1) || roleNumbers.value.includes(2)) {
        items.push({
            title: 'Upload Bibles',
            href: bible_create(),
            icon: CogIcon,
        });
    }

    // Create References - only for role numbers 1 & 2 (admin & editor)
    if (roleNumbers.value.includes(1) || roleNumbers.value.includes(2)) {
        items.push({
            title: 'Create References',
            href: references_create(),
            icon: BookOpen,
        });
    }

    // Role Management - only for role number 1 (admin)
    if (roleNumbers.value.includes(1)) {
        items.push({
            title: 'Role Management',
            href: role_management(),
            icon: UserCog2,
        });
    }

    // License - available to everyone
    items.push({
        title: 'License',
        href: license(),
        icon: FileText,
    });

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <SidebarGroup class="px-2 py-0">
                <div class="mr-4 space-y-3 py-2">
                    <div class="space-y-1">
                        <LanguageSelectorSideBar />
                    </div>
                    <div class="space-y-1">
                        <AppearanceSideBar />
                    </div>
                </div>
            </SidebarGroup>
            <SidebarSeparator />
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
