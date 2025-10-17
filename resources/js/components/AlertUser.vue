<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { ref, watch } from 'vue';

interface Props {
    open?: boolean;
    title?: string;
    message?: string;
    confirmButtonText?: string;
    variant?: 'success' | 'error' | 'warning' | 'info';
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
    title: 'Notification',
    confirmButtonText: 'OK',
    variant: 'info',
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isOpen = ref(props.open);

watch(
    () => props.open,
    (newVal) => {
        isOpen.value = newVal;
    },
);

watch(isOpen, (newVal) => {
    emit('update:open', newVal);
});

function handleClose() {
    isOpen.value = false;
}
</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ message }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogAction @click="handleClose">{{
                    confirmButtonText
                }}</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
