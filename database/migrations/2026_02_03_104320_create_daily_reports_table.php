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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->foreignId('document_id')->constrained('submissions', 'document_id')->onDelete('cascade');
            $table->string('report_title', 255);
            $table->text('accomplishments');
            $table->text('tasks_completed');
            $table->text('challenges');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
