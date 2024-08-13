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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trans_user_id');
            $table->date('trans_date');
            $table->decimal('trans_amount', 10, 2);
            $table->enum('trans_type', ['credit', 'debit']);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

              $table->foreign('trans_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
