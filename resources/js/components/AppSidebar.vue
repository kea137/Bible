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
    license,
    notes,
    role_management,
    references_create,
    highlighted_verses_page,
} from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookCopy,
    BookOpen,
    CogIcon,
    FileText,
    Highlighter,
    LayoutGrid,
    LibraryBig,
    StickyNote,
    UserCog2,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import AppearanceSideBar from './AppearanceSideBar.vue';
import LanguageSelectorSideBar from './LanguageSelectorSideBar.vue';

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

const footerNavItems: NavItem[] = [
    {
        title: 'Upload Bibles',
        href: bible_create(),
        icon: CogIcon,
    },
    {
        title: 'Create References',
        href: references_create(),
        icon: BookOpen,
    },
    {
        title: 'Role Management',
        href: role_management(),
        icon: UserCog2,
    },
    {
        title: 'License',
        href: license(),
        icon: FileText,
    },
];
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
