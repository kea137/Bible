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
}>();

type LessonType = {
    id: number;
    title: string;
    description: string;
    language: string;
    readable: boolean;
    no_paragraphs: number;
    paragraphs: {
        text: string;
    }[];
}

const editableLesson = ref<LessonType>({ ...props.lesson });

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
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-4">
                <Card class="col-span-1 md:col-span-4">
                    <Form
                        v-bind="LessonController.update.form(props.lesson.id)"
                        v-slot="{ errors, processing }"
                    >
                        <CardHeader>
                            <CardTitle>{{ t('Edit Lesson') }}</CardTitle>
                            <CardDescription
                                >{{ t('Edit Lesson by filling out the form below.') }}</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                class="mt-2 grid grid-cols-1 gap-4 md:grid-cols-4"
                            >
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="name">{{ t('Title') }}</Label>
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
                                    <Label for="language">{{ t('Language') }}</Label>
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
                                    <Label for="readable">{{ t('Readable') }}</Label>
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
                                    <Label for="no_paragraphs">{{ t('Number of Paragraphs') }}</Label>
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
                                    class="col-span-2 flex flex-col space-y-1.5"
                                >
                                    <Label for="description">{{ t('Description') }}</Label>
                                    <Textarea
                                        id="description"
                                        name="description"
                                        :tabindex="3"
                                        :placeholder="t('Description of the Lesson')"
                                        v-model="editableLesson.description"
                                    />
                                    <InputError :message="errors.description" />
                                </div>
                                
                                <!-- Scripture Reference Help -->
                                <div class="col-span-4 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950">
                                    <h4 class="mb-2 font-semibold text-blue-900 dark:text-blue-100">
                                        {{ t('Using Scripture References') }}
                                    </h4>
                                    <div class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
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
                                
                                <div v-for="(paragraph, idx) in editableLesson.paragraphs" :key="`paragraph-${idx}`" class="col-span-3">
                                    <div class="col-span-2 flex flex-col space-y-1.5">
                                        <Label :for="`text-${idx}`">Paragraph {{ idx + 1 }}</Label>
                                        <Textarea
                                            :id="`text-${idx}`"
                                            :name="`paragraphs[${idx}][text]`"
                                            type="text"
                                            placeholder="Paragraph Text goes here... You can use 'GEN 1:1' for short references or '''JHN 3:16''' for full verses."
                                            v-model="editableLesson.paragraphs[idx].text"
                                            rows="4"
                                        />
                                        <InputError :message="errors[`paragraphs.${idx}.text`]" />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="mt-6 flex justify-between px-6 pb-4">
                            <Button
                                variant="outline"
                                :class="{ 'opacity-25': processing }"
                                :disabled="processing"
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
