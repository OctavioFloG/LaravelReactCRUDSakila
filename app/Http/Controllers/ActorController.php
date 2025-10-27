<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class ActorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Actor::all());
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
    public function show($id)
    {
        $actor = Actor::find($id);
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

        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:45',
                'last_name'  => 'required|string|max:45',
                'films'      => 'sometimes|array',
                'films.*'    => 'sometimes'
            ]);

            $actor->update(Arr::only($validated, ['first_name', 'last_name']));

            if ($request->has('films')) {
                $filmIds = collect($request->input('films', []))
                    ->map(fn($f) => is_array($f) ? ($f['film_id'] ?? null) : $f)
                    ->filter(fn($id) => is_numeric($id))
                    ->map(fn($id) => (int)$id)
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($filmIds)) {
                    $actor->films()->sync($filmIds);
                }
                // permitir explícitamente limpiar relaciones,
                // envía un flag separado, p.ej. clear_films=true y aquí haces ->sync([]) solo si llega ese flag.
                // if ($request->boolean('clear_films')) { $actor->films()->sync([]); }
            }

            return response()->json([
                'message' => 'Actor actualizado correctamente',
                'data'    => $actor->load('films')
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'error'   => 'Error de base de datos',
                'detalle' => $e->getMessage()
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'Error inesperado',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $actor = Actor::find($id);
        if (!$actor) {
            return response()->json(['message' => 'Actor no encontrado'], 404);
        }

        try {
            $actor->delete();
            return response()->json(data: ['message' => 'Actor eliminado correctamente']);
        } catch (QueryException $e) {
            if ($e->getCode() == "23000") {
                return response()->json(data: [
                    'error' => 'No se puede eliminar el actor',
                    'detalle' => 'Este actor está vinculado a una o más tablas.'
                ], status: 409);
            }
            throw $e;
        }
    }
}
