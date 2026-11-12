<?php
namespace W5D;

use RuntimeException;
use Throwable;

/**
 * Request kernel. handle() is pure (no globals) so the route-matrix test can call it.
 * run() is the real web entry point used by index.php.
 */
final class App
{
    public static function run(): void
    {
        $res = self::handle(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
            $_GET,
            $_POST,
            $_COOKIE,
            $_SERVER
        );
        http_response_code($res['status']);
        foreach (self::securityHeaders() as $k => $v) {
            header("{$k}: {$v}");
        }
        foreach ($res['headers'] ?? [] as $k => $v) {
            header("{$k}: {$v}");
        }
        if (($res['method'] ?? '') === 'HEAD') {
            return;
        }
        echo $res['body'];
    }

    /** @return array{status:int,headers:array<string,string>,body:string} */
    public static function handle(string $method, string $uri, array $get, array $post, array $cookies, array $server = []): array
    {
        $method = strtoupper($method);
        $path = rawurldecode((string) (parse_url($uri, PHP_URL_PATH) ?: '/'));
        $query = (string) (parse_url($uri, PHP_URL_QUERY) ?? '');
        if (!in_array($method, ['GET', 'HEAD', 'POST'], true)) {
            return ['status' => 405, 'headers' => ['Allow' => 'GET, HEAD, POST'], 'body' => 'Method not allowed', 'method' => $method];
        }
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return self::error(400, 'Bad request', $method);
        }
        $admin = preg_quote(trim((string) Config::app('admin_path', 'panel-x9z'), '/'), '#');
        $needsSession = $method === 'POST' || (bool) preg_match("#^/(?:app|contact|trust/corrections|{$admin})(?:/|$)#", $path);
        try {
            if ($needsSession) {
                Session::start();
            }
            Lang::set(Lang::detect($cookies, (string) ($server['HTTP_ACCEPT_LANGUAGE'] ?? '')));
            $req = [
                'method' => $method, 'path' => $path, 'get' => $get, 'post' => $post,
                'cookies' => $cookies, 'server' => $server,
            ];
            $res = self::dispatch($req);
        } catch (Throwable $e) {
            $id = substr(bin2hex(random_bytes(6)), 0, 12);
            self::log("request={$id} path={$path} error=" . $e->getMessage());
            $res = self::error(500, Config::app('debug') ? $e->getMessage() : t('error_generic', $id), $method);
        }
        $res['method'] = $method;
        if ($method === 'HEAD') {
            $res['body'] = '';
        }
        return $res;
    }

    private static function dispatch(array $req): array
    {
        $match = Router::match($req['method'], $req['path']);
        if ($match === null) {
            return self::error(404, null, $req['method']);
        }
        [$handler, $p] = $match;
        return match ($handler) {
            'page' => Controllers\Site::page($p['id']),
            'index' => Controllers\Site::index($p['id']),
            'index' => Controllers\Site::index($p['id']),
            'macro' => Controllers\Site::macro($p['slug']),
            'service' => Controllers\Site::service($p['slug']),
            'project' => Controllers\Site::project($p['slug']),
            'layer' => Controllers\Site::layer($p['slug']),
            'governorate' => Controllers\Site::governorate($p['slug']),
            'notfound' => self::error(404, null, $req['method']),
            'kpis' => Controllers\Api::kpis(),
            'sitemap' => Controllers\Api::sitemap(),
            'robots' => Controllers\Api::robots(),
            'register' => Controllers\Register::handle($req),
            'form' => Controllers\Forms::handle($req, $p['id']),
            'language' => self::language($req, $p['code']),
            'portal' => Controllers\Portal::handle($req, $p['path']),
            default => self::error(404, null, $req['method']),
        };
    }

    private static function language(array $req, string $code): array
    {
        $back = (string) ($req['get']['back'] ?? '/');
        if (!str_starts_with($back, '/') || str_starts_with($back, '//') || str_contains($back, "\n")) {
            $back = '/';
        }
        $code = in_array($code, Config::app('languages', ['en']), true) ? $code : 'en';
        $secure = self::isHttps($req['server']);
        setcookie('w5d_lang', $code, [
            'expires' => time() + 60 * 60 * 24 * 365, 'path' => '/', 'secure' => $secure,
            'httponly' => false, 'samesite' => 'Lax',
        ]);
        return ['status' => 303, 'headers' => ['Location' => $back, 'Cache-Control' => 'no-store'], 'body' => ''];
    }

    public static function error(int $status, ?string $message, string $method = 'GET'): array
    {
        $title = $status === 404 ? t('err404_title') : t('err_title');
        $body = View::page('error', ['status' => $status, 'message' => $message]);
        $res = Page::wrap($title, '', $body, '/404', $status, true);
        $res['headers']['Cache-Control'] = 'no-store';
        return $res;
    }

    public static function securityHeaders(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), camera=(), microphone=()',
            'Content-Security-Policy' => "default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; "
                . "font-src 'self'; connect-src 'self'; form-action 'self'; frame-ancestors 'self'; base-uri 'self'; object-src 'none'",
        ];
    }

    public static function isHttps(array $server): bool
    {
        return (($server['HTTPS'] ?? '') !== '' && ($server['HTTPS'] ?? '') !== 'off')
            || (($server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }

    public static function log(string $line): void
    {
        $dir = W5D_ROOT . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        @file_put_contents($dir . '/app.log', gmdate('c') . ' ' . $line . "\n", FILE_APPEND | LOCK_EX);
    }

    /** Client IP as seen by the server. Only the first hop behind a trusted proxy is honoured. */
    public static function ip(array $server): string
    {
        $ip = (string) ($server['REMOTE_ADDR'] ?? '0.0.0.0');
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}
