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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            
            // Polymorphic columns
            $table->unsignedBigInteger('reference_id'); // This stores the actual ID (1, 2, 3...)
            $table->string('reference_type'); // This stores the model name (App\Models\Submission, etc.)

            $table->string('message', 500);
            $table->boolean('is_read')->default(false);
            $table->enum('type', ['Urgent', 'Warning', 'Success', 'Info']);
            $table->timestamps();

            $table->index(['reference_id', 'reference_type']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
