<script setup lang="ts">
import AlertUser from '@/components/AlertUser.vue';
import NotesDialog from '@/components/NotesDialog.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { verse_link } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronDown,
    ChevronRight,
    ExternalLink,
    GitBranch,
    Grid3x3,
    GripVertical,
    Link2,
    LoaderCircle,
    Map,
    Pencil,
    Plus,
    StickyNote,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('Verse Link'),
        href: verse_link().url,
    },
];

interface Canvas {
    id: number;
    name: string;
    description: string | null;
    nodes_count: number;
    created_at: string;
    updated_at: string;
}

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
}

interface Reference {
    id: string;
    reference: string;
    verse: Verse;
}

interface NoteLoad {
    id: number;
    title: string;
    content: string;
}

defineProps<{
    canvases: Canvas[];
}>();

const page = usePage();
const alertSuccess = ref(false);
const alertError = ref(false);
const alertMessage = ref('');

// Canvas state
const selectedCanvas = ref<Canvas | null>(null);
const canvasData = ref<{
    nodes: Node[];
    connections: Connection[];
} | null>(null);
const loading = ref(false);

// Dialog states
const createCanvasDialog = ref(false);
const editCanvasDialog = ref(false);
const deleteCanvasDialog = ref(false);
const addVerseDialog = ref(false);
const notesDialogOpen = ref(false);
const selectedNodeForNote = ref<Node | null>(null);
const userNoteForNode = ref<NoteLoad | null>(null);

// Form data
const newCanvasName = ref('');
const newCanvasDescription = ref('');
const editCanvasName = ref('');
const editCanvasDescription = ref('');
const saving = ref(false);

// Search verse form
const searchBookId = ref<string>('');
const searchChapter = ref('');
const searchVerse = ref('');
const searchResults = ref<Verse[]>([]);
const searching = ref(false);
const bibles = ref<any[]>([]);
const selectedBibleId = ref<string>('');
const books = ref<any[]>([]);
const selectedVerseToAdd = ref<Verse | null>(null);

// Connection state
const connectingFrom = ref<Node | null>(null);
const isConnecting = ref(false);

// Drag state
const draggedNode = ref<Node | null>(null);
const dragStartPos = ref({ x: 0, y: 0 });
const nodeStartPos = ref({ x: 0, y: 0 });
const selectedNodesStartPos = ref<Map<number, { x: number; y: number }>>(
    new Map(),
);

// Multi-select state
const selectedNodes = ref<Set<number>>(new Set());

// Snap-to-grid state
const snapToGrid = ref(false);
const gridSize = 20; // Match the background grid size

// Canvas viewport
const canvasRef = ref<HTMLElement | null>(null);

// Minimap state
const minimapRef = ref<HTMLElement | null>(null);
const showMinimap = ref(true);

// References state for collapsible
const nodeReferences = ref<Record<number, Reference[]>>({});
const loadingReferences = ref<Record<number, boolean>>({});
const expandedReferences = ref<Record<number, boolean>>({});

async function loadBibles() {
    try {
        const response = await fetch('/api/bibles');
        if (response.ok) {
            bibles.value = await response.json();
            if (bibles.value.length > 0) {
                selectedBibleId.value = bibles.value[0].id.toString();
                books.value = bibles.value[0].books || [];
            }
        }
    } catch (error) {
        console.error('Failed to load bibles:', error);
    }
}

watch(selectedBibleId, (newId) => {
    const bible = bibles.value.find((b) => b.id.toString() === newId);
    if (bible) {
        books.value = bible.books || [];
        searchBookId.value = '';
    }
});

onMounted(() => {
    loadBibles();
});

function getCsrfToken(): string {
    let csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
    if (!csrfToken && page.props.csrf_token) {
        csrfToken = String(page.props.csrf_token);
    }
    return csrfToken || '';
}

async function createCanvas() {
    if (!newCanvasName.value.trim()) {
        alertMessage.value = t('Please enter a canvas name');
        alertError.value = true;
        return;
    }

    saving.value = true;
    try {
        const response = await fetch('/api/verse-link/canvas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                name: newCanvasName.value,
                description: newCanvasDescription.value || null,
            }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alertMessage.value = t('Canvas created successfully');
            alertSuccess.value = true;
            createCanvasDialog.value = false;
            newCanvasName.value = '';
            newCanvasDescription.value = '';
            router.reload({ only: ['canvases'] });
        } else {
            alertMessage.value = result.message || t('Failed to create canvas');
            alertError.value = true;
        }
    } catch (error) {
        alertMessage.value = t('Failed to create canvas');
        alertError.value = true;
        console.error(error);
    } finally {
        saving.value = false;
    }
}

