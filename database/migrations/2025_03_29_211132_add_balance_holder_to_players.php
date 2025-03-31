<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('balance_holder_id')->nullable()->constrained('players')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['balance_holder_id']);
            $table->dropColumn('balance_holder_id');
        });
    }
};
