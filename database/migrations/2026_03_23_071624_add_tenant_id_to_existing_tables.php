<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tạo 1 cửa hàng (Tenant) mặc định cho dữ liệu cũ (Dữ liệu của Hưng Thịnh hiện tại)
        $defaultTenantId = DB::table('tenants')->insertGetId([
            'name' => 'Hưng Thịnh Coffee (CS1)',
            'subdomain' => 'hungthinh1',
            'plan_type' => 'premium',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Thêm tenant_id vào các bảng có sẵn
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
            $table->unsignedBigInteger('table_id')->nullable()->after('tenant_id')->index();
            $table->unsignedBigInteger('cashier_id')->nullable()->after('note')->index();
        });

        // Cập nhật toàn bộ data cũ sang tenant mặc định
        DB::table('categories')->update(['tenant_id' => $defaultTenantId]);
        DB::table('products')->update(['tenant_id' => $defaultTenantId]);
        DB::table('orders')->update(['tenant_id' => $defaultTenantId]);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'table_id', 'cashier_id']);
        });
    }
};
