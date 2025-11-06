<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import Label from '@/components/ui/label/Label.vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Download, Image as ImageIcon, Palette, Share2, Type } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import LogoImage from '/resources/images/logo-small.png';

const { t } = useI18n();
const props = defineProps<{
    verseReference: string;
    verseText: string;
    verseId: number;
    backgroundImages?: Array<{
        id: number;
        url: string;
        thumbnail: string;
        photographer: string;
        photographer_url: string;
        alt: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Share Verse'),
        href: '/share',
    },
];

const canvasRef = ref<HTMLCanvasElement | null>(null);
const imageDataUrl = ref<string>('');
const currentBackgroundIndex = ref(0);
const isGenerating = ref(false);
const shareError = ref<string>('');
const customColor1 = ref('#667eea');
const customColor2 = ref('#764ba2');
const customColor3 = ref('#f093fb');
const useCustomColors = ref(false);
const selectedFont = ref('serif');
const selectedFontSize = ref(48);
const isBoldText = ref(false);
const backgroundType = ref<'gradient' | 'image'>('gradient');
const currentImageIndex = ref(0);
const loadedImages = ref<Map<string, HTMLImageElement>>(new Map());

// Font options
const fontOptions = [
    { value: 'monospace', label: 'Monospace (Clean)' },
    { value: 'Georgia', label: 'Georgia' },
    { value: 'Arial', label: 'Arial' },
    { value: 'Verdana', label: 'Verdana' },
    { value: 'Courier New', label: 'Courier New' },
    { value: 'Comic Sans MS', label: 'Comic Sans (Fun)' },
    { value: 'Trebuchet MS', label: 'Trebuchet MS' },
    { value: 'Impact', label: 'Impact' },
    { value: 'Lucida Console', label: 'Lucida Console' },
    { value: 'Papyrus', label: 'Papyrus (Artistic)' },
    { value: 'Brush Script MT', label: 'Brush Script (Handwritten)' },
    { value: 'Tahoma', label: 'Tahoma' },
    { value: 'Garamond', label: 'Garamond' },
    { value: 'Copperplate', label: 'Copperplate' },
    { value: 'Futura', label: 'Futura' },
    { value: 'Franklin Gothic Medium', label: 'Franklin Gothic' },
    { value: 'Gill Sans', label: 'Gill Sans' },
    { value: 'Optima', label: 'Optima' },
];

// Font Size options
const fontSize_Options = [
    { value: 32, label: 'Medium (32px)' },
    { value: 40, label: 'Large (40px)' },
    { value: 48, label: 'Extra Large (48px)' },
    { value: 56, label: 'Huge (56px)' },
    { value: 64, label: 'Massive (64px)' },
];

// Beautiful background gradients with heavenly/pure essence
const backgrounds = [
    {
        type: 'gradient',
        colors: ['#3a2e5d', '#4b326e', '#2d1b3a'],
        name: 'Divine Purple',
    },
    {
        type: 'gradient',
        colors: ['#1a2a6c', '#1e3c72', '#2a5298'],
        name: 'Heavenly Blue',
    },
    {
        type: 'gradient',
        colors: ['#6a0572', '#ab2187', '#430a5d'],
        name: 'Grace Pink',
    },
    {
        type: 'gradient',
        colors: ['#232526', '#414345', '#2c3e50'],
        name: 'Soft Heaven',
    },
    {
        type: 'gradient',
        colors: ['#3a6186', '#89253e', '#1f1c2c'],
        name: 'Peaceful Dawn',
    },
    {
        type: 'gradient',
        colors: ['#232526', '#414345', '#434343'],
        name: 'Serenity',
    },
    {
        type: 'gradient',
        colors: ['#42275a', '#734b6d', '#2b5876'],
        name: 'Rose Garden',
    },
    {
        type: 'gradient',
        colors: ['#16222a', '#3a6073', '#1a2980'],
        name: 'Ocean Breeze',
    },
    {
        type: 'gradient',
        colors: ['#2c3e50', '#fd746c', '#1a1a1a'],
        name: 'Sunset Glory',
    },
    {
        type: 'gradient',
        colors: ['#41295a', '#2F0743', '#1e1e2f'],
        name: 'Cosmic Purple',
    },
    {
        type: 'gradient',
        colors: ['#232526', '#1a2980', '#283e51'],
        name: 'Sky Blue',
    },
    {
        type: 'gradient',
        colors: ['#ff5858', '#6a0572', '#2c3e50'],
        name: 'Coral Sunset',
    },
    {
        type: 'gradient',
        colors: ['#134E5E', '#71B280', '#0f2027'],
        name: 'Fresh Green',
    },
    {
        type: 'gradient',
        colors: ['#cb2d3e', '#ef473a', '#232526'],
        name: 'Fiery Love',
    },
    {
        type: 'gradient',
        colors: ['#4a00e0', '#8e2de2', '#2c3e50'],
        name: 'Royal Purple',
    },
];

