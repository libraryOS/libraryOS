<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Class Member
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $user_id
 * @property int|null $role_id
 * @property string|null $timezone
 * @property Carbon|null $birthdate
 * @property Carbon|null $joined_at
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 */
class Member extends Model
{
    use HasFactory;

    protected $table = 'members';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'user_id',
        'role_id',
        'timezone',
        'birthdate',
        'joined_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'birthdate' => 'date',
        'joined_at' => 'datetime',
    ];

    /**
     * Get the user record associated with the member.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Organization record associated with the member.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the role record associated with the member.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Determine whether the member has a given permission key through their assigned role.
     */
    public function hasPermission(string $key): bool
    {
        if ($this->role_id === null) {
            return false;
        }

        return $this->role()
            ->whereHas('permissions', fn ($query) => $query->where('permissions.key', $key))
            ->exists();
    }

    /**
     * Get the permission keys that the member has through their assigned role.
     *
     * @return Collection<int, string>
     */
    public function getPermissions(): Collection
    {
        if ($this->role_id === null) {
            return collect();
        }

        return $this->role->permissions->pluck('key');
    }
}
