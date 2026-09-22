<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;

class GrantRegionPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:grant-region-permissions {--role= : Only grant to this role id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grants the permissions added by the customization work to existing admin roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $groups = [
            'region' => ['region.list', 'region.create', 'region.edit', 'region.delete', 'region.backfill'],
            'carrier' => ['carrier.list', 'carrier.create', 'carrier.edit', 'carrier.delete'],
            'pickup-point' => ['pickup-point.list', 'pickup-point.create', 'pickup-point.edit', 'pickup-point.delete'],
            'service-group' => ['service-group.list', 'service-group.create', 'service-group.edit', 'service-group.delete'],
            'order-history' => ['settings.app.orderHistory'],
            'region-defaults' => ['settings.app.regionDefaults'],
            'sms-gateway' => ['settings.app.smsGateway'],
            'admin-commission' => ['settings.app.adminCommission'],
        ];

        $roleId = $this->option('role');

        if ($roleId != '') {
            $roles = Role::where('id', $roleId)->get();
        } else {
            $roles = Role::all();
        }

        if ($roles->isEmpty()) {
            $this->error('No roles found.');
            return 1;
        }

        $granted = 0;

        foreach ($roles as $role) {

            foreach ($groups as $permission => $routes) {

                for ($i = 0; $i < count($routes); $i++) {

                    $exists = Permission::where('role_id', $role->id)
                        ->where('permission', $permission)
                        ->where('routes', $routes[$i])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    Permission::create([
                        'role_id' => $role->id,
                        'permission' => $permission,
                        'routes' => $routes[$i],
                    ]);

                    $granted++;
                }
            }

            $this->line('Permissions ensured for role: ' . $role->role_name);
        }

        $this->info('Done. ' . $granted . ' permission row(s) created.');

        return 0;
    }
}
