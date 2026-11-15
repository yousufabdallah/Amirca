<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_no', 20)->nullable()->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('full_name')->index();
            $table->string('phone', 30)->index();
            $table->string('phone_alt', 30)->nullable();
            $table->text('civil_id')->nullable();            // مشفّر
            $table->string('civil_id_hash', 64)->nullable()->unique(); // للبحث ومنع التكرار
            $table->string('civil_id_last4', 4)->nullable();
            $table->string('email')->nullable();
            $table->string('governorate', 100)->nullable();
            $table->string('wilayat', 100)->nullable();
            $table->string('address')->nullable();
            $table->text('internal_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('vehicle_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 20)->default('#1f3a68');
            $table->string('icon', 50)->default('bi-circle');
            // المجموعة تُستخدم في الإحصائيات: قبل الشراء / تم الشراء / شحن / وصلت / تم التسليم
            $table->enum('stage_group', ['pre_purchase', 'purchased', 'shipping', 'arrived', 'delivered'])->default('purchased');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('file_no', 20)->nullable()->unique();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('vehicle_statuses')->restrictOnDelete();
            // بيانات السيارة
            $table->string('make', 80)->index();
            $table->string('model', 80);
            $table->string('trim', 80)->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('color', 50)->nullable();
            $table->string('vin', 30)->nullable()->index();
            $table->string('fuel_type', 30)->nullable();
            $table->string('engine_size', 30)->nullable();
            $table->string('transmission', 30)->nullable();
            $table->unsignedInteger('mileage')->nullable();
            $table->string('mileage_unit', 5)->default('mi');
            $table->string('condition', 100)->nullable();
            $table->text('notes')->nullable();
            // بيانات الشراء
            $table->string('purchase_location')->nullable();
            $table->string('auction_name', 100)->nullable();
            $table->string('auction_url', 500)->nullable();
            $table->date('purchase_date')->nullable()->index();
            $table->decimal('purchase_price', 14, 3)->default(0);
            $table->decimal('auction_fees', 14, 3)->default(0);
            $table->decimal('inland_transport', 14, 3)->default(0);
            $table->string('cost_currency', 3)->default('OMR');
            $table->text('purchase_notes')->nullable();
            // بيانات الشحن
            $table->string('departure_port', 100)->nullable();
            $table->string('arrival_port', 100)->nullable();
            $table->string('shipping_company', 100)->nullable();
            $table->string('vessel_name', 100)->nullable();
            $table->string('container_no', 50)->nullable()->index();
            $table->date('shipping_date')->nullable();
            $table->date('eta')->nullable()->index();
            $table->date('arrived_at')->nullable();
            // الحساب المالي
            $table->decimal('total_due', 14, 3)->default(0);
            $table->string('currency', 3)->default('OMR');
            $table->text('financial_notes')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('vehicle_cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('amount', 14, 3);
            $table->timestamps();
        });

        Schema::create('vehicle_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['vehicle_id', 'sort_order']);
        });

        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->cascadeOnDelete();
            $table->enum('type', ['purchase_invoice', 'shipping', 'customs', 'client', 'other'])->default('other');
            $table->string('title');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->boolean('visible_to_client')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('vehicle_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('type', ['status', 'note', 'eta', 'arrival', 'created'])->default('status');
            $table->foreignId('from_status_id')->nullable()->constrained('vehicle_statuses')->nullOnDelete();
            $table->foreignId('to_status_id')->nullable()->constrained('vehicle_statuses')->nullOnDelete();
            $table->text('note')->nullable();
            $table->boolean('visible_to_client')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 30)->unique();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->decimal('amount', 14, 3);
            $table->string('currency', 3)->default('OMR');
            $table->date('paid_at')->index();
            $table->enum('method', ['cash', 'bank_transfer', 'card', 'other']);
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'cancelled'])->default('active')->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('category', 100)->index();
            $table->string('description');
            $table->decimal('amount', 14, 3);
            $table->date('expense_date')->index();
            $table->string('reference', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('internal_notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');
            $table->text('body');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('icon', 50)->default('bi-bell');
            $table->string('dedupe_key', 150)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'dedupe_key']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description', 500);
            $table->json('changes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->text('message');
            $table->boolean('is_handled')->default(false);
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['inquiries', 'audit_logs', 'notifications', 'internal_notes', 'expenses', 'payments', 'vehicle_status_history',
            'vehicle_documents', 'vehicle_images', 'vehicle_cost_items', 'vehicles', 'vehicle_statuses', 'clients', 'settings'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
