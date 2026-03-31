<?php

namespace App\Http\Controllers;

use App\Models\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    // Mostrar todas las universidades
    public function index(Request $request)
    {
        $query = University::query();

        // Búsqueda por siglas o nombre
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('initials', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $universities = $query->orderBy('name')->paginate(15);
        return view('universities.index', compact('universities'));
    }

    // Mostrar el formulario para crear una nueva universidad
    public function create()
    {
        return view('universities.create');
    }

    // Guardar una nueva universidad en la base de datos
    public function store(Request $request)
    {
        $validated = $request->validate([
            'initials' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        // Normalizar sigla para evitar duplicados por mayúsculas/minúsculas o espacios.
        $normalizedInitials = strtoupper(preg_replace('/\s+/', '', trim($validated['initials'])));

        $duplicateExists = University::query()
            ->whereRaw('UPPER(REPLACE(TRIM(initials), " ", "")) = ?', [$normalizedInitials])
            ->exists();

        if ($duplicateExists) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una universidad con esa sigla.'
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['initials' => 'Ya existe una universidad con esa sigla.']);
        }

        $validated['initials'] = $normalizedInitials;

        $university = University::create($validated);

        // Si es una petición AJAX, devolver JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'university' => $university,
                'message' => 'Universidad creada correctamente.'
            ]);
        }

        return redirect()->route('universities.index')->with('success', 'Universidad creada correctamente.');
    }

    // Mostrar una universidad específica
    public function show(University $university)
    {
        return view('universities.show', compact('university'));
    }

    // Mostrar el formulario para editar una universidad
    public function edit(University $university)
    {
        return view('universities.edit', compact('university'));
    }

    // Actualizar una universidad en la base de datos
    public function update(Request $request, University $university)
    {
        $validated = $request->validate([
            'initials' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        $normalizedInitials = strtoupper(preg_replace('/\s+/', '', trim($validated['initials'])));

        $duplicateExists = University::query()
            ->where('id', '!=', $university->id)
            ->whereRaw('UPPER(REPLACE(TRIM(initials), " ", "")) = ?', [$normalizedInitials])
            ->exists();

        if ($duplicateExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['initials' => 'Ya existe una universidad con esa sigla.']);
        }

        $validated['initials'] = $normalizedInitials;

        $university->update($validated);

        return redirect()->route('universities.index')->with('success', 'Universidad actualizada correctamente.');
    }

    // Alternar el estado activo/inactivo de una universidad
    public function toggleStatus(University $university)
    {
        $university->active = !$university->active;
        $university->save();

        return redirect()->route('universities.index')->with('success', 'Estado de la universidad actualizado correctamente.');
    }
}
