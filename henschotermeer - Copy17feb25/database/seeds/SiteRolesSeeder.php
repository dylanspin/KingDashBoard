<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\User;
use App\Profile;
use App\Permission;

class SiteRolesSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $service_role = App\Role::firstOrcreate(['name' => 'service']);
        App\Role::firstOrcreate(['name' => 'manager']);
        App\Role::firstOrcreate(['name' => 'operator']);
        $permission = Permission::firstOrcreate(['name' => 'All Permission']);
        $service_role->givePermissionTo($permission);

       


       

      
    }

}
