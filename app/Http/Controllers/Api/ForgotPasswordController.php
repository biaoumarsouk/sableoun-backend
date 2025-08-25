<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    // Étape 1: Demande de lien de réinitialisation
    public function forgot(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email']);
        Password::sendResetLink($credentials);
        return response()->json(["message" => "Un lien de réinitialisation a été envoyé à votre adresse e-mail."]);
    }

    // Étape 2: Réinitialisation du mot de passe
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
            : response()->json(["message" => "Le jeton de réinitialisation est invalide."], 400);
    }
}