<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendeeController;
use Illuminate\Support\Facades\Route;

/*
 * Authentication Routes
 */
Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

/*
 * Public route to list all organizations (no auth required)
 */
Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations.index');

/*
 * Authenticated Routes (Admin-only)
 */
Route::middleware('auth:sanctum')->group(function () {
    // Logout route
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Organization routes except index (index is public now)
    Route::apiResource('organizations', OrganizationController::class)
        ->only(['store', 'show', 'update', 'destroy'])
        ->parameters(['organizations' => 'slug']);
});

/*
 * Tenant-scoped Routes (Events and Attendees)
 */
Route::prefix('{org_slug}')->middleware('tenant')->group(function () {
    // Public routes - anyone can list and view events (no auth)
    Route::apiResource('events', EventController::class)
        ->only(['index', 'show']);

    // Authenticated routes for managing events and attendees
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('events', EventController::class)
            ->except(['index', 'show']); // store, update, destroy

        // Attendee routes for events (all standard resource routes enabled)
        Route::apiResource('events.attendees', AttendeeController::class)
            ->middleware('throttle:60,1');

        // Soft delete management routes for events
        Route::get('events/trashed', [EventController::class, 'trashed'])->name('events.trashed');
        Route::post('events/{event}/restore', [EventController::class, 'restore'])->name('events.restore');
        Route::delete('events/{event}/force-delete', [EventController::class, 'forceDelete'])->name('events.forceDelete');
    });
});
