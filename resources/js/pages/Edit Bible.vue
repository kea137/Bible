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
import { bible_edit, bibles_configure } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    bible: {
        id: number;
        name: string;
        abbreviation: string;
        language: string;
        version: string;
        description: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configure Bibles',
        href: bibles_configure().url,
    },
    {
        title: 'Edit Bible',
        href: bible_edit({ bible: props.bible.id }).url,
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
    <Head title="Edit Bible" />
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
                        v-bind="BibleController.update.form()"
                        v-slot="{ errors, processing }"
                    >
                        <CardHeader>
                            <CardTitle>Edit Bible</CardTitle>
                            <CardDescription
                                >Update the Bible information below.</CardDescription
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
                                        placeholder="Name of the Bible"
                                        :value="bible.name"
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
                                        :value="bible.abbreviation"
                                    />
                                    <InputError
                                        :message="errors.abbreviation"
                                    />
                                </div>
                                <div
                                    class="col-span-1 flex flex-col space-y-1.5"
                                >
                                    <Label for="language">Language</Label>
                                    <Select name="language" :default-value="bible.language">
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
                                                <SelectItem value="English"
                                                    >English</SelectItem
                                                >
                                                <SelectItem value="Swahili"
                                                    >Swahili</SelectItem
                                                >
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
                                        :value="bible.version"
                                    />
                                    <InputError :message="errors.version" />
                                </div>
                                <div
                                    class="col-span-4 flex flex-col space-y-1.5"
                                >
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        name="description"
                                        :tabindex="1"
                                        placeholder="Description of the Bible"
                                        :value="bible.description"
                                    />
                                    <InputError :message="errors.description" />
                                </div>
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
                                Update Bible
                            </Button>
                        </CardFooter>
                    </Form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
