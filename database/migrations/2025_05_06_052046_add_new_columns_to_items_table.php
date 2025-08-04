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
        Schema::create('items', function (Blueprint $table) {
            $table->id()->unassigned()->autoIncrement();
$table->string('name');
$table->foreignId('category_id')->constrained()->cascadeOnDelete();
$table->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
$table->foreignId('group_id')->nullable()->constrained()->cascadeOnDelete();
$table->string('model')->nullable();
$table->enum('status', ['available', 'damaged', 'lost'])->default('available');
$table->string('serial_number')->nullable();
$table->enum('unit', ['Kg', 'Cartons', 'PC', 'L','M','Sqm'])->default('PC');
$table->integer('quantity');
$table->string('flight_case')->nullable();
$table->text('remarks')->nullable();
$table->string('image')->nullable();
$table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            //
        });
    }
};