async function openCanvas(canvas: Canvas) {
    selectedCanvas.value = canvas;
    loading.value = true;

    try {
        const response = await fetch(`/api/verse-link/canvas/${canvas.id}`);
        if (response.ok) {
            const data = await response.json();
            canvasData.value = {
                nodes: data.nodes || [],
                connections: data.connections || [],
            };
        } else {
            alertMessage.value = t('Failed to load canvas');
            alertError.value = true;
        }
    } catch (error) {
        alertMessage.value = t('Failed to load canvas');
        alertError.value = true;
        console.error(error);
    } finally {
        loading.value = false;
    }
}

function openEditCanvasDialog() {
    if (!selectedCanvas.value) return;
    editCanvasName.value = selectedCanvas.value.name;
    editCanvasDescription.value = selectedCanvas.value.description || '';
    editCanvasDialog.value = true;
}

async function updateCanvas() {
    if (!selectedCanvas.value) return;

    saving.value = true;
    try {
        const response = await fetch(
            `/api/verse-link/canvas/${selectedCanvas.value.id}`,
            {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    name: editCanvasName.value,
                    description: editCanvasDescription.value || null,
                }),
            },
        );

        const result = await response.json();

        if (response.ok && result.success) {
            selectedCanvas.value.name = editCanvasName.value;
            selectedCanvas.value.description = editCanvasDescription.value;
            alertMessage.value = t('Canvas updated successfully');
            alertSuccess.value = true;
            editCanvasDialog.value = false;
            router.reload({ only: ['canvases'] });
        } else {
            alertMessage.value = result.message || t('Failed to update canvas');
            alertError.value = true;
        }
    } catch (error) {
        alertMessage.value = t('Failed to update canvas');
        alertError.value = true;
        console.error(error);
    } finally {
        saving.value = false;
    }
}

async function deleteCanvas() {
    if (!selectedCanvas.value) return;

    saving.value = true;
    try {
        const response = await fetch(
            `/api/verse-link/canvas/${selectedCanvas.value.id}`,
            {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
            },
        );

        const result = await response.json();

        if (response.ok && result.success) {
            alertMessage.value = t('Canvas deleted successfully');
            alertSuccess.value = true;
            deleteCanvasDialog.value = false;
            selectedCanvas.value = null;
            canvasData.value = null;
            router.reload({ only: ['canvases'] });
        } else {
            alertMessage.value = result.message || t('Failed to delete canvas');
            alertError.value = true;
        }
    } catch (error) {
        alertMessage.value = t('Failed to delete canvas');
        alertError.value = true;
        console.error(error);
    } finally {
        saving.value = false;
    }
}

async function searchVerses() {
    if (!searchBookId.value || !searchChapter.value || !searchVerse.value) {
        alertMessage.value = t('Please select a book, chapter, and verse');
        alertError.value = true;
        return;
    }

    searching.value = true;
    selectedVerseToAdd.value = null;
    try {
        const params = new URLSearchParams({
            book_id: searchBookId.value,
            chapter_number: searchChapter.value,
            verse_number: searchVerse.value,
        });

        const response = await fetch(`/api/verse-link/search?${params}`);
        if (response.ok) {
            searchResults.value = await response.json();
        } else {
            alertMessage.value = t('Failed to search verses');
            alertError.value = true;
        }
    } catch (error) {
        alertMessage.value = t('Failed to search verses');
        alertError.value = true;
        console.error(error);
    } finally {
        searching.value = false;
    }
}

function selectVerseToAdd(verse: Verse) {
    selectedVerseToAdd.value = verse;
}

function cancelVerseSelection() {
    selectedVerseToAdd.value = null;
}

async function confirmAddVerse() {
    if (!selectedVerseToAdd.value) return;
    await addVerseToCanvas(selectedVerseToAdd.value);
}

async function addVerseToCanvas(verse: Verse) {
    if (!selectedCanvas.value || !canvasData.value) return;

    // Calculate position (center of visible canvas area with some offset)
    const existingNodes = canvasData.value.nodes.length;
    const posX = 100 + (existingNodes % 3) * 350;
    const posY = 100 + Math.floor(existingNodes / 3) * 250;

    saving.value = true;
    try {
        const response = await fetch('/api/verse-link/node', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                canvas_id: selectedCanvas.value.id,
                verse_id: verse.id,
                position_x: posX,
                position_y: posY,
            }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            canvasData.value.nodes.push(result.node);
            alertMessage.value = t('Verse added to canvas');
            alertSuccess.value = true;
            addVerseDialog.value = false;
            searchResults.value = [];
            searchBookId.value = '';
            searchChapter.value = '';
            searchVerse.value = '';
            selectedVerseToAdd.value = null;
        } else {
            alertMessage.value = result.message || t('Failed to add verse');
            alertError.value = true;
        }
    } catch (error) {
        alertMessage.value = t('Failed to add verse');
        alertError.value = true;
        console.error(error);
    } finally {
        saving.value = false;
    }
}

