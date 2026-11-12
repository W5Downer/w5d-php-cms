<?php
namespace W5D\Controllers;

use W5D\App;
use W5D\Audit;
use W5D\Config;
use W5D\DB;
use W5D\Page;
use W5D\RateLimit;
use W5D\Scope;
use W5D\View;

/**
 * Contact and trust-correction forms. Stored in tickets (kind = contact|correction).
 * Validation, CSRF, rate limit and a honeypot field are all applied before a write.
 */
final class Forms
{
    public static function handle(array $req, string $id): array
    {
        $isPost = $req['method'] === 'POST';
        $values = [];
        $errors = [];
        $status = 200;
        $notice = null;
        $ip = App::ip($req['server']);

        if ($isPost) {
            $values = array_map(static fn ($v) => is_string($v) ? trim($v) : '', $req['post']);
            if (!csrf_valid($req['post']['_csrf'] ?? null)) {
                $notice = ['error', t('form_csrf')];
                $status = 403;
            } elseif (($values['website'] ?? '') !== '') {
                // Honeypot: pretend success, store nothing.
                $notice = ['ok', t('form_sent')];
            } elseif (!RateLimit::hit('form:' . $id . ':' . $ip, (int) Config::app('rate_limit_contact_per_hour', 5))) {
                $notice = ['error', t('form_rate')];
                $status = 429;
            } else {
                $errors = self::validate($id, $values);
                if ($errors === []) {
                    try {
                        self::store($id, $values, $ip);
                        $notice = ['ok', t('form_sent')];
                        $values = [];
                    } catch (\Throwable) {
                        $notice = ['error', t('error_generic', 'F-' . substr(md5((string) microtime()), 0, 8))];
                        $status = 500;
                    }
                } else {
                    $notice = ['error', t('form_error')];
                    $status = 422;
                }
            }
        }

        $isContact = $id === 'contact';
        $form = [
            'type' => 'form', 'id' => 'form-' . $id, 'tier' => 'E', 'focus' => 5,
            'title' => $isContact ? ['en' => 'Write to the right desk', 'fr' => 'Écrire au bon service', 'ar' => 'اكتب إلى الجهة المناسبة']
                : ['en' => 'File a correction', 'fr' => 'Signaler une correction', 'ar' => 'تقديم تصحيح'],
            'data' => [
                'action' => '/' . $id . '/',
                'submit' => ['en' => 'Send', 'fr' => 'Envoyer', 'ar' => 'إرسال'],
                'fields' => self::fields($id),
            ],
            'errors' => $errors,
            'values' => $values,
        ];
        $notice_html = '';
        if ($notice) {
            $notice_html = '<div class="' . ($notice[0] === 'ok' ? 'notice' : 'form-banner') . '" role="status">' . e($notice[1]) . '</div>';
        }
        $doc = ['id' => $id, 'path' => '/' . $id . '/', 'title' => $form['title'], 'blocks' => []];
        $body = '<h1>' . e(loc($form['title'])) . '</h1>' . $notice_html . View::block($form, $doc);
        return Page::wrap(loc($form['title']), '', $body, '/' . $id . '/', $status, false, false);
    }

    /** @return array<string,string> field => message */
    public static function validate(string $id, array $v): array
    {
        $e = [];
        if (($v['name'] ?? '') === '' || mb_strlen($v['name']) > 120) {
            $e['name'] = t('form_name_required');
        }
        if (!filter_var($v['email'] ?? '', FILTER_VALIDATE_EMAIL) || mb_strlen($v['email']) > 190) {
            $e['email'] = t('form_email_bad');
        }
        if (($v['message'] ?? '') === '' || mb_strlen($v['message']) < 10) {
            $e['message'] = t('form_message_short');
        } elseif (mb_strlen($v['message']) > 4000) {
            $e['message'] = t('form_message_long');
        }
        if ($id === 'trust-corrections') {
            if (($v['statement_ref'] ?? '') === '') {
                $e['statement_ref'] = t('form_ref_required');
            }
        }
        return $e;
    }

    private static function fields(string $id): array
    {
        $fields = [
            ['name' => 'name', 'label' => ['en' => 'Name', 'fr' => 'Nom', 'ar' => 'الاسم'], 'required' => true, 'max' => 120],
            ['name' => 'email', 'label' => ['en' => 'E-mail', 'fr' => 'E-mail', 'ar' => 'البريد الإلكتروني'], 'type' => 'email', 'required' => true],
        ];
        if ($id !== 'contact') {
            $fields[] = ['name' => 'statement_ref', 'label' => ['en' => 'Statement or figure to correct', 'fr' => 'Déclaration ou chiffre à corriger', 'ar' => 'البيان أو الرقم المراد تصحيحه'], 'required' => true, 'max' => 190,
                'help' => ['en' => 'For example: "Trust statement, section 5.6". We respond within the SLA shown on the corrections page.']];
        }
        $fields[] = ['name' => 'message', 'label' => ['en' => 'Message', 'fr' => 'Message', 'ar' => 'الرسالة'], 'type' => 'textarea', 'required' => true];
        $fields[] = ['name' => 'website', 'hidden' => true];
        return $fields;
    }

    private static function store(string $id, array $v, string $ip): void
    {
        $cid = Scope::countryId(Config::app('default_country', 'tn'));
        $kind = $id === 'contact' ? 'contact' : 'correction';
        $body = $v['message'] . ($kind === 'correction' ? "\n\nReference: " . $v['statement_ref'] : '');
        $tid = DB::insert('INSERT INTO tickets (country_id, kind, sender_name, sender_email, subject, body, status, due_at)
            VALUES (?, ?, ?, ?, ?, ?, \'open\', DATE_ADD(UTC_TIMESTAMP(), INTERVAL 14 DAY))', [
            $cid, $kind, substr($v['name'], 0, 120), substr($v['email'], 0, 190),
            $kind === 'contact' ? 'Contact' : 'Correction: ' . substr((string) ($v['statement_ref'] ?? ''), 0, 150),
            substr($body, 0, 4000),
        ]);
        Audit::write(null, $cid, 'form.' . $kind, 'ticket', (string) $tid, null, $ip);
    }
}
