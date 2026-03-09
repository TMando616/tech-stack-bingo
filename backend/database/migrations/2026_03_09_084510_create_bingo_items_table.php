<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションの実行
     * bingo_itemsテーブルを作成し、マスの内容、達成フラグ、達成日、位置を定義します。
     */
    public function up(): void
    {
        Schema::create('bingo_items', function (Blueprint $table) {
            $table->id();
            $table->string('label')->comment('マス目の内容（技術名など）');
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
