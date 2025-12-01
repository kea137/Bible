<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { getLinkTypeColor } from '@/composables/useLinkTypes';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BookOpen, GitBranch } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Verse {
    id: number;
    verse_number: number;
    text: string;
    book: {
        id: number;
        title: string;
    };
    chapter: {
        id: number;
        chapter_number: number;
    };
    bible: {
        id: number;
        name: string;
        version: string;
    };
}

interface Node {
    id: number;
    canvas_id: number;
    verse_id: number;
    position_x: number;
    position_y: number;
    note: string | null;
    verse: Verse;
}

interface Connection {
    id: number;
    canvas_id: number;
    source_node_id: number;
    target_node_id: number;
    label: string | null;
    link_type: string;
}

interface Canvas {
    id: number;
    name: string;
    description: string | null;
    nodes: Node[];
    connections: Connection[];
}

interface Props {
    canvas: Canvas;
    isReadOnly: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Shared Canvas'),
        href: '#',
    },
];

// Get connection line path
function getConnectionPath(connection: Connection): string {
    const sourceNode = props.canvas.nodes.find(
        (n) => n.id === connection.source_node_id,
    );
    const targetNode = props.canvas.nodes.find(
        (n) => n.id === connection.target_node_id,
    );

    if (!sourceNode || !targetNode) return '';

    const cardWidth = 300;
    const cardHeight = 150;

    const x1 = sourceNode.position_x + cardWidth / 2;
    const y1 = sourceNode.position_y + cardHeight / 2;
    const x2 = targetNode.position_x + cardWidth / 2;
    const y2 = targetNode.position_y + cardHeight / 2;

    // Bezier curve for smoother lines
    const midX = (x1 + x2) / 2;

    return `M ${x1} ${y1} Q ${midX} ${y1} ${midX} ${(y1 + y2) / 2} T ${x2} ${y2}`;
}

// Get mid-point of connection for label
function getConnectionMidPoint(connection: Connection): {
    x: number;
    y: number;
} {
    const sourceNode = props.canvas.nodes.find(
        (n) => n.id === connection.source_node_id,
    );
    const targetNode = props.canvas.nodes.find(
        (n) => n.id === connection.target_node_id,
    );

    if (!sourceNode || !targetNode) return { x: 0, y: 0 };

    const cardWidth = 300;
    const cardHeight = 150;

    const x1 = sourceNode.position_x + cardWidth / 2;
    const y1 = sourceNode.position_y + cardHeight / 2;
    const x2 = targetNode.position_x + cardWidth / 2;
    const y2 = targetNode.position_y + cardHeight / 2;

    return {
        x: (x1 + x2) / 2,
        y: (y1 + y2) / 2 - 10, // Offset above the line
    };
}

// Calculate canvas bounds for SVG
const canvasBounds = computed(() => {
    if (!props.canvas || props.canvas.nodes.length === 0) {
        return { width: 2000, height: 1500 };
    }

    let maxX = 0;
    let maxY = 0;

    for (const node of props.canvas.nodes) {
        maxX = Math.max(maxX, node.position_x + 400);
        maxY = Math.max(maxY, node.position_y + 300);
    }

    return {
        width: Math.max(2000, maxX),
        height: Math.max(1500, maxY),
    };
});
</script>

