<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('smtp_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject')->nullable();
            $table->enum('status', ['queued', 'sending', 'sent', 'failed', 'paused', 'cancelled'])->default('queued');
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->text('smtp_log')->nullable();
            $table->string('message_id')->nullable();
            $table->string('smtp_response_code')->nullable();
            $table->text('smtp_banner')->nullable();
            $table->boolean('is_single_send')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->index(['campaign_id', 'status']);
            $table->index(['recipient_email']);
            $table->index(['status', 'created_at']);
            $table->index(['is_single_send']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_logs');
    }
};