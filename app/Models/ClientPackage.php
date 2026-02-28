<?php

namespace App\Models;

use App\Enums\PackageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPackage extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'package_template_id',
        'name',
        'total_procedures',
        'remaining_procedures',
        'purchased_amount',
        'expires_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_procedures' => 'int',
            'remaining_procedures' => 'int',
            'purchased_amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'status' => PackageStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PackageTemplate::class, 'package_template_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PackageRedemption::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
