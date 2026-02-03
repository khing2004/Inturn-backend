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
            $table->unsignedBigInteger('document_id');
            $table->string('report_title', 255);
            $table->text('accomplishments');
            $table->text('tasks_completed');
            $table->text('challenges');
            $table->timestamps();

            // Foreign key
            $table->foreign('document_id')->references('document_id')->on('submissions')->onDelete('cascade');
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
