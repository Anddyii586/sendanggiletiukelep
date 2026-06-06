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
        Schema::table('services', function (Blueprint $table): void {
            if (! Schema::hasColumn('services', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('services', 'pricing_type')) {
                $table->enum('pricing_type', ['per_person', 'per_trip'])->default('per_person')->after('price');
            }

            if (! Schema::hasColumn('services', 'image_path')) {
                $table->string('image_path')->nullable()->after('pricing_type');
            }

            if (! Schema::hasColumn('services', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('services', 'sort_order')) {
                $table->unsignedInteger('sort_order')->nullable()->after('is_featured');
            }
        });

        DB::table('services')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function (object $service): void {
                $baseSlug = Str::slug($service->name) ?: 'package-'.$service->id;
                $slug = $baseSlug;
                $counter = 2;

                while (
                    DB::table('services')
                        ->where('slug', $slug)
                        ->where('id', '!=', $service->id)
                        ->exists()
                ) {
                    $slug = $baseSlug.'-'.$counter++;
                }

                DB::table('services')->where('id', $service->id)->update([
                    'slug' => $slug,
                    'pricing_type' => DB::raw("COALESCE(pricing_type, 'per_person')"),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            foreach (['sort_order', 'is_featured', 'image_path', 'pricing_type', 'slug'] as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
