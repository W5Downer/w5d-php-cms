<?php
namespace W5D;

/**
 * Country scope. Every portal action passes through canActOn(): a row may be read or changed
 * only when the actor's country is the row's country, or the actor's role spans all countries.
 */
final class Scope
{
    public static function canActOn(array $actor, int|string|null $rowCountryId): bool
    {
        if (ACL::allCountries((string) $actor['role_code'])) {
            return true;
        }
        if ($rowCountryId === null || $actor['country_id'] === null) {
            return false;
        }
        return (int) $actor['country_id'] === (int) $rowCountryId;
    }

    /** Country id for an ISO code, or null. */
    public static function countryId(string $iso): ?int
    {
        $id = DB::value('SELECT id FROM countries WHERE iso = ?', [strtolower($iso)]);
        return $id === null ? null : (int) $id;
    }
}
