<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ClientStatus;
use App\Enums\ExpenseType;
use App\Enums\PackageStatus;
use App\Enums\PaymentMethod;
use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPackage;
use App\Models\Device;
use App\Models\PackageTemplate;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class MetaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'clients' => Client::query()->select(['id', 'full_name', 'phone'])->orderBy('full_name')->limit(200)->get(),
            'services' => Service::query()->select(['id', 'name', 'color', 'base_price'])->where('is_active', true)->orderBy('name')->get(),
            'devices' => Device::query()->select(['id', 'name'])->orderBy('name')->get(),
            'masters' => User::query()->select(['id', 'name', 'email'])->orderBy('name')->get(),
            'package_templates' => PackageTemplate::query()->select(['id', 'name', 'procedure_count', 'price'])->where('is_active', true)->orderBy('name')->get(),
            'active_packages' => ClientPackage::query()
                ->select(['id', 'client_id', 'name', 'remaining_procedures', 'expires_at'])
                ->where('status', PackageStatus::ACTIVE->value)
                ->where('remaining_procedures', '>', 0)
                ->orderByDesc('expires_at')
                ->limit(500)
                ->get(),
            'enums' => [
                'client_statuses' => ClientStatus::values(),
                'visit_statuses' => VisitStatus::values(),
                'payment_methods' => PaymentMethod::values(),
                'package_statuses' => PackageStatus::values(),
                'expense_types' => ExpenseType::values(),
            ],
        ]);
    }
}
