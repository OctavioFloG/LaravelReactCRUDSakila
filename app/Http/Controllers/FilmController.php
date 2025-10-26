<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Language;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $films = Film::with('actors')->get();
        return response()->json($films);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'language_id' => 'required|integer',
            'rental_duration' => 'nullable|integer',
            'rental_rate' => 'nullable|numeric',
            'length' => 'nullable|integer',
            'replacement_cost' => 'nullable|numeric',
            'rating' => 'nullable|string',
            'actors' => 'array'
        ]);

        $film = Film::create($validated);

        if ($request->has('actors')) {
            $film->actors()->sync($request->actors);
        }

        return response()->json(['message' => 'Película creada', 'data' => $film], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $film = Film::with('actors')->find($id);

        if (!$film) {
            return response()->json(['message' => 'Película no encontrada'], 404);
        }

        return response()->json($film);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $film = Film::find($id);

        if (!$film) {
            return response()->json(['message' => 'Película no encontrada'], 404);
        }

        $film->update($request->all());

        if ($request->has('actors')) {
            $film->actors()->sync($request->actors);
        }

        return response()->json(['message' => 'Película actualizada', 'data' => $film]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $film = Film::find($id);

        if (!$film) {
            return response()->json(['message' => 'Película no encontrada'], 404);
        }

        
        try {
            $film->delete();
        return response()->json(data: ['message' => 'Película eliminada']);
        } catch (QueryException $e) {
            if ($e->getCode() == "23000") {
                return response()->json(data: [
                    'error' => 'No se puede eliminar la película',
                    'detalle' => 'Esta pelicula está vinculado a una o más tablas.'
                ], status: 409);
            }
            throw $e;
        }
    }
}
