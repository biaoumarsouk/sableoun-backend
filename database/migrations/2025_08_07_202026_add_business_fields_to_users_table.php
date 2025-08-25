<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // On groupe les informations de base
            $table->string('company_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('profile_photo_path')->nullable()->after('password');

            // On groupe les informations sur l'activité
            $table->string('main_service')->nullable()->after('company_name');
            $table->string('category')->nullable()->after('main_service');
            $table->text('services_description')->nullable()->after('category');

            // --- LOCALISATION ---
            $table->string('country')->nullable()->after('category');
            $table->string('region')->nullable()->after('country');
            $table->string('city')->nullable()->after('region');
            $table->string('currency')->nullable()->after('city');
            $table->string('phone_indicator')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // L'ordre dans dropColumn n'a pas d'importance
            $table->dropColumn([
                'company_name',
                'phone',
                'profile_photo_path',
                'main_service',
                'category',
                'services_description',
                'country',
                'region',
                'city',
                'currency',
                'phone_indicator',
            ]);
        });
    }
};