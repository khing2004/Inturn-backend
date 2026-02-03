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
        Schema::create('interns', function (Blueprint $table) {
            $table->id('intern_id');

            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('admins', 'admin_id')->onDelete('cascade');
            $table->string('university', 50);
            $table->string('department', 50);
            $table->string('supervisor', 50);
            $table->date('start_date');
            $table->string('phone_number', 20);
            $table->string('emergency_contact', 20);
            $table->string('emergency_contact_name', 50);
            $table->string('address', 100);
            $table->enum('status', ['Pending', 'Active', 'Inactive', 'Complete', 'Cancelled'])->default('Pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interns');
    }
};
