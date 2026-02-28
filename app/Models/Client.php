<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'phone',
        'birth_date',
        'status',
        'notes',
        'contra_pregnancy',
        'contra_allergy',
        'contra_skin_damage',
        'contra_varicose',
        'last_visit_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'status' => ClientStatus::class,
            'contra_pregnancy' => 'bool',
            'contra_allergy' => 'bool',
            'contra_skin_damage' => 'bool',
            'contra_varicose' => 'bool',
            'last_visit_at' => 'datetime',
        ];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function clientPackages(): HasMany
    {
        return $this->hasMany(ClientPackage::class);
    }
}
