<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_type')->default('card')->after('category_id');
        });

        // Copy existing payment method types stored in `type` to `transaction_type`
        DB::table('transactions')
            ->whereIn('type', ['card', 'cash'])
            ->update(['transaction_type' => DB::raw('type'), 'type' => 'expense']);

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type')->default('expense')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type')->default('card')->change();
            $table->dropColumn('transaction_type');
        });
    }
};
