<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Mailer;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.warehouse.create', $tenant->domain), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function warehouse_cannot_be_created_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'reception_branch' => 'XXX'
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'branch_from', 'branch_to', 'mailer_id', 'trackings', 'reference', 'qty',
        ]);
    }

    /** @test */
    public function it_creates_the_warehouse_for_direct_comission()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.create');
        $response->assertViewHas(['userBranches', 'branches', 'mailers',]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'mailer_id' => $mailer->id,
            'trackings' => '12345,234434,55645',
            'reference' => 'The reference',
            'qty' => 2,

            //
            'client_name' => 'The client of the direct comission',
            'client_email' => 'direct.comission@client.test',
            'volumetric_weight' => 21,
            'real_weight' => 23,
            'total' => $mailer->vol_price * 21,
            'notes' => 'The notes of the invoice',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 12, 'width' => 12, 'height' => 12, 'real_weight' => 14,  ],
                ['qty' => 1, 'type' => 2, 'length' => 10, 'width' => 10, 'height' => 10, 'real_weight' => 9 , ],
            ]
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $this->assertDatabaseHas('warehouses', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'mailer_id' => $mailer->id,
            'trackings' => '12345,234434,55645',
            'reference' => 'The reference',
            'qty' => 2,
            'status' => 'A',
        ]);

        $this->assertDatabaseHas('invoices', [
            "tenant_id" => $tenant->id,
            "warehouse_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_id' => $branchB->id,
            'client_name' => 'The client of the direct comission',
            'client_email' => 'direct.comission@client.test',
            'status' => 'A',
            'volumetric_weight' => 21,
            'real_weight' => 23,
            'total' => $mailer->vol_price * 21,
            'notes' => 'The notes of the invoice',
        ]);

        tap($branchB->invoices->first()->details->first(), function ($detail) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 1);
            $this->assertEquals($detail->length, 12);
            $this->assertEquals($detail->width, 12);
            $this->assertEquals($detail->height, 12);
            $this->assertEquals($detail->vol_weight, 13);
            $this->assertEquals($detail->real_weight, 14);
        });

        tap($branchB->invoices->first()->details->last(), function ($detail) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 2);
            $this->assertEquals($detail->length, 10);
            $this->assertEquals($detail->width, 10);
            $this->assertEquals($detail->height, 10);
            $this->assertEquals($detail->vol_weight, 8);
            $this->assertEquals($detail->real_weight, 9);
        });
    }

    /** @test */
    public function client_gets_the_invoice_by_email()
    {
        $this->markTestIncomplete('client_gets_the_invoice_by_email');
    }
}