<template>
    <Head :title="`${t('Shared Canvas')} - ${canvas.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="verse-link-desktop-wrapper min-w-[900px] overflow-x-auto">
            <div
                class="flex h-full flex-1 flex-col gap-4 overflow-hidden rounded-xl p-4"
            >
                <!-- Canvas Header -->
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-b pb-3"
                >
                    <div class="flex items-center gap-3">
                        <GitBranch class="h-5 w-5 text-primary" />
                        <div>
                            <h1 class="text-lg font-semibold">
                                {{ canvas.name }}
                            </h1>
                            <p
                                v-if="canvas.description"
                                class="text-xs text-muted-foreground"
                            >
                                {{ canvas.description }}
                            </p>
                            <p class="text-xs text-muted-foreground italic">
                                {{ t('Read-only view') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Canvas Area -->
                <div class="relative flex-1">
                    <ScrollArea
                        class="flex-1 rounded-lg border-2 border-dashed border-border"
                    >
                        <div
                            class="relative bg-muted/20"
                            :style="{
                                backgroundImage: `radial-gradient(circle, hsl(var(--border)) 1px, transparent 1px)`,
                                backgroundSize: '20px 20px',
                                minWidth: `${canvasBounds.width}px`,
                                minHeight: `${canvasBounds.height}px`,
                            }"
                        >
                            <!-- SVG for connection lines -->
                            <svg
                                class="absolute top-0 left-0"
                                :width="canvasBounds.width"
                                :height="canvasBounds.height"
                                style="
                                    z-index: 1;
                                    pointer-events: none;
                                    overflow: visible;
                                "
                            >
                                <defs>
                                    <marker
                                        id="arrowhead-shared"
                                        markerWidth="10"
                                        markerHeight="7"
                                        refX="9"
                                        refY="3.5"
                                        orient="auto"
                                    >
                                        <polygon
                                            points="0 0, 10 3.5, 0 7"
                                            fill="hsl(var(--primary))"
                                        />
                                    </marker>
                                </defs>
                                <g
                                    v-for="connection in canvas.connections"
                                    :key="connection.id"
                                >
                                    <path
                                        :d="getConnectionPath(connection)"
                                        stroke-width="3"
                                        marker-end="url(#arrowhead-shared)"
                                        :style="{
                                            stroke: getLinkTypeColor(
                                                connection.link_type ||
                                                    'general',
                                            ),
                                            fill: 'none',
                                            pointerEvents: 'none',
                                        }"
                                        class="opacity-70"
                                    />
                                    <!-- Label for connection -->
                                    <text
                                        v-if="connection.label"
                                        :x="getConnectionMidPoint(connection).x"
                                        :y="getConnectionMidPoint(connection).y"
                                        text-anchor="middle"
                                        class="pointer-events-none fill-current text-xs select-none"
                                        :style="{
                                            fill: getLinkTypeColor(
                                                connection.link_type ||
                                                    'general',
                                            ),
                                        }"
                                    >
                                        {{ connection.label }}
                                    </text>
                                </g>
                            </svg>

                            <!-- Empty State -->
                            <div
                                v-if="canvas.nodes.length === 0"
                                class="absolute inset-0 flex flex-col items-center justify-center"
                            >
                                <BookOpen
                                    class="mb-4 h-12 w-12 text-muted-foreground/30"
                                />
                                <p class="text-muted-foreground">
                                    {{ t('This canvas has no verses') }}
                                </p>
                            </div>

                            <!-- Verse Cards -->
                            <div
                                v-for="node in canvas.nodes"
                                :key="node.id"
                                class="absolute w-[300px]"
                                :style="{
                                    left: `${node.position_x}px`,
                                    top: `${node.position_y}px`,
                                    zIndex: 1,
                                }"
                            >
                                <Card class="shadow-lg">
                                    <CardHeader>
                                        <CardTitle class="text-sm">
                                            {{ node.verse.book.title }}
                                            {{
                                                node.verse.chapter
                                                    .chapter_number
                                            }}:{{ node.verse.verse_number }}
                                        </CardTitle>
                                        <CardDescription class="text-xs">
                                            {{ node.verse.bible.name }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="pt-0">
                                        <p class="text-sm leading-relaxed">
                                            {{ node.verse.text }}
                                        </p>
                                        <p
                                            v-if="node.note"
                                            class="mt-3 border-l-2 border-primary pl-2 text-xs text-muted-foreground italic"
                                        >
                                            {{ node.note }}
                                        </p>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </ScrollArea>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
