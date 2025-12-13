<?php

namespace CastMart\SMS\Contracts;

interface SmsDriverInterface
{
    /**
     * SMS gönder
     */
    public function send(string $to, string $message, array $options = []): array;

    /**
     * Toplu SMS gönder
     */
    public function sendBulk(array $recipients, string $message, array $options = []): array;

    /**
     * OTP gönder
     */
    public function sendOtp(string $to, string $code): array;

    /**
     * SMS durumu sorgula
     */
    public function getStatus(string $messageId): array;

    /**
     * Bakiye sorgula
     */
    public function getBalance(): array;

    /**
     * Driver adını getir
     */
    public function getName(): string;

    /**
     * Driver aktif mi?
     */
    public function isEnabled(): bool;
}
