<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ItemTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ItemType
 *
 * @property int $id
 * @property int $organization_id
 * @property string $key
 * @property string|null $name
 * @property string|null $name_translation_key
 * @property string|null $description
 * @property bool $is_loanable
 * @property bool $is_holdable
 * @property bool $is_visible_in_catalog
 * @property int|null $default_loan_days
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 */
class ItemType extends Model
{
    /** @use HasFactory<ItemTypeFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_types';

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
        'is_loanable',
        'is_holdable',
        'is_visible_in_catalog',
        'default_loan_days',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_loanable' => 'boolean',
            'is_holdable' => 'boolean',
            'is_visible_in_catalog' => 'boolean',
        ];
    }

    /**
     * Get the organization that owns the item type.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the display name of the item type.
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
