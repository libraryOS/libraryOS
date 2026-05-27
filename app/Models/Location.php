<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $organization_id
 * @property int $branch_id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $code
 * @property string|null $description
 * @property bool $is_active
 * @property bool $is_public
 * @property bool $supports_pickups
 * @property bool $supports_returns
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 */
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    protected $table = 'locations';

    protected $fillable = [
        'organization_id', 'branch_id', 'parent_id',
        'name', 'code', 'description',
        'is_active', 'is_public', 'supports_pickups', 'supports_returns',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'supports_pickups' => 'boolean',
            'supports_returns' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }
}
