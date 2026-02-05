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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id('document_id');
            $table->foreignId('intern_id')->constrained('interns', 'intern_id')->onDelete('cascade');
            $table->enum('type', ['Daily Report', 'Document', 'Other']);
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->timestamp('date_submitted');
            $table->string('description', 500)->nullable();
            $table->enum('status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
            $table->text('admin_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
