<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Gère l'inscription d'un nouvel utilisateur (vendeur).
     */ 
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:2048',
            'company_name' => 'nullable|string|max:255',
            'main_service' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'services_description' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'phone_indicator' => 'required|string|max:10',
            'country' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'currency' => 'required|string|max:10',
        ]);

        if ($request->hasFile('profile_photo')) {
            $validatedData['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $validatedData['password'] = Hash::make($validatedData['password']);
        
        $user = User::create($validatedData);

        // Génération du slug unique après la création pour avoir l'ID
        $nameForSlug = $user->company_name ?: $user->name;
        $user->slug = $user->id . '-' . Str::slug($nameForSlug);
        $user->save();

        return response()->json(['message' => 'Utilisateur enregistré avec succès!'], 201);
    }

    /**
     * Gère la connexion d'un utilisateur.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $user = $request->user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Gère la déconnexion d'un utilisateur.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    /**
     * Met à jour le profil de l'utilisateur authentifié.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone' => 'required|string|max:20',
                'phone_indicator' => 'required|string|max:10',
                'company_name' => 'nullable|string|max:255',
                'main_service' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'services_description' => 'nullable|string',
                'country' => 'nullable|string|max:255',
                'region' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'currency' => 'required|string|max:10', // <-- LA CORRECTION PRINCIPALE EST ICI
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'remove_profile_photo' => 'nullable|boolean',
            ]);

            if ($request->boolean('remove_profile_photo')) {
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                    $validated['profile_photo_path'] = null; // On met le chemin à null
                }
            } 
            // Cas 2 : L'utilisateur envoie une nouvelle photo (ceci prend le dessus sur la suppression)
            else if ($request->hasFile('profile_photo')) {
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }
                $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
            }

            $user->update($validated);

            // On révoque le token actuel pour déconnecter l'utilisateur
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Profil mis à jour ! Vous allez être déconnecté pour des raisons de sécurité.',
                'user' => $user->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gère spécifiquement les erreurs de validation pour renvoyer un code 422
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Gère toutes les autres erreurs potentielles
            Log::error('Erreur lors de la mise à jour du profil:', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Une erreur interne est survenue.'], 500);
        }
    }
}