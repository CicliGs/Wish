<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $wishLists = DB::table('wish_lists')->whereNull('uuid')->get();
        foreach ($wishLists as $wishList) {
            DB::table('wish_lists')->where('id', $wishList->id)->update([
                'uuid' => (string) Str::uuid(),
            ]);
        }
    }

    public function down(): void
    {
    }
}; 