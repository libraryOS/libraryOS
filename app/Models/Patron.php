<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PatronFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class Patron
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $user_id
 * @property int $patron_type_id
 * @property int|null $home_branch_id
 * @property string $card_number
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string $status
 * @property Carbon|null $membership_expires_at
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 */
class Patron extends Model
{
    /** @use HasFactory<PatronFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patrons';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'user_id',
        'patron_type_id',
        'home_branch_id',
        'card_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'membership_expires_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'membership_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the organization that owns the patron.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the linked user account for this patron.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patron type assigned to this patron.
     *
     * @return BelongsTo<PatronType, $this>
     */
    public function patronType(): BelongsTo
    {
        return $this->belongsTo(PatronType::class);
    }

    /**
     * Get the patron's home branch.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function homeBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'home_branch_id');
    }

    /**
     * Get the log entries recorded for this patron.
     *
     * @return HasMany<PatronLog, $this>
     */
    public function patronLogs(): HasMany
    {
        return $this->hasMany(PatronLog::class);
    }

    /**
     * Get the patron logs where this patron acted as the actor.
     *
     * @return MorphMany<PatronLog, $this>
     */
    public function patronLogsAsActor(): MorphMany
    {
        return $this->morphMany(PatronLog::class, 'actor');
    }
}
