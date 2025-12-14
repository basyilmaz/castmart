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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // API key tanımlayıcı adı
            $table->string('key', 64)->unique(); // SHA-256 hash
            $table->string('prefix', 8); // Görüntüleme için prefix (örn: cast_xxx)
            $table->unsignedBigInteger('user_id')->nullable(); // İlişkili kullanıcı
            $table->string('user_type')->nullable(); // admin, customer, app
            $table->json('permissions')->nullable(); // İzinler
            $table->json('rate_limits')->nullable(); // Özel rate limit
            $table->json('ip_whitelist')->nullable(); // İzin verilen IP'ler
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['key', 'is_active']);
            $table->index('user_id');
        });

        Schema::create('api_key_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_key_id');
            $table->string('endpoint');
            $table->string('method', 10);
            $table->string('ip');
            $table->integer('response_code');
            $table->integer('response_time_ms');
            $table->json('request_data')->nullable();
            $table->timestamp('created_at');

            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('cascade');
            $table->index(['api_key_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_key_logs');
        Schema::dropIfExists('api_keys');
    }
};
