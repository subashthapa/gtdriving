<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'create courses']);
        Permission::create(['name' => 'assign instructors']);
        Permission::create(['name' => 'view courses']);

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'SuperAdmin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo(['manage users', 'create courses', 'assign instructors']);

        $instructor = Role::create(['name' => 'Instructor']);
        $instructor->givePermissionTo(['create courses', 'view courses']);

        $learner = Role::create(['name' => 'Learner']);
        $learner->givePermissionTo(['view courses']);
    }
}
