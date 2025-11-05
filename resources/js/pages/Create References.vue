<script setup lang="ts">
import ReferenceController from '@/actions/App/Http/Controllers/ReferenceController';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { references_configure, references_create } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle, UploadCloudIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps<{
    bibles: Array<{
        id: number;
        name: string;
        abbreviation: string;
        language: string;
    }>;
    selected_bible: {
        id: number;
        name: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Configure References'),
        href: references_configure().url,
    },
    {
        title: t('Upload References'),
        href: references_create().url,
    },
];

const isDragActive = ref(false);
const selectedFiles = ref<File[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const selectedBibleId = ref(props.selected_bible.id.toString());

const handleDragOver = () => {
    isDragActive.value = true;
};

const handleDragLeave = () => {
    isDragActive.value = false;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragActive.value = false;
    if (event.dataTransfer?.files) {
        selectedFiles.value = Array.from(event.dataTransfer.files);
    }
};

const openFilePicker = () => {
    fileInput.value?.click();
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        selectedFiles.value = Array.from(target.files);
    }
};

const page = usePage();
const successMessage = computed(() => page.props.success as string);
const errorMessage = computed(() => page.props.error as string);
const alertSuccess = ref(!!successMessage.value);
const alertError = ref(!!errorMessage.value);
const alertErrorMessage = ref('');
</script>

<template>
    <Head :title="t('Create References')" />

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
                        v-bind="ReferenceController.store.form()"
                        v-slot="{ errors, processing }"
                    >
                        <CardHeader>
                            <CardTitle>{{ t('Upload References') }}</CardTitle>
                            <CardDescription>
                                {{
                                    t(
                                        'Upload a JSON file containing verse references',
                                    )
                                }}.
                                {{ t('Ensure the file') }}
                                {{ t('is properly formatted.') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="mt-2 grid grid-cols-1 gap-4 md:grid-cols-2"
                            >
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="bible_id">{{
                                        t('Select Bible')
                                    }}</Label>
                                    <Select v-model="selectedBibleId">
                                        <SelectTrigger>
                                            <SelectValue
                                                :placeholder="
                                                    t('Select a Bible')
                                                "
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>{{
                                                    t('Bibles')
                                                }}</SelectLabel>
                                                <SelectItem
                                                    v-for="bible in props.bibles"
                                                    :key="bible.id"
                                                    :value="bible.id.toString()"
                                                >
                                                    {{ bible.name }} ({{
                                                        bible.language
                                                    }})
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <input
                                        type="hidden"
                                        name="bible_id"
                                        :value="selectedBibleId"
                                    />
                                    <InputError :message="errors.bible_id" />
                                </div>
                                <div
                                    class="col-span-1 flex w-full items-center justify-center"
                                >
                                    <div
                                        :class="[
                                            'flex w-full max-w-xl cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed p-6 transition-colors',
                                            isDragActive
                                                ? 'border-primary'
                                                : 'border-gray-100 bg-white dark:border-accent dark:bg-background',
                                        ]"
                                        @dragover.prevent="handleDragOver"
                                        @dragleave="handleDragLeave"
                                        @drop="handleDrop"
                                        @click="openFilePicker"
                                    >
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            name="file"
                                            id="file"
                                            class="hidden"
                                            @change="handleFileChange"
                                            accept=".json"
                                        />
                                        <template v-if="isDragActive">
                                            <p
                                                class="text-lg font-medium text-primary"
                                            >
                                                {{
                                                    t(
                                                        'Drop your JSON file here!',
                                                    )
                                                }}
                                            </p>
                                        </template>
                                        <template v-else>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <UploadCloudIcon
                                                    class="h-6 w-6 text-gray-700 dark:text-white"
                                                />
                                                <p
                                                    class="text-base font-medium text-gray-700 dark:text-white"
                                                >
                                                    {{
                                                        t(
                                                            'Drag & drop JSON file or',
                                                        )
                                                    }}
                                                    {{ t('click to upload') }}
                                                </p>
                                            </div>
                                            <p
                                                v-if="selectedFiles.length > 0"
                                                class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{ selectedFiles[0].name }}
                                            </p>
                                        </template>
                                    </div>
                                </div>
                                <InputError
                                    :message="errors.file"
                                    class="col-span-2"
                                />
                            </div>
                        </CardContent>
                        <CardFooter class="mt-6 flex justify-between px-6 pb-4">
                            <Button
                                type="submit"
                                :class="{ 'opacity-25': processing }"
                                :disabled="processing"
                            >
                                <LoaderCircle
                                    v-if="processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                {{ t('Upload References') }}
                            </Button>
                        </CardFooter>
                    </Form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