const currentBackground = computed(() => {
    if (useCustomColors.value) {
        return {
            type: 'gradient',
            colors: [
                customColor1.value,
                customColor2.value,
                customColor3.value,
            ],
            name: 'Custom',
        };
    }
    return backgrounds[currentBackgroundIndex.value];
});

function wrapText(
    ctx: CanvasRenderingContext2D,
    text: string,
    maxWidth: number,
): string[] {
    if (!text?.trim()) {
        return [''];
    }

    const words = text.split(' ');
    const lines: string[] = [];
    let currentLine = words[0];

    for (let i = 1; i < words.length; i++) {
        const word = words[i];
        const width = ctx.measureText(currentLine + ' ' + word).width;
        if (width < maxWidth) {
            currentLine += ' ' + word;
        } else {
            lines.push(currentLine);
            currentLine = word;
        }
    }
    lines.push(currentLine);
    return lines;
}

function generateImage() {
    if (!canvasRef.value) return;

    isGenerating.value = true;

    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Set canvas size (1080x1080 for Instagram, good for most platforms)
    canvas.width = 1080;
    canvas.height = 1080;

    const drawTextAndLogo = () => {
        // Add subtle overlay for better text readability
        ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Set text properties
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
        ctx.shadowBlur = 15;
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 2;

        // Draw verse text
        const maxWidth = canvas.width - 200;
        const lineHeight = 60;
        const fontWeight = isBoldText.value ? 'bold' : 'normal';
        ctx.font = `${fontWeight} ${selectedFontSize.value}px ${selectedFont.value}`;

        const lines = wrapText(ctx, props.verseText, maxWidth);
        const totalTextHeight = lines.length * lineHeight;
        let y = (canvas.height - totalTextHeight) / 2;

        lines.forEach((line) => {
            ctx.fillText(line, canvas.width / 2, y);
            y += lineHeight;
        });

        // Draw reference
        ctx.font = `bold ${selectedFontSize.value}px ${selectedFont.value}`;
        ctx.fillText(props.verseReference, canvas.width / 2, y + 80);

        // Draw decorative elements
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.shadowBlur = 5;
        const decorPadding = 150;
        ctx.beginPath();
        ctx.moveTo(decorPadding, y + 120);
        ctx.lineTo(canvas.width - decorPadding, y + 120);
        ctx.stroke();

        // Function to finalize the image
        const finalizeImage = () => {
            imageDataUrl.value = canvas.toDataURL('image/png', 1.0);
            isGenerating.value = false;
        };

        // Load and draw logo badge at bottom right corner
        const logo = new Image();
        logo.onload = () => {
            // Logo size (small rounded square badge)
            const logoSize = 80;
            const margin = 30;
            const logoX = canvas.width - logoSize - margin;
            const logoY = canvas.height - logoSize - margin;
            const borderRadius = 15;

            // Reset shadow for logo
            ctx.shadowColor = 'transparent';
            ctx.shadowBlur = 0;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 0;

            // Draw semi-transparent white background for logo
            ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
            ctx.beginPath();
            ctx.roundRect(logoX, logoY, logoSize, logoSize, borderRadius);
            ctx.fill();

            // Add subtle shadow around logo background
            ctx.shadowColor = 'rgba(0, 0, 0, 0.2)';
            ctx.shadowBlur = 10;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 2;
            ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
            ctx.beginPath();
            ctx.roundRect(logoX, logoY, logoSize, logoSize, borderRadius);
            ctx.fill();

            // Reset shadow for drawing logo
            ctx.shadowColor = 'transparent';
            ctx.shadowBlur = 0;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 0;

            // Draw logo with some padding inside the rounded square
            const logoPadding = 10;
            ctx.save();
            ctx.beginPath();
            ctx.roundRect(logoX, logoY, logoSize, logoSize, borderRadius);
            ctx.clip();
            ctx.drawImage(
                logo,
                logoX + logoPadding,
                logoY + logoPadding,
                logoSize - logoPadding * 2,
                logoSize - logoPadding * 2,
            );
            ctx.restore();

            // Finalize the image
            finalizeImage();
        };

        logo.onerror = () => {
            // If logo fails to load, still generate the image without it
            console.warn('Logo failed to load, generating image without logo');
            finalizeImage();
        };

        // Set logo source - using the imported logo
        logo.src = LogoImage;
    };

    // Draw background based on type
    if (backgroundType.value === 'image' && props.backgroundImages && props.backgroundImages.length > 0) {
        const currentImage = props.backgroundImages[currentImageIndex.value];
        const imageUrl = currentImage.url;
        
        // Check if image is already loaded
        if (loadedImages.value.has(imageUrl)) {
            const bgImg = loadedImages.value.get(imageUrl)!;
            ctx.drawImage(bgImg, 0, 0, canvas.width, canvas.height);
            drawTextAndLogo();
        } else {
            // Load the image
            const bgImg = new Image();
            // Note: We try crossOrigin first, but handle CORS errors gracefully
            bgImg.crossOrigin = 'anonymous';
            bgImg.onload = () => {
                loadedImages.value.set(imageUrl, bgImg);
                ctx.drawImage(bgImg, 0, 0, canvas.width, canvas.height);
                drawTextAndLogo();
            };
            bgImg.onerror = () => {
                console.error('Failed to load background image from Pexels');
                // Try loading without crossOrigin as fallback
                const bgImgRetry = new Image();
                bgImgRetry.onload = () => {
                    try {
                        loadedImages.value.set(imageUrl, bgImgRetry);
                        ctx.drawImage(bgImgRetry, 0, 0, canvas.width, canvas.height);
                        drawTextAndLogo();
                    } catch (e) {
                        console.error('Canvas tainted, using gradient fallback', e);
                        // Draw gradient as fallback without changing UI state
                        const tempGradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
                        const colors = currentBackground.value.colors;
                        colors.forEach((color, index) => {
                            tempGradient.addColorStop(index / (colors.length - 1), color);
                        });
                        ctx.fillStyle = tempGradient;
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        drawTextAndLogo();
                    }
                };
                bgImgRetry.onerror = () => {
                    console.error('Failed to load image even without CORS, using gradient');
                    // Draw gradient as fallback without changing UI state
                    const tempGradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
                    const colors = currentBackground.value.colors;
                    colors.forEach((color, index) => {
                        tempGradient.addColorStop(index / (colors.length - 1), color);
                    });
                    ctx.fillStyle = tempGradient;
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    drawTextAndLogo();
                };
                bgImgRetry.src = imageUrl;
            };
            bgImg.src = imageUrl;
        }
    } else {
        // Create gradient background
        const gradient = ctx.createLinearGradient(
            0,
            0,
            canvas.width,
            canvas.height,
        );
        const colors = currentBackground.value.colors;
        colors.forEach((color, index) => {
            gradient.addColorStop(index / (colors.length - 1), color);
        });

        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        drawTextAndLogo();
    }
}

