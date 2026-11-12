<?php
namespace W5D;

/** Append-only audit trail. The application never UPDATEs or DELETEs audit_log rows. */
final class Audit
{
    public static function write(?int $actor, ?int $countryId, string $action, ?string $subject, ?string $subjectId, ?string $detail, ?string $ip): void
    {
        try {
            DB::run('INSERT INTO audit_log (country_id, actor_id, action, subject, subject_id, detail, ip_hash)
                VALUES (?, ?, ?, ?, ?, ?, ?)', [
                $countryId, $actor, substr($action, 0, 64), $subject, $subjectId,
                $detail === null ? null : substr($detail, 0, 500),
                $ip === null ? null : hash_hmac('sha256', $ip, Config::app('app_key', 'dev')),
            ]);
        } catch (\Throwable) {
            // Auditing must not break the request; failures are logged to the app log.
            App::log('audit write failed: ' . $action);
        }
    }
}
