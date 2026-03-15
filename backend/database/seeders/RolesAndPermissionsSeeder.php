<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'manage-users',
            'manage-projects',
            'manage-billing',
            'manage-ai-requests',
            'view-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
            'view-ai-activity',
            'download-code',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        
        // Owner - All permissions
        $owner = Role::create(['name' => 'Owner']);
        $owner->givePermissionTo(Permission::all());

        // Admin - Most permissions, maybe not billing?
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
            'manage-users',
            'manage-projects',
            'manage-ai-requests',
            'view-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
            'view-ai-activity',
            'download-code',
        ]);

        // Developer - Manage projects and AI requests
        $developer = Role::create(['name' => 'Developer']);
        $developer->givePermissionTo([
            'manage-projects',
            'manage-ai-requests',
            'view-projects',
            'create-projects',
            'edit-projects',
            'view-ai-activity',
            'download-code',
        ]);

        // Viewer - View only
        $viewer = Role::create(['name' => 'Viewer']);
        $viewer->givePermissionTo([
            'view-projects',
            'view-ai-activity',
        ]);
    }
}
