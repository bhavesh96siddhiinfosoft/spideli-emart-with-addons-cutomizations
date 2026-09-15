<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;

/**
 * Ensures the Super Administrator role holds every permission the panel knows
 * about.
 *
 * This panel has no implicit super-admin: `PermissionMiddleware` aborts 403 on
 * any permission the role has no row for, and the menus hide anything the role
 * cannot reach. So a newly added feature is invisible to the Super
 * Administrator until its permission is granted, which is a trap every time.
 *
 * The list is not maintained here. It is read from the role form, which is
 * already the authoritative list of permissions - a feature is not usable
 * without a checkbox there - so a permission added to the form is picked up by
 * this command without anyone remembering to update a second list.
 */
class GrantSuperAdminPermissions extends Command
{
    protected $signature = 'app:grant-super-admin-permissions
                            {--role= : Grant to this role id instead of the Super Administrator}
                            {--dry-run : List what would be granted and change nothing}';

    protected $description = 'Grants every known permission to the Super Administrator role';

    /** The role form that declares every permission checkbox. */
    private const PERMISSION_FORM = 'resources/views/role/save.blade.php';

    public function handle()
    {
        $role = $this->resolveRole();

        if ($role === null) {
            return 1;
        }

        $pairs = $this->permissionsFromForm();

        if (empty($pairs)) {
            $this->error('No permissions found in ' . self::PERMISSION_FORM . '. Has the form changed shape?');
            return 1;
        }

        $dryRun = $this->option('dry-run');
        $granted = 0;

        foreach ($pairs as $pair) {

            $exists = Permission::where('role_id', $role->id)
                ->where('permission', $pair['permission'])
                ->where('routes', $pair['routes'])
                ->exists();

            if ($exists) {
                continue;
            }

            $this->line('  + ' . $pair['permission'] . ' / ' . $pair['routes']);

            if (!$dryRun) {
                Permission::create([
                    'role_id' => $role->id,
                    'permission' => $pair['permission'],
                    'routes' => $pair['routes'],
                ]);
            }

            $granted++;
        }

        $this->info(sprintf(
            '%s %d permission row(s) for role "%s". %d already present.',
            $dryRun ? 'Would create' : 'Created',
            $granted,
            $role->role_name,
            count($pairs) - $granted
        ));

        if ($granted > 0 && !$dryRun) {
            $this->warn('Log out and log in again - permissions are read at login.');
        }

        return 0;
    }

    private function resolveRole()
    {
        $roleId = $this->option('role');

        if ($roleId != '') {
            $role = Role::find($roleId);
            if ($role === null) {
                $this->error('No role with id ' . $roleId . '.');
            }
            return $role;
        }

        /* Matched on the name rather than a hardcoded id, so a panel whose
         * roles were created in a different order still works. */
        $role = Role::where('role_name', 'like', '%Super%')->first();

        if ($role === null) {
            $this->error('No role whose name contains "Super". Pass --role=<id> instead.');
        }

        return $role;
    }

    /**
     * Every permission checkbox in the role form, as permission/routes pairs.
     */
    private function permissionsFromForm()
    {
        $path = base_path(self::PERMISSION_FORM);

        if (!file_exists($path)) {
            $this->error('Cannot read ' . self::PERMISSION_FORM);
            return [];
        }

        $html = file_get_contents($path);

        preg_match_all('/<input\b[^>]*class="permission"[^>]*>/i', $html, $inputs);

        $pairs = [];
        $seen = [];

        foreach ($inputs[0] as $input) {

            if (!preg_match('/\bvalue="([^"]+)"/i', $input, $value)) {
                continue;
            }
            if (!preg_match('/\bname="([^"\[]+)\[\]"/i', $input, $name)) {
                continue;
            }

            $key = $name[1] . '|' . $value[1];

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $pairs[] = ['permission' => $name[1], 'routes' => $value[1]];
        }

        return $pairs;
    }
}
