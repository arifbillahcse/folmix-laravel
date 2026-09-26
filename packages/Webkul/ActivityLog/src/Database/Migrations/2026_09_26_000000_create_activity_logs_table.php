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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            /**
             * Who performed the action. Stored as a type/id pair (rather than
             * a foreign key) plus a snapshotted name/email, so the log stays
             * intact and readable even after the admin or customer account
             * is later deleted.
             */
            $table->string('causer_type', 20)->nullable();
            $table->unsignedInteger('causer_id')->nullable();
            $table->string('causer_name')->nullable();

            /**
             * What happened: login, logout, login_failed, created, updated,
             * deleted.
             */
            $table->string('event', 30);

            /**
             * What it happened to, e.g. "Product" / "Order". Also stored as
             * a type/id pair with a snapshotted label for the same reason as
             * the causer above.
             */
            $table->string('subject_type', 60)->nullable();
            $table->unsignedInteger('subject_id')->nullable();
            $table->string('subject_name')->nullable();

            $table->text('description')->nullable();

            /**
             * Changed attributes (old/new) for update events, free-form
             * context for everything else.
             */
            $table->json('properties')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['causer_type', 'causer_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('event');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
