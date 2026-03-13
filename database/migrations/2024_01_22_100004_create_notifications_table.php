<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('icon')->default('bell');
            $table->string('color')->default('blue');
            $table->string('link')->nullable();
            $table->boolean('read')->default(false);
            $table->json('data')->nullable();
            $table->timestamps();
            $table->index(['read', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_notifications');
    }
};