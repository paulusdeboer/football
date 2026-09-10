<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->text('token')->nullable();
            $table->boolean('enabled')->default(false);
            $table->string('group_id')->nullable();
            $table->string('group_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('connection_status')->default('not_configured');
            $table->timestamp('checked_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
        DB::table('whatsapp_settings')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('action_key')->unique();
            $table->foreignId('game_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('retry_of_id')->nullable()->constrained('whatsapp_messages')->nullOnDelete();
            $table->string('kind');
            $table->string('destination')->nullable();
            $table->unsignedInteger('settings_version');
            $table->text('body');
            $table->string('request_hash', 64);
            $table->string('status')->default('prepared');
            $table->string('error_code')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_settings');
    }
};
