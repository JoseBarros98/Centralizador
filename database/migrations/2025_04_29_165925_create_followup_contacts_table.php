<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_followups_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['call', 'message']);
            $table->date('contact_date');
            $table->boolean('got_response')->default(false);
            $table->date('response_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followup_contacts');
    }
};
