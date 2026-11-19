<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shop admins (Zach, 2026-11-13): who may manage which operator.
 *
 * Being a shop admin is a grant on top of a normal account, not a new
 * role_id. One person can manage several operators, and an operator can
 * have several admins.
 *
 *   operator_admins        the grant itself; revoking sets revoked_at
 *   operator_invites       a single use link that ends in a grant. Only the
 *                          SHA-256 of the token is stored, never the token
 *   operator_claims        "this is my shop" requests, approved by us
 *   operator_admin_audit   one row for every grant, revoke and shop edit
 *
 * Additive. Four new tables on mysql_trips, nothing existing is altered.
 * No foreign keys: operators is not ours to constrain and users live on
 * the other connection.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        $schema = Schema::connection('mysql_trips');

        if (!$schema->hasTable('operator_admins')) {
            $schema->create('operator_admins', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('operator_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('granted_by')->nullable();
                $table->string('granted_via', 20);          // invite, promotion, claim
                $table->unsignedBigInteger('invite_id')->nullable();
                $table->dateTime('revoked_at')->nullable();
                $table->timestamps();
                $table->unique(['operator_id', 'user_id']);
                $table->index('user_id');
            });
        }

        if (!$schema->hasTable('operator_invites')) {
            $schema->create('operator_invites', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('operator_id');
                $table->char('token_hash', 64)->unique();
                $table->string('email', 190)->nullable();   // if set, only this address may accept
                $table->unsignedBigInteger('created_by');
                $table->dateTime('expires_at');
                $table->dateTime('accepted_at')->nullable();
                $table->unsignedBigInteger('accepted_user_id')->nullable();
                $table->dateTime('revoked_at')->nullable();
                $table->timestamps();
                $table->index('operator_id');
            });
        }

        if (!$schema->hasTable('operator_claims')) {
            $schema->create('operator_claims', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('operator_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role_at_shop', 80)->nullable();
                $table->string('contact_phone', 40)->nullable();
                $table->text('note')->nullable();
                $table->string('status', 20)->default('pending'); // pending, approved, declined
                $table->unsignedBigInteger('decided_by')->nullable();
                $table->dateTime('decided_at')->nullable();
                $table->timestamps();
                $table->index(['operator_id', 'status']);
                $table->index('user_id');
            });
        }

        if (!$schema->hasTable('operator_admin_audit')) {
            $schema->create('operator_admin_audit', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('operator_id');
                $table->unsignedBigInteger('user_id');
                $table->string('action', 60);
                $table->string('subject_type', 60)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->json('changes')->nullable();
                $table->dateTime('created_at');
                $table->index(['operator_id', 'created_at']);
            });
        }
    }

    public function down()
    {
        $schema = Schema::connection('mysql_trips');
        $schema->dropIfExists('operator_admin_audit');
        $schema->dropIfExists('operator_claims');
        $schema->dropIfExists('operator_invites');
        $schema->dropIfExists('operator_admins');
    }
};
