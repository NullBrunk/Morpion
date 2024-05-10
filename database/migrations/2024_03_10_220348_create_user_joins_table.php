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
        Schema::create('user_joins', function (Blueprint $table) {

            $table->foreignIdFor(\App\Models\User::class);

            $table->foreignIdFor(\App\Models\Game::class);
                
            $table->string("symbol");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_joins');
    }
};
