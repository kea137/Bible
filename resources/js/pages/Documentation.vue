<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    BookOpen,
    Code,
    Database,
    Highlighter,
    Map,
    Settings,
    Target,
    Users,
    Zap,
} from 'lucide-vue-next';
</script>

<template>
    <Head title="Documentation" />

    <AppLayout :breadcrumbs="[{ label: 'Documentation', href: '' }]">
        <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
            <div class="rounded-xl bg-card p-6 shadow-sm">
                <div class="mb-8 border-b border-border pb-6">
                    <h1 class="mb-3 text-4xl font-bold text-foreground">
                        Bible Application Documentation
                    </h1>
                    <p class="text-lg text-muted-foreground">
                        Complete guide to all features and functionality
                    </p>
                </div>

                <div class="prose prose-sm dark:prose-invert max-w-none">
                    <!-- Quick Navigation -->
                    <div class="mb-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <a
                            href="#getting-started"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <Zap class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    Getting Started
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Installation and first steps
                            </p>
                        </a>

                        <a
                            href="#features"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <Settings class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    Features
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Core application features
                            </p>
                        </a>

                        <a
                            href="#bible-reading"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <BookOpen class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    Bible Reading
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Reading interface and navigation
                            </p>
                        </a>

                        <a
                            href="#cross-references"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <Map class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    Cross-References
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Navigate related verses
                            </p>
                        </a>

                        <a
                            href="#highlighting"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <Highlighter class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    Highlighting & Notes
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Mark verses and add notes
                            </p>
                        </a>

                        <a
                            href="#reading-plan"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <Target class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    Reading Plan
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Track your progress
                            </p>
                        </a>

                        <a
                            href="#user-management"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <Users class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    User Management
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Account and security settings
                            </p>
                        </a>

                        <a
                            href="#configuration"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <Database class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    Configuration
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Admin setup and management
                            </p>
                        </a>

                        <a
                            href="#developer-guide"
                            class="group block rounded-lg border border-border p-4 transition-colors hover:border-primary hover:bg-accent"
                        >
                            <div class="mb-2 flex items-center gap-2">
                                <Code class="h-5 w-5 text-primary" />
                                <h3
                                    class="m-0 text-base font-semibold text-foreground"
                                >
                                    Developer Guide
                                </h3>
                            </div>
                            <p class="m-0 text-sm text-muted-foreground">
                                Technical documentation
                            </p>
                        </a>
                    </div>

                    <!-- Overview Section -->
                    <section id="overview" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Overview
                        </h2>
                        <p class="text-foreground">
                            The Bible Application is a modern Bible reading and
                            study platform built with Laravel, Vue.js, and
                            Inertia.js. It provides a comprehensive set of tools
                            for reading, studying, and managing Bible
                            translations with advanced features like
                            cross-references, highlighting, notes, and reading
                            progress tracking.
                        </p>

                        <div
                            class="mt-6 rounded-lg border border-border bg-muted/50 p-4"
                        >
                            <h3
                                class="mb-3 text-lg font-semibold text-foreground"
                            >
                                Key Features
                            </h3>
                            <ul class="space-y-2 text-sm text-foreground">
                                <li>✅ Multiple Bible Translations</li>
                                <li>✅ Cross-Reference System</li>
                                <li>✅ Verse Highlighting with color codes</li>
                                <li>✅ Personal Notes</li>
                                <li>✅ Reading Plan with progress tracking</li>
                                <li>✅ Parallel Bible comparison view</li>
                                <li>✅ User Authentication with 2FA</li>
                                <li>✅ Dark Mode support</li>
                                <li>✅ Responsive design for all devices</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Getting Started Section -->
                    <section id="getting-started" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Getting Started
                        </h2>

                        <h3
                            class="mt-6 mb-3 text-xl font-semibold text-foreground"
                        >
                            For Users
                        </h3>
                        <ol class="space-y-2 text-foreground">
                            <li>
                                <strong>Create an account</strong> or log in if
                                you already have one
                            </li>
                            <li>
                                <strong>Browse available Bibles</strong> from
                                the Bibles page
                            </li>
                            <li>
                                <strong>Select a Bible translation</strong> to
                                start reading
                            </li>
                            <li>
                                <strong>Explore features</strong> like
                                highlighting, notes, and cross-references
                            </li>
                        </ol>

                        <h3
                            class="mt-6 mb-3 text-xl font-semibold text-foreground"
                        >
                            For Developers
                        </h3>
                        <div class="rounded-lg bg-muted p-4">
                            <pre
                                class="overflow-x-auto text-sm"
                            ><code># Clone repository
