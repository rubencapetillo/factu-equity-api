<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role_admin = Role::create(['name' => 'Admin']);
        $role_collaborator = Role::create(['name' => 'Collaborator']);

        Permission::create(['name' => 'view-invoices'])->syncRoles([$role_admin, $role_collaborator]);
        Permission::create(['name' => 'upload-invoices'])->assignRole($role_admin);
    }
}
