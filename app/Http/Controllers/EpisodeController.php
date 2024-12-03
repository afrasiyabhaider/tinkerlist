<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Http\Resources\EpisodeResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class EpisodeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/episodes",
     *     summary="Get all episodes",
     *     description="Retrieve all episodes",
     *     tags={"Episodes"},
     *     @OA\Response(
     *         response=200,
     *         description="List of episodes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Episode Title"),
     *                 @OA\Property(property="description", type="string", example="Episode description goes here"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-02T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-02T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function index()
    {
        try {
            $episodes = Episode::with('parts')->get();
            return EpisodeResource::collection($episodes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching episodes.'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/episodes",
     *     summary="Create a new episode",
     *     description="Create a new episode in the database",
     *     tags={"Episodes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="title", type="string", example="Episode Title"),
     *             @OA\Property(property="description", type="string", example="Episode description goes here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Episode created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Episode Title"),
     *             @OA\Property(property="description", type="string", example="Episode description goes here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|unique:episodes|string|max:255',  // Ensure title is unique
                'description' => 'nullable|string',  // Description is optional
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 400);
        }
        try {

            // Create episode from validated data
            $episode = Episode::create($validated);

            // Return the created episode as a resource
            return new EpisodeResource($episode);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating the episode.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/episodes/{id}",
     *     summary="Get a specific episode",
     *     description="Retrieve details of a specific episode by ID",
     *     tags={"Episodes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Episode details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Episode Title"),
     *             @OA\Property(property="description", type="string", example="Episode description goes here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Episode not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $episode = Episode::with('parts')->findOrFail($id);
            return new EpisodeResource($episode);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Episode not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while retrieving the episode.'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/episodes/{id}",
     *     summary="Update an episode",
     *     description="Update the details of a specific episode",
     *     tags={"Episodes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="title", type="string", example="Episode Title"),
     *             @OA\Property(property="description", type="string", example="Episode description goes here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Episode updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Episode Title"),
     *             @OA\Property(property="description", type="string", example="Episode description goes here"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-02T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-02T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Episode not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|unique:episodes,title,' . $id . '|string|max:255',  // Ensure title is unique except for current episode
                'description' => 'nullable|string',  // Description is optional
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 400);
        }
        try {
            // Find and update the episode
            $episode = Episode::with('parts')->findOrFail($id);
            $episode->update($validated);

            // Return the updated episode as a resource
            return new EpisodeResource($episode);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Episode not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the episode.'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/episodes/{id}",
     *     summary="Delete an episode",
     *     description="Delete a specific episode by ID",
     *     tags={"Episodes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Episode deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Episode not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $episode = Episode::with('parts')->findOrFail($id);
            $episode->parts()->delete();
            $episode->delete();

            // Return a 204 response indicating successful deletion
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Episode not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the episode.'], 500);
        }
    }
}