function changeBackground() {
    if (backgroundType.value === 'image' && props.backgroundImages && props.backgroundImages.length > 0) {
        currentImageIndex.value = (currentImageIndex.value + 1) % props.backgroundImages.length;
    } else {
        currentBackgroundIndex.value = (currentBackgroundIndex.value + 1) % backgrounds.length;
    }
    generateImage();
}

function downloadImage(platform?: string) {
    const link = document.createElement('a');
    const timestamp = new Date().getTime();
    const sanitizedRef = props.verseReference.replace(/[^a-z0-9]/gi, '_');

    let filename = `verse_${sanitizedRef}_${timestamp}.png`;
    if (platform) {
        filename = `${platform}_${filename}`;
    }

    link.download = filename;
    link.href = imageDataUrl.value;
    link.click();
}

async function shareImage() {
    shareError.value = '';
    try {
        // Convert data URL to blob
        const response = await fetch(imageDataUrl.value);
        const blob = await response.blob();

        // Use Web Share API if available
        if (navigator.share) {
            const file = new File([blob], 'verse.png', { type: 'image/png' });
            await navigator.share({
                title: props.verseReference,
                text: props.verseText,
                files: [file],
            });
        } else {
            // Fallback: just download
            shareError.value =
                'Native sharing not supported on this device. Downloading image instead.';
            downloadImage();
        }
    } catch (error) {
        console.error('Error sharing:', error);
        if (error instanceof Error && error.name === 'AbortError') {
            // User cancelled the share, no error message needed
            return;
        }
        // Show error and fallback to download
        shareError.value =
            'Failed to share image. Downloading instead. Error: ' +
            (error instanceof Error ? error.message : 'Unknown error');
        downloadImage();
    }
}

