<?php
namespace W5D;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Single PDO connection per process. Prepared statements only.
 *
 * Drivers: 'sqlite' (default, file at DB_SQLITE_PATH) and 'mysql' (MySQL 8 or MariaDB 10.6+).
 * Dialect differences live in Sql.php, never in callers. Transactions go through begin()/commit()/
 * rollBack() because SQLite uses BEGIN IMMEDIATE (a single writer, no row locks).
 */
final class DB
{
    public const DRIVERS = ['sqlite', 'mysql'];

    private static ?PDO $pdo = null;
    private static ?string $error = null;

    public static function driver(): string
    {
        $driver = strtolower((string) Config::app('db_driver', 'sqlite'));
        if (!in_array($driver, self::DRIVERS, true)) {
            throw new RuntimeException("DB_DRIVER must be one of: " . implode(', ', self::DRIVERS));
        }
        return $driver;
    }

    public static function isSqlite(): bool
    {
        return self::driver() === 'sqlite';
    }

    /** Absolute path of the SQLite file. Relative paths are resolved from the project root. */
    public static function sqlitePath(): string
    {
        $path = (string) Config::app('db_sqlite_path', 'storage/w5d.sqlite');
        if ($path === '') {
            $path = 'storage/w5d.sqlite';
        }
        return str_starts_with($path, '/') ? $path : W5D_ROOT . '/' . $path;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        if (self::$error !== null) {
            throw new RuntimeException(self::$error);
        }
        try {
            self::$pdo = self::isSqlite() ? self::connectSqlite() : self::connectMysql();
        } catch (PDOException $e) {
            self::$error = 'Database unavailable';
            throw new RuntimeException(self::$error, 0, $e);
        }
        return self::$pdo;
    }

    private static function connectSqlite(): PDO
    {
        $file = self::sqlitePath();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new PDO('sqlite:' . $file, null, null, self::options());
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA synchronous = NORMAL');
        return $pdo;
    }

    private static function connectMysql(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            Env::get('DB_HOST', '127.0.0.1'),
            Env::get('DB_PORT', '3306'),
            Env::get('DB_NAME', 'w5d')
        );
        $opts = self::options() + [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+00:00'",
        ];
        return new PDO($dsn, Env::get('DB_USER', 'w5d'), Env::get('DB_PASSWORD', ''), $opts);
    }

    /** @return array<int,mixed> */
    private static function options(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    /**
     * Starts a write transaction. SQLite takes the write lock up front (BEGIN IMMEDIATE), which
     * serialises writers the same way MySQL row locks do for the registration path.
     */
    public static function begin(): void
    {
        if (self::isSqlite()) {
            self::pdo()->exec('BEGIN IMMEDIATE');
            return;
        }
        self::pdo()->beginTransaction();
    }

    public static function commit(): void
    {
        self::pdo()->commit();
    }

    public static function rollBack(): void
    {
        if (self::inTransaction()) {
            self::pdo()->rollBack();
        }
    }

    /** PDO reports the SQLite transaction state correctly after a manual BEGIN. */
    public static function inTransaction(): bool
    {
        return self::$pdo !== null && self::pdo()->inTransaction();
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
