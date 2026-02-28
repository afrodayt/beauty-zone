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
        Schema::create('package_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->unsignedInteger('procedure_count');
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('duration_days')->default(90);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_templates');
    }
};
