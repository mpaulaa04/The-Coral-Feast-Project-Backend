<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_item_categories', function (Blueprint $table): void {
            $table->string('slug')
                ->after('name')
                ->nullable()
                ->comment('Unique slug identifier for the inventory item category');
        });

        $mappings = [
            'Peces' => 'fish',
            'Plantas' => 'plants',
            'Suplementos' => 'supplements',
        ];

        DB::table('inventory_item_categories')->get()->each(function ($category) use ($mappings): void {
            $slug = $mappings[$category->name] ?? Str::slug($category->name);

            DB::table('inventory_item_categories')
                ->where('id', $category->id)
                ->update(['slug' => $slug]);
        });

        DB::table('inventory_item_categories')
            ->whereNull('slug')
            ->orWhere('slug', '=','')
            ->update(['slug' => DB::raw('CONCAT("category-", id)')]);

        DB::statement('ALTER TABLE inventory_item_categories MODIFY slug VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE inventory_item_categories ADD UNIQUE KEY inventory_item_categories_slug_unique (slug)');
    }

    public function down(): void
    {
        Schema::table('inventory_item_categories', function (Blueprint $table): void {
            $table->dropUnique('inventory_item_categories_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
