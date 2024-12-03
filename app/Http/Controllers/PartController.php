<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartResource;
use App\Models\Episode;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/episode/{episode}/parts",
     *     summary="List all parts of an episode",
     *     description="Get a list of all parts associated with a specific episode.",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         description="ID of the episode",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of parts",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *             property="id",
     *             type="integer",
     *             description="ID of the part"
     *         ),
     *         @OA\Property(
     *             property="episode_id",
     *             type="integer",
     *             description="ID of the episode the part belongs to"
     *         ),
     *         @OA\Property(
     *             property="title",
     *             type="string",
     *             description="Title of the part"
     *         ),
     *         @OA\Property(
     *             property="description",
     *             type="string",
     *             description="Description of the part"
     *         ),
     *         @OA\Property(
     *             property="position",
     *             type="integer",
     *             description="Position of the part within the episode"
     *         ),
     *         @OA\Property(
     *             property="created_at",
     *             type="string",
     *             format="date-time",
     *             description="The timestamp when the part was created"
     *         ),
     *         @OA\Property(
     *             property="updated_at",
     *             type="string",
     *             format="date-time",
     *             description="The timestamp when the part was last updated"
     *         )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=404, description="Episode not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function listParts(Episode $episode)
    {
        // Paginate parts if there are too many
        $parts = Part::with('episode')
            ->where('episode_id', $episode->id)
            ->orderBy('position')
            ->paginate(10); // Adjust pagination size as needed

        // Return the paginated parts
        return PartResource::collection($parts);
    }
    /**
     * @OA\Post(
     *     path="/episode/{episode}/part",
     *     summary="Add a new part to the episode",
     *     description="Add a new part to the episode at a specified position.",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         description="ID of the episode",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="position", type="integer", description="Position of the part"),
     *             @OA\Property(property="title", type="string", description="Title of the part"),
     *             @OA\Property(property="description", type="string", description="Description of the part"),
     *             example={"position": 2, "title": "New Part", "description": "A new part added"}
     *         )
     *     ),
     *     @OA\Response(response=200, description="Part added successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function addPart(Request $request, Episode $episode)
    {
        // Validate the inputs, including unique validation for title
        try {
            $validated = $request->validate([
                'position' => 'required|integer|min:0',
                'title' => 'required|string|unique:parts,title',
                'description' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 400);
        }

        // Increment positions of existing parts
        Part::where('episode_id', $episode->id)
            ->where('position', '>=', $request->position)
            ->increment('position');

        // Create the new part
        $part = Part::create([
            'episode_id' => $episode->id,
            'position' => $request->position,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return new PartResource($part->load('episode'));
    }

    /**
     * @OA\Delete(
     *     path="/episode/{episode}/parts/{part}",
     *     summary="Delete a part from the episode",
     *     description="Delete a specific part from the episode.",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         description="ID of the episode",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="part",
     *         in="path",
     *         description="ID of the part to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Part deleted successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function deletePart(Episode $episode, Part $part)
    {
        // Validate part existence and deletion permissions
        $this->validateDeleteRequest($episode, $part);

        // Decrement positions of parts after the deleted part
        Part::where('episode_id', $episode->id)
            ->where('position', '>', $part->position)
            ->decrement('position');

        // Delete the part
        $part->delete();

        return response()->json(['message' => 'Part deleted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/episode/{episode}/parts/update/positions",
     *     summary="Update positions of multiple parts",
     *     description="Update the positions of multiple parts within an episode.",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         description="ID of the episode",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="positions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "position"},
     *                     @OA\Property(property="id", type="integer", description="ID of the part"),
     *                     @OA\Property(property="position", type="integer", description="New position of the part")
     *                 ),
     *                 example={
     *                     {"id": 1, "position": 2},
     *                     {"id": 5, "position": 3}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Positions updated successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function updatePartsPositions(Request $request, Episode $episode)
    {
        try {
            // Step 1: Validate input
            $validated = $request->validate([
                'id' => 'required|exists:parts,id',
                'position' => 'required|integer|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Step 2: Get the part that we need to update
            $part = Part::where('episode_id', $episode->id)
                ->where('id', $validated['id'])
                ->first();

            if (!$part) {
                return response()->json(['message' => 'Part not found'], 404);
            }

            // Step 3: Calculate the new position based on the number of parts
            $newPosition = $this->calculatePositionBasedOnCount($episode, $validated['position']);

            // Step 4: Skip if the part is already at the requested position
            if ($part->position === $newPosition) {
                return response()->json(['message' => 'Position is already set to the requested value'], 200);
            }

            // Step 5: Shift other parts if needed (either up or down based on the direction of movement)
            $this->shiftPositions($episode, $part, $newPosition);

            // Step 6: Update the part's position in the database
            $part->update(['position' => $newPosition]);

            DB::commit();

            return response()->json(['message' => 'Position updated successfully'], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error occurred, something went wrong'], 500);
        }
    }

    /**
     * Calculate the new position based on the count of parts in the episode.
     * If the requested position is greater than the number of existing parts, assign the position
     * to the next available slot.
     */
    private function calculatePositionBasedOnCount(Episode $episode, int $requestedPosition): int
    {
        // Get the count of parts in the episode
        $totalParts = Part::where('episode_id', $episode->id)->count();

        // If the requested position exceeds the total parts count, set it to the next available slot
        if ($requestedPosition >= $totalParts) {
            return $totalParts; // This will add the part at the last available position
        }

        // Otherwise, return the requested position
        return $requestedPosition;
    }

    /**
     * Shift parts up or down depending on the direction of the change.
     */
    private function shiftPositions(Episode $episode, Part $part, int $newPosition)
    {
        $oldPosition = $part->position;

        // Scenario 1: Moving part upwards (to a smaller position), shift parts down
        if ($newPosition < $oldPosition) {
            $this->shiftPartsDown($episode, $newPosition, $oldPosition);
        }
        // Scenario 2: Moving part downwards (to a larger position), shift parts up
        elseif ($newPosition > $oldPosition) {
            $this->shiftPartsUp($episode, $oldPosition, $newPosition);
        }
    }

    /**
     * Shift parts down when moving a part upwards (to a lower position).
     * This happens when a part's position is updated to a smaller value.
     */
    private function shiftPartsDown(Episode $episode, int $startPosition, int $endPosition)
    {
        Part::where('episode_id', $episode->id)
            ->where('position', '>=', $startPosition)
            ->where('position', '<', $endPosition)
            ->increment('position');
    }

    /**
     * Shift parts up when moving a part downwards (to a higher position).
     * This happens when a part's position is updated to a larger value.
     */
    private function shiftPartsUp(Episode $episode, int $startPosition, int $endPosition)
    {
        Part::where('episode_id', $episode->id)
            ->where('position', '>=', $startPosition)
            ->where('position', '<', $endPosition)
            ->decrement('position');
    }

    /**
     * @OA\Post(
     *     path="/episode/{episode}/parts/reorder",
     *     summary="Reorder all parts in the episode",
     *     description="Reorder all parts in the episode based on their new positions.",
     *     tags={"Parts"},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         description="ID of the episode",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Parts reordered successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function reorderParts(Episode $episode)
    {
        $parts = Part::where('episode_id', $episode->id)
            ->orderBy('position')
            ->get();

        $position = 0;
        foreach ($parts as $part) {
            $part->update(['position' => $position]);
            $position++;
        }

        return response()->json(['message' => 'Parts reordered successfully']);
    }

    /**
     * Custom method to validate the deletion request
     */
    protected function validateDeleteRequest(Episode $episode, Part $part)
    {
        // Check if the part belongs to the episode
        if ($part->episode_id !== $episode->id) {
            abort(404, "Part does not belong to the episode.");
        }
    }
}
