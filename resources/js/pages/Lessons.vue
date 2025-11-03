<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Pagination from '@/components/ui/pagination/Pagination.vue';
import PaginationContent from '@/components/ui/pagination/PaginationContent.vue';
import PaginationEllipsis from '@/components/ui/pagination/PaginationEllipsis.vue';
import PaginationItem from '@/components/ui/pagination/PaginationItem.vue';
import PaginationNext from '@/components/ui/pagination/PaginationNext.vue';
import PaginationPrevious from '@/components/ui/pagination/PaginationPrevious.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { lessons as lessonsRoute } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { LibraryBigIcon, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Lessons'),
        href: lessonsRoute().url,
    },
];

const props = defineProps<{
    lessons: {
        id: number;
        title: string;
        description: string;
        language: string;
    }[];
}>();

const pageSize = 5;
const currentPage = ref(1);

const paginatedLessons = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return props.lessons.slice(start, start + pageSize);
});

function handlePageChange(page: number) {
    currentPage.value = page;
}

const searchOpen = ref(false);

function viewLesson(lessonId: number) {
    router.visit(`/lessons/show/${lessonId}`);
    searchOpen.value = false;
}

const page = usePage();
const successMessage = computed(() => page.props.success as string);
const errorMessage = computed(() => page.props.error as string);
const alertSuccess = ref(!!successMessage.value);
const alertError = ref(!!errorMessage.value);

</script>

<template>
    <Head title="Lessons" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        :title="t('Success')"
        :confirmButtonText="'OK'"
        :message="t('Operation was successful!')"
        variant="success"
        @update:open="() => (alertSuccess = false)"
    />
    <AlertUser
        v-if="alertError"
        :open="true"
        :title="t('Error')"
        :confirmButtonText="'OK'"
        :message="t('Operation failed! Please try again.')"
        variant="error"
        @update:open="() => (alertError = false)"
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
                                {{t('Lessons')}}
                            </CardTitle>
                            <CardDescription class="text-xs sm:text-sm"
                                >{{t('Available Lessons')}}</CardDescription
                            >
                        </div>
                        <Button
                            @click="searchOpen = true"
                            variant="outline"
                            class="w-full sm:w-auto"
                        >
                            <Search class="mr-2 h-4 w-4" />
                            {{t('Search Lessons')}}
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="paginatedLessons.length > 0"
                        class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="lesson in paginatedLessons"
                            :key="lesson.id"
                            class="group relative cursor-pointer overflow-hidden rounded-lg border border-border transition-all hover:shadow-lg hover:scale-[1.02]"
                            @click="viewLesson(lesson.id)"
                        >
                            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-primary/10 opacity-0 transition-opacity group-hover:opacity-100"></div>
                            <div class="relative p-4">
                                <div class="mb-2 flex items-start justify-between">
                                    <LibraryBigIcon class="h-8 w-8 text-primary/70 transition-colors group-hover:text-primary" />
                                </div>
                                <h3 class="mb-2 text-lg font-semibold line-clamp-2">
                                    {{ lesson.title }}
                                </h3>
                                <p class="mb-3 text-sm text-muted-foreground line-clamp-2">
                                    {{ lesson.description }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-muted-foreground">
                                    <span class="rounded-full bg-primary/10 px-2 py-1">
                                        {{ lesson.language }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="py-6 text-center text-sm text-muted-foreground sm:py-8 sm:text-base"
                    >
                        <LibraryBigIcon class="mx-auto mb-4 h-16 w-16 text-muted-foreground/50" />
                        <p>{{t('No Lessons Available')}}</p>
                        <p class="mt-2 text-xs">{{t('Check back later for new lessons')}}</p>
                    </div>
                </CardContent>
            </Card>
            <div class="mt-8 w-full">
                <Pagination :items-per-page="pageSize" :total="lessons.length" :default-page="1" @update:page="handlePageChange">
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
        </div>
    </AppLayout>
</template>
