<?php

declare(strict_types=1);

use App\Http\Controllers\App\Organization\Adminland\AdminlandController;
use App\Http\Controllers\App\Organization\Adminland\BranchController;
use App\Http\Controllers\App\Organization\Adminland\ItemTypeController;
use App\Http\Controllers\App\Organization\Adminland\LocationController;
use App\Http\Controllers\App\Organization\Adminland\MemberController;
use App\Http\Controllers\App\Organization\Adminland\PatronTypeController;
use App\Http\Controllers\App\Organization\Adminland\RoleController;
use App\Http\Controllers\App\Organization\JoinOrganizationController;
use App\Http\Controllers\App\Organization\OrganizationController;
use App\Http\Controllers\App\Settings\ApiKeyController;
use App\Http\Controllers\App\Settings\AutoDeleteAccountController;
use App\Http\Controllers\App\Settings\EmailSentController;
use App\Http\Controllers\App\Settings\LogController;
use App\Http\Controllers\App\Settings\PasswordController;
use App\Http\Controllers\App\Settings\RecoveryCodeController;
use App\Http\Controllers\App\Settings\SecurityController;
use App\Http\Controllers\App\Settings\SettingsController;
use App\Http\Controllers\App\Settings\TwoFAController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/marketing.php';

Route::put('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware(['auth', 'verified', 'throttle:60,1', 'set.locale'])->group(function (): void {
    Route::get('organizations', [OrganizationController::class, 'index'])->name('organization.index');

    // create
    Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organization.create');
    Route::post('organizations', [OrganizationController::class, 'store'])->name('organization.store');

    // join
    Route::get('organizations/join', [JoinOrganizationController::class, 'create'])->name('organization.join.create');
    Route::post('organizations/join', [JoinOrganizationController::class, 'store'])->name('organization.join.store');

    Route::middleware(['organization'])->group(function (): void {
        Route::get('organizations/{slug}', [OrganizationController::class, 'show'])->name('organization.show');

        // adminland
        Route::prefix('organizations/{slug}/adminland')->group(function (): void {
            Route::get('', [AdminlandController::class, 'index'])->name('organization.adminland.index');

            // members
            Route::get('/members', [MemberController::class, 'index'])->name('organization.adminland.member.index');

            // roles
            Route::get('/roles', [RoleController::class, 'index'])->name('organization.adminland.role.index');

            // branches
            Route::get('/branches', [BranchController::class, 'index'])->name('organization.adminland.branch.index');
            Route::get('/branches/create', [BranchController::class, 'create'])->name('organization.adminland.branch.create');
            Route::post('/branches', [BranchController::class, 'store'])->name('organization.adminland.branch.store');
            Route::get('/branches/{branch}', [BranchController::class, 'edit'])->name('organization.adminland.branch.edit');
            Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('organization.adminland.branch.update');
            Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('organization.adminland.branch.destroy');

            // item types
            Route::get('/item-types', [ItemTypeController::class, 'index'])->name('organization.adminland.item-type.index');
            Route::get('/item-types/create', [ItemTypeController::class, 'create'])->name('organization.adminland.item-type.create');
            Route::post('/item-types', [ItemTypeController::class, 'store'])->name('organization.adminland.item-type.store');
            Route::get('/item-types/{item_type}', [ItemTypeController::class, 'edit'])->name('organization.adminland.item-type.edit');
            Route::put('/item-types/{item_type}', [ItemTypeController::class, 'update'])->name('organization.adminland.item-type.update');
            Route::delete('/item-types/{item_type}', [ItemTypeController::class, 'destroy'])->name('organization.adminland.item-type.destroy');

            // patron types
            Route::get('/patron-types', [PatronTypeController::class, 'index'])->name('organization.adminland.patron-type.index');
            Route::get('/patron-types/create', [PatronTypeController::class, 'create'])->name('organization.adminland.patron-type.create');
            Route::post('/patron-types', [PatronTypeController::class, 'store'])->name('organization.adminland.patron-type.store');
            Route::get('/patron-types/{patron_type}', [PatronTypeController::class, 'edit'])->name('organization.adminland.patron-type.edit');
            Route::put('/patron-types/{patron_type}', [PatronTypeController::class, 'update'])->name('organization.adminland.patron-type.update');
            Route::delete('/patron-types/{patron_type}', [PatronTypeController::class, 'destroy'])->name('organization.adminland.patron-type.destroy');

            // locations
            Route::get('/locations', [LocationController::class, 'index'])->name('organization.adminland.location.index');
            Route::get('/locations/create', [LocationController::class, 'create'])->name('organization.adminland.location.create');
            Route::post('/locations', [LocationController::class, 'store'])->name('organization.adminland.location.store');
            Route::get('/locations/{location}', [LocationController::class, 'edit'])->name('organization.adminland.location.edit');
            Route::put('/locations/{location}', [LocationController::class, 'update'])->name('organization.adminland.location.update');
            Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('organization.adminland.location.destroy');
        });
    });

    // settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings/profile', [SettingsController::class, 'update'])->name('settings.profile.update');

    // log dedicated page
    Route::get('settings/logs', [LogController::class, 'index'])->name('settings.logs.index');

    // emails dedicated page
    Route::get('settings/emails', [EmailSentController::class, 'index'])->name('settings.emails.index');

    // security
    Route::get('settings/security', [SecurityController::class, 'index'])->name('settings.security.index');
    Route::put('settings/security/password', [PasswordController::class, 'update'])->name('settings.security.password.update');

    // 2fa
    Route::get('settings/security/2fa/create', [TwoFAController::class, 'create'])->name('settings.security.2fa.create');
    Route::post('settings/security/2fa', [TwoFAController::class, 'store'])->name('settings.security.2fa.store');
    Route::delete('settings/security/2fa', [TwoFAController::class, 'destroy'])->name('settings.security.2fa.destroy');
    Route::get('settings/security/recovery-codes', [RecoveryCodeController::class, 'show'])->name('settings.security.recoverycodes.show');

    // auto delete account
    Route::put('settings/security/auto-delete-account', [AutoDeleteAccountController::class, 'update'])->name('settings.security.auto-delete.update');

    // api
    Route::get('settings/api-keys/create', [ApiKeyController::class, 'create'])->name('settings.api-keys.create');
    Route::post('settings/api-keys', [ApiKeyController::class, 'store'])->name('settings.api-keys.store');
    Route::delete('settings/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('settings.api-keys.destroy');
});

require __DIR__.'/auth.php';
