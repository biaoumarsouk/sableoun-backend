<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AfficheController extends Controller
{
    // --- Méthodes Publiques ---

    public function publicIndex()
    {
        $today = now()->toDateString();
        $affiches = Affiche::with('user')
            ->where('status', 'published') // Uniquement les affiches publiées
            ->where(function ($query) use ($today) {
                $query->where('start_date', '<=', $today)->orWhereNull('start_date');
            })
            ->where(function ($query) use ($today) {
                $query->where('end_date', '>=', $today)->orWhereNull('end_date');
            })
            ->latest()
            ->get();
        return response()->json($affiches);
    }

    public function show(Affiche $affiche)
    {
        if ($affiche->status !== 'published') {
            abort(404, 'Affiche non trouvée.');
        }
        return response()->json($affiche->load('user'));
    }

    // --- Méthodes Protégées ---
    
    public function userAffiches(Request $request)
    {
        return response()->json($request->user()->affiches()->latest()->get());
    }

    public function showForEditing(Affiche $affiche)
    {
        if (Gate::denies('manage-affiche', $affiche)) {
            abort(403, 'Action non autorisée.');
        }
        return response()->json($affiche);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'image'        => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'start_date'   => 'nullable|date_format:Y-m-d',
            'end_date'     => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'status'       => 'required|in:published,draft', // Validation du statut
        ]);
        
        $validated['start_date'] = $validated['start_date'] ?: null;
        $validated['end_date'] = $validated['end_date'] ?: null;
        
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('affiches', 'public');
        }

        $affiche = $request->user()->affiches()->create($validated);
        return response()->json($affiche, 201);
    }

    public function update(Request $request, Affiche $affiche)
    {
        if (Gate::denies('manage-affiche', $affiche)) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'start_date'  => 'nullable|date_format:Y-m-d',
            'end_date'    => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'status'      => 'required|in:published,draft', // Validation du statut
        ]);

        $validated['start_date'] = $validated['start_date'] ?: null;
        $validated['end_date'] = $validated['end_date'] ?: null;

        if ($request->hasFile('image')) {
            if ($affiche->image_path) { Storage::disk('public')->delete($affiche->image_path); }
            $validated['image_path'] = $request->file('image')->store('affiches', 'public');
        }

        $affiche->update($validated);
        return response()->json(['message' => 'Affiche mise à jour avec succès!', 'affiche' => $affiche]);
    }
    
    public function destroy(Affiche $affiche)
    {
        if (Gate::denies('manage-affiche', $affiche)) {
            abort(403, 'Action non autorisée.');
        }
        if ($affiche->image_path) { Storage::disk('public')->delete($affiche->image_path); }
        $affiche->delete();
        return response()->json(['message' => 'Affiche supprimée avec succès!']);
    }
}