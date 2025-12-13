<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SMS Log tablosu
        Schema::create('castmart_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->text('message');
            $table->string('message_id', 100)->nullable()->index();
            $table->string('provider', 50)->default('netgsm');
            $table->string('status', 30)->default('sent'); // sent, delivered, failed, pending
            $table->string('type', 30)->nullable(); // order, otp, marketing, etc.
            $table->decimal('cost', 10, 4)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['phone', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        // OTP Doğrulama tablosu
        Schema::create('castmart_otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('code', 10);
            $table->string('message_id', 100)->nullable();
            $table->timestamp('expires_at');
            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->boolean('expired')->default(false);
            $table->timestamps();
            
            $table->index(['phone', 'code', 'verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('castmart_otp_verifications');
        Schema::dropIfExists('castmart_sms_logs');
    }
};
