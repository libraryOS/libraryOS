<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Adminland\BranchController;
use App\Http\Controllers\Api\Adminland\MemberController;
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

            // adminland - members
            Route::get('organizations/{id}/adminland/members', [MemberController::class, 'index'])->name('organization.adminland.member.index');
            Route::get('organizations/{id}/adminland/members/{memberId}', [MemberController::class, 'show'])->name('organization.adminland.member.show');
        });
    });
});
