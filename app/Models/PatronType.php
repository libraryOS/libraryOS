<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PatronTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class PatronType
 *
 * @property int $id
 * @property int $organization_id
 * @property string $key
 * @property string $name
 * @property string|null $name_translation_key
 * @property string|null $description
 * @property bool $is_active
 * @property int|null $membership_duration_days
 * @property int|null $max_loans
 * @property bool $keep_loan_history
 * @property bool $can_receive_notifications
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 */
class PatronType extends Model
{
    /** @use HasFactory<PatronTypeFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patron_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'key',
        'name',
        'name_translation_key',
        'description',
        'is_active',
        'membership_duration_days',
        'max_loans',
        'keep_loan_history',
        'can_receive_notifications',
        'minimum_age',
        'maximum_age',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'keep_loan_history' => 'boolean',
            'can_receive_notifications' => 'boolean',
        ];
    }

    /**
     * Get the organization that owns the patron type.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the patrons assigned to this patron type.
     *
     * @return HasMany<Patron, $this>
     */
    public function patrons(): HasMany
    {
        return $this->hasMany(Patron::class);
    }
}
