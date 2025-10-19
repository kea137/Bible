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
import { ref } from 'vue';

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
    <Head title="Configure Bibles" />

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

    <AlertDialog
        :open="deleteDialogOpen"
        @update:open="deleteDialogOpen = $event"
    >
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This will permanently delete this Bible and all its
                    associated books, chapters, and verses. This action cannot
                    be undone.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="deleteBible"
                    >Delete</AlertDialogAction
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
                <AlertDialogTitle
                    >Install All Bibles and References?</AlertDialogTitle
                >
                <AlertDialogDescription>
                    This will install all Bible translations from the resources
                    directory and all references for the first Bible. This is a
                    heavy operation that will run in the background. You will be
                    notified when it completes. Are you sure you want to
                    proceed?
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="bootupBibles"
                    >Install</AlertDialogAction
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
                                >Configure Bibles</CardTitle
                            >
                            <CardDescription class="text-xs sm:text-sm"
                                >Manage your Bible translations - create,
                                update, or delete</CardDescription
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
                                    >Boot Up All Bibles</span
                                >
                                <span class="sm:hidden">Boot Up Bibles</span>
                            </Button>
                            <Button
                                @click="createBible"
                                class="w-full sm:w-auto"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                <span class="hidden sm:inline"
                                    >Upload New Bible</span
                                >
                                <span class="sm:hidden">Upload Bible</span>
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
                                        >Name</TableHead
                                    >
                                    <TableHead class="text-xs sm:text-sm"
                                        >Abbreviation</TableHead
                                    >
                                    <TableHead
                                        class="hidden text-xs sm:text-sm md:table-cell"
                                        >Language</TableHead
                                    >
                                    <TableHead
                                        class="hidden text-xs sm:text-sm lg:table-cell"
                                        >Version</TableHead
                                    >
                                    <TableHead
                                        class="text-right text-xs sm:text-sm"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="bible in props.bibles"
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
                            No Bibles found. Upload your first Bible to get
                            started.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
