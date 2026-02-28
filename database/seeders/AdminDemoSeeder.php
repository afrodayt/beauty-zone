<?php

namespace Database\Seeders;

use App\Enums\ClientStatus;
use App\Enums\ExpenseType;
use App\Enums\PackageStatus;
use App\Enums\PaymentMethod;
use App\Enums\VisitStatus;
use App\Models\Client;
use App\Models\ClientPackage;
use App\Models\Device;
use App\Models\Expense;
use App\Models\PackageRedemption;
use App\Models\PackageTemplate;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDemoSeeder extends Seeder
{
    /**
     * Seed demo data for admin area.
     */
    public function run(): void
    {
        $masters = collect([
            ['name' => 'Anna Master', 'email' => 'anna.master@example.com'],
            ['name' => 'Olga Master', 'email' => 'olga.master@example.com'],
        ])->map(static fn (array $data): User => User::query()->firstOrCreate(
            ['email' => $data['email']],
            ['name' => $data['name'], 'password' => Hash::make('password')]
        ));

        $services = collect([
            ['name' => 'Laser Epilation', 'code' => 'laser-epilation', 'color' => '#0d6efd', 'duration_minutes' => 60, 'base_price' => 95],
            ['name' => 'Skin Care', 'code' => 'skin-care', 'color' => '#20c997', 'duration_minutes' => 45, 'base_price' => 70],
            ['name' => 'Massage', 'code' => 'massage', 'color' => '#fd7e14', 'duration_minutes' => 50, 'base_price' => 80],
        ])->map(static fn (array $data): Service => Service::query()->firstOrCreate(
            ['code' => $data['code']],
            [...$data, 'is_active' => true]
        ));

        $devices = collect([
            ['name' => 'Laser Pro X', 'purchased_at' => '2025-02-01', 'cost' => 12000],
            ['name' => 'Hydra Skin', 'purchased_at' => '2024-10-15', 'cost' => 8500],
        ])->map(static fn (array $data): Device => Device::query()->firstOrCreate(
            ['name' => $data['name']],
            [...$data, 'note' => 'Seed data']
        ));

        $clients = collect([
            ['full_name' => 'Elena Petrova', 'phone' => '+380500001111', 'status' => ClientStatus::ACTIVE->value],
            ['full_name' => 'Maria Bondar', 'phone' => '+380500002222', 'status' => ClientStatus::ACTIVE->value],
            ['full_name' => 'Inna Koval', 'phone' => '+380500003333', 'status' => ClientStatus::NEW->value],
            ['full_name' => 'Yana Marchenko', 'phone' => '+380500004444', 'status' => ClientStatus::LOST->value],
        ])->map(static fn (array $data): Client => Client::query()->firstOrCreate(
            ['phone' => $data['phone']],
            [
                ...$data,
                'birth_date' => '1990-01-01',
                'notes' => 'Seeded client',
                'contra_pregnancy' => false,
                'contra_allergy' => false,
                'contra_skin_damage' => false,
                'contra_varicose' => false,
            ]
        ));

        $template = PackageTemplate::query()->firstOrCreate(
            ['name' => 'Laser 6 sessions'],
            [
                'service_id' => $services->firstWhere('code', 'laser-epilation')?->id,
                'procedure_count' => 6,
                'price' => 480,
                'duration_days' => 180,
                'description' => 'Best value package',
                'is_active' => true,
            ]
        );

        $package = ClientPackage::query()->firstOrCreate(
            ['client_id' => $clients[0]->id, 'name' => 'Laser 6 sessions'],
            [
                'package_template_id' => $template->id,
                'total_procedures' => 6,
                'remaining_procedures' => 5,
                'purchased_amount' => 480,
                'expires_at' => CarbonImmutable::now('UTC')->addMonths(5),
                'status' => PackageStatus::ACTIVE->value,
            ]
        );

        $visitOne = Visit::query()->firstOrCreate(
            ['client_id' => $clients[0]->id, 'starts_at' => CarbonImmutable::now('UTC')->subDays(2)],
            [
                'service_id' => $services[0]->id,
                'device_id' => $devices[0]->id,
                'master_id' => $masters[0]->id,
                'client_package_id' => $package->id,
                'zone' => 'Legs',
                'price' => 95,
                'payment_method' => PaymentMethod::PACKAGE->value,
                'visit_status' => VisitStatus::ARRIVED->value,
                'master_comment' => 'Good tolerance',
                'deduct_from_package' => true,
            ]
        );

        PackageRedemption::query()->firstOrCreate(
            ['visit_id' => $visitOne->id],
            [
                'client_package_id' => $package->id,
                'procedures_deducted' => 1,
                'redeemed_at' => $visitOne->starts_at,
                'note' => 'Seed redemption',
            ]
        );

        $visitTwo = Visit::query()->firstOrCreate(
            ['client_id' => $clients[1]->id, 'starts_at' => CarbonImmutable::now('UTC')->subDay()],
            [
                'service_id' => $services[1]->id,
                'device_id' => $devices[1]->id,
                'master_id' => $masters[1]->id,
                'zone' => 'Face',
                'price' => 70,
                'payment_method' => PaymentMethod::CARD->value,
                'visit_status' => VisitStatus::ARRIVED->value,
                'master_comment' => 'Hydration program',
                'deduct_from_package' => false,
            ]
        );

        Payment::query()->firstOrCreate(
            ['client_id' => $clients[1]->id, 'visit_id' => $visitTwo->id],
            [
                'amount' => 70,
                'payment_method' => PaymentMethod::CARD->value,
                'paid_at' => $visitTwo->starts_at,
                'note' => 'Seed payment',
            ]
        );

        Payment::query()->firstOrCreate(
            ['client_id' => $clients[0]->id, 'visit_id' => null, 'amount' => 480],
            [
                'payment_method' => PaymentMethod::CARD->value,
                'paid_at' => CarbonImmutable::now('UTC')->subDays(7),
                'note' => 'Package purchase',
            ]
        );

        Expense::query()->firstOrCreate(
            ['type' => ExpenseType::RENT->value, 'date' => CarbonImmutable::now('UTC')->startOfMonth()->toDateString()],
            [
                'amount' => 1500,
                'note' => 'Monthly rent',
            ]
        );

        Expense::query()->firstOrCreate(
            ['type' => ExpenseType::SUPPLIES->value, 'date' => CarbonImmutable::now('UTC')->subDays(3)->toDateString()],
            [
                'amount' => 240,
                'note' => 'Consumables',
            ]
        );
    }
}
