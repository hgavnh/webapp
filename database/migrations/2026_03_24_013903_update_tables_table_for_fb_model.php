<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
            $table->unsignedBigInteger('room_id')->nullable()->after('tenant_id')->index();
            $table->string('name')->nullable()->after('room_id');
            $table->string('status')->default('available')->after('name'); // available, occupied, reserved
            $table->integer('capacity')->default(4)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'room_id', 'name', 'status', 'capacity']);
        });
    }
};
