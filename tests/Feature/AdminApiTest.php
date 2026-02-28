<?php

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Enums\PackageStatus;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Enums\VisitStatus;
use App\Models\Client;
use App\Models\ClientPackage;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_it_creates_client_via_admin_api(): void
    {
        $response = $this->postJson('/api/admin/clients', [
            'full_name' => 'Test Client',
            'phone' => '+100000001',
            'birth_date' => '1995-02-15',
            'status' => ClientStatus::NEW->value,
            'notes' => 'Test note',
            'contra_pregnancy' => false,
            'contra_allergy' => true,
            'contra_skin_damage' => false,
            'contra_varicose' => false,
        ]);

        $response->assertCreated()->assertJsonPath('data.full_name', 'Test Client');

        $this->assertDatabaseHas('clients', [
            'phone' => '+100000001',
            'status' => ClientStatus::NEW->value,
        ]);
    }

    public function test_it_deducts_package_when_visit_created(): void
    {
        $client = Client::query()->create([
            'full_name' => 'Package Client',
            'phone' => '+100000002',
            'status' => ClientStatus::ACTIVE,
        ]);
        $service = Service::query()->create([
            'name' => 'Laser',
            'code' => 'laser',
            'color' => '#0d6efd',
            'duration_minutes' => 60,
            'base_price' => 100,
            'is_active' => true,
        ]);
        $master = User::factory()->create();
        $package = ClientPackage::query()->create([
            'client_id' => $client->id,
            'name' => 'Pack',
            'total_procedures' => 5,
            'remaining_procedures' => 2,
            'purchased_amount' => 300,
            'status' => PackageStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/admin/visits', [
            'client_id' => $client->id,
            'service_id' => $service->id,
            'device_id' => null,
            'master_id' => $master->id,
            'client_package_id' => $package->id,
            'zone' => 'Legs',
            'starts_at' => now('UTC')->addDay()->toIso8601String(),
            'price' => 100,
            'payment_method' => PaymentMethod::PACKAGE->value,
            'visit_status' => VisitStatus::ARRIVED->value,
            'master_comment' => 'ok',
            'deduct_from_package' => true,
        ]);

        $response->assertOk();
        $package->refresh();

        $this->assertSame(1, $package->remaining_procedures);
        $this->assertDatabaseCount('package_redemptions', 1);
    }

    public function test_it_creates_master_user_via_admin_api(): void
    {
        $response = $this->postJson('/api/admin/users', [
            'name' => 'Master User',
            'email' => 'master@example.com',
            'role' => UserRole::MASTER->value,
            'password' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'master@example.com')
            ->assertJsonPath('data.role', UserRole::MASTER->value);

        $this->assertDatabaseHas('users', [
            'email' => 'master@example.com',
            'role' => UserRole::MASTER->value,
        ]);
    }
}
