<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->withTrashed()
            ->where(function ($query): void {
                foreach (User::PROTECTED_ADMIN_IDENTITIES as $identity) {
                    $query->orWhere(function ($identityQuery) use ($identity): void {
                        $identityQuery
                            ->where('email', $identity['email'])
                            ->where('name', $identity['name']);
                    });
                }
            })
            ->update(['role' => User::ROLE_ADMIN]);
    }

    public function down(): void
    {
        // The previous role of these users is unknown, so do not downgrade them on rollback.
    }
};
