<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * データベースのメインシータ
     */
    public function run(): void
    {
        // BingoItemSeederを実行
        $this->call([
            BingoItemSeeder::class,
        ]);
    }
}
