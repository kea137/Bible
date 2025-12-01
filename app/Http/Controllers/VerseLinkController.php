<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $this->authorize('update', $canvas);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_collaborative' => 'sometimes|boolean',
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
        $this->authorize('delete', $canvas);

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
        $this->authorize('view', $canvas);

        $canvas->load([
            'nodes.verse.book',
            'nodes.verse.chapter',
            'nodes.verse.bible',
            'nodes.lastModifiedBy:id,name',
            'connections.lastModifiedBy:id,name',
            'permissions.user:id,name',
        ]);

        // Include user's permission level
        $userPermission = $canvas->getPermissionForUser(Auth::id());
        $canvas->user_role = $userPermission ? $userPermission->role : ($canvas->user_id === Auth::id() ? 'owner' : null);
        $canvas->can_edit = $canvas->userCanEdit(Auth::id());

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
        $this->authorize('update', $canvas);

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
            'version' => 1,
            'last_modified_by' => Auth::id(),
            'last_modified_at' => now(),
        ]);

        $node->load(['verse.book', 'verse.chapter', 'verse.bible', 'lastModifiedBy:id,name']);

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
        $this->authorize('update', $canvas);

        $validated = $request->validate([
            'position_x' => 'sometimes|integer',
            'position_y' => 'sometimes|integer',
            'note' => 'nullable|string',
            'version' => 'required|integer',
        ]);

        // Check for version conflict
        if ($validated['version'] !== $node->version) {
            return response()->json([
                'success' => false,
                'error' => 'version_conflict',
                'message' => 'This node was modified by another user. Please refresh and try again.',
                'current_node' => $node->fresh(['lastModifiedBy:id,name']),
            ], 409);
        }

        // Update with new version
        $updateData = array_merge(
            array_filter($validated, fn($key) => $key !== 'version', ARRAY_FILTER_USE_KEY),
            [
                'version' => $node->version + 1,
                'last_modified_by' => Auth::id(),
                'last_modified_at' => now(),
            ]
        );

        $node->update($updateData);
        $node->load('lastModifiedBy:id,name');

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
        $this->authorize('update', $canvas);

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
        $this->authorize('update', $canvas);

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
            'version' => 1,
            'last_modified_by' => Auth::id(),
            'last_modified_at' => now(),
        ]);

        $connection->load('lastModifiedBy:id,name');

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
        $this->authorize('update', $canvas);

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
        $this->authorize('view', $canvas);

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

    /**
     * Share a canvas with another user.
     */
    public function shareCanvas(Request $request, VerseLinkCanvas $canvas): JsonResponse
    {
        $this->authorize('managePermissions', $canvas);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:editor,viewer',
        ]);

        // Prevent sharing with self
        if ($validated['user_id'] == $canvas->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot share canvas with yourself',
            ], 422);
        }

        // Check if permission already exists
        $existing = $canvas->permissions()->where('user_id', $validated['user_id'])->first();
        
        if ($existing) {
            // Update existing permission
            $existing->update(['role' => $validated['role']]);
            $permission = $existing;
        } else {
            // Create new permission
            $permission = $canvas->permissions()->create([
                'user_id' => $validated['user_id'],
                'role' => $validated['role'],
            ]);
        }

        // Enable collaborative mode if not already enabled
        if (!$canvas->is_collaborative) {
            $canvas->update(['is_collaborative' => true]);
        }

        $permission->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Canvas shared successfully',
            'permission' => $permission,
        ]);
    }

    /**
     * Remove a user's permission from a canvas.
     */
    public function removePermission(Request $request, VerseLinkCanvas $canvas, User $user): JsonResponse
    {
        $this->authorize('managePermissions', $canvas);

        $permission = $canvas->permissions()->where('user_id', $user->id)->first();

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have access to this canvas',
            ], 404);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission removed successfully',
        ]);
    }

    /**
     * Get all users with access to a canvas.
     */
    public function getCollaborators(VerseLinkCanvas $canvas): JsonResponse
    {
        $this->authorize('view', $canvas);

        $collaborators = $canvas->permissions()
            ->with('user:id,name')
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->user->id,
                    'name' => $permission->user->name,
                    'role' => $permission->role,
                ];
            });

        // Add owner
        $collaborators->prepend([
            'id' => $canvas->user->id,
            'name' => $canvas->user->name,
            'role' => 'owner',
        ]);

        return response()->json($collaborators);
    }

    /**
     * Search for users to share canvas with.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $users = User::where('name', 'like', '%' . $validated['query'] . '%')
            ->where('id', '!=', Auth::id())
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
