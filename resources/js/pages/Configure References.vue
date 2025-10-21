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
import { references_configure, references_create } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configure References',
        href: references_configure().url,
    },
];

const props = defineProps<{
    bibles: Array<{
        id: number;
        name: string;
        abbreviation: string;
        language: string;
        reference_count: number;
    }>;
}>();

const pageSize = 5;
const currentPage = ref(1);

const paginatedBibles = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return props.bibles.slice(start, start + pageSize);
});

function handlePageChange(page: number) {
    currentPage.value = page;
}

const page = usePage();
const success = page.props.success;
const error = page.props.error;
const info = page.props.info;

const alertSuccess = ref(false);
const alertError = ref(false);
const alertInfo = ref(false);
const deleteDialogOpen = ref(false);
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

function createReferences() {
    router.visit(references_create().url);
}

function confirmDelete(bibleId: number) {
    bibleToDelete.value = bibleId;
    deleteDialogOpen.value = true;
}

function deleteReferences() {
    if (bibleToDelete.value) {
        router.delete(`/references/${bibleToDelete.value}`, {
            onSuccess: () => {
                deleteDialogOpen.value = false;
                bibleToDelete.value = null;
            },
        });
    }
}
</script>

<template>
    <Head title="Configure References" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        title="Success"
        :confirmButtonText="'OK'"
        message="Operation was Successful"
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
        message="Operation Failed. Try again later!"
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
                <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This will permanently delete all references for this Bible.
                    This action cannot be undone.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="deleteReferences"
                    >Delete</AlertDialogAction
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
                                >Configure References</CardTitle
                            >
                            <CardDescription class="text-xs sm:text-sm"
                                >Manage Bible verse references - upload or
                                delete</CardDescription
                            >
                        </div>
                        <Button
                            @click="createReferences"
                            class="w-full sm:w-auto"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            <span class="hidden sm:inline"
                                >Upload New References</span
                            >
                            <span class="sm:hidden">Upload References</span>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="props.bibles.length > 0" class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="text-xs sm:text-sm"
                                        >Bible Name</TableHead
                                    >
                                    <TableHead class="text-xs sm:text-sm"
                                        >Abbreviation</TableHead
                                    >
                                    <TableHead
                                        class="hidden text-xs sm:text-sm md:table-cell"
                                        >Language</TableHead
                                    >
                                    <TableHead class="text-xs sm:text-sm"
                                        >Reference Count</TableHead
                                    >
                                    <TableHead
                                        class="text-right text-xs sm:text-sm"
                                        >Actions</TableHead
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
                                    <TableCell class="text-xs sm:text-sm">{{
                                        bible.reference_count
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <div
                                            class="flex justify-end gap-1 sm:gap-2"
                                        >
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                @click="confirmDelete(bible.id)"
                                                :disabled="
                                                    bible.reference_count === 0
                                                "
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
                            No Bibles with references found. Upload references
                            to get started.
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
