<?php

namespace App\Support;

use App\Models\Department;
use Illuminate\Support\Collection;

/**
 * Helpers for reading the department org chart as a hierarchy.
 *
 * A user is typically attached to every department on their branch — e.g.
 * Ministry > Technical Departments > Animal Health & Reproductive Services.
 * The branch describes where they sit; the *deepest* entry is the unit they
 * actually belong to, and the one their reporting scope should follow.
 * Treating the first-returned (root) department as primary makes every user
 * look like they belong to the whole Ministry.
 */
class DepartmentHierarchy
{
    /**
     * id => parent_id for the whole chart, fetched once.
     *
     * @return array<int, int|null>
     */
    public static function parentMap(): array
    {
        return Department::pluck('parent_id', 'id')->all();
    }

    /**
     * How many levels below the root a department sits (root = 0).
     *
     * Guards against a cycle in parent_id so a bad row cannot hang a request.
     *
     * @param  array<int, int|null>  $parentMap
     */
    public static function depth(int $departmentId, array $parentMap): int
    {
        $depth = 0;
        $seen = [];
        $current = $parentMap[$departmentId] ?? null;

        while ($current !== null && ! isset($seen[$current])) {
            $seen[$current] = true;
            $depth++;
            $current = $parentMap[$current] ?? null;
        }

        return $depth;
    }

    /**
     * Order departments root-first so the LAST entry is the most specific unit.
     *
     * Each row gains a `depth` attribute so clients can pick the deepest without
     * refetching the chart.
     *
     * @param  Collection<int, Department>  $departments
     * @return Collection<int, Department>
     */
    public static function orderRootToLeaf(Collection $departments): Collection
    {
        if ($departments->isEmpty()) {
            return $departments;
        }

        $parentMap = self::parentMap();

        return $departments
            ->each(fn (Department $d) => $d->setAttribute('depth', self::depth($d->id, $parentMap)))
            ->sortBy('depth')
            ->values();
    }

    /**
     * The single department a user should be treated as belonging to: the
     * deepest one on their branch.
     *
     * @param  Collection<int, Department>  $departments
     */
    public static function deepest(Collection $departments): ?Department
    {
        return self::orderRootToLeaf($departments)->last();
    }
}
