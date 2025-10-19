<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Download, Share2 } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    verseReference: string;
    verseText: string;
    verseId: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Share Verse',
        href: '/share',
    },
];

const canvasRef = ref<HTMLCanvasElement | null>(null);
const imageDataUrl = ref<string>('');
const currentBackgroundIndex = ref(0);
const isGenerating = ref(false);
const shareError = ref<string>('');

// Beautiful background gradients with heavenly/pure essence
const backgrounds = [
    {
        type: 'gradient',
        colors: ['#667eea', '#764ba2', '#f093fb'],
        name: 'Divine Purple',
    },
    {
        type: 'gradient',
        colors: ['#4facfe', '#00f2fe', '#43e97b'],
        name: 'Heavenly Blue',
    },
    {
        type: 'gradient',
        colors: ['#fa709a', '#fee140', '#fbc2eb'],
        name: 'Grace Pink',
    },
    {
        type: 'gradient',
        colors: ['#a8edea', '#fed6e3', '#ffecd2'],
        name: 'Soft Heaven',
    },
    {
        type: 'gradient',
        colors: ['#ff9a9e', '#fecfef', '#ffecd2'],
        name: 'Peaceful Dawn',
    },
    {
        type: 'gradient',
        colors: ['#ffecd2', '#fcb69f', '#a1c4fd'],
        name: 'Serenity',
    },
];

const currentBackground = computed(
    () => backgrounds[currentBackgroundIndex.value],
);

function wrapText(
    ctx: CanvasRenderingContext2D,
    text: string,
    maxWidth: number,
): string[] {
    if (!text || text.trim() === '') {
        return [''];
    }

    const words = text.split(' ');
    const lines: string[] = [];

    if (words.length === 0) {
        return [''];
    }

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

    // Create gradient background
    const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
    const colors = currentBackground.value.colors;
    colors.forEach((color, index) => {
        gradient.addColorStop(index / (colors.length - 1), color);
    });

    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Add subtle overlay for better text readability
    ctx.fillStyle = 'rgba(255, 255, 255, 0.1)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Set text properties
    ctx.fillStyle = '#ffffff';
    ctx.textAlign = 'center';
    ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
    ctx.shadowBlur = 10;
    ctx.shadowOffsetX = 2;
    ctx.shadowOffsetY = 2;

    // Draw verse text
    const maxWidth = canvas.width - 200;
    const lineHeight = 60;
    const fontSize = 48;
    ctx.font = `${fontSize}px serif`;

    const lines = wrapText(ctx, props.verseText, maxWidth);
    const totalTextHeight = lines.length * lineHeight;
    let y = (canvas.height - totalTextHeight) / 2;

    lines.forEach((line) => {
        ctx.fillText(line, canvas.width / 2, y);
        y += lineHeight;
    });

    // Draw reference
    ctx.font = 'bold 36px sans-serif';
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

    // Convert to data URL
    imageDataUrl.value = canvas.toDataURL('image/png', 1.0);
    isGenerating.value = false;
}

function changeBackground() {
    currentBackgroundIndex.value =
        (currentBackgroundIndex.value + 1) % backgrounds.length;
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
</script>

<template>
    <Head title="Share Verse" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Share2 class="h-5 w-5" />
                        Share Verse
                    </CardTitle>
                    <CardDescription
                        >Create a beautiful image to share your favorite
                        verse</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div
                        class="grid gap-6 lg:grid-cols-2"
                    >
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
                            <div class="flex flex-col gap-2">
                                <p class="text-sm text-muted-foreground">
                                    Current Style:
                                    <span class="font-semibold">{{
                                        currentBackground.name
                                    }}</span>
                                </p>
                                <Button
                                    @click="changeBackground"
                                    variant="outline"
                                    :disabled="isGenerating"
                                    class="w-full"
                                >
                                    Change Background Style
                                </Button>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-4">
                            <div>
                                <h3 class="mb-2 font-semibold">
                                    Download for Platforms
                                </h3>
                                <p class="mb-4 text-sm text-muted-foreground">
                                    Download optimized images for different
                                    social media platforms
                                </p>
                                <div
                                    class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                                >
                                    <Button
                                        @click="downloadImage('instagram')"
                                        variant="outline"
                                        size="sm"
                                        :disabled="!imageDataUrl"
                                    >
                                        <Download class="mr-2 h-4 w-4" />
                                        Instagram
                                    </Button>
                                    <Button
                                        @click="downloadImage('whatsapp')"
                                        variant="outline"
                                        size="sm"
                                        :disabled="!imageDataUrl"
                                    >
                                        <Download class="mr-2 h-4 w-4" />
                                        WhatsApp
                                    </Button>
                                    <Button
                                        @click="downloadImage('twitter')"
                                        variant="outline"
                                        size="sm"
                                        :disabled="!imageDataUrl"
                                    >
                                        <Download class="mr-2 h-4 w-4" />
                                        X/Twitter
                                    </Button>
                                    <Button
                                        @click="downloadImage('facebook')"
                                        variant="outline"
                                        size="sm"
                                        :disabled="!imageDataUrl"
                                    >
                                        <Download class="mr-2 h-4 w-4" />
                                        Facebook
                                    </Button>
                                    <Button
                                        @click="downloadImage('snapchat')"
                                        variant="outline"
                                        size="sm"
                                        :disabled="!imageDataUrl"
                                    >
                                        <Download class="mr-2 h-4 w-4" />
                                        Snapchat
                                    </Button>
                                    <Button
                                        @click="downloadImage('tiktok')"
                                        variant="outline"
                                        size="sm"
                                        :disabled="!imageDataUrl"
                                    >
                                        <Download class="mr-2 h-4 w-4" />
                                        TikTok
                                    </Button>
                                </div>
                            </div>

                            <div class="rounded-lg border p-4">
                                <h3 class="mb-2 font-semibold">
                                    Share Directly
                                </h3>
                                <p class="mb-4 text-sm text-muted-foreground">
                                    Share the image directly from your device
                                    using the native share menu
                                </p>
                                <Button
                                    @click="shareImage"
                                    class="w-full"
                                    :disabled="!imageDataUrl"
                                >
                                    <Share2 class="mr-2 h-4 w-4" />
                                    Share Image
                                </Button>
                                <p
                                    v-if="shareError"
                                    class="mt-2 text-sm text-yellow-600 dark:text-yellow-400"
                                >
                                    {{ shareError }}
                                </p>
                            </div>

                            <div class="rounded-lg border p-4">
                                <h3 class="mb-2 font-semibold">Verse Details</h3>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-semibold"
                                            >Reference:</span
                                        >
                                        {{ verseReference }}
                                    </div>
                                    <div>
                                        <span class="font-semibold">Text:</span>
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
