<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private function generateUid(string $prefix): string
    {
        return strtoupper($prefix.'_'.Str::random(8));
    }

    private function ensureColumnAndPopulate(string $table, string $prefix): void
    {
        if (! Schema::hasColumn($table, 'uid')) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('uid', 20)->after('id');
            });
        }

        $rows = DB::table($table)->where('uid', '')->orWhereNull('uid')->get();

        foreach ($rows as $row) {
            DB::table($table)->where('id', $row->id)->update(['uid' => $this->generateUid($prefix)]);
        }

        // Add unique constraint if not already present
        $indexes = Schema::getIndexes($table);
        $hasUnique = collect($indexes)->contains(fn ($index) => in_array('uid', $index['columns']) && $index['unique']);

        if (! $hasUnique) {
            Schema::table($table, function (Blueprint $table) {
                $table->unique('uid');
            });
        }
    }

    public function up(): void
    {
        $this->ensureColumnAndPopulate('categories', 'CAT');
        $this->ensureColumnAndPopulate('subcategories', 'SUB');
        $this->ensureColumnAndPopulate('products', 'PRD');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('uid');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn('uid');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
};
