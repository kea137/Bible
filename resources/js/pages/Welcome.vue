<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Highlighter,
    Library,
    NotebookPen,
    SplitSquareHorizontal,
    Calendar,
    Link2,
    Moon,
} from 'lucide-vue-next';
import Logo from '/resources/images/logo.png';

const imageUrl = Logo;

const features = [
    {
        icon: BookOpen,
        title: 'Multiple Bible Translations',

    },
    {
        icon: SplitSquareHorizontal,
        title: 'Parallel Bible Reading',

    },
    {
        icon: Highlighter,
        title: 'Highlight & Bookmark',

    },
    {
        icon: NotebookPen,
        title: 'Personal Notes',

    },
    {
        icon: Calendar,
        title: 'Reading Plans',

    },
    {
        icon: Link2,
        title: 'Cross References',

    },
    {
        icon: Library,
        title: 'Verse Study Tools',

    },
    {
        icon: Moon,
        title: 'Dark Mode',

    },
];
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row"
            >
                <div
                    class="flex-1 rounded-br-lg rounded-bl-lg bg-white p-4 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <h1 class="mb-2 text-2xl font-semibold">
                        Welcome to Bible App
                    </h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                        A comprehensive platform for studying God's Word with
                        powerful tools.
                    </p>

                    <div class="mb-2">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                            Features
                        </h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="feature in features"
                                :key="feature.title"
                                class="flex gap-3"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f53003]/10 dark:bg-[#FF4433]/10"
                                >
                                    <component
                                        :is="feature.icon"
                                        class="h-4 w-4 text-[#f53003] dark:text-[#FF4433]"
                                    />
                                </div>
                                <div>
                                    <h3 class="mb-0.5 font-medium leading-tight">
                                        {{ feature.title }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 text-sm leading-normal">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="register()"
                            class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            Get Started
                        </Link>
                        <Link
                            v-else
                            :href="dashboard()"
                            class="inline-block mt-4 rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            Go to Dashboard
                        </Link>
                    </div>
                </div>
                <div
                    class="relative -mb-px aspect-335/376 w-full shrink-0 overflow-hidden rounded-t-lg bg-[#fff2f2] border border-[#e5e5e5] lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:bg-[#ffffff] dark:border-[#333]"
                >
                    <img
                        :src="imageUrl"
                        alt="Jesus"
                        class="mx-auto h-full w-full object-cover rounded-lg shadow-lg"
                    />
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
