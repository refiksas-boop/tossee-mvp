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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['text', 'textarea', 'number', 'select', 'multiselect', 'checkbox', 'radio', 'boolean', 'date']);
            $table->string('unit')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('position')->default(0);
            $table->boolean('filterable')->default(false);
            $table->timestamps();

            $table->index('category_id');
            $table->index('subcategory_id');
            $table->index('filterable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