onMounted(() => {
    generateImage();
});

// Watch for changes in customization options
watch(
    [
        customColor1,
        customColor2,
        customColor3,
        selectedFont,
        selectedFontSize,
        isBoldText,
        backgroundType,
        currentImageIndex,
    ],
    () => {
        generateImage();
    },
);
</script>

<template>
    <Head :title="t('Share Verse')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Share2 class="h-5 w-5" />
                        {{ t('Share Verse') }}
                    </CardTitle>
                    <CardDescription
                        >{{
                            t('Create a beautiful image to share your favorite')
                        }}
                        {{ t('verse') }}</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Canvas Preview -->
                        <div class="flex flex-col gap-4">
                            <div
                                class="flex items-center justify-center rounded-lg border bg-muted p-4"
                            >
                                <canvas
                                    ref="canvasRef"
                                    class="max-h-[500px] max-w-full rounded-lg shadow-lg"
                                    style="display: block"
                                ></canvas>
                            </div>

                            <!-- Background Style Selection -->
                            <div class="flex flex-col gap-2">
                                <!-- Background Type Toggle -->
                                <div class="rounded-lg border p-4">
                                    <div class="mb-3 flex items-center gap-2">
                                        <ImageIcon class="h-4 w-4" />
                                        <h3 class="font-semibold">
                                            {{ t('Background Type') }}
                                        </h3>
                                    </div>
                                    <div class="mb-3 flex gap-2">
                                        <Button
                                            @click="backgroundType = 'gradient'"
                                            :variant="backgroundType === 'gradient' ? 'default' : 'outline'"
                                            size="sm"
                                            class="flex-1"
                                        >
                                            <Palette class="mr-2 h-4 w-4" />
                                            {{ t('Gradient') }}
                                        </Button>
                                        <Button
                                            @click="backgroundType = 'image'"
                                            :variant="backgroundType === 'image' ? 'default' : 'outline'"
                                            size="sm"
                                            class="flex-1"
                                            :disabled="!backgroundImages || backgroundImages.length === 0"
                                        >
                                            <ImageIcon class="mr-2 h-4 w-4" />
                                            {{ t('Image') }}
                                        </Button>
                                    </div>
                                    <p class="text-xs text-muted-foreground" v-if="backgroundType === 'image' && backgroundImages && backgroundImages.length > 0">
                                        {{ t('Using serene nature images from Pexels') }}
                                    </p>
                                    <p class="text-xs text-yellow-600 dark:text-yellow-400" v-if="!backgroundImages || backgroundImages.length === 0">
                                        {{ t('No Pexels API key configured. Add PEXELS_API_KEY to your .env file to enable image backgrounds. Run "php artisan config:clear" after adding it.') }}
                                    </p>
                                </div>

                                <p class="text-sm text-muted-foreground" v-if="backgroundType === 'gradient'">
                                    {{ t('Current Style:') }}
                                    <span class="font-semibold">{{
                                        currentBackground.name
                                    }}</span>
                                </p>
                                <p class="text-sm text-muted-foreground" v-else-if="backgroundImages && backgroundImages.length > 0">
                                    {{ t('Image by') }}
                                    <a :href="backgroundImages[currentImageIndex].photographer_url" target="_blank" class="font-semibold underline">
                                        {{ backgroundImages[currentImageIndex].photographer }}
                                    </a>
                                    {{ t('on Pexels') }}
                                </p>
                                <Button
                                    @click="changeBackground"
                                    variant="outline"
                                    :disabled="isGenerating || (backgroundType === 'gradient' && useCustomColors)"
                                    class="w-full"
                                >
                                    {{ backgroundType === 'image' ? t('Change Image') : t('Change Background Style') }}
                                </Button>
                            </div>

                            <!-- Custom Colors (only show for gradients) -->
                            <div class="rounded-lg border p-4" v-if="backgroundType === 'gradient'">
                                <div class="mb-3 flex items-center gap-2">
                                    <Palette class="h-4 w-4" />
                                    <h3 class="font-semibold">
                                        {{ t('Custom Colors') }}
                                    </h3>
                                </div>
                                <div class="mb-3 flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        v-model="useCustomColors"
                                        id="useCustomColors"
                                        class="h-4 w-4"
                                    />
                                    <Label for="useCustomColors">{{
                                        t('Use custom color mix')
                                    }}</Label>
                                </div>
                                <div
                                    v-if="useCustomColors"
                                    class="grid grid-cols-3 gap-2"
                                >
                                    <div>
                                        <Label class="text-xs">{{
                                            t('Color 1')
                                        }}</Label>
                                        <input
                                            type="color"
                                            v-model="customColor1"
                                            class="h-10 w-full cursor-pointer rounded border"
                                        />
                                    </div>
                                    <div>
                                        <Label class="text-xs">{{
                                            t('Color 2')
                                        }}</Label>
                                        <input
                                            type="color"
                                            v-model="customColor2"
                                            class="h-10 w-full cursor-pointer rounded border"
                                        />
                                    </div>
                                    <div>
                                        <Label class="text-xs">{{
                                            t('Color 3')
                                        }}</Label>
                                        <input
                                            type="color"
                                            v-model="customColor3"
                                            class="h-10 w-full cursor-pointer rounded border"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Font Selection -->
                            <div class="rounded-lg border p-4">
                                <div class="mb-3 flex items-center gap-2">
                                    <Type class="h-4 w-4" />
                                    <h3 class="font-semibold">
                                        {{ t('Text Style') }}
                                    </h3>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex flex-row">
                                        <div class="w-full">
                                            <Label class="text-sm">{{
                                                t('Font Family')
                                            }}</Label>
                                            <Select v-model="selectedFont">
                                                <SelectTrigger class="w-full">
                                                    <SelectValue
                                                        :placeholder="
                                                            t('Select a font')
                                                        "
                                                    />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem
                                                            v-for="font in fontOptions"
                                                            :key="font.value"
                                                            :value="font.value"
                                                        >
                                                            {{ font.label }}
                                                        </SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="mx-4 w-full">
                                            <Label class="text-sm">{{
                                                t('Font Size')
                                            }}</Label>
                                            <Select v-model="selectedFontSize">
                                                <SelectTrigger class="w-full">
                                                    <SelectValue
                                                        :placeholder="
                                                            t('Select size')
                                                        "
                                                    />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem
                                                            v-for="font in fontSize_Options"
                                                            :key="font.value"
                                                            :value="font.value"
                                                        >
                                                            {{ font.label }}
                                                        </SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            v-model="isBoldText"
                                            id="boldText"
                                            class="h-4 w-4"
                                        />
                                        <Label for="boldText">{{
                                            t('Use bold text')
                                        }}</Label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-4">
                            <div>
                                <h3 class="mb-2 font-semibold">
                                    {{ t('Download for Platforms') }}
                                </h3>
                                <p class="mb-4 text-sm text-muted-foreground">
                                    {{
                                        t(
                                            'Download optimized images for social media',
                                        )
                                    }}
                                </p>
                                <div class="grid grid-cols-1 gap-2">
                                    <Button
                                        @click="downloadImage('instagram')"
                                        variant="outline"
                                        size="sm"
                                        :disabled="!imageDataUrl"
                                    >
                                        <Download class="mr-2 h-4 w-4" />
                                        {{ t('Download Image') }}
                                    </Button>
                                </div>
                            </div>

                            <div class="rounded-lg border p-4">
                                <h3 class="mb-2 font-semibold">
                                    {{ t('Share Directly') }}
                                </h3>
                                <p class="mb-4 text-sm text-muted-foreground">
                                    {{
                                        t(
                                            'Share the image directly from your device',
                                        )
                                    }}
                                    {{ t('using the native share menu') }}
                                </p>
                                <Button
                                    @click="shareImage"
                                    class="w-full"
                                    :disabled="!imageDataUrl"
                                >
                                    <Share2 class="mr-2 h-4 w-4" />
                                    {{ t('Share Image') }}
                                </Button>
                                <p
                                    v-if="shareError"
                                    class="mt-2 text-sm text-yellow-600 dark:text-yellow-400"
                                >
                                    {{ shareError }}
                                </p>
                            </div>

                            <div class="rounded-lg border p-4">
                                <h3 class="mb-2 font-semibold">
                                    {{ t('Verse Details') }}
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-semibold">{{
                                            t('Reference:')
                                        }}</span>
                                        {{ verseReference }}
                                    </div>
                                    <div>
                                        <span class="font-semibold">{{
                                            t('Text:')
                                        }}</span>
                                        <p class="mt-1 text-muted-foreground">
                                            {{ verseText }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