async function updateNodePosition(node: Node) {
    try {
        await fetch(`/api/verse-link/node/${node.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                position_x: node.position_x,
                position_y: node.position_y,
            }),
        });
    } catch (error) {
        console.error('Failed to update node position:', error);
    }
}

async function deleteNode(node: Node) {
    if (!canvasData.value) return;

    try {
        const response = await fetch(`/api/verse-link/node/${node.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
        });

        if (response.ok) {
            canvasData.value.nodes = canvasData.value.nodes.filter(
                (n) => n.id !== node.id,
            );
            canvasData.value.connections = canvasData.value.connections.filter(
                (c) =>
                    c.source_node_id !== node.id &&
                    c.target_node_id !== node.id,
            );
            alertMessage.value = t('Verse removed from canvas');
            alertSuccess.value = true;
        }
    } catch (error) {
        alertMessage.value = t('Failed to remove verse');
        alertError.value = true;
        console.error(error);
    }
}

function startConnecting(node: Node) {
    connectingFrom.value = node;
    isConnecting.value = true;
}

function cancelConnecting() {
    connectingFrom.value = null;
    isConnecting.value = false;
}

async function connectToNode(targetNode: Node) {
    if (
        !connectingFrom.value ||
        !selectedCanvas.value ||
        !canvasData.value ||
        connectingFrom.value.id === targetNode.id
    ) {
        cancelConnecting();
        return;
    }

    try {
        const response = await fetch('/api/verse-link/connection', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                canvas_id: selectedCanvas.value.id,
                source_node_id: connectingFrom.value.id,
                target_node_id: targetNode.id,
            }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
            canvasData.value.connections.push(result.connection);
            alertMessage.value = t('Connection created');
            alertSuccess.value = true;
        } else {
            alertMessage.value =
                result.message || t('Failed to create connection');
            alertError.value = true;
        }
    } catch (error) {
        alertMessage.value = t('Failed to create connection');
        alertError.value = true;
        console.error(error);
    } finally {
        cancelConnecting();
    }
}

