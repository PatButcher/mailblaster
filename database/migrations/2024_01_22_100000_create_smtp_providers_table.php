<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('smtp_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->integer('port')->default(587);
            $table->string('username');
            $table->string('password');
            $table->enum('encryption', ['tls', 'ssl', 'none'])->default('tls');
            $table->string('from_email');
            $table->string('from_name');
            $table->integer('max_daily_emails')->default(500);
            $table->integer('daily_sent_count')->default(0);
            $table->integer('total_sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('priority')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamp('daily_reset_at')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->string('test_status')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('smtp_providers');
    }
};