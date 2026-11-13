<?php
namespace W5D;

/**
 * Portal authentication. Passwords are Argon2id (password_hash); sessions regenerate on
 * login and logout; failed logins are rate limited per IP.
 */
final class Auth
{
    private static ?array $user = null;

    /** Forget the cached user (tests switch sessions within one process). */
    public static function reset(): void
    {
        self::$user = null;
    }

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        $id = (int) ($_SESSION['uid'] ?? 0);
        if ($id <= 0 || !DB::available()) {
            return null;
        }
        $row = DB::one('SELECT u.id, u.username, u.email, u.role_code, u.country_id, u.disabled, c.iso AS country_iso
            FROM users u LEFT JOIN countries c ON c.id = u.country_id WHERE u.id = ?', [$id]);
        if ($row === null || (int) $row['disabled'] === 1) {
            return null;
        }
        return self::$user = $row;
    }

    /** @return true|string  true on success, or an error key */
    public static function attempt(string $username, string $password, string $ip): bool|string
    {
        if (!RateLimit::hit('login:' . $ip, (int) Config::app('rate_limit_login_per_hour', 10))) {
            return 'rate_limited';
        }
        $row = DB::one('SELECT id, password_hash, disabled FROM users WHERE username = ?', [$username]);
        // Verify even when the user is missing, so timing does not reveal which usernames exist.
        $hash = $row['password_hash'] ?? '$argon2id$v=19$m=65536,t=4,p=1$c2FsdHNhbHRzYWx0$AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $ok = password_verify($password, $hash);
        if ($row === null || !$ok || (int) $row['disabled'] === 1) {
            Audit::write(null, null, 'login.failed', 'user', null, 'username=' . substr($username, 0, 40), $ip);
            return 'invalid';
        }
        Session::regenerate();
        $_SESSION['uid'] = (int) $row['id'];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        self::$user = null;
        Audit::write((int) $row['id'], null, 'login.ok', 'user', (string) $row['id'], null, $ip);
        return true;
    }

    public static function logout(): void
    {
        $uid = (int) ($_SESSION['uid'] ?? 0);
        if ($uid > 0) {
            Audit::write($uid, null, 'logout', 'user', (string) $uid, null, null);
        }
        Session::destroy();
        self::$user = null;
    }

    /** Returns the user or null. Callers redirect to the login page on null. */
    public static function require(string $perm): ?array
    {
        $user = self::user();
        if ($user === null) {
            return null;
        }
        return ACL::can((string) $user['role_code'], $perm) ? $user : false;
    }
}
