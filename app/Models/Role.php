<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Role
 *
 * @property int $id
 * @property int|null $organization_id
 * @property string $key
 * @property string|null $name
 * @property string|null $name_translation_key
 * @property string|null $description
 * @property bool $is_system
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 */
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

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
        'is_system',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    /**
     * Get the organization that owns this role.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the display name of the role.
     * Returns the name field if set, otherwise returns the translated value of
     * name_translation_key.
     */
    public function getName(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }

        return __($this->name_translation_key ?? '');
    }
}
