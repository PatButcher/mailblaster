<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->string('from_name');
            $table->string('from_email');
            $table->string('reply_to')->nullable();
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->enum('recipient_filter', ['all', 'subscribed', 'tagged'])->default('subscribed');
            $table->string('tags_filter')->nullable();
            $table->enum('status', ['draft', 'queued', 'sending', 'completed', 'paused', 'cancelled'])->default('draft');
            $table->integer('total_recipients')->default(0);
            $table->integer('batch_size')->default(50);
            $table->integer('delay_between_batches')->default(60);
            $table->string('created_by')->nullable();
            $table->unsignedBigInteger('duplicated_from')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
};