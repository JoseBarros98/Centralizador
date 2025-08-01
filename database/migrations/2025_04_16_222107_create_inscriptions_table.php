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
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); //Código generado en base al nombre y ci
            $table->string('first_name');
            $table->string('paternal_surname')->nullable();
            $table->string('maternal_surname')->nullable();
            $table->string('ci');
            $table->string('phone');
            $table->string('program_id')->constrained('');
            $table->enum('payment_plan', ['Crédito', 'Contado']);
            $table->enum('payment_method', ['QR', 'Efectivo', 'Deposito', 'Transferencia']);
            $table->decimal('enrollment_fee', 10, 2);
            $table->decimal('first_installment', 10, 2);
            $table->decimal('total_paid', 10, 2);
            $table->string('receipt_numbre')->nullable();
            $table->enum('status', ['Completo', 'Adelanto', 'Completando']);
            $table->enum('gender', ['Masculino', 'Femenino']);
            $table->string('profession');
            $table->string('residence');
            $table->string('location'); //Sede
            $table->date('inscription_date');
            $table->text('notes')->nullable();
            $table->text('certification')->nullable();

            //Documentación
            $table->boolean('has_identity_card')->default(false);
            $table->boolean('has_degree_title')->default(false);
            $table->boolean('has_academic_diploma')->default(false);
            $table->boolean('has_birth_certificate')->default(false);
            $table->boolean('has_commitment_letter')->default(false);
            $table->string('commitment_letter_path')->nullable();
            $table->text('document_observations')->nullable();


            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
