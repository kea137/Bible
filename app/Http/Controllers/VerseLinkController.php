<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use App\Models\VerseLinkCanvas;
use App\Models\VerseLinkConnection;
use App\Models\VerseLinkNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VerseLinkController extends Controller
{
    /**
     * Display a listing of the user's canvases.
     */
    public function index(): Response
    {
        $canvases = VerseLinkCanvas::where('user_id', Auth::id())
            ->withCount('nodes')
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Verse Link', [
            'canvases' => $canvases,
        ]);
    }

    /**
     * Store a new canvas.
     */
    public function storeCanvas(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $canvas = VerseLinkCanvas::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Canvas created successfully',
            'canvas' => $canvas,
        ], 201);
    }

    /**
     * Update a canvas.
     */
    public function updateCanvas(Request $request, VerseLinkCanvas $canvas): JsonResponse
    {
        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $canvas->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Canvas updated successfully',
            'canvas' => $canvas,
        ]);
    }

    /**
     * Delete a canvas.
     */
    public function destroyCanvas(VerseLinkCanvas $canvas): JsonResponse
    {
        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $canvas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Canvas deleted successfully',
        ]);
    }

    /**
     * Get a canvas with its nodes and connections.
     */
    public function showCanvas(VerseLinkCanvas $canvas): JsonResponse
    {
        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $canvas->load([
            'nodes.verse.book',
            'nodes.verse.chapter',
            'nodes.verse.bible',
            'connections',
        ]);

        return response()->json($canvas);
    }

    /**
     * Add a verse node to a canvas.
     */
    public function storeNode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'canvas_id' => 'required|exists:verse_link_canvases,id',
            'verse_id' => 'required|exists:verses,id',
            'position_x' => 'required|integer',
            'position_y' => 'required|integer',
            'note' => 'nullable|string',
        ]);

        $canvas = VerseLinkCanvas::findOrFail($validated['canvas_id']);

        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if verse already exists on canvas
        $existingNode = VerseLinkNode::where('canvas_id', $canvas->id)
            ->where('verse_id', $validated['verse_id'])
            ->first();

        if ($existingNode) {
            return response()->json([
                'success' => false,
                'message' => 'This verse already exists on the canvas',
            ], 422);
        }

        $node = VerseLinkNode::create([
            'canvas_id' => $validated['canvas_id'],
            'verse_id' => $validated['verse_id'],
            'position_x' => $validated['position_x'],
            'position_y' => $validated['position_y'],
            'note' => $validated['note'] ?? null,
        ]);

        $node->load(['verse.book', 'verse.chapter', 'verse.bible']);

        return response()->json([
            'success' => true,
            'message' => 'Verse added to canvas',
            'node' => $node,
        ], 201);
    }

    /**
     * Update a node's position or note.
     */
    public function updateNode(Request $request, VerseLinkNode $node): JsonResponse
    {
        $canvas = $node->canvas;
        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'position_x' => 'sometimes|integer',
            'position_y' => 'sometimes|integer',
            'note' => 'nullable|string',
        ]);

        $node->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Node updated successfully',
            'node' => $node,
        ]);
    }

    /**
     * Delete a node from a canvas.
     */
    public function destroyNode(VerseLinkNode $node): JsonResponse
    {
        $canvas = $node->canvas;
        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $node->delete();

        return response()->json([
            'success' => true,
            'message' => 'Node deleted successfully',
        ]);
    }

    /**
     * Create a connection between two nodes.
     */
    public function storeConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'canvas_id' => 'required|exists:verse_link_canvases,id',
            'source_node_id' => 'required|exists:verse_link_nodes,id',
            'target_node_id' => 'required|exists:verse_link_nodes,id|different:source_node_id',
            'label' => 'nullable|string|max:255',
        ]);

        $canvas = VerseLinkCanvas::findOrFail($validated['canvas_id']);

        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if connection already exists (in either direction)
        $existingConnection = VerseLinkConnection::where('canvas_id', $canvas->id)
            ->where(function ($query) use ($validated) {
                $query->where(function ($q) use ($validated) {
                    $q->where('source_node_id', $validated['source_node_id'])
                        ->where('target_node_id', $validated['target_node_id']);
                })
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('source_node_id', $validated['target_node_id'])
                            ->where('target_node_id', $validated['source_node_id']);
                    });
            })
            ->first();

        if ($existingConnection) {
            return response()->json([
                'success' => false,
                'message' => 'A connection between these verses already exists',
            ], 422);
        }

        $connection = VerseLinkConnection::create([
            'canvas_id' => $validated['canvas_id'],
            'source_node_id' => $validated['source_node_id'],
            'target_node_id' => $validated['target_node_id'],
            'label' => $validated['label'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Connection created successfully',
            'connection' => $connection,
        ], 201);
    }

    /**
     * Delete a connection.
     */
    public function destroyConnection(VerseLinkConnection $connection): JsonResponse
    {
        $canvas = $connection->canvas;
        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Connection deleted successfully',
        ]);
    }

    /**
     * Search for verses to add to canvas.
     */
    public function searchVerses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'chapter_number' => 'required|integer|min:1',
            'verse_number' => 'nullable|integer|min:1',
        ]);

        $query = Verse::with(['book', 'chapter', 'bible'])
            ->where('book_id', $validated['book_id'])
            ->whereHas('chapter', function ($q) use ($validated) {
                $q->where('chapter_number', $validated['chapter_number']);
            });

        if (isset($validated['verse_number'])) {
            $query->where('verse_number', $validated['verse_number']);
        }

        $verses = $query->limit(50)->get();

        return response()->json($verses);
    }

    /**
     * Get references for a verse node (used for collapsible reference button).
     */
    public function getNodeReferences(VerseLinkNode $node): JsonResponse
    {
        $canvas = $node->canvas;
        if ($canvas->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $verse = $node->verse;
        $verse->load(['book', 'chapter', 'bible']);

        // Use the ReferenceService to get parsed references with verse data
        $referenceService = app(\App\Services\ReferenceService::class);
        $references = $referenceService->getReferencesForVerse($verse);

        // Load the verse text and related data for each reference
        foreach ($references as &$ref) {
            if (isset($ref['verse']) && $ref['verse'] instanceof Verse) {
                $ref['verse']->load(['book', 'chapter']);
            }
        }

        return response()->json($references);
    }
}
