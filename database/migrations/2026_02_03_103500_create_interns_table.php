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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('admin_id');
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

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('admin_id')->on('admins')->onDelete('cascade');
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
