<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_requests', function (Blueprint $table) {
            $table->id();
            $table->string('server_id', 64);
            $table->string('url');
            $table->string('method', 10);
            $table->string('client_ip', 45);
            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('response_time');
            $table->string('user_agent', 255)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['server_id', 'created_at']);
            $table->index('created_at');
            $table->index('status_code');
            $table->index('response_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cover_requests');
    }
};
