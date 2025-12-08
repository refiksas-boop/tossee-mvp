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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('listing_type_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('price_type', ['fixed', 'negotiable', 'free', 'on_request', 'monthly', 'hourly', 'per_m2', 'per_day'])->default('fixed');
            $table->string('currency', 3)->default('EUR');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'archived'])->default('pending');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->integer('view_count')->default(0);
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('contact_via_messages')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('category_id');
            $table->index('subcategory_id');
            $table->index('status');
            $table->index('published_at');
            $table->index(['is_featured', 'is_urgent']);
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