git clone https://github.com/kea137/Bible.git
cd Bible

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start server
php artisan serve</code></pre>
                        </div>
                    </section>

                    <!-- Features Section -->
                    <section id="features" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Features
                        </h2>

                        <div class="space-y-6">
                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Dashboard
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Your personal dashboard provides quick
                                    stats, recent highlights, and quick access
                                    to continue reading.
                                </p>
                            </div>

                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Bible Library
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Browse all available Bible translations with
                                    filtering by language and quick search
                                    capabilities.
                                </p>
                            </div>

                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Parallel Bibles
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Compare up to 3 Bible translations
                                    side-by-side with synchronized scrolling.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Bible Reading Section -->
                    <section id="bible-reading" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Bible Reading
                        </h2>

                        <h3
                            class="mt-6 mb-3 text-xl font-semibold text-foreground"
                        >
                            Reading Interface
                        </h3>
                        <div class="space-y-4">
                            <div
                                class="rounded-lg border border-border bg-muted/30 p-4"
                            >
                                <h4 class="mb-2 font-semibold text-foreground">
                                    Main Content (2/3 screen)
                                </h4>
                                <ul
                                    class="space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>
                                        • Book, chapter, and verse navigation
                                    </li>
                                    <li>• Clean, readable text</li>
                                    <li>• Interactive verse numbers</li>
                                    <li>• Right-click context menu</li>
                                </ul>
                            </div>

                            <div
                                class="rounded-lg border border-border bg-muted/30 p-4"
                            >
                                <h4 class="mb-2 font-semibold text-foreground">
                                    References Sidebar (1/3 screen)
                                </h4>
                                <ul
                                    class="space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>
                                        • Upper section: List of
                                        cross-references
                                    </li>
                                    <li>
                                        • Lower section: Full text of clicked
                                        reference
                                    </li>
                                    <li>
                                        • Collapsible for full-screen reading
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <h3
                            class="mt-6 mb-3 text-xl font-semibold text-foreground"
                        >
                            Navigation Features
                        </h3>
                        <ul class="space-y-2 text-foreground">
                            <li>
                                <strong>Book selector:</strong> Choose from all
                                66 books
                            </li>
                            <li>
                                <strong>Chapter selector:</strong> Quick chapter
                                navigation
                            </li>
                            <li>
                                <strong>Previous/Next buttons:</strong> Move
                                between chapters
                            </li>
                            <li>
                                <strong>Keyboard shortcuts:</strong> Arrow keys
                                for navigation
                            </li>
                        </ul>
                    </section>

                    <!-- Cross-References Section -->
                    <section id="cross-references" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Cross-Reference System
                        </h2>

                        <p class="mb-4 text-foreground">
                            The cross-reference system connects related Bible
                            verses, enabling deep scripture study.
                        </p>

                        <div class="space-y-4">
                            <div
                                class="rounded-lg border border-border bg-accent/50 p-4"
                            >
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Reference Preview
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    <strong>Hover</strong> over verse numbers to
                                    see a preview of cross-references and the
                                    total number of references.
                                </p>
                            </div>

                            <div
                                class="rounded-lg border border-border bg-accent/50 p-4"
                            >
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Reference Sidebar
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    <strong>Upper panel:</strong> List of all
                                    cross-references<br />
                                    <strong>Lower panel:</strong> Full verse
                                    text when clicked<br />
                                    Navigate to any reference with a single
                                    click.
                                </p>
                            </div>

                            <div
                                class="rounded-lg border border-border bg-accent/50 p-4"
                            >
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Verse Study Page
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Access via right-click menu on any verse to
                                    see full verse context, all cross-references
                                    with complete text, and the same verse in
                                    other translations.
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-6 rounded-lg border border-border bg-muted/50 p-4"
                        >
                            <h3
                                class="mb-3 text-lg font-semibold text-foreground"
                            >
                                Book Abbreviations
                            </h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <h4
                                        class="mb-2 font-semibold text-foreground"
                                    >
                                        Old Testament (1-39)
                                    </h4>
                                    <code class="text-xs text-foreground">
                                        GEN, EXO, LEV, NUM, DEU, JOS, JDG, RUT,
                                        1SA, 2SA, 1KI, 2KI, 1CH, 2CH, EZR, NEH,
                                        EST, JOB, PSA, PRO, ECC, SNG, ISA, JER,
                                        LAM, EZK, DAN, HOS, JOL, AMO, OBA, JON,
                                        MIC, NAM, HAB, ZEP, HAG, ZEC, MAL
                                    </code>
                                </div>
                                <div>
                                    <h4
                                        class="mb-2 font-semibold text-foreground"
                                    >
                                        New Testament (40-66)
                                    </h4>
                                    <code class="text-xs text-foreground">
                                        MAT, MAR, LUK, JOH, ACT, ROM, 1CO, 2CO,
                                        GAL, EPH, PHP, COL, 1TH, 2TH, 1TI, 2TI,
                                        TIT, PHM, HEB, JAM, 1PE, 2PE, 1JO, 2JO,
                                        3JO, JUD, REV
                                    </code>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Highlighting Section -->
                    <section id="highlighting" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Highlighting & Notes
                        </h2>

                        <div class="space-y-6">
                            <div>
                                <h3
                                    class="mb-3 text-xl font-semibold text-foreground"
                                >
                                    Verse Highlighting
                                </h3>
                                <div
                                    class="rounded-lg border border-border bg-muted/30 p-4"
                                >
                                    <h4
                                        class="mb-2 font-semibold text-foreground"
                                    >
                                        How to Highlight:
                                    </h4>
                                    <ol
                                        class="space-y-1 text-sm text-foreground"
                                    >
                                        <li>
                                            1. <strong>Right-click</strong> on
                                            any verse
                                        </li>
                                        <li>
                                            2. Choose highlight color:
                                            <ul class="mt-1 ml-4 space-y-1">
                                                <li
                                                    class="flex items-center gap-2"
                                                >
                                                    <span
                                                        class="inline-block h-3 w-3 rounded bg-yellow-300"
                                                    ></span>
                                                    <span
                                                        >Yellow - General
                                                        highlights</span
                                                    >
                                                </li>
                                                <li
                                                    class="flex items-center gap-2"
                                                >
                                                    <span
                                                        class="inline-block h-3 w-3 rounded bg-green-300"
                                                    ></span>
                                                    <span
                                                        >Green - Important
                                                        verses</span
                                                    >
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            3. Highlight saves automatically
                                        </li>
                                    </ol>
                                </div>
                            </div>

                            <div>
                                <h3
                                    class="mb-3 text-xl font-semibold text-foreground"
                                >
                                    Personal Notes
                                </h3>
                                <div
                                    class="rounded-lg border border-border bg-muted/30 p-4"
                                >
                                    <p class="mb-2 text-sm text-foreground">
                                        Create personal notes attached to verses
                                        or standalone. Features include:
                                    </p>
                                    <ul
                                        class="space-y-1 text-sm text-muted-foreground"
                                    >
                                        <li>• Rich text editor</li>
                                        <li>• Private to your account</li>
                                        <li>
                                            • Searchable by content or reference
                                        </li>
                                        <li>• Exportable</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Reading Plan Section -->
                    <section id="reading-plan" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Reading Plan
                        </h2>

                        <p class="mb-4 text-foreground">
                            Track your Bible reading progress through all 66
                            books with visual progress bars and statistics.
                        </p>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Progress Tracking
                                </h3>
                                <ul
                                    class="space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>• Mark chapters as complete</li>
                                    <li>• Visual progress bars</li>
                                    <li>• Statistics dashboard</li>
                                    <li>• Reading streaks</li>
                                </ul>
                            </div>

                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Reading Statistics
                                </h3>
                                <ul
                                    class="space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>• Total chapters read</li>
                                    <li>• Percentage complete</li>
                                    <li>• Books completed</li>
                                    <li>• Reading history</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- User Management Section -->
                    <section id="user-management" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            User Management
                        </h2>

                        <div class="space-y-6">
                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    Account Features
                                </h3>
                                <ul class="space-y-2 text-sm text-foreground">
                                    <li>
                                        <strong>Profile Settings:</strong>
                                        Update personal information, email, and
                                        password
                                    </li>
                                    <li>
                                        <strong>Security:</strong> Two-factor
                                        authentication and session management
                                    </li>
                                    <li>
                                        <strong>Preferences:</strong> Theme
                                        selection, language, and default Bible
                                    </li>
                                </ul>
                            </div>

                            <div
                                class="rounded-lg border border-border bg-muted/50 p-4"
                            >
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    User Roles
                                </h3>
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <h4
                                            class="font-semibold text-foreground"
                                        >
                                            Admin
                                        </h4>
                                        <p class="text-muted-foreground">
                                            Full system access: manage Bibles,
                                            references, users, and system
                                            configuration
                                        </p>
                                    </div>
                                    <div>
                                        <h4
                                            class="font-semibold text-foreground"
                                        >
                                            Editor
                                        </h4>
                                        <p class="text-muted-foreground">
                                            Content management: upload Bibles,
                                            upload references, configure Bibles
                                        </p>
                                    </div>
                                    <div>
                                        <h4
                                            class="font-semibold text-foreground"
                                        >
                                            User
                                        </h4>
                                        <p class="text-muted-foreground">
                                            Standard access: read Bibles, create
                                            notes, highlights, and track reading
                                            progress
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Configuration Section -->
                    <section id="configuration" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Configuration (Admin)
                        </h2>

                        <div class="space-y-6">
                            <div
                                class="rounded-lg border border-border bg-muted/30 p-4"
                            >
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    Bible Management
                                </h3>
                                <p class="mb-2 text-sm text-foreground">
                                    Configure Bibles page allows administrators
                                    to:
                                </p>
                                <ul
                                    class="space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>• View all installed Bibles</li>
                                    <li>
                                        • Upload new translations (supports
                                        multiple JSON formats)
                                    </li>
                                    <li>• Edit Bible information</li>
                                    <li>• Delete Bibles</li>
                                    <li>
                                        • Boot up all Bibles from resources
                                        directory
                                    </li>
                                </ul>
                            </div>

                            <div
                                class="rounded-lg border border-border bg-muted/30 p-4"
                            >
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    Reference Management
                                </h3>
                                <p class="mb-2 text-sm text-foreground">
                                    Configure References page for uploading
                                    cross-reference data in JSON format.
                                </p>
                                <div class="mt-2 rounded bg-muted p-3">
                                    <code class="text-xs text-foreground">
                                        {"1":{"v":"GEN 1 1","r":{"2063":"EXO 20
                                        11","14373":"PSA 33 6"}}}
                                    </code>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-border bg-accent/50 p-4"
                            >
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    Boot Up Feature
                                </h3>
                                <p class="text-sm text-foreground">
                                    Administrators can quickly populate the
                                    database by clicking "Boot Up All Bibles"
                                    which queues background jobs to:
                                </p>
                                <ul
                                    class="mt-2 space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>
                                        • Install all Bibles from
                                        resources/Bibles directory
                                    </li>
                                    <li>
                                        • Install references from
                                        resources/References directory
                                    </li>
                                    <li>
                                        • Process asynchronously (requires queue
                                        worker:
                                        <code>php artisan queue:work</code>)
                                    </li>
                                </ul>
                            </div>

                            <div
                                class="rounded-lg border border-border bg-muted/30 p-4"
                            >
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    Role Management
                                </h3>
                                <p class="text-sm text-foreground">
                                    Admin-only page to view all users,
                                    assign/remove roles, and manage user
                                    accounts.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Developer Guide Section -->
                    <section id="developer-guide" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Developer Guide
                        </h2>

                        <div class="space-y-6">
                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    Tech Stack
                                </h3>
                                <div class="grid gap-2 text-sm sm:grid-cols-2">
                                    <div>
                                        <strong>Backend:</strong> Laravel 11
                                    </div>
                                    <div>
                                        <strong>Frontend:</strong> Vue.js 3 +
                                        Inertia.js
                                    </div>
                                    <div>
                                        <strong>Styling:</strong> Tailwind CSS
                                    </div>
                                    <div>
                                        <strong>UI Components:</strong> Reka UI
                                        (Radix Vue)
                                    </div>
                                    <div><strong>Build Tool:</strong> Vite</div>
                                    <div>
                                        <strong>Database:</strong>
                                        MySQL/PostgreSQL
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-border bg-muted/50 p-4"
                            >
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    API Endpoints
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <strong class="text-foreground"
                                            >Bible Endpoints:</strong
                                        >
                                        <code
                                            class="mt-1 block text-xs text-muted-foreground"
                                        >
                                            GET /api/bibles<br />
                                            GET
                                            /api/bibles/books/chapters/{'{'}chapter{'}'}
                                        </code>
                                    </div>
                                    <div>
                                        <strong class="text-foreground"
                                            >Reference Endpoints:</strong
                                        >
                                        <code
                                            class="mt-1 block text-xs text-muted-foreground"
                                        >
                                            GET
                                            /api/verses/{'{'}verse{'}'}/references<br />
                                            GET /verses/{'{'}verse{'}'}/study
                                        </code>
                                    </div>
                                    <div>
                                        <strong class="text-foreground"
                                            >Highlight Endpoints:</strong
                                        >
                                        <code
                                            class="mt-1 block text-xs text-muted-foreground"
                                        >
                                            POST /api/verse-highlights<br />
                                            GET /api/verse-highlights<br />
                                            DELETE
                                            /api/verse-highlights/{'{'}verse{'}'}
                                        </code>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-3 text-lg font-semibold text-foreground"
                                >
                                    Development Commands
                                </h3>
                                <div class="rounded bg-muted p-3">
                                    <code class="text-xs text-foreground">
                                        npm run dev # Development with hot
                                        reload<br />
                                        npm run build # Production build<br />
                                        npm run lint # Lint code<br />
                                        npm run format # Format code<br />
                                        php artisan test # Run tests<br />
                                        php artisan queue:work # Start queue
                                        worker
                                    </code>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Troubleshooting Section -->
                    <section id="troubleshooting" class="mb-12">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Troubleshooting
                        </h2>

                        <div class="space-y-4">
                            <div class="rounded-lg border border-border p-4">
                                <h3 class="mb-2 font-semibold text-foreground">
                                    References not showing?
                                </h3>
                                <ul
                                    class="space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>
                                        • Verify reference JSON was uploaded
                                    </li>
                                    <li>• Check verse IDs match database</li>
                                    <li>
                                        • Inspect browser console for errors
                                    </li>
                                </ul>
                            </div>

                            <div class="rounded-lg border border-border p-4">
                                <h3 class="mb-2 font-semibold text-foreground">
                                    Highlights not saving?
                                </h3>
                                <ul
                                    class="space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>• Ensure user is authenticated</li>
                                    <li>• Check CSRF token</li>
                                    <li>• Verify API endpoint accessibility</li>
                                </ul>
                            </div>

                            <div class="rounded-lg border border-border p-4">
                                <h3 class="mb-2 font-semibold text-foreground">
                                    Queue jobs not running?
                                </h3>
                                <ul
                                    class="space-y-1 text-sm text-muted-foreground"
                                >
                                    <li>
                                        • Start queue worker:
                                        <code>php artisan queue:work</code>
                                    </li>
                                    <li>• Check jobs table for pending jobs</li>
                                    <li>
                                        • Review failed_jobs table for errors
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Additional Resources -->
                    <section id="resources" class="mb-8">
                        <h2 class="mb-4 text-2xl font-bold text-foreground">
                            Additional Resources
                        </h2>

                        <div class="space-y-4">
                            <div
                                class="rounded-lg border border-border bg-accent/50 p-4"
                            >
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Credits
                                </h3>
                                <p class="mb-2 text-sm text-foreground">
                                    This project utilizes resources from:
                                </p>
                                <ul class="space-y-1 text-sm">
                                    <li>
                                        <a
                                            href="https://github.com/jadenzaleski/BibleTranslations"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-primary hover:underline"
                                        >
                                            jadenzaleski/BibleTranslations
                                        </a>
                                        <span class="text-muted-foreground">
                                            - Bible translation JSON files</span
                                        >
                                    </li>
                                    <li>
                                        <a
                                            href="https://github.com/josephilipraja/bible-cross-reference-json"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-primary hover:underline"
                                        >
                                            josephilipraja/bible-cross-reference-json
                                        </a>
                                        <span class="text-muted-foreground">
                                            - Cross-reference data</span
                                        >
                                    </li>
                                </ul>
                            </div>

                            <div class="rounded-lg border border-border p-4">
                                <h3
                                    class="mb-2 text-lg font-semibold text-foreground"
                                >
                                    Getting Help
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    For questions, issues, or contributions,
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
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