async function deleteConnection(connection: Connection) {
    if (!canvasData.value) return;

    try {
        const response = await fetch(
            `/api/verse-link/connection/${connection.id}`,
            {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            canvasData.value.connections = canvasData.value.connections.filter(
                (c) => c.id !== connection.id,
            );
        }
    } catch (error) {
        console.error('Failed to delete connection:', error);
    }
}

// Helper function for snap-to-grid
function snapToGridValue(value: number): number {
    if (!snapToGrid.value) return value;
    return Math.round(value / gridSize) * gridSize;
}

// Multi-select handlers
function toggleNodeSelection(node: Node, event?: MouseEvent) {
    if (event && !event.shiftKey && !event.ctrlKey && !event.metaKey) {
        // Clear selection if no modifier key
        selectedNodes.value.clear();
        selectedNodes.value.add(node.id);
    } else {
        // Toggle selection with modifier key
        if (selectedNodes.value.has(node.id)) {
            selectedNodes.value.delete(node.id);
        } else {
            selectedNodes.value.add(node.id);
        }
    }
}

function deselectAllNodes() {
    selectedNodes.value.clear();
}

// Drag handlers
function startDrag(event: MouseEvent, node: Node) {
    if (isConnecting.value) {
        connectToNode(node);
        return;
    }

    // If node is not selected, select it (and clear others if no modifier key)
    if (!selectedNodes.value.has(node.id)) {
        toggleNodeSelection(node, event);
    }

    draggedNode.value = node;
    dragStartPos.value = { x: event.clientX, y: event.clientY };
    nodeStartPos.value = { x: node.position_x, y: node.position_y };

    // Store starting positions of all selected nodes
    if (canvasData.value) {
        selectedNodesStartPos.value.clear();
        const nodesToMove = canvasData.value.nodes.filter((n) =>
            selectedNodes.value.has(n.id),
        );
        nodesToMove.forEach((n) => {
            selectedNodesStartPos.value.set(n.id, {
                x: n.position_x,
                y: n.position_y,
            });
        });
    }

    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
}

function onDrag(event: MouseEvent) {
    if (!draggedNode.value || !canvasData.value) return;

    const dx = event.clientX - dragStartPos.value.x;
    const dy = event.clientY - dragStartPos.value.y;

    // If multiple nodes are selected, move them all
    if (selectedNodes.value.size > 1) {
        const nodesToMove = canvasData.value.nodes.filter((n) =>
            selectedNodes.value.has(n.id),
        );
        nodesToMove.forEach((n) => {
            const startPos = selectedNodesStartPos.value.get(n.id);
            if (startPos) {
                const newX = Math.max(0, startPos.x + dx);
                const newY = Math.max(0, startPos.y + dy);

                n.position_x = snapToGridValue(newX);
                n.position_y = snapToGridValue(newY);
            }
        });
    } else {
        const newX = Math.max(0, nodeStartPos.value.x + dx);
        const newY = Math.max(0, nodeStartPos.value.y + dy);

        draggedNode.value.position_x = snapToGridValue(newX);
        draggedNode.value.position_y = snapToGridValue(newY);
    }
}

function stopDrag() {
    if (!draggedNode.value || !canvasData.value) {
        draggedNode.value = null;
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('mouseup', stopDrag);
        return;
    }

    // Update positions for all selected nodes
    if (selectedNodes.value.size > 1) {
        const nodesToUpdate = canvasData.value.nodes.filter((n) =>
            selectedNodes.value.has(n.id),
        );
        nodesToUpdate.forEach((n) => updateNodePosition(n));
    } else {
        updateNodePosition(draggedNode.value);
    }
    
    draggedNode.value = null;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
}

onUnmounted(() => {
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
});

// Get connection line path
function getConnectionPath(connection: Connection): string {
    if (!canvasData.value) return '';

    const sourceNode = canvasData.value.nodes.find(
        (n) => n.id === connection.source_node_id,
    );
    const targetNode = canvasData.value.nodes.find(
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

// Load references for a node
async function toggleReferences(node: Node) {
    const nodeId = node.id;

    if (expandedReferences.value[nodeId]) {
        expandedReferences.value[nodeId] = false;
        return;
    }

    if (nodeReferences.value[nodeId]) {
        expandedReferences.value[nodeId] = true;
        return;
    }

    loadingReferences.value[nodeId] = true;
    try {
        const response = await fetch(
            `/api/verse-link/node/${nodeId}/references`,
        );
        if (response.ok) {
            nodeReferences.value[nodeId] = await response.json();
            expandedReferences.value[nodeId] = true;
        }
    } catch (error) {
        console.error('Failed to load references:', error);
    } finally {
        loadingReferences.value[nodeId] = false;
    }
}

async function openNotesForNode(node: Node) {
    selectedNodeForNote.value = node;
    userNoteForNode.value = null;
    try {
        const response = await fetch(`/api/notes/verse/${node.verse.id}`);
        if (response.ok) {
            const notes = await response.json();
            // If backend returns an array, pick the first note or null
            // userNoteForNode.value = notes.length > 0 ? notes[0] : null;
            userNoteForNode.value = notes[0] || null;
        }
    } catch (error) {
        console.error('Failed to fetch note:', error);
    }
    notesDialogOpen.value = true;
}
function handleNoteSaved() {
    alertMessage.value = t('Note saved successfully');
    alertSuccess.value = true;
}

function goToVerseStudy(verseId: number) {
    router.visit(`/verses/${verseId}/study`);
}

function goBack() {
    selectedCanvas.value = null;
    canvasData.value = null;
    selectedNodes.value.clear();
}

// Minimap functionality
function navigateToMinimapPosition(event: MouseEvent) {
    if (!minimapRef.value || !canvasRef.value) return;

    const minimapRect = minimapRef.value.getBoundingClientRect();
    const clickX = event.clientX - minimapRect.left;
    const clickY = event.clientY - minimapRect.top;

    const minimapWidth = minimapRect.width;
    const minimapHeight = minimapRect.height;

    const canvasScrollElement = canvasRef.value.parentElement;
    if (!canvasScrollElement) return;

    const scrollX = (clickX / minimapWidth) * canvasBounds.value.width;
    const scrollY = (clickY / minimapHeight) * canvasBounds.value.height;

    canvasScrollElement.scrollTo({
        left: scrollX - canvasScrollElement.clientWidth / 2,
        top: scrollY - canvasScrollElement.clientHeight / 2,
        behavior: 'smooth',
    });
}

// Calculate canvas bounds for SVG
const canvasBounds = computed(() => {
    if (!canvasData.value || canvasData.value.nodes.length === 0) {
        return { width: 2000, height: 1500 };
    }

    let maxX = 0;
    let maxY = 0;

    for (const node of canvasData.value.nodes) {
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
    <Head :title="t('Verse Link')" />

    <AlertUser
        v-if="alertSuccess"
        :open="true"
        :title="t('Success')"
        :confirmButtonText="'OK'"
        :message="alertMessage"
        variant="success"
        @update:open="() => (alertSuccess = false)"
    />
    <AlertUser
        v-if="alertError"
        :open="true"
        :title="t('Error')"
        :confirmButtonText="'OK'"
        :message="alertMessage"
        variant="error"
        @update:open="() => (alertError = false)"
    />

    <!-- Create Canvas Dialog -->
    <Dialog v-model:open="createCanvasDialog">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ t('Create New Canvas') }}</DialogTitle>
                <DialogDescription>
                    {{
                        t(
                            'Create a new canvas to link and study related Bible verses.',
                        )
                    }}
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="canvas-name">{{ t('Name') }}</Label>
                    <Input
                        id="canvas-name"
                        v-model="newCanvasName"
                        :placeholder="t('e.g., Salvation Verses')"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="canvas-description">{{
                        t('Description (Optional)')
                    }}</Label>
                    <Textarea
                        id="canvas-description"
                        v-model="newCanvasDescription"
                        :placeholder="t('Describe the purpose of this canvas')"
                        rows="3"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="createCanvasDialog = false"
                    :disabled="saving"
                >
                    {{ t('Cancel') }}
                </Button>
                <Button @click="createCanvas" :disabled="saving">
                    <LoaderCircle
                        v-if="saving"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ t('Create') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Edit Canvas Dialog -->
    <Dialog v-model:open="editCanvasDialog">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ t('Edit Canvas') }}</DialogTitle>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="edit-canvas-name">{{ t('Name') }}</Label>
                    <Input id="edit-canvas-name" v-model="editCanvasName" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit-canvas-description">{{
                        t('Description')
                    }}</Label>
                    <Textarea
                        id="edit-canvas-description"
                        v-model="editCanvasDescription"
                        rows="3"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="editCanvasDialog = false"
                    :disabled="saving"
                >
                    {{ t('Cancel') }}
                </Button>
                <Button @click="updateCanvas" :disabled="saving">
                    <LoaderCircle
                        v-if="saving"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ t('Save') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Delete Canvas Confirmation -->
    <AlertDialog v-model:open="deleteCanvasDialog">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ t('Delete Canvas') }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{
                        t(
                            'Are you sure you want to delete this canvas? All verse links and notes will be permanently removed.',
                        )
                    }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{ t('Cancel') }}</AlertDialogCancel>
                <AlertDialogAction
                    @click="deleteCanvas"
                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                >
                    {{ t('Delete') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <!-- Add Verse Dialog -->
    <Dialog v-model:open="addVerseDialog">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>{{ t('Add Verse to Canvas') }}</DialogTitle>
                <DialogDescription>
                    {{ t('Search for a verse to add to your canvas.') }}
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label>{{ t('Bible') }}</Label>
                        <Select v-model="selectedBibleId">
                            <SelectTrigger>
                                <SelectValue :placeholder="t('Select Bible')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="bible in bibles"
                                    :key="bible.id"
                                    :value="bible.id.toString()"
                                >
                                    {{ bible.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label>{{ t('Book') }}</Label>
                        <Select v-model="searchBookId">
                            <SelectTrigger>
                                <SelectValue :placeholder="t('Select Book')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="book in books"
                                    :key="book.id"
                                    :value="book.id.toString()"
                                >
                                    {{ book.title }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="search-chapter">{{ t('Chapter') }}</Label>
                        <Input
                            id="search-chapter"
                            v-model="searchChapter"
                            type="number"
                            min="1"
                            :placeholder="t('Chapter number')"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="search-verse"
                            >{{ t('Verse') }}
                            <span class="text-destructive">*</span></Label
                        >
                        <Input
                            id="search-verse"
                            v-model="searchVerse"
                            type="number"
                            min="1"
                            :placeholder="t('Verse number')"
                        />
                    </div>
                </div>
                <Button
                    @click="searchVerses"
                    :disabled="
                        searching ||
                        !searchBookId ||
                        !searchChapter ||
                        !searchVerse
                    "
                >
                    <LoaderCircle
                        v-if="searching"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ t('Search') }}
                </Button>

                <!-- Search Results -->
                <ScrollArea
                    v-if="searchResults.length > 0 && !selectedVerseToAdd"
                    class="h-[200px] rounded-md border p-2"
                >
                    <div class="space-y-2">
                        <div
                            v-for="verse in searchResults"
                            :key="verse.id"
                            class="cursor-pointer rounded-lg border p-3 transition-colors hover:bg-accent"
                            @click="selectVerseToAdd(verse)"
                        >
                            <p class="text-sm font-medium text-primary">
                                {{ verse.book.title }}
                                {{ verse.chapter.chapter_number }}:{{
                                    verse.verse_number
                                }}
                            </p>
                            <p
                                class="line-clamp-2 text-xs text-muted-foreground"
                            >
                                {{ verse.text }}
                            </p>
                        </div>
                    </div>
                </ScrollArea>

                <!-- Selected Verse Preview -->
                <div
                    v-if="selectedVerseToAdd"
                    class="rounded-lg border-2 border-primary bg-primary/5 p-4"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-semibold text-primary">
                            {{ t('Selected Verse') }}
                        </p>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="cancelVerseSelection"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                    <p class="mb-2 text-base font-medium">
                        {{ selectedVerseToAdd.book.title }}
                        {{ selectedVerseToAdd.chapter.chapter_number }}:{{
                            selectedVerseToAdd.verse_number
                        }}
                    </p>
                    <p class="text-sm text-muted-foreground italic">
                        "{{ selectedVerseToAdd.text }}"
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        {{ selectedVerseToAdd.bible.name }}
                    </p>
                </div>
            </div>
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="addVerseDialog = false"
                    :disabled="saving"
                >
                    {{ t('Cancel') }}
                </Button>
                <Button
                    v-if="selectedVerseToAdd"
                    @click="confirmAddVerse"
                    :disabled="saving"
                >
                    <LoaderCircle
                        v-if="saving"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ t('Add to Canvas') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Notes Dialog -->
    <NotesDialog
        v-if="selectedNodeForNote"
        :open="notesDialogOpen"
        @update:open="notesDialogOpen = $event"
        :verse-id="selectedNodeForNote.verse.id"
        :verse-text="selectedNodeForNote.verse.text"
        :verse-reference="`${selectedNodeForNote.verse.book.title} ${selectedNodeForNote.verse.chapter.chapter_number}:${selectedNodeForNote.verse.verse_number}`"
        :note="userNoteForNode"
        @saved="handleNoteSaved"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Desktop-only layout wrapper for Verse Link -->
        <div class="verse-link-desktop-wrapper min-w-[900px] overflow-x-auto">
            <div
                class="flex h-full flex-1 flex-col gap-4 overflow-hidden rounded-xl p-4"
            >
                <!-- Canvas List View -->
                <template v-if="!selectedCanvas">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <GitBranch class="h-5 w-5 text-primary" />
                            <h1 class="text-xl font-semibold">
                                {{ t('Verse Link Canvases') }}
                            </h1>
                        </div>
                        <Button @click="createCanvasDialog = true">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ t('New Canvas') }}
                        </Button>
                    </div>

                    <div
                        v-if="canvases.length === 0"
                        class="flex flex-1 flex-col items-center justify-center py-12 text-center"
                    >
                        <GitBranch
                            class="mb-4 h-12 w-12 text-muted-foreground/50"
                        />
                        <h3 class="text-lg font-semibold">
                            {{ t('No Canvases Yet') }}
                        </h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{
                                t(
                                    'Create a canvas to start linking Bible verses together for deeper study.',
                                )
                            }}
                        </p>
                        <Button @click="createCanvasDialog = true" class="mt-4">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ t('Create Your First Canvas') }}
                        </Button>
                    </div>

                    <div v-else class="grid grid-cols-3 gap-4">
                        <Card
                            v-for="canvas in canvases"
                            :key="canvas.id"
                            class="cursor-pointer transition-colors hover:bg-accent/50"
                            @click="openCanvas(canvas)"
                        >
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center justify-between"
                                >
                                    <span class="truncate">{{
                                        canvas.name
                                    }}</span>
                                    <GitBranch class="h-4 w-4 text-primary" />
                                </CardTitle>
                                <CardDescription v-if="canvas.description">
                                    {{ canvas.description }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p class="text-sm text-muted-foreground">
                                    {{ canvas.nodes_count }}
                                    {{ t('verses') }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </template>

                <!-- Canvas Editor View -->
                <template v-else>
                    <!-- Canvas Header -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-2 border-b pb-3"
                    >
                        <div class="flex items-center gap-3">
                            <Button variant="ghost" size="sm" @click="goBack">
                                <X class="h-4 w-4" />
                            </Button>
                            <div>
                                <h1 class="text-lg font-semibold">
                                    {{ selectedCanvas.name }}
                                </h1>
                                <p
                                    v-if="selectedCanvas.description"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ selectedCanvas.description }}
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                v-if="isConnecting"
                                variant="secondary"
                                size="sm"
                                @click="cancelConnecting"
                            >
                                <X class="mr-1 h-4 w-4" />
                                {{ t('Cancel Link') }}
                            </Button>
                            <!-- Snap to Grid Toggle -->
                            <Button
                                variant="outline"
                                size="sm"
                                :class="{ 'bg-primary/10': snapToGrid }"
                                @click="snapToGrid = !snapToGrid"
                                :title="t('Snap to Grid')"
                            >
                                <Grid3x3 class="h-4 w-4" />
                            </Button>
                            <!-- Minimap Toggle -->
                            <Button
                                variant="outline"
                                size="sm"
                                :class="{ 'bg-primary/10': showMinimap }"
                                @click="showMinimap = !showMinimap"
                                :title="t('Toggle Minimap')"
                            >
                                <Map class="h-4 w-4" />
                            </Button>
                            <!-- Selection Controls -->
                            <Button
                                v-if="selectedNodes.size > 0"
                                variant="outline"
                                size="sm"
                                @click="deselectAllNodes"
                                :title="t('Deselect All')"
                            >
                                {{ selectedNodes.size }} {{ t('selected') }}
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="openEditCanvasDialog"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="deleteCanvasDialog = true"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>

                        <!-- Fixed Add Verse Button -->
                        <Button
                            variant="outline"
                            size="lg"
                            class="fixed right-6 bottom-6 z-50 mr-4 shadow-lg"
                            @click="addVerseDialog = true"
                        >
                            <Plus class="mr-2 h-5 w-5" />
                            {{ t('Add Verse') }}
                        </Button>
                    </div>

                    <!-- Connection Mode Indicator -->
                    <div
                        v-if="isConnecting"
                        class="rounded-lg bg-primary/10 px-4 py-2 text-center text-sm"
                    >
                        <span class="font-medium">
                            {{ t('Linking from:') }}
                            {{ connectingFrom?.verse.book.title }}
                            {{
                                connectingFrom?.verse.chapter.chapter_number
                            }}:{{ connectingFrom?.verse.verse_number }}
                        </span>
                        <span class="text-muted-foreground">
                            — {{ t('Click on another verse to create a link') }}
                        </span>
                    </div>

                    <!-- Loading State -->
                    <div
                        v-if="loading"
                        class="flex flex-1 items-center justify-center"
                    >
                        <LoaderCircle
                            class="h-8 w-8 animate-spin text-primary"
                        />
                    </div>

                    <!-- Canvas Area with Minimap -->
                    <div v-else-if="canvasData" class="relative flex-1">
                        <!-- Minimap -->
                        <div
                            v-if="showMinimap && canvasData.nodes.length > 0"
                            class="absolute top-4 right-4 z-50 rounded-lg border-2 border-border bg-background/95 p-2 shadow-lg backdrop-blur"
                            style="width: 200px; height: 150px"
                        >
                            <div class="text-xs font-medium mb-1 text-muted-foreground">
                                {{ t('Minimap') }}
                            </div>
                            <div
                                ref="minimapRef"
                                class="relative cursor-pointer overflow-hidden rounded border bg-muted/30"
                                style="width: 184px; height: 120px"
                                @click="navigateToMinimapPosition"
                            >
                                <!-- Minimap nodes -->
                                <div
                                    v-for="node in canvasData.nodes"
                                    :key="node.id"
                                    class="absolute rounded-sm transition-colors"
                                    :class="
                                        selectedNodes.has(node.id)
                                            ? 'bg-primary'
                                            : 'bg-primary/50'
                                    "
                                    :style="{
                                        left: `${(node.position_x / canvasBounds.width) * 184}px`,
                                        top: `${(node.position_y / canvasBounds.height) * 120}px`,
                                        width: '8px',
                                        height: '8px',
                                    }"
                                />
                            </div>
                        </div>

                        <!-- Canvas Area -->
                        <ScrollArea
                            class="flex-1 rounded-lg border-2 border-dashed border-border"
                        >
                        <div
                            ref="canvasRef"
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
                                    pointer-events: auto;
                                    overflow: visible;
                                "
                            >
                                <defs>
                                    <marker
                                        id="arrowhead"
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
                                    v-for="connection in canvasData.connections"
                                    :key="connection.id"
                                >
                                    <path
                                        :d="getConnectionPath(connection)"
                                        stroke-width="3"
                                        marker-end="url(#arrowhead)"
                                        style="
                                            stroke: currentColor;
                                            color: hsl(var(--primary));
                                            fill: none;
                                            pointer-events: auto;
                                            cursor: pointer;
                                        "
                                        class="opacity-70 transition-opacity hover:opacity-100"
                                        @click="deleteConnection(connection)"
                                    />
                                </g>
                            </svg>

                            <!-- Empty State -->
                            <div
                                v-if="canvasData.nodes.length === 0"
                                class="absolute inset-0 flex flex-col items-center justify-center"
                            >
                                <BookOpen
                                    class="mb-4 h-12 w-12 text-muted-foreground/30"
                                />
                                <p class="text-muted-foreground">
                                    {{
                                        t(
                                            'Add verses to start building your study',
                                        )
                                    }}
                                </p>
                                <Button
                                    variant="outline"
                                    class="mt-4"
                                    @click="addVerseDialog = true"
                                >
                                    <Plus class="mr-2 h-4 w-4" />
                                    {{ t('Add First Verse') }}
                                </Button>
                            </div>

                            <!-- Verse Cards -->
                            <div
                                v-for="node in canvasData.nodes"
                                :key="node.id"
                                class="absolute w-[300px]"
                                :style="{
                                    left: `${node.position_x}px`,
                                    top: `${node.position_y}px`,
                                    zIndex: 1,
                                }"
                                @click="toggleNodeSelection(node, $event)"
                            >
                                <Card
                                    class="shadow-lg transition-all"
                                    :class="{
                                        'ring-2 ring-primary':
                                            isConnecting &&
                                            connectingFrom?.id !== node.id,
                                        'ring-2 ring-primary/50':
                                            connectingFrom?.id === node.id,
                                        'ring-4 ring-blue-500':
                                            selectedNodes.has(node.id) &&
                                            !isConnecting,
                                        'cursor-move': !isConnecting,
                                        'cursor-pointer':
                                            isConnecting &&
                                            connectingFrom?.id !== node.id,
                                    }"
                                >
                                    <CardHeader
                                        class="cursor-move"
                                        @mousedown="startDrag($event, node)"
                                    >
                                        <div
                                            class="flex items-start justify-between"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <GripVertical
                                                    class="h-4 w-4 text-muted-foreground"
                                                />
                                                <CardTitle
                                                    class="text-sm select-none"
                                                >
                                                    {{ node.verse.book.title }}
                                                    {{
                                                        node.verse.chapter
                                                            .chapter_number
                                                    }}:{{
                                                        node.verse.verse_number
                                                    }}
                                                </CardTitle>
                                            </div>
                                            <div class="flex gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 w-6 p-0"
                                                    @click.stop="
                                                        startConnecting(node)
                                                    "
                                                    :title="t('Create Link')"
                                                >
                                                    <Link2 class="h-3 w-3" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 w-6 p-0"
                                                    @click.stop="
                                                        goToVerseStudy(
                                                            node.verse.id,
                                                        )
                                                    "
                                                    :title="t('Study Verse')"
                                                >
                                                    <ExternalLink
                                                        class="h-3 w-3"
                                                    />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 w-6 p-0 text-destructive"
                                                    @click.stop="
                                                        deleteNode(node)
                                                    "
                                                    :title="t('Remove')"
                                                >
                                                    <X class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                        <CardDescription
                                            class="text-xs select-none"
                                        >
                                            {{ node.verse.bible.name }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="pt-0">
                                        <p
                                            class="mb-3 text-sm leading-relaxed select-none"
                                        >
                                            {{ node.verse.text }}
                                        </p>

                                        <!-- Action buttons -->
                                        <div class="flex flex-col gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="h-7 w-full text-xs select-none"
                                                @click.stop="
                                                    openNotesForNode(node)
                                                "
                                            >
                                                <StickyNote
                                                    class="mr-1 h-3 w-3"
                                                />
                                                {{ t('Notes') }}
                                            </Button>

                                            <!-- References Collapsible -->
                                            <Collapsible class="flex-1">
                                                <CollapsibleTrigger as-child>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        class="h-7 w-full text-xs select-none"
                                                        @click.stop="
                                                            toggleReferences(
                                                                node,
                                                            )
                                                        "
                                                    >
                                                        <LoaderCircle
                                                            v-if="
                                                                loadingReferences[
                                                                    node.id
                                                                ]
                                                            "
                                                            class="mr-1 h-3 w-3 animate-spin"
                                                        />
                                                        <template v-else>
                                                            <ChevronDown
                                                                v-if="
                                                                    expandedReferences[
                                                                        node.id
                                                                    ]
                                                                "
                                                                class="mr-1 h-3 w-3"
                                                            />
                                                            <ChevronRight
                                                                v-else
                                                                class="mr-1 h-3 w-3"
                                                            />
                                                        </template>
                                                        {{ t('References') }}
                                                    </Button>
                                                </CollapsibleTrigger>
                                                <CollapsibleContent
                                                    class="mt-2"
                                                >
                                                    <!-- Loading State -->
                                                    <div
                                                        v-if="
                                                            loadingReferences[
                                                                node.id
                                                            ]
                                                        "
                                                        class="flex items-center justify-center py-4"
                                                    >
                                                        <LoaderCircle
                                                            class="h-5 w-5 animate-spin text-primary"
                                                        />
                                                        <span
                                                            class="ml-2 text-xs text-muted-foreground"
                                                        >
                                                            {{
                                                                t(
                                                                    'Loading references...',
                                                                )
                                                            }}
                                                        </span>
                                                    </div>
                                                    <!-- References List -->
                                                    <ScrollArea
                                                        v-else-if="
                                                            expandedReferences[
                                                                node.id
                                                            ] &&
                                                            nodeReferences[
                                                                node.id
                                                            ]
                                                        "
                                                        class="h-100"
                                                    >
                                                        <div
                                                            v-if="
                                                                nodeReferences[
                                                                    node.id
                                                                ].length === 0
                                                            "
                                                            class="py-2 text-center text-xs text-muted-foreground"
                                                        >
                                                            {{
                                                                t(
                                                                    'No references found',
                                                                )
                                                            }}
                                                        </div>
                                                        <div
                                                            v-else
                                                            class="space-y-2"
                                                        >
                                                            <div
                                                                v-for="ref in nodeReferences[
                                                                    node.id
                                                                ]"
                                                                :key="ref.id"
                                                                class="cursor-pointer rounded-md border bg-muted/50 p-2 transition-colors hover:bg-accent"
                                                                @click.stop="
                                                                    selectVerseToAdd(
                                                                        ref.verse,
                                                                    );
                                                                    confirmAddVerse();
                                                                "
                                                            >
                                                                <p
                                                                    class="text-xs font-medium text-primary"
                                                                >
                                                                    {{
                                                                        ref
                                                                            .verse
                                                                            .book
                                                                            ?.title
                                                                    }}
                                                                    {{
                                                                        ref
                                                                            .verse
                                                                            .chapter
                                                                            ?.chapter_number
                                                                    }}:{{
                                                                        ref
                                                                            .verse
                                                                            .verse_number
                                                                    }}
                                                                </p>
                                                                <p
                                                                    class="mt-1 text-xs text-muted-foreground"
                                                                >
                                                                    "{{
                                                                        ref
                                                                            .verse
                                                                            .text
                                                                    }}"
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </ScrollArea>
                                                </CollapsibleContent>
                                            </Collapsible>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </ScrollArea>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
