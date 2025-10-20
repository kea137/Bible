<script setup lang="ts">
import BibleController from '@/actions/App/Http/Controllers/BibleController';
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
import { bible_create, bibles_configure } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle, UploadCloudIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configure Bibles',
        href: bibles_configure().url,
    },

    {
        title: 'Create Bible',
        href: bible_create().url,
    },
];

const props = defineProps<{
    languages: {
        id: number;
        name: string;
        code: string;
    }[];
}>();

const isDragActive = ref(false);
const selectedFiles = ref<File[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);

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
        // You can now process the selectedFiles (e.g., upload them)
    }
};

const openFilePicker = () => {
    fileInput.value?.click();
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        selectedFiles.value = Array.from(target.files);
        // You can now process the selectedFiles (e.g., upload them)
    }
};

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
    <Head title="Create Bible" />
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
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-4">
                <Card class="col-span-1 md:col-span-4">
                    <Form
                        v-bind="BibleController.store.form()"
                        v-slot="{ errors, processing }"
                    >
                        <CardHeader>
                            <CardTitle>Create Bible</CardTitle>
                            <CardDescription
                                >Create a new Bible by filling out the form
                                below.</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                class="mt-2 grid grid-cols-1 gap-4 md:grid-cols-4"
                            >
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="name">Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        :tabindex="1"
                                        type="text"
                                        placeholder="Name of the Course"
                                    />
                                    <InputError :message="errors.name" />
                                </div>
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="abbreviation"
                                        >Abbreviation</Label
                                    >
                                    <Input
                                        id="abbreviation"
                                        name="abbreviation"
                                        :tabindex="1"
                                        type="text"
                                        placeholder="Abbreviation of the Bible"
                                    />
                                    <InputError
                                        :message="errors.abbreviation"
                                    />
                                </div>
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="language">Language</Label>
                                    <Select name="language">
                                        <SelectTrigger id="language">
                                            <SelectValue
                                                placeholder="Select Language"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel
                                                    >Languages</SelectLabel
                                                >
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
                                    <Label for="version">Version</Label>
                                    <Input
                                        id="version"
                                        name="version"
                                        :tabindex="1"
                                        type="text"
                                        placeholder="Version of the Bible"
                                    />
                                    <InputError :message="errors.version" />
                                </div>
                                <div
                                    class="col-span-2 flex flex-col space-y-1.5"
                                >
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        name="description"
                                        :tabindex="1"
                                        placeholder="Description of the Bible"
                                    />
                                    <InputError :message="errors.description" />
                                </div>
                                <div
                                    class="col-span-2 mt-6 flex w-full items-center justify-center"
                                >
                                    <div
                                        :class="[
                                            'flex w-full max-w-xl flex-col items-center justify-center rounded-md border-2 border-dashed p-6 transition-colors',
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
                                            multiple
                                        />
                                        <template v-if="isDragActive">
                                            <p
                                                class="text-lg font-medium text-primary"
                                            >
                                                Drop your files here!
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
                                                    Drag & drop the Bible json
                                                    file or click to upload
                                                </p>
                                            </div>
                                            <p
                                                v-if="selectedFiles.length > 0"
                                                class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{ selectedFiles.length }}
                                                file selected
                                            </p>
                                        </template>
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
                                Create Bible
                            </Button>
                        </CardFooter>
                    </Form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
