<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use Illuminate\Http\Request;

class ActorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Actor::with('films')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:45',
            'last_name' => 'required|string|max:45',
            'films' => 'array'
        ]);

        $actor = Actor::create($validated);

        if ($request->has('films')) {
            $actor->films()->sync($request->films);
        }

        return response()->json(['message' => 'Actor creado', 'data' => $actor], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $actor = Actor::with('films')->find($id);

        if (!$actor) {
            return response()->json(['message' => 'Actor no encontrado'], 404);
        }

        return response()->json($actor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $actor = Actor::find($id);

        if (!$actor) {
            return response()->json(['message' => 'Actor no encontrado'], 404);
        }

        $actor->update($request->all());

        if ($request->has('films')) {
            $actor->films()->sync($request->films);
        }

        return response()->json(['message' => 'Actor actualizado', 'data' => $actor]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $actor = Actor::find($id);

        if (!$actor) {
            return response()->json(['message' => 'Actor no encontrado'], 404);
        }

        $actor->delete();

        return response()->json(['message' => 'Actor eliminado']);
    }
}
