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
import Table from '@/components/ui/table/Table.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { delete_user, role_management, update_roles } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Save, Trash2, UserCog } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();
const auth = computed(() => page.props.auth);
const roleNumbers = computed(() => auth.value?.roleNumbers || []);
const isAdmin = computed(() => roleNumbers.value.includes(1));

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Role Management'),
        href: role_management().url,
    },
];

type User = {
    id: number;
    name: string;
    email: string;
    roles: string[];
    role_ids: number[];
};

type Role = {
    id: number;
    name: string;
    role_number: number;
    description: string;
};

const props = defineProps<{
    users: User[];
    roles: Role[];
}>();

// Success/error handling
const success = computed(() => page.props.success as string);
const error = computed(() => page.props.error as string);
const alertSuccess = ref(!!success.value);
const alertError = ref(!!error.value);

// Delete confirmation dialog state
const showDeleteDialog = ref(false);
const userToDelete = ref<{ id: number; name: string } | null>(null);

// User role management
const selectedUserRoles = ref<{ [userId: number]: number[] }>({});

// Initialize selected roles
props.users.forEach((user) => {
    selectedUserRoles.value[user.id] = [...user.role_ids];
});

const updateUserRole = (userId: number, roleId: number) => {
    const currentRoles = selectedUserRoles.value[userId] || [];
    const index = currentRoles.indexOf(roleId);

    if (index > -1) {
        currentRoles.splice(index, 1);
    } else {
        currentRoles.push(roleId);
    }

    selectedUserRoles.value[userId] = [...currentRoles];
};

const saveUserRoles = (userId: number) => {
    router.put(
        update_roles(userId).url,
        {
            role_ids: selectedUserRoles.value[userId],
        },
        {
            preserveScroll: true,
        },
    );
};

const openDeleteDialog = (userId: number, userName: string) => {
    userToDelete.value = { id: userId, name: userName };
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (userToDelete.value) {
        router.delete(delete_user(userToDelete.value.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteDialog.value = false;
                userToDelete.value = null;
            },
        });
    }
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    userToDelete.value = null;
};

const pageSize = 5;
const currentPage = ref(1);

const paginatedBibles = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return props.users.slice(start, start + pageSize);
});

function handlePageChange(page: number) {
    currentPage.value = page;
}
</script>

<template>
    <Head :title="t('Role Management')" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        :title="t('Success')"
        :confirmButtonText="'OK'"
        :message="t('Operation was successful')"
        variant="success"
        @update:open="alertSuccess = false"
    />

    <AlertUser
        v-if="alertError"
        :open="true"
        :title="t('Error')"
        :confirmButtonText="'OK'"
        :message="t('Operation failed! Please try again.')"
        variant="error"
        @update:open="
            () => {
                alertError = false;
            }
        "
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <!-- User Role Management Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <UserCog class="h-6 w-6" />
                        <CardTitle>{{ t('User Role Management') }}</CardTitle>
                    </div>
                    <CardDescription
                        >{{ t('Assign and manage user roles in the system') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('Name') }}</TableHead>
                                <TableHead>{{ t('Email') }}</TableHead>
                                <TableHead>{{ t('Current Roles') }}</TableHead>
                                <TableHead>{{ t('Assign Roles') }}</TableHead>
                                <TableHead>{{ t('Actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users" :key="user.id">
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <div class="flex gap-1">
                                        <span
                                            v-for="role in user.roles"
                                            :key="role"
                                            class="rounded-md bg-primary/10 px-2 py-1 text-xs"
                                        >
                                            {{ role }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-2">
                                        <label
                                            v-for="role in roles"
                                            :key="role.id"
                                            class="flex cursor-pointer items-center gap-2"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    selectedUserRoles[
                                                        user.id
                                                    ]?.includes(role.id)
                                                "
                                                @change="
                                                    updateUserRole(
                                                        user.id,
                                                        role.id,
                                                    )
                                                "
                                                class="h-4 w-4 rounded border-gray-300"
                                            />
                                            <span class="text-sm">{{
                                                role.name
                                            }}</span>
                                        </label>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button
                                            size="sm"
                                            @click="saveUserRoles(user.id)"
                                        >
                                            <Save class="h-4 w-4" />
                                            {{ t('Save') }}
                                        </Button>
                                        <Button
                                            v-if="
                                                isAdmin &&
                                                user.id !== auth.user?.id
                                            "
                                            size="sm"
                                            variant="destructive"
                                            @click="
                                                openDeleteDialog(
                                                    user.id,
                                                    user.name,
                                                )
                                            "
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            {{ t('Delete') }}
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
                <div class="mt-8 w-full">
                    <Pagination
                        :items-per-page="pageSize"
                        :total="users.length"
                        :default-page="1"
                        @update:page="handlePageChange"
                    >
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious />

                            <template
                                v-for="(item, index) in items"
                                :key="index"
                            >
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :value="item.value"
                                    :is-active="item.value === currentPage"
                                    @click="handlePageChange(item.value)"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                            </template>

                            <PaginationEllipsis
                                v-if="
                                    items.some(
                                        (i: { type: string }) =>
                                            i.type === 'ellipsis',
                                    )
                                "
                            />

                            <PaginationNext />
                        </PaginationContent>
                    </Pagination>
                </div>
            </Card>
        </div>

        <!-- Delete User Confirmation Dialog -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ t('Delete User') }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ t('Are you sure you want to delete user') }}
                        <strong>{{ userToDelete?.name }}</strong
                        >? {{ t('This action cannot be undone.') }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="cancelDelete">
                        {{ t('Cancel') }}
                    </AlertDialogCancel>
                    <AlertDialogAction
                        @click="confirmDelete"
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                    >
                        {{ t('Delete') }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
