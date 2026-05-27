<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PatronLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class PatronLog
 *
 * @property int $id
 * @property int $organization_id
 * @property int $patron_id
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property string $action
 * @property string|null $description
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 */
class PatronLog extends Model
{
    /** @use HasFactory<PatronLogFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patron_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'patron_id',
        'actor_type',
        'actor_id',
        'action',
        'description',
        'metadata',
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the organization that owns the patron log.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the patron that this log entry is about.
     *
     * @return BelongsTo<Patron, $this>
     */
    public function patron(): BelongsTo
    {
        return $this->belongsTo(Patron::class);
    }

    /**
     * Get the actor who performed this log action.
     *
     * @return MorphTo<Model, $this>
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
