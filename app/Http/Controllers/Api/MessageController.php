<?php

// Fichier: app/Http-Controllers/Api/MessageController.php (Finalisé)

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    /**
     * Stocke un nouveau message envoyé par un visiteur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'seller_id'    => 'required|exists:users,id',
            'product_id'   => 'nullable|exists:products,id',
            'affiche_id'   => 'nullable|exists:affiches,id',
            'sender_name'  => 'required|string|max:255',
            'sender_email' => 'required|email|max:255',
            'message'      => 'required|string|min:10',
        ]);

        // --- CORRECTION DE SÉCURITÉ ET DE LOGIQUE ---
        // On s'assure qu'au moins un des deux identifiants est fourni
        // pour éviter les messages "orphelins".
        if (empty($validated['product_id']) && empty($validated['affiche_id'])) {
            return response()->json(['message' => 'Un produit ou une affiche doit être associé.'], 422);
        }

        Message::create($validated);

        // TODO: Envoyer une notification par e-mail au vendeur

        return response()->json(['message' => 'Votre message a bien été envoyé !'], 201);
    }

    /**
     * Liste les messages reçus par le vendeur authentifié.
     */
    public function index(Request $request)
    {
        $messages = $request->user()
            ->messagesReceived()
            // On charge les relations nécessaires pour la liste
            ->with([
                'product:id,name', // Pour la liste, on n'a besoin que du nom
                'affiche:id,title' // et du titre
            ])
            ->latest() // Trie par date de création, du plus récent au plus ancien
            ->get();

        return response()->json($messages);
    }

    /**
     * Affiche un message spécifique et le marque comme lu.
     */
    public function show(Message $message)
    {
        // Vérifie si l'utilisateur connecté est bien le destinataire du message
        if (Gate::denies('view-message', $message)) {
            abort(403, 'Action non autorisée.');
        }

        // On marque le message comme lu (si ce n'est pas déjà le cas)
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        // On charge les relations complètes pour la page de détail
        $message->load(['product', 'affiche']);

        return response()->json($message);
    }
}