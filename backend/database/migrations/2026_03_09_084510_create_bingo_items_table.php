<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションの実行
     */
    public function up(): void
    {
        Schema::create('bingo_items', function (Blueprint $table) {
            $table->id();
            // ボードIDへの関連付け (UserではなくBoardに紐付ける)
            $table->foreignId('bingo_board_id')->constrained()->cascadeOnDelete();
            $table->string('label')->comment('マス目の内容');
            $table->boolean('is_achieved')->default(false)->comment('達成フラグ');
            $table->date('achieved_at')->nullable()->comment('達成日');
            $table->unsignedTinyInteger('position')->comment('位置座標（0-24）');
            $table->timestamps();
        });
    }

    /**
     * マイグレーションのリバース
     */
    public function down(): void
    {
        Schema::dropIfExists('bingo_items');
    }
};
