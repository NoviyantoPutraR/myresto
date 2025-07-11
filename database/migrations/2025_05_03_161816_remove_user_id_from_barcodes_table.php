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
        Schema::table('barcodes', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // kalau ada foreign key
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('barcodes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(); // jika ingin bisa dibatalkan
            // $table->foreign('user_id')->references('id')->on('users'); // optional
        });
    }
};
