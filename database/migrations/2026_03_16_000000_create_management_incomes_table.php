<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('management_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('item');
            $table->decimal('income_amount', 12, 2);
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('gestion');
            $table->text('observation')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['mes', 'gestion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('management_incomes');
    }
};
