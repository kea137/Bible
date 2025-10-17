<script setup lang="ts">
import ReferenceController from '@/actions/App/Http/Controllers/ReferenceController';
import AlertUser from '@/components/AlertUser.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { references, bibles } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle, UploadCloudIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    bibles: {
        id: number;
        name: string;
        abbreviation: string;
        language: string;
    }[];
    selected_bible: {
        id: number;
        name: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Bibles',
        href: bibles().url,
    },
    {
        title: 'Create References',
        href: references({ bible: props.selected_bible.id }).url,
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
    <Head title="Create References" />
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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-4">
                <Card class="col-span-1 md:col-span-4">
                    <Form v-bind="ReferenceController.store.form()" v-slot="{ errors, processing }">
                        <CardHeader>
                            <CardTitle>Upload References</CardTitle>
                            <CardDescription>
                                Upload a JSON file containing verse references for {{ selected_bible.name }}.
                                Format: {"1":{"v":"GEN 1 1","r":{"2063":"EXO 20 11",...}}}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="mt-2 grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="col-span-1 flex flex-col space-y-1.5">
                                    <Label for="bible_id">Select Bible</Label>
                                    <Select v-model="selectedBibleId">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a Bible" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Bibles</SelectLabel>
                                                <SelectItem
                                                    v-for="bible in bibles"
                                                    :key="bible.id"
                                                    :value="bible.id.toString()"
                                                >
                                                    {{ bible.name }} ({{ bible.language }})
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <input type="hidden" name="bible_id" :value="selectedBibleId" />
                                    <InputError :message="errors.bible_id" />
                                </div>
                                <div class="col-span-1 flex w-full items-center justify-center">
                                    <div
                                        :class="[
                                            'flex w-full max-w-xl flex-col items-center justify-center rounded-md border-2 border-dashed p-6 transition-colors cursor-pointer',
                                            isDragActive
                                                ? 'border-primary'
                                                : 'border-gray-100 dark:border-accent bg-white dark:bg-background',
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
                                            <p class="text-lg font-medium text-primary">Drop your JSON file here!</p>
                                        </template>
                                        <template v-else>
                                            <div class="flex items-center gap-2">
                                                <UploadCloudIcon class="h-6 w-6 text-gray-700 dark:text-white" />
                                                <p class="text-base font-medium text-gray-700 dark:text-white">
                                                    Drag & drop JSON file or click to upload
                                                </p>
                                            </div>
                                            <p v-if="selectedFiles.length > 0" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                {{ selectedFiles[0].name }}
                                            </p>
                                        </template>
                                    </div>
                                </div>
                                <InputError :message="errors.file" class="col-span-2" />
                            </div>
                        </CardContent>
                        <CardFooter class="mt-6 flex justify-between px-6 pb-4">
                            <Button type="submit" :class="{ 'opacity-25': processing }" :disabled="processing">
                                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin mr-2" />
                                Upload References
                            </Button>
                        </CardFooter>
                    </Form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
