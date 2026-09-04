<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rating_requests', function (Blueprint $table): void {
            $table->string('status')->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedInteger('token_version')->default(1);
            $table->foreignId('replacement_of_id')->nullable()
                ->constrained('rating_requests')->nullOnDelete();
        });

        DB::table('rating_requests')->select(['id', 'created_at'])->orderBy('id')->get()
            ->each(function (object $request): void {
                $createdAt = Carbon::parse($request->created_at);

                DB::table('rating_requests')->where('id', $request->id)->update([
                    'sent_at' => $createdAt,
                    'expires_at' => $createdAt->copy()->addHours(72),
                    'status' => 'pending',
                    'token_version' => 1,
                ]);
            });

        Schema::create('rating_request_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rating_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('previous_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('new_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->string('type');
            $table->timestamp('expires_at')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->index(['rating_request_id', 'type']);
        });

        DB::table('rating_requests')->select([
            'id', 'player_id', 'expires_at', 'created_at', 'updated_at',
        ])->orderBy('id')->get()->each(function (object $request): void {
            DB::table('rating_request_events')->insert([
                'rating_request_id' => $request->id,
                'new_player_id' => $request->player_id,
                'type' => 'initial_send',
                'expires_at' => $request->expires_at,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_request_events');

        Schema::table('rating_requests', function (Blueprint $table): void {
            $table->dropForeign(['replacement_of_id']);
            $table->dropColumn([
                'status', 'sent_at', 'expires_at', 'completed_at',
                'revoked_at', 'token_version', 'replacement_of_id',
            ]);
        });
    }
};
