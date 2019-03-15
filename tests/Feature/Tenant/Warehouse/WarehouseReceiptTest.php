<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Mailer;
use Logistics\DB\Tenant\Invoice;
use Illuminate\Support\Facades\Queue;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Jobs\Tenant\SendWarehouseReceiptEmail;

class WarehouseReceiptTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_like_this_tenant_user()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.warehouse.receipt', [$tenant->domain, 1]), []);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function it_successfully_shows_the_warehouse_receipt()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        $this->actingAs($admin);

        \Gate::define('edit-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, ]);

        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
        ]);

        $warehouse = $tenant->warehouses()->create([
            'branch_to' => $branchB->id,
            'branch_from' => $branch->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'type' => 'A',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'branch_id' => $warehouse->branch_to,
            'created_by_code' => $admin->id,
            'updated_by_code' => $admin->id,
            'client_name' => 'The Name of the client',
            'client_email' => 'email@client.com',
            'volumetric_weight' => 8,
            'real_weight' => 9,
            'manual_id' => 9,
            'total' => $mailer->vol_price * 8,
        ]);

        $detail = $invoice->details()->create([
            'qty' => 1,
            'type' => 1,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'vol_weight' => 8,
            'real_weight' => 9,
        ]);

        $responseA = $this->actingAs($admin)->get(route('tenant.warehouse.receipt', [$tenant->domain, $warehouse->id, ]));
        $responseB = $this->actingAs($admin)->get(route('tenant.warehouse.receipt', [$tenant->domain, $warehouse->id, '__send_it' => '1', ]));

        $responseA->assertStatus(200);
        $responseA->assertViewIs('tenant.warehouse.receipt');
        $responseA->assertViewHas([ 'warehouse', 'branchTo', 'mailer', 'client', 'invoice', ]);

        $responseB->assertStatus(200);
        $responseB->assertJson([
            'error' => false,
            'msg' => __("Success"),
        ]);

        Queue::assertPushed(SendWarehouseReceiptEmail::class, function ($job) use ($tenant) {
            return $job->tenant->id === $tenant->id;
        });
    }
}
