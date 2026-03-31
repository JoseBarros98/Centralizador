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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique()->nullable()->comment('id_docente de la BD externa');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('bank')->nullable();
            $table->string('account_number')->nullable();
            $table->enum('bill', ['Si', 'No'])->default('No');
            $table->enum('esam_worker', ['Si', 'No'])->default('No');
            $table->string('profession')->nullable();
            $table->string('ci')->nullable();
            $table->enum('academic_degree', ['Lic.', 'Ing.', 'Dr.', 'M.Sc.', 'Ph.D.','M.Sc. Ing.', 'M.Sc. Lic.', 'M.Sc. Dr.', 'Ph.D. Ing.', 'Ph.D. Lic.'])->nullable();
            $table->boolean('is_external')->default(false)->comment('Indica si el docente viene de la BD externa');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
