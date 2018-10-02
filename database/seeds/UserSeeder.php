<?php

use Logistics\DB\User;
use Illuminate\Database\Seeder;
use Logistics\DB\Tenant\Tenant;
use Logistics\DB\Tenant\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::whereId(1)->first();
        $miami = $tenant->branches->where('name', '=', 'Miami')->first();
        $prla = $tenant->branches->where('name', '=', 'Los Andes 2')->first();

        $admin1 = factory(User::class)->states('admin')->create([
            'tenant_id' => $tenant->id,
            'email' => '2brainzdev@gmail.com',
            'permissions' => Permission::all()->pluck('slug')->toArray(),
        ]);
        $admin1->branches()->sync([$prla->id]);
        $admin1->branchesForInvoice()->sync([$miami->id]);

        if (!app()->environment('production')) {
            $employee1 = factory(User::class)->states('employee')->create([
                'tenant_id' => $tenant->id,
                'email' => 'employee1@middleton-services.test',
                ]);
            $employee1->branches()->sync([$prla->id]);
            $employee1->branchesForInvoice()->sync([$miami->id]);
        }
    }
}
