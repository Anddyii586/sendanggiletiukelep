<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table): void {
            if (! Schema::hasColumn('galleries', 'cloudinary_public_id')) {
                $table->string('cloudinary_public_id')->nullable()->after('image_path');
            }

            if (! Schema::hasColumn('galleries', 'cloudinary_secure_url')) {
                $table->string('cloudinary_secure_url')->nullable()->after('cloudinary_public_id');
            }

            if (! Schema::hasColumn('galleries', 'storage_disk')) {
                $table->string('storage_disk')->nullable()->after('cloudinary_secure_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table): void {
            foreach (['storage_disk', 'cloudinary_secure_url', 'cloudinary_public_id'] as $column) {
                if (Schema::hasColumn('galleries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
