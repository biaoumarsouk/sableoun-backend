<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log; // <-- IMPORTANT : Importer la façade Log
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Gère la demande de lien de réinitialisation de mot de passe.
     */
    public function forgot(Request $request)
    {
        // 1. Log du début de la requête pour voir si on entre bien ici.
        Log::info('--- Début de la demande de réinitialisation de mot de passe ---');
        
        $credentials = $request->validate(['email' => 'required|email']);
        Log::info('Email reçu pour réinitialisation:', $credentials);

        try {
            // 2. Tente d'envoyer le lien. C'est l'étape qui interagit avec Mailjet.
            Log::info('Tentative d\'envoi de l\'email de réinitialisation via Password::sendResetLink...');
            
            $status = Password::sendResetLink($credentials);
            
            // 3. Log du résultat pour savoir ce que Laravel a répondu.
            Log::info('Résultat de Password::sendResetLink:', ['status' => $status]);

            // 4. On vérifie le statut et on renvoie la réponse au client.
            if ($status == Password::RESET_LINK_SENT) {
                Log::info('--- SUCCÈS : Lien de réinitialisation envoyé. ---');
                return response()->json(['message' => 'Si un compte correspondant à cet e-mail existe, un e-mail de réinitialisation y a été envoyé.']);
            }

            // Si l'e-mail n'a pas été trouvé, le statut est Password::INVALID_USER.
            // On renvoie un message de succès générique pour des raisons de sécurité.
            Log::warning('--- ÉCHEC : Utilisateur non trouvé. Statut retourné:', ['status' => $status]);
            return response()->json(['message' => 'Si un compte correspondant à cet e-mail existe, un e-mail de réinitialisation y a été envoyé.'], 200);

        } catch (\Exception $e) {
            // 5. Si une erreur FATALE se produit (ex: échec de connexion à Mailjet), on la capture et on la logue.
            Log::error('ERREUR FATALE LORS DE L\'ENVOI DE L\'EMAIL DE RÉINITIALISATION:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Donne tous les détails techniques
            ]);
            
            // On renvoie une erreur 500 au frontend pour qu'il sache que ça a échoué.
            return response()->json(['message' => 'Erreur serveur lors de la tentative d\'envoi de l\'e-mail.'], 500);
        }
    }

    /**
     * Gère la réinitialisation effective du mot de passe.
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
                event(new PasswordReset($user));
            }
        );

        return $response == Password::PASSWORD_RESET
            ? response()->json(["message" => "Le mot de passe a été réinitialisé avec succès."])
            : response()->json(["message" => "Le jeton de réinitialisation est invalide ou a expiré."], 400);
    }
}