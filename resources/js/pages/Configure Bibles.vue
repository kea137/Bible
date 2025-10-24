<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import Button from '@/components/ui/button/Button.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationEllipsis from '@/components/ui/pagination/PaginationEllipsis.vue';
import PaginationItem from '@/components/ui/pagination/PaginationItem.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { bible_create, bible_edit, bibles_configure } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Database, Edit, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configure Bibles',
        href: bibles_configure().url,
    },
];

const props = defineProps<{
    bibles: Array<{
        id: number;
        name: string;
        abbreviation: string;
        language: string;
        version: string;
        description: string;
    }>;
}>();

const page = usePage();
const success = page.props.success;
const error = page.props.error;
const info = page.props.info;

const alertSuccess = ref(false);
const alertError = ref(false);
const alertInfo = ref(false);
const deleteDialogOpen = ref(false);
const bootupDialogOpen = ref(false);
const bibleToDelete = ref<number | null>(null);

if (success) {
    alertSuccess.value = true;
}

if (error) {
    alertError.value = true;
}

if (info) {
    alertInfo.value = true;
}

function createBible() {
    router.visit(bible_create().url);
}

function editBible(bibleId: number) {
    router.visit(bible_edit({ bible: bibleId }).url);
}

function confirmDelete(bibleId: number) {
    bibleToDelete.value = bibleId;
    deleteDialogOpen.value = true;
}

function deleteBible() {
    if (bibleToDelete.value) {
        router.delete(`/bibles/${bibleToDelete.value}`, {
            onSuccess: () => {
                deleteDialogOpen.value = false;
                bibleToDelete.value = null;
            },
        });
    }
}

function confirmBootup() {
    bootupDialogOpen.value = true;
}

const pageSize = 5;
const currentPage = ref(1);

const paginatedBibles = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return props.bibles.slice(start, start + pageSize);
});

function handlePageChange(page: number) {
    currentPage.value = page;
}

function bootupBibles() {
    router.post(
        '/bibles/bootup',
        {},
        {
            onSuccess: () => {
                bootupDialogOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="t('Configure Bibles')" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        title="Success"
        :confirmButtonText="'OK'"
        :message="t('Operation was Successful!')"
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
        :message="t('Operation Failed. Try again later!')"
        variant="error"
        @update:open="
            () => {
                alertError = false;
            }
        "
    />

    <AlertDialog
        :open="deleteDialogOpen"
        @update:open="deleteDialogOpen = $event"
    >
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{t('Are you sure?')}}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{t('This will permanently delete this Bible and all its')}}
                    {{t('associated books, chapters, and verses. This action cannot')}}
                    {{t('be undone.')}}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{t('Cancel')}}</AlertDialogCancel>
                <AlertDialogAction @click="deleteBible"
                    >{{t('Delete')}}</AlertDialogAction
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <AlertDialog
        :open="bootupDialogOpen"
        @update:open="bootupDialogOpen = $event"
    >
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{t('Install All Bibles and References?')}}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{t('This will install all Bible translations from the resources')}}
                    {{t('directory and all references for the first Bible. This is a')}}
                    {{t('heavy operation that will run in the background. You will be')}}
                    {{t('notified when it completes. Are you sure you want to')}}
                    {{t('proceed?')}}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{t('Cancel')}}</AlertDialogCancel>
                <AlertDialogAction @click="bootupBibles"
                    >{{t('Install')}}</AlertDialogAction
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-2 sm:p-4"
        >
            <Card>
                <CardHeader>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <CardTitle class="text-base sm:text-lg"
                                >{{t('Configure Bibles')}}</CardTitle
                            >
                            <CardDescription class="text-xs sm:text-sm"
                                >{{t('Manage your Bible translations - create,')}}
                                {{t('update, or delete')}}</CardDescription
                            >
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                            <Button
                                variant="outline"
                                @click="confirmBootup"
                                class="w-full sm:w-auto"
                            >
                                <Database class="mr-2 h-4 w-4" />
                                <span class="hidden sm:inline"
                                    >{{t('Boot Up All Bibles')}}</span
                                >
                                <span class="sm:hidden">{{t('Boot Up Bibles')}}</span>
                            </Button>
                            <Button
                                @click="createBible"
                                class="w-full sm:w-auto"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                <span class="hidden sm:inline"
                                    >{{t('Upload New Bible')}}</span
                                >
                                <span class="sm:hidden">{{t('Upload Bible')}}</span>
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="props.bibles.length > 0" class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="text-xs sm:text-sm"
                                        >{{t('Name')}}</TableHead
                                    >
                                    <TableHead class="text-xs sm:text-sm"
                                        >{{t('Abbreviation')}}</TableHead
                                    >
                                    <TableHead
                                        class="hidden text-xs sm:text-sm md:table-cell"
                                        >{{t('Language')}}</TableHead
                                    >
                                    <TableHead
                                        class="hidden text-xs sm:text-sm lg:table-cell"
                                        >{{t('Version')}}</TableHead
                                    >
                                    <TableHead
                                        class="text-right text-xs sm:text-sm"
                                        >{{t('Actions')}}</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="bible in paginatedBibles"
                                    :key="bible.id"
                                >
                                    <TableCell
                                        class="text-xs font-medium sm:text-sm"
                                        >{{ bible.name }}</TableCell
                                    >
                                    <TableCell class="text-xs sm:text-sm">{{
                                        bible.abbreviation
                                    }}</TableCell>
                                    <TableCell
                                        class="hidden text-xs sm:text-sm md:table-cell"
                                        >{{ bible.language }}</TableCell
                                    >
                                    <TableCell
                                        class="hidden text-xs sm:text-sm lg:table-cell"
                                        >{{ bible.version }}</TableCell
                                    >
                                    <TableCell class="text-right">
                                        <div
                                            class="flex justify-end gap-1 sm:gap-2"
                                        >
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="editBible(bible.id)"
                                            >
                                                <Edit
                                                    class="h-3 w-3 sm:h-4 sm:w-4"
                                                />
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                @click="confirmDelete(bible.id)"
                                            >
                                                <Trash2
                                                    class="h-3 w-3 sm:h-4 sm:w-4"
                                                />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="py-8 text-center text-muted-foreground">
                        <p class="text-xs sm:text-sm">
                            {{t('No Bibles found. Upload your first Bible to get')}}
                            {{t('started.')}}
                        </p>
                    </div>
                    <div class="mt-8 w-full">
                        <Pagination :items-per-page="pageSize" :total="bibles.length" :default-page="1" @update:page="handlePageChange">
                            <PaginationContent v-slot="{ items }">
                                <PaginationPrevious />

                                <template v-for="(item, index) in items" :key="index">
                                    <PaginationItem
                                        v-if="item.type === 'page'"
                                        :value="item.value"
                                        :is-active="item.value === currentPage"
                                        @click="handlePageChange(item.value)"
                                    >
                                        {{ item.value }}
                                    </PaginationItem>
                                </template>

                                <PaginationEllipsis v-if="items.some((i: { type: string }) => i.type === 'ellipsis')" />

                                <PaginationNext />
                            </PaginationContent>
                        </Pagination>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
