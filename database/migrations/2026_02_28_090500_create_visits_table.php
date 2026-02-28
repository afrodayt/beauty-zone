<?php

use App\Enums\PaymentMethod;
use App\Enums\VisitStatus;
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
        Schema::create('visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('master_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_package_id')->nullable()->constrained('client_packages')->nullOnDelete();
            $table->string('zone');
            $table->dateTime('starts_at');
            $table->decimal('price', 10, 2);
            $table->string('payment_method', 20)->default(PaymentMethod::CASH->value);
            $table->string('visit_status', 20)->default(VisitStatus::SCHEDULED->value);
            $table->text('master_comment')->nullable();
            $table->boolean('deduct_from_package')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('starts_at');
            $table->index('visit_status');
            $table->index('payment_method');
            $table->index('master_id');
            $table->index('service_id');
            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
