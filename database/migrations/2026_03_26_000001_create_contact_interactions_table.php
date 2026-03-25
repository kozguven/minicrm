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
        Schema::create('contact_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 32);
            $table->dateTime('happened_at');
            $table->string('summary');
            $table->text('notes')->nullable();
            $table->dateTime('follow_up_due_at')->nullable();
            $table->dateTime('follow_up_completed_at')->nullable();
            $table->timestamps();

            $table->index(['contact_id', 'happened_at'], 'ci_contact_happened_idx');
            $table->index(['follow_up_completed_at', 'follow_up_due_at'], 'ci_followup_state_due_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_interactions');
    }
};
