<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('item_returns', function (Blueprint $table) {
        $table->foreignId('stock_movement_id')->constrained()->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('item_returns', function (Blueprint $table) {
        $table->dropForeign(['stock_movement_id']);
        $table->dropColumn('stock_movement_id');
    });
}

};
