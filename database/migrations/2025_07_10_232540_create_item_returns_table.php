<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_movement_id')
                ->constrained()
                ->onDelete('cascade');

            $table->unsignedInteger('good_condition')->default(0);
            $table->unsignedInteger('damaged_quantity')->default(0);
            $table->unsignedInteger('lost_quantity')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_returns');
    }
};
