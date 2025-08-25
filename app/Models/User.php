<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- AJOUTER CETTE LIGNE
use App\Notifications\CustomResetPasswordNotification; // <-- 1. IMPORTER

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // app/Models/User.php

    protected $fillable = [
        'name',
        'email',
        'phone', 
        'password',
        'profile_photo_path', // <-- AJOUTER
        'company_name',
        'slug',
        'main_service',         // <-- Ajouté et corrigé
        'category',
        'services_description', // <-- Corrigé
        'country',
        'region',
        'city',
        'currency',
        'phone_indicator',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Dans la classe User
    public function affiches()
    {
        return $this->hasMany(Affiche::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'seller_id');
    }

    // Dans app/Models/User.php

    public function subscription()
    {
        // Un utilisateur n'a qu'un seul abonnement actif à la fois
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }

    public function hasActiveSubscription(): bool
    {
        // Fonction pratique pour vérifier si l'utilisateur est abonné
        return $this->subscription()->exists();
    }
}
