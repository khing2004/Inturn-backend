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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id('evaluation_id');
            $table->foreignId('intern_id')->constrained('interns', 'intern_id')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('admins', 'admin_id')->onDelete('cascade');
            $table->integer('technical_skills_rating');
            $table->integer('communication_rating');
            $table->text('admin_comments');
            $table->date('evaluation_date');
            $table->enum('period', ['Weekly', 'Monthly']);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
