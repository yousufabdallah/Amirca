<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * السيارات يمكن تسجيلها باسم المكتب (بدون عميل) وعرضها في المعرض العام.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->foreign('client_id')->references('id')->on('clients')->restrictOnDelete();
            $table->boolean('is_listed')->default(true)->after('archived_at');
            $table->decimal('sale_price', 14, 3)->nullable()->after('is_listed');
            $table->text('public_notes')->nullable()->after('sale_price');
        });
        Schema::table('vehicle_documents', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
        Schema::table('vehicle_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['is_listed', 'sale_price', 'public_notes']);
        });
    }
};
