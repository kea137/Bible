<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import {
    Command,
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import AppLayout from '@/layouts/AppLayout.vue';
import { bibles } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { LibraryBigIcon, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bibles',
        href: bibles().url,
    },
];

const props = defineProps<{
    biblesList: {
        id: number;
        name: string;
        abbreviation: string;
        description: string;
        language: string;
        version: string;
        books: {
            id: number;
            title: string;
            book_number: number;
            chapters: {
                id: number;
                chapter_number: number;
            }[];
        }[];
    }[];
}>();

const searchOpen = ref(false);
const searchQuery = ref('');

const filteredBibles = computed(() => {
    if (!searchQuery.value) {
        return props.biblesList;
    }
    const query = searchQuery.value.toLowerCase();
    return props.biblesList.filter(
        (bible) =>
            bible.name.toLowerCase().includes(query) ||
            bible.language.toLowerCase().includes(query) ||
            bible.version.toLowerCase().includes(query) ||
            bible.abbreviation.toLowerCase().includes(query),
    );
});

function viewBible(bibleId: number) {
    router.visit(`/bibles/${bibleId}`);
    searchOpen.value = false;
}

const page = usePage();
const success = page.props.success;
const error = page.props.error;
const info = page.props.info;

const alertSuccess = ref(false);
const alertError = ref(false);
const alertInfo = ref(false);

if (success) {
    alertSuccess.value = true;
}

if (error) {
    alertError.value = true;
}

if (info) {
    alertInfo.value = true;
}
</script>

<template>
    <Head title="Bibles" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        title="Success"
        :confirmButtonText="'OK'"
        :message="success"
        variant="success"
        @update:open="
            () => {
                alertSuccess = false;
            }
        "
    />
    <AlertUser
        v-if="alertError"
        :open="true"
        title="Error"
        :confirmButtonText="'OK'"
        :message="error"
        variant="error"
        @update:open="
            () => {
                alertError = false;
            }
        "
    />
    <AlertUser
        v-if="alertInfo"
        :open="true"
        title="Information"
        :confirmButtonText="'OK'"
        :message="info"
        variant="info"
        @update:open="
            () => {
                alertInfo = false;
            }
        "
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-2 sm:gap-4 sm:p-4"
        >
            <Card>
                <CardHeader class="pb-3">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <CardTitle
                                class="flex items-center gap-2 text-base sm:text-lg"
                            >
                                <LibraryBigIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                                Bibles
                            </CardTitle>
                            <CardDescription class="text-xs sm:text-sm"
                                >Available Bibles</CardDescription
                            >
                        </div>
                        <Button
                            @click="searchOpen = true"
                            variant="outline"
                            class="w-full sm:w-auto"
                        >
                            <Search class="mr-2 h-4 w-4" />
                            Search Bibles
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="filteredBibles.length > 0"
                        class="space-y-2 sm:space-y-3"
                    >
                        <div
                            v-for="bible in filteredBibles"
                            :key="bible.id"
                            class="flex cursor-pointer items-center justify-between rounded-lg border border-border p-2 transition-colors hover:bg-accent/50 sm:p-3"
                            @click="viewBible(bible.id)"
                        >
                            <div class="flex-1">
                                <p class="text-sm font-medium sm:text-base">
                                    {{ bible.name }}
                                </p>
                                <p
                                    class="text-xs text-muted-foreground sm:text-sm"
                                >
                                    {{ bible.language }} • {{ bible.version }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-6 text-center text-sm text-muted-foreground sm:py-8 sm:text-base"
                    >
                        <p>No Bibles Available</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Search Dialog -->
            <CommandDialog
                :open="searchOpen"
                @update:open="searchOpen = $event"
            >
                <Command>
                    <CommandInput
                        v-model="searchQuery"
                        placeholder="Search bibles by name, language, or version..."
                    />
                    <CommandList>
                        <CommandEmpty>No bibles found.</CommandEmpty>
                        <CommandGroup heading="Bibles">
                            <CommandItem
                                v-for="bible in filteredBibles"
                                :key="bible.id"
                                :value="bible.name"
                                @select="viewBible(bible.id)"
                            >
                                <div class="flex flex-col">
                                    <span class="font-medium">{{
                                        bible.name
                                    }}</span>
                                    <span class="text-xs text-muted-foreground">
                                        {{ bible.language }} •
                                        {{ bible.version }}
                                    </span>
                                </div>
                            </CommandItem>
                        </CommandGroup>
                    </CommandList>
                </Command>
            </CommandDialog>
        </div>
    </AppLayout>
</template>
