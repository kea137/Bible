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
import { references_configure, references_create } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

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
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Configure References</CardTitle>
                            <CardDescription
                                >Manage Bible verse references - upload or
                                delete</CardDescription
                            >
                        </div>
                        <Button @click="createReferences">
                            <Plus class="mr-2 h-4 w-4" />
                            Upload New References
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="props.bibles.length > 0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Bible Name</TableHead>
                                    <TableHead>Abbreviation</TableHead>
                                    <TableHead>Language</TableHead>
                                    <TableHead>Reference Count</TableHead>
                                    <TableHead class="text-right"
                                        >Actions</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="bible in props.bibles"
                                    :key="bible.id"
                                >
                                    <TableCell class="font-medium">{{
                                        bible.name
                                    }}</TableCell>
                                    <TableCell>{{
                                        bible.abbreviation
                                    }}</TableCell>
                                    <TableCell>{{ bible.language }}</TableCell>
                                    <TableCell>{{
                                        bible.reference_count
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                @click="confirmDelete(bible.id)"
                                                :disabled="
                                                    bible.reference_count === 0
                                                "
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="py-8 text-center text-muted-foreground">
                        <p>
                            No Bibles with references found. Upload references
                            to get started.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
