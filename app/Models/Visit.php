<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\VisitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'service_id',
        'device_id',
        'master_id',
        'client_package_id',
        'zone',
        'starts_at',
        'price',
        'payment_method',
        'visit_status',
        'master_comment',
        'deduct_from_package',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'price' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'visit_status' => VisitStatus::class,
            'deduct_from_package' => 'bool',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function clientPackage(): BelongsTo
    {
        return $this->belongsTo(ClientPackage::class);
    }

    public function packageRedemption(): HasOne
    {
        return $this->hasOne(PackageRedemption::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
