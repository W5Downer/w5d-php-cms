<?php
/**
 * config/roles.php — the eight account roles and their portal menu permissions (single source).
 * bin/migrate.php seeds the roles, permissions and role_permissions tables from this file.
 * Permissions are menu keys: "menu.<key>". ACL::can() reads this map; the UI sidebar is filtered by it.
 *
 * Scope: owner and admin act on every country. All other roles act only on their own country
 * (users.country_id). Scope::canActOn() enforces this on every action.
 */
$allMenus = [
    'command', 'countries', 'regions', 'recruitment', 'applicants', 'identity', 'macro-services',
    'academies', 'teams', 'weekly-work', 'projects', 'clients', 'quality', 'payroll', 'investors',
    'communications', 'support', 'website', 'variables', 'users', 'reports', 'compliance', 'audit', 'system',
];
$perms = static fn (array $keys): array => array_map(static fn ($k) => 'menu.' . $k, $keys);

return [
    'roles' => [
        'owner' => ['label' => 'Owner', 'perms' => $perms($allMenus), 'all_countries' => true],
        'admin' => ['label' => 'Administrator', 'perms' => $perms(array_values(array_diff($allMenus, ['system', 'users']))), 'all_countries' => true],
        'manager' => ['label' => 'Manager', 'perms' => $perms(['command', 'applicants', 'teams', 'weekly-work', 'quality', 'projects']), 'all_countries' => false],
        'leader' => ['label' => 'Service leader', 'perms' => $perms(['command', 'teams', 'weekly-work', 'quality', 'projects', 'macro-services']), 'all_countries' => false],
        'member' => ['label' => 'Team member', 'perms' => $perms(['weekly-work', 'projects']), 'all_countries' => false],
        'investor' => ['label' => 'Investor', 'perms' => $perms(['reports', 'projects']), 'all_countries' => false],
        'customer' => ['label' => 'Client', 'perms' => $perms(['projects', 'clients']), 'all_countries' => false],
        'user' => ['label' => 'Registered user', 'perms' => $perms(['projects']), 'all_countries' => false],
    ],
    'menus' => $allMenus,
];
