<?php
namespace W5D;

/** Role → permission checks, read from config/roles.php. */
final class ACL
{
    private static ?array $map = null;

    private static function map(): array
    {
        return self::$map ??= require W5D_ROOT . '/config/roles.php';
    }

    public static function can(string $role, string $perm): bool
    {
        $roles = self::map()['roles'] ?? [];
        return in_array($perm, $roles[$role]['perms'] ?? [], true);
    }

    /** Sidebar menu keys this role may see, in declared order. */
    public static function menusFor(string $role): array
    {
        $out = [];
        foreach (self::map()['menus'] as $key) {
            if (self::can($role, 'menu.' . $key)) {
                $out[] = $key;
            }
        }
        return $out;
    }

    public static function roles(): array
    {
        return array_keys(self::map()['roles']);
    }

    public static function allCountries(string $role): bool
    {
        return (bool) (self::map()['roles'][$role]['all_countries'] ?? false);
    }
}
