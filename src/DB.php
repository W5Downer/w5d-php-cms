<?php
namespace W5D;

use PDO;
use PDOException;
use RuntimeException;

/** Single PDO connection per process. Prepared statements only. */
final class DB
{
    private static ?PDO $pdo = null;
    private static ?string $error = null;

    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        if (self::$error !== null) {
            throw new RuntimeException(self::$error);
        }
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            Env::get('DB_HOST', '127.0.0.1'),
            Env::get('DB_PORT', '3306'),
            Env::get('DB_NAME', 'w5d')
        );
        try {
            self::$pdo = new PDO($dsn, Env::get('DB_USER', 'w5d'), Env::get('DB_PASSWORD', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+00:00'",
            ]);
        } catch (PDOException $e) {
            self::$error = 'Database unavailable';
            throw new RuntimeException(self::$error, 0, $e);
        }
        return self::$pdo;
    }

    public static function available(): bool
    {
        try {
            self::pdo();
            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function value(string $sql, array $params = []): mixed
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $v = $st->fetchColumn();
        return $v === false ? null : $v;
    }

    public static function run(string $sql, array $params = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::pdo()->lastInsertId();
    }

    public static function reset(): void
    {
        self::$pdo = null;
        self::$error = null;
    }
}
