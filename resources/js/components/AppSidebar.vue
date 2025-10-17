<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import LanguageSelector from '@/components/LanguageSelector.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { dashboard, bibles, bible_create, role_management, license, bibles_parallel } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, CogIcon, FileText, LayoutGrid, LibraryBig, UserCog2, BookCopy } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

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
        title: 'Create Bibles',
        href: bible_create(),
        icon: BookOpen,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Upload Bibles',
        href: bible_create(),
        icon: CogIcon,
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
            <div class="px-3 py-2">
                <div class="text-xs font-medium text-sidebar-foreground/70 mb-2">Language</div>
                <LanguageSelector />
            </div>
            <SidebarSeparator />
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
