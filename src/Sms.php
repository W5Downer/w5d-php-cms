<?php
namespace W5D;

/**
 * OTP by SMS: interface only (README Task C2). The stub adapter sends nothing, so no phone
 * number is ever verified by it. A real processor must be contracted and tested before
 * verified-identity claims are published (report §2.2: "A mock OTP flow is not verified identity").
 */
interface SmsAdapter
{
    /** Queue a one-time code for delivery. Returns false when the adapter cannot send. */
    public function send(string $phone, string $message, string $lang): bool;
}

final class StubSms implements SmsAdapter
{
    public function send(string $phone, string $message, string $lang): bool
    {
        // Record the attempt as "queued" only. The message body is not stored and nothing is sent.
        return false;
    }
}

final class Sms
{
    public static function adapter(): SmsAdapter
    {
        return match (Config::app('sms_adapter', 'stub')) {
            default => new StubSms(),
        };
    }
}
