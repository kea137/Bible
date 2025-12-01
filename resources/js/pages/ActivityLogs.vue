<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationEllipsis from '@/components/ui/pagination/PaginationEllipsis.vue';
import PaginationItem from '@/components/ui/pagination/PaginationItem.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Table from '@/components/ui/table/Table.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { activity_logs } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Filter, RotateCcw } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Activity Logs'),
        href: activity_logs().url,
    },
];

type ActivityLog = {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    subject_user: {
        id: number;
        name: string;
        email: string;
    } | null;
    action: string;
    description: string;
    metadata: Record<string, any> | null;
    ip_address: string;
    created_at: string;
};

type PaginatedLogs = {
    data: ActivityLog[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
};

const props = defineProps<{
    logs: PaginatedLogs;
    actions: string[];
    filters: {
        action?: string;
        user_id?: number;
        date_from?: string;
        date_to?: string;
    };
}>();

// Filter state
const selectedAction = ref(props.filters.action || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const applyFilters = () => {
    const params: Record<string, any> = {};

    if (selectedAction.value) {
        params.action = selectedAction.value;
    }
    if (dateFrom.value) {
        params.date_from = dateFrom.value;
    }
    if (dateTo.value) {
        params.date_to = dateTo.value;
    }

    router.get(activity_logs().url, params, {
        preserveScroll: true,
        preserveState: false,
    });
};

const resetFilters = () => {
    selectedAction.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get(activity_logs().url);
};

const goToPage = (page: number) => {
    const params: Record<string, any> = { page };

    if (selectedAction.value) params.action = selectedAction.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;

    router.get(activity_logs().url, params, {
        preserveScroll: true,
        preserveState: false,
    });
};

const getActionBadgeClass = (action: string) => {
    if (action.includes('deletion')) {
        return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
    } else if (action.includes('export')) {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
    } else if (action.includes('role')) {
        return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
    }
    return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};

const hasActiveFilters = computed(() => {
    return selectedAction.value || dateFrom.value || dateTo.value;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="t('Activity Logs')" />

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('Activity Logs') }}</CardTitle>
                    <CardDescription>
                        {{
                            t(
                                'View and filter sensitive actions performed by administrators and users',
                            )
                        }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Filters -->
                    <div class="space-y-4">
                        <div
                            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4"
                        >
                            <div class="space-y-2">
                                <Label for="action">{{ t('Action') }}</Label>
                                <Select v-model="selectedAction">
                                    <SelectTrigger id="action">
                                        <SelectValue
                                            :placeholder="t('All actions')"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">
                                            {{ t('All actions') }}
                                        </SelectItem>
                                        <SelectItem
                                            v-for="action in actions"
                                            :key="action"
                                            :value="action"
                                        >
                                            {{ action }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="date_from">{{
                                    t('From date')
                                }}</Label>
                                <Input
                                    id="date_from"
                                    type="date"
                                    v-model="dateFrom"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="date_to">{{ t('To date') }}</Label>
                                <Input
                                    id="date_to"
                                    type="date"
                                    v-model="dateTo"
                                />
                            </div>

                            <div class="flex items-end space-x-2">
                                <Button
                                    @click="applyFilters"
                                    class="flex-1 gap-2"
                                >
                                    <Filter class="h-4 w-4" />
                                    {{ t('Filter') }}
                                </Button>
                                <Button
                                    @click="resetFilters"
                                    variant="outline"
                                    :disabled="!hasActiveFilters"
                                    class="gap-2"
                                >
                                    <RotateCcw class="h-4 w-4" />
                                    {{ t('Reset') }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Results info -->
                    <div class="text-sm text-muted-foreground">
                        {{
                            t('Showing {from} to {to} of {total} logs', {
                                from: logs.from || 0,
                                to: logs.to || 0,
                                total: logs.total,
                            })
                        }}
                    </div>

                    <!-- Activity Logs Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{{ t('Action') }}</TableHead>
                                    <TableHead>{{ t('User') }}</TableHead>
                                    <TableHead>{{
                                        t('Description')
                                    }}</TableHead>
                                    <TableHead>{{ t('IP Address') }}</TableHead>
                                    <TableHead>{{ t('Date') }}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-if="logs.data.length === 0"
                                    class="hover:bg-transparent"
                                >
                                    <TableCell
                                        colspan="5"
                                        class="h-24 text-center"
                                    >
                                        {{ t('No activity logs found') }}
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="log in logs.data"
                                    :key="log.id"
                                >
                                    <TableCell>
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="
                                                getActionBadgeClass(log.action)
                                            "
                                        >
                                            {{ log.action }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            v-if="log.user"
                                            class="flex flex-col"
                                        >
                                            <span class="font-medium">{{
                                                log.user.name
                                            }}</span>
                                            <span
                                                class="text-xs text-muted-foreground"
                                                >{{ log.user.email }}</span
                                            >
                                        </div>
                                        <span
                                            v-else
                                            class="text-muted-foreground"
                                            >{{ t('System') }}</span
                                        >
                                    </TableCell>
                                    <TableCell>
                                        <div class="max-w-md">
                                            <p class="line-clamp-2">
                                                {{ log.description }}
                                            </p>
                                            <div
                                                v-if="log.subject_user"
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {{ t('Subject') }}:
                                                {{ log.subject_user.name }} ({{
                                                    log.subject_user.email
                                                }})
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span class="font-mono text-xs">{{
                                            log.ip_address || 'N/A'
                                        }}</span>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm whitespace-nowrap">
                                            {{ formatDate(log.created_at) }}
                                        </span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="logs.last_page > 1"
                        class="flex items-center justify-center"
                    >
                        <Pagination
                            :current-page="logs.current_page"
                            :total-pages="logs.last_page"
                        >
                            <PaginationContent>
                                <PaginationItem>
                                    <PaginationPrevious
                                        @click="goToPage(logs.current_page - 1)"
                                        :disabled="logs.current_page === 1"
                                    />
                                </PaginationItem>

                                <template
                                    v-for="page in logs.last_page"
                                    :key="page"
                                >
                                    <PaginationItem
                                        v-if="
                                            page === 1 ||
                                            page === logs.last_page ||
                                            Math.abs(
                                                page - logs.current_page,
                                            ) <= 1
                                        "
                                    >
                                        <Button
                                            variant="outline"
                                            @click="goToPage(page)"
                                            :class="
                                                page === logs.current_page
                                                    ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                                                    : ''
                                            "
                                        >
                                            {{ page }}
                                        </Button>
                                    </PaginationItem>
                                    <PaginationItem
                                        v-else-if="
                                            page === logs.current_page - 2 ||
                                            page === logs.current_page + 2
                                        "
                                    >
                                        <PaginationEllipsis />
                                    </PaginationItem>
                                </template>

                                <PaginationItem>
                                    <PaginationNext
                                        @click="goToPage(logs.current_page + 1)"
                                        :disabled="
                                            logs.current_page === logs.last_page
                                        "
                                    />
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
