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
            $table->unsignedBigInteger('intern_id');
            $table->unsignedBigInteger('admin_id');
            $table->integer('technical_skills_rating');
            $table->integer('communication_rating');
            $table->text('admin_comments');
            $table->date('evaluation_date');
            $table->enum('period', ['Weekly', 'Monthly']);
            $table->timestamps();

            // Foreign keys
            $table->foreign('intern_id')->references('intern_id')->on('interns')->onDelete('cascade');
            $table->foreign('admin_id')->references('admin_id')->on('admins')->onDelete('cascade');
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
