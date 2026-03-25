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
        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreignId('owner_user_id')->nullable()->after('company_id')->constrained('users')->nullOnDelete();
            $table->string('lead_source', 64)->nullable()->after('phone');
            $table->string('lead_status', 32)->default('new')->after('lead_source');
            $table->string('priority', 16)->default('medium')->after('lead_status');
            $table->timestamp('last_contacted_at')->nullable()->after('priority');
        });

        Schema::table('opportunities', function (Blueprint $table): void {
            $table->foreignId('owner_user_id')->nullable()->after('contact_id')->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('probability')->default(50)->after('value');
            $table->string('next_step')->nullable()->after('expected_close_date');
            $table->timestamp('next_step_due_at')->nullable()->after('next_step');
            $table->string('health_status', 32)->default('watch')->after('next_step_due_at');
        });

        Schema::table('crm_tasks', function (Blueprint $table): void {
            $table->foreignId('assigned_user_id')->nullable()->after('opportunity_id')->constrained('users')->nullOnDelete();
            $table->string('priority', 16)->default('medium')->after('title');
            $table->string('task_type', 32)->default('manual')->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_tasks', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropColumn(['priority', 'task_type']);
        });

        Schema::table('opportunities', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropColumn(['probability', 'next_step', 'next_step_due_at', 'health_status']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropColumn(['lead_source', 'lead_status', 'priority', 'last_contacted_at']);
        });
    }
};
