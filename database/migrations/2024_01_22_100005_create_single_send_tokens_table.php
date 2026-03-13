<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('single_send_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->longText('body_html');
            $table->text('body_text')->nullable();
            $table->foreignId('smtp_provider_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'sent', 'failed'])->default('pending');
            $table->text('result_log')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('single_send_tokens');
    }
};