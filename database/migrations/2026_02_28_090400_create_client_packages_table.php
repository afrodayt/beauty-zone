<?php

use App\Enums\PackageStatus;
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
        Schema::create('client_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('package_template_id')->nullable()->constrained('package_templates')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('total_procedures');
            $table->unsignedInteger('remaining_procedures');
            $table->decimal('purchased_amount', 10, 2)->default(0);
            $table->dateTime('expires_at')->nullable();
            $table->string('status', 20)->default(PackageStatus::ACTIVE->value);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_packages');
    }
};
