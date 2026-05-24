<?php

declare(strict_types=1);

namespace App\Actions;

use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Jobs\PopulateOrganization;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Create an organization for a user.
 * The user will be added to the organization as the first user.
 */
class CreateOrganization
{
    private Organization $organization;

    public function __construct(
        public User $user,
        public string $name,
    ) {}

    public function execute(): Organization
    {
        $this->sanitize();
        $this->validate();
        $this->create();
        $this->generateSlug();
        $this->generateInvitationCode();
        $this->addMembership();
        $this->populate();
        $this->log();

        return $this->organization;
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);
    }

    private function validate(): void
    {
        // make sure the organization name doesn't contain any special characters
        if (in_array(preg_match('/^[a-zA-Z0-9\s\-_]+$/', $this->name), [0, false], true)) {
            throw ValidationException::withMessages([
                'organization_name' => 'Organization name can only contain letters, numbers, spaces, hyphens and underscores',
            ]);
        }

        // make sure the organization name is not part of a reserved list of keywords
        $reservedNames = config('app.reserved_organization_keywords', []);
        if (Str::is($reservedNames, Str::lower($this->name))) {
            throw ValidationException::withMessages([
                'organization_name' => 'Organization name cannot contain reserved words like admin, support, contact, etc.',
            ]);
        }
    }

    private function create(): void
    {
        $this->organization = Organization::query()->create([
            'name' => $this->name,
        ]);
    }

    private function generateSlug(): void
    {
        $slug = $this->organization->id.'-'.Str::of($this->name)->slug('-');

        $this->organization->slug = $slug;
    }

    private function generateInvitationCode(): void
    {
        $this->organization->invitation_code = Str::random(64);
        $this->organization->save();
    }

    private function addMembership(): void
    {
        Member::query()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'joined_at' => now(),
        ]);
    }

    private function populate(): void
    {
        PopulateOrganization::dispatch($this->organization)->onQueue('low');
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: 'organization_creation',
            description: sprintf('Created an organization called %s', $this->name),
        )->onQueue('low');
    }
}
