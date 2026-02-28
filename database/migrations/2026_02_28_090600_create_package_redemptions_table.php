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
        Schema::create('package_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_package_id')->constrained('client_packages')->cascadeOnDelete();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->unsignedInteger('procedures_deducted')->default(1);
            $table->dateTime('redeemed_at');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique('visit_id');
            $table->index('redeemed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_redemptions');
    }
};
