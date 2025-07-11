<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_reserved_until_to_barcodes_table.php
    public function up()
    {
        Schema::table('barcodes', function (Blueprint $table) {
            $table->timestamp('reserved_until')->nullable();
        });
    }

    public function down()
    {
        Schema::table('barcodes', function (Blueprint $table) {
            $table->dropColumn('reserved_until');
        });
    }
};
