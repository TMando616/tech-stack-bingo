<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bingo_boards', function (Blueprint $table) {
            // ボード一覧取得 (orderBy('created_at', 'desc')) 用
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('bingo_items', function (Blueprint $table) {
            // ボード表示 (orderBy('position')) 用
            $table->index(['bingo_board_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bingo_boards', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('bingo_items', function (Blueprint $table) {
            $table->dropIndex(['bingo_board_id', 'position']);
        });
    }
};
