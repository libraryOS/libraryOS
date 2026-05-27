<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Adminland\BranchController;
use App\Http\Controllers\Api\Adminland\ItemTypeController;
use App\Http\Controllers\Api\Adminland\LocationController;
use App\Http\Controllers\Api\Adminland\MemberController;
use App\Http\Controllers\Api\Adminland\PatronTypeController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function (): void {
    Route::get('health', [HealthController::class, 'show'])->middleware('throttle:60,1');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {
        // organizations
        Route::post('organizations', [OrganizationController::class, 'create'])->name('organization.create');
        Route::get('organizations', [OrganizationController::class, 'index'])->name('organization.index');
        Route::middleware(['organization.api'])->group(function (): void {
            Route::get('organizations/{id}', [OrganizationController::class, 'show'])->name('organization.show');
            Route::put('organizations/{id}', [OrganizationController::class, 'update'])->name('organization.update');
            Route::delete('organizations/{id}', [OrganizationController::class, 'destroy'])->name('organization.destroy');

            // adminland - branches
            Route::get('organizations/{id}/adminland/branches', [BranchController::class, 'index'])->name('organization.adminland.branch.index');
            Route::post('organizations/{id}/adminland/branches', [BranchController::class, 'store'])->name('organization.adminland.branch.store');
            Route::get('organizations/{id}/adminland/branches/{branchId}', [BranchController::class, 'show'])->name('organization.adminland.branch.show');
            Route::put('organizations/{id}/adminland/branches/{branchId}', [BranchController::class, 'update'])->name('organization.adminland.branch.update');
            Route::delete('organizations/{id}/adminland/branches/{branchId}', [BranchController::class, 'destroy'])->name('organization.adminland.branch.destroy');

            // adminland - item types
            Route::get('organizations/{id}/adminland/item-types', [ItemTypeController::class, 'index'])->name('organization.adminland.item-type.index');
            Route::post('organizations/{id}/adminland/item-types', [ItemTypeController::class, 'store'])->name('organization.adminland.item-type.store');
            Route::get('organizations/{id}/adminland/item-types/{itemTypeId}', [ItemTypeController::class, 'show'])->name('organization.adminland.item-type.show');
            Route::put('organizations/{id}/adminland/item-types/{itemTypeId}', [ItemTypeController::class, 'update'])->name('organization.adminland.item-type.update');
            Route::delete('organizations/{id}/adminland/item-types/{itemTypeId}', [ItemTypeController::class, 'destroy'])->name('organization.adminland.item-type.destroy');

            // adminland - members
            Route::get('organizations/{id}/adminland/members', [MemberController::class, 'index'])->name('organization.adminland.member.index');
            Route::get('organizations/{id}/adminland/members/{memberId}', [MemberController::class, 'show'])->name('organization.adminland.member.show');

            // adminland - patron types
            Route::get('organizations/{id}/adminland/patron-types', [PatronTypeController::class, 'index'])->name('organization.adminland.patron-type.index');
            Route::post('organizations/{id}/adminland/patron-types', [PatronTypeController::class, 'store'])->name('organization.adminland.patron-type.store');
            Route::get('organizations/{id}/adminland/patron-types/{patronTypeId}', [PatronTypeController::class, 'show'])->name('organization.adminland.patron-type.show');
            Route::put('organizations/{id}/adminland/patron-types/{patronTypeId}', [PatronTypeController::class, 'update'])->name('organization.adminland.patron-type.update');
            Route::delete('organizations/{id}/adminland/patron-types/{patronTypeId}', [PatronTypeController::class, 'destroy'])->name('organization.adminland.patron-type.destroy');

            // adminland - locations
            Route::get('organizations/{id}/adminland/locations', [LocationController::class, 'index'])->name('organization.adminland.location.index');
            Route::post('organizations/{id}/adminland/locations', [LocationController::class, 'store'])->name('organization.adminland.location.store');
            Route::get('organizations/{id}/adminland/locations/{locationId}', [LocationController::class, 'show'])->name('organization.adminland.location.show');
            Route::put('organizations/{id}/adminland/locations/{locationId}', [LocationController::class, 'update'])->name('organization.adminland.location.update');
            Route::delete('organizations/{id}/adminland/locations/{locationId}', [LocationController::class, 'destroy'])->name('organization.adminland.location.destroy');
        });
    });
});
