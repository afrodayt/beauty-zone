<?php

use App\Enums\ClientStatus;
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
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('phone');
            $table->date('birth_date')->nullable();
            $table->string('status', 20)->default(ClientStatus::NEW->value);
            $table->text('notes')->nullable();
            $table->boolean('contra_pregnancy')->default(false);
            $table->boolean('contra_allergy')->default(false);
            $table->boolean('contra_skin_damage')->default(false);
            $table->boolean('contra_varicose')->default(false);
            $table->dateTime('last_visit_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('phone');
            $table->index('status');
            $table->index('last_visit_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
