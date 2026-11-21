<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vehicles', 'sold_at')) {
            Schema::table('vehicles', fn (Blueprint $t) => $t->timestamp('sold_at')->nullable()->after('is_listed'));
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', fn (Blueprint $t) => $t->dropColumn('sold_at'));
    }
};
