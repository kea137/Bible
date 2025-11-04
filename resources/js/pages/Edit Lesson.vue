<script setup lang="ts">
import LessonController from '@/actions/App/Http/Controllers/LessonController';
import AlertUser from '@/components/AlertUser.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit_lesson, manage_lessons } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { watchEffect, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    languages: {
        id: number;
        name: string;
        code: string;
    }[];
    lesson: LessonType;
    series?: {
        id: number;
        title: string;
        description: string;
    }[];
}>();

type LessonType = {
    id: number;
    title: string;
    description: string;
    language: string;
    readable: boolean;
    no_paragraphs: number;
    series_id?: number | null;
    episode_number?: number | null;
    paragraphs: {
        text: string;
    }[];
}

const editableLesson = ref<LessonType>({ ...props.lesson });
const createNewSeries = ref(false);
const newSeriesTitle = ref('');
const newSeriesDescription = ref('');
const selectedSeriesId = ref<string | undefined>(props.lesson.series_id?.toString());

watchEffect(() => {
    while (editableLesson.value.paragraphs.length < editableLesson.value.no_paragraphs) {
        editableLesson.value.paragraphs.push({ text: '' });
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Lessons Management'),
        href: manage_lessons().url,
    },

    {
        title: t('Edit Lesson'),
        href: edit_lesson(props.lesson.id).url,
    },
];

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
    <Head :title="t('Edit Lesson')" />

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
            class="flex h-full flex-1 flex-col gap-3 overflow-x-auto rounded-xl p-2 sm:gap-4 sm:p-4"
        >
            <div class="grid w-full grid-cols-1 gap-3 sm:gap-4">
                <Card>
                    <Form
                        v-bind="LessonController.update.form(props.lesson.id)"
                        v-slot="{ errors, processing }"
                    >
                        <CardHeader class="pb-3 sm:pb-6">
                            <CardTitle class="text-lg sm:text-xl">{{ t('Edit Lesson') }}</CardTitle>
                            <CardDescription class="text-xs sm:text-sm"
                                >{{ t('Edit Lesson by filling out the form below.') }}</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                class="mt-2 grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2 lg:grid-cols-4"
                            >
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5 md:col-span-2 lg:col-span-1"
                                >
                                    <Label for="name" class="text-sm sm:text-base">{{ t('Title') }}</Label>
                                    <Input
                                        id="title"
                                        name="title"
                                        :tabindex="1"
                                        type="text"
                                        :placeholder="t('Title of the Lesson')"
                                        v-model="editableLesson.title"
                                    />
                                    <InputError :message="errors.title" />
                                </div>
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="language" class="text-sm sm:text-base">{{ t('Language') }}</Label>
                                    <Select name="language"
                                            :default-value="editableLesson.language"
                                    >
                                        <SelectTrigger id="language">
                                            <SelectValue
                                                :placeholder="t('Select Language')"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>{{ t('Languages') }}</SelectLabel>
                                                <template v-for="language in props.languages" :key="language.id">
                                                    <SelectItem :value="language.name">
                                                        {{ language.name }}
                                                    </SelectItem>
                                                </template>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="errors.language" />
                                </div>
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="readable" class="text-sm sm:text-base">{{ t('Readable') }}</Label>
                                    <Select name="readable"
                                            :default-value="editableLesson.readable ? 'True' : 'False'"
                                    >
                                        <SelectTrigger id="readable">
                                            <SelectValue
                                                :placeholder="t('Readable')"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>{{ t('Readable') }}</SelectLabel>
                                                    <SelectItem value="True">
                                                        True
                                                    </SelectItem>
                                                    <SelectItem value="False">
                                                        False
                                                    </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="errors.readable" />
                                </div>
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="no_paragraphs" class="text-sm sm:text-base">{{ t('Number of Paragraphs') }}</Label>
                                    <Input
                                        id="no_paragraphs"
                                        name="no_paragraphs"
                                        :tabindex="2"
                                        type="number"
                                        v-model="editableLesson.no_paragraphs"
                                        placeholder="Number of Paragraphs"
                                    />
                                    <InputError :message="errors.no_paragraph" />
                                </div>
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5 md:col-span-2 lg:col-span-4"
                                >
                                    <Label for="description" class="text-sm sm:text-base">{{ t('Description') }}</Label>
                                    <Textarea
                                        id="description"
                                        name="description"
                                        :tabindex="3"
                                        :placeholder="t('Description of the Lesson')"
                                        v-model="editableLesson.description"
                                    />
                                    <InputError :message="errors.description" />
                                </div>
                                
                                <!-- Series Management -->
                                <div class="col-span-1 space-y-3 md:col-span-2 lg:col-span-4">
                                    <div class="flex items-center space-x-2">
                                        <input 
                                            type="checkbox" 
                                            id="create-new-series" 
                                            v-model="createNewSeries"
                                            class="h-4 w-4 rounded border-gray-300"
                                        />
                                        <Label for="create-new-series" class="cursor-pointer text-sm sm:text-base">
                                            {{ t('Create New Series or Add to Existing') }}
                                        </Label>
                                    </div>
                                    
                                    <template v-if="createNewSeries">
                                        <!-- Option to create new series or select existing -->
                                        <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">
                                            <div class="flex flex-col space-y-1.5">
                                                <Label class="text-sm sm:text-base">{{ t('New Series Title') }}</Label>
                                                <Input
                                                    v-model="newSeriesTitle"
                                                    placeholder="Enter new series title..."
                                                />
                                            </div>
                                            <div class="flex flex-col space-y-1.5">
                                                <Label class="text-sm sm:text-base">{{ t('Or Select Existing Series') }}</Label>
                                                <Select v-model="selectedSeriesId">
                                                    <SelectTrigger>
                                                        <SelectValue :placeholder="t('Select Series')" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>{{ t('Available Series') }}</SelectLabel>
                                                            <template v-if="props.series && props.series.length > 0">
                                                                <SelectItem 
                                                                    v-for="s in props.series" 
                                                                    :key="s.id" 
                                                                    :value="s.id.toString()"
                                                                >
                                                                    {{ s.title }}
                                                                </SelectItem>
                                                            </template>
                                                            <SelectItem v-else value="none" disabled>
                                                                {{ t('No series available') }}
                                                            </SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                        <input v-if="selectedSeriesId" type="hidden" name="series_id" :value="selectedSeriesId" />
                                        
                                        <div v-if="newSeriesTitle" class="flex flex-col space-y-1.5">
                                            <Label class="text-sm sm:text-base">{{ t('Series Description') }}</Label>
                                            <Textarea
                                                v-model="newSeriesDescription"
                                                placeholder="Enter series description..."
                                                rows="2"
                                            />
                                        </div>
                                        
                                        <input v-if="newSeriesTitle" type="hidden" name="new_series_title" :value="newSeriesTitle" />
                                        <input v-if="newSeriesDescription" type="hidden" name="new_series_description" :value="newSeriesDescription" />
                                        
                                        <div class="flex flex-col space-y-1.5">
                                            <Label class="text-sm sm:text-base">{{ t('Episode Number') }}</Label>
                                            <Input
                                                type="number"
                                                name="episode_number"
                                                :placeholder="editableLesson.episode_number?.toString() || '1'"
                                                :default-value="editableLesson.episode_number?.toString()"
                                                min="1"
                                            />
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Scripture Reference Help -->
                                <div class="col-span-1 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-950 sm:p-4 md:col-span-2 lg:col-span-4">
                                    <h4 class="mb-2 text-sm font-semibold text-blue-900 dark:text-blue-100 sm:text-base">
                                        {{ t('Using Scripture References') }}
                                    </h4>
                                    <div class="space-y-2 text-xs text-blue-800 dark:text-blue-200 sm:text-sm">
                                        <p>
                                            <strong>{{ t('Short References') }}:</strong> Use single quotes 'BOOK CHAPTER:VERSE' to add clickable references.
                                        </p>
                                        <p class="ml-4 italic">{{ t('Example') }}: 'GEN 1:1' or '2KI 2:2'</p>
                                        <p class="mt-2">
                                            <strong>{{ t('Full Verses') }}:</strong> Use triple quotes '''BOOK CHAPTER:VERSE''' to insert the full verse text.
                                        </p>
                                        <p class="ml-4 italic">{{ t('Example') }}: '''JHN 3:16''' will be replaced with the actual verse</p>
                                    </div>
                                </div>
                                
                                <div v-for="(paragraph, idx) in editableLesson.paragraphs" :key="`paragraph-${idx}`" class="col-span-1 md:col-span-2 lg:col-span-4">
                                    <div class="flex flex-col space-y-1.5">
                                        <Label :for="`text-${idx}`" class="text-sm sm:text-base">Paragraph {{ idx + 1 }}</Label>
                                        <Textarea
                                            :id="`text-${idx}`"
                                            :name="`paragraphs[${idx}][text]`"
                                            type="text"
                                            placeholder="Paragraph Text goes here... You can use 'GEN 1:1' for short references or '''JHN 3:16''' for full verses."
                                            v-model="editableLesson.paragraphs[idx].text"
                                            rows="4"
                                            class="text-sm sm:text-base"
                                        />
                                        <InputError :message="errors[`paragraphs.${idx}.text`]" />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="mt-4 flex justify-between px-3 pb-3 sm:mt-6 sm:px-6 sm:pb-4">
                            <Button
                                variant="outline"
                                :class="{ 'opacity-25': processing }"
                                :disabled="processing"
                                class="text-sm sm:text-base"
                            >
                                <LoaderCircle
                                    v-if="processing"
                                    class="h-4 w-4 animate-spin"
                                />
                                {{ t('Edit Lesson') }}
                            </Button>
                        </CardFooter>
                    </Form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
