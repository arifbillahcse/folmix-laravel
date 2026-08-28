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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('username')->nullable()->after('last_name');
            $table->string('vat_number')->nullable()->after('phone');
            $table->string('address')->nullable()->after('vat_number');
            $table->string('postcode')->nullable()->after('address');
            $table->string('city')->nullable()->after('postcode');
            $table->string('country')->nullable()->after('city');
            $table->string('website')->nullable()->after('country');

            $table->unique(['username', 'channel_id'], 'customers_username_channel_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_username_channel_unique');

            $table->dropColumn([
                'username',
                'vat_number',
                'address',
                'postcode',
                'city',
                'country',
                'website',
            ]);
        });
    }
};
