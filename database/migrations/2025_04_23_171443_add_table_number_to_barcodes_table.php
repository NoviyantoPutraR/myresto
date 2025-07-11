<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableNumberToBarcodesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('barcodes', 'table_number')) {
            Schema::table('barcodes', function (Blueprint $table) {
                $table->string('table_number')->nullable();
            });
        }
    }


    public function down()
    {
        Schema::table('barcodes', function (Blueprint $table) {
            $table->dropColumn('table_number'); // Menghapus kolom table_number jika migrasi di-rollback
        });
    }
}
