<?php

namespace Tests\Feature\Tenant\Warehouse;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Box;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Client;
use Logistics\DB\Tenant\Mailer;
use Illuminate\Support\Facades\Queue;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\Jobs\Tenant\SendWarehouseCreatedEmail;

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
            'branch_from', 'branch_to', 'type', 'tot_weight', 'tot_packages',
        ]);
    }

    /** @test */
    public function it_validates_the_id_when_manually_provided()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'manual_id' => 'xxx',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'manual_id',
        ]);
    }

    /** @test */
    public function manual_id_shoul_be_unique_by_branch()
    {
        /// $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('create-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        $tenant->warehouses()->create([
            'branch_to' => $branchB->id,
            'branch_from' => $branch->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'manual_id' => 1,
            'type' => 'A',
        ]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'gen_invoice' => 1,
            'tot_packages' => 2,
            'tot_weight' => 21,
            'manual_id' => 1,

            //
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'chk_t_volumetric_weight' => true,
            'total' => $client->vol_price * 21,
            'notes' => 'The notes of the invoice',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14, 'vol_price' => $client->vol_price,  ],
                ['qty' => 1, 'type' => 2, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, 'vol_price' => $client->vol_price, ],
            ]
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.create', $tenant->domain));

        $response->assertSessionHasErrors([
            'manual_id',
        ]);
    }

    /** @test */
    public function it_successfuly_creates_the_warehouse_when_client_pays_volume()
    {
        $this->withoutExceptionHandling();

        // Notification::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('create-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00 ]);

        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.create');
        $response->assertViewHas(['userBranches', 'branches', 'mailers',]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'gen_invoice' => 1,
            'tot_packages' => 2,
            'tot_weight' => 21,
            'created_at' => '2017-01-30',

            //
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'chk_t_volumetric_weight' => true,
            'total' => $client->vol_price * 21,
            'notes' => 'The notes of the invoice',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14, 'vol_price' => $client->vol_price,  ],
                ['qty' => 1, 'type' => 2, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, 'vol_price' => $client->vol_price, ],
            ]
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $this->assertDatabaseHas('warehouses', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'status' => 'A',
            'tot_packages' => 2,
            'tot_weight' => 21,
            'manual_id' => 1,
        ]);

        $this->assertDatabaseHas('invoices', [
            "tenant_id" => $tenant->id,
            "warehouse_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_id' => $branchB->id,
            'client_id' => $client->id,
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'status' => 'A',
            'volumetric_weight' => 21,
            'real_weight' => 23,
            'total' => $client->vol_price * 21,
            'notes' => 'The notes of the invoice',
            'i_using' => 'V',
        ]);

        $wh = $tenant->warehouses()->first();
        $inv = $tenant->invoices()->first();

        $this->assertEquals('2017-01-30', $wh->created_at->format('Y-m-d'));
        $this->assertEquals('2017-01-30', $inv->created_at->format('Y-m-d'));
        $this->assertEquals('2017-02-06', $inv->due_at->format('Y-m-d'));

        tap($branchB->invoices->first()->details->first(), function ($detail) use ($client) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 1);
            $this->assertEquals($detail->length, 12);
            $this->assertEquals($detail->width, 12);
            $this->assertEquals($detail->height, 12);
            $this->assertEquals($detail->vol_weight, 13);
            $this->assertEquals($detail->real_weight, 14);
            $this->assertEquals($detail->real_price, 0);
            $this->assertEquals($detail->vol_price, $client->vol_price);
            $this->assertEquals($detail->total, $client->vol_price * $detail->vol_weight);
        });

        tap($branchB->invoices->first()->details->last(), function ($detail) use ($client) {
            $this->assertEquals($detail->invoice_id, 1);
            $this->assertEquals($detail->qty, 1);
            $this->assertEquals($detail->type, 2);
            $this->assertEquals($detail->length, 10);
            $this->assertEquals($detail->width, 10);
            $this->assertEquals($detail->height, 10);
            $this->assertEquals($detail->vol_weight, 8);
            $this->assertEquals($detail->real_weight, 9);
            $this->assertEquals($detail->real_price, 0);
            $this->assertEquals($detail->vol_price, $client->vol_price);
            $this->assertEquals($detail->total, $client->vol_price * $detail->vol_weight);
        });

        $this->assertCount(1, $branchB->notifications);
    }

    /** @test */
    public function it_successfully_increment_manual_id_when_not_in_migration_mode()
    {
        $this->withoutExceptionHandling();

        // Notification::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('create-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);

        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $tenant->warehouses()->create([
            'branch_to' => $branchB->id,
            'branch_from' => $branch->id,
            'client_id' => $client->id,
            'mailer_id' => $mailer->id,
            'trackings' => '1234',
            'reference' => 'The reference',
            'qty' => 1,
            'manual_id' => 15,
            'type' => 'A',
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.create');
        $response->assertViewHas(['userBranches', 'branches', 'mailers',]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'gen_invoice' => 1,
            'tot_packages' => 2,
            'tot_weight' => 21,

            //
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'chk_t_volumetric_weight' => true,
            'total' => $client->vol_price * 21,
            'notes' => 'The notes of the invoice',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14, 'vol_price' => $client->vol_price,  ],
                ['qty' => 1, 'type' => 2, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, 'vol_price' => $client->vol_price, ],
            ]
        ]);

        $lastWH = $tenant->warehouses->fresh()->last();

        $this->assertEquals(16, $lastWH->manual_id);
    }

    /** @test */
    public function it_successfully_creates_the_warehouse_without_autoincrementing()
    {
        $this->withoutExceptionHandling();

        // Notification::fake();

        $tenant = factory(TenantModel::class)->create(['migration_mode' => true, ]);
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id,]);

        \Gate::define('create-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'vol_price' => 2.00]);

        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.create');
        $response->assertViewHas(['userBranches', 'branches', 'mailers',]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'manual_id' => 5,
            'gen_invoice' => 1,
            'tot_packages' => 2,
            'tot_weight' => 21,

            //
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'chk_t_volumetric_weight' => true,
            'total' => $client->vol_price * 21,
            'notes' => 'The notes of the invoice',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14, 'vol_price' => $client->vol_price,  ],
                ['qty' => 1, 'type' => 2, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, 'vol_price' => $client->vol_price, ],
            ]
        ]);

        $warehouse = $tenant->warehouses->fresh()->where('manual_id', 5)->first();
        $this->assertEquals(5, $warehouse->manual_id);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));
    }

    /** @test */
    public function it_successfuly_creates_the_warehouse_when_client_special_rate()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        \Gate::define('create-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)
            ->create(['tenant_id' => $tenant->id, 'pay_volume' => true, 'special_rate' => true, 'vol_price' => 2.00, 'real_price' => 1.50, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.create');
        $response->assertViewHas(['userBranches', 'branches', 'mailers', ]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'gen_invoice' => 1,
            'tot_packages' => 2,
            'tot_weight' => 21,

            //
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'total' => $client->real_price * 23,
            'notes' => 'The notes of the invoice',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14, ],
                ['qty' => 1, 'type' => 2, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, ],
            ]
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $this->assertDatabaseHas('warehouses', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'status' => 'A',
        ]);

        $this->assertDatabaseHas('invoices', [
            "tenant_id" => $tenant->id,
            "warehouse_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_id' => $branchB->id,
            'client_id' => $client->id,
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'status' => 'A',
            'volumetric_weight' => 21,
            'real_weight' => 23,
            'total' => $client->real_price * 23,
            'notes' => 'The notes of the invoice',
        ]);
    }

    /** @test */
    public function it_successfuly_creates_the_warehouse_when_client_does_not_pay_volume_nor_special_rate()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        \Gate::define('create-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)
            ->create(['tenant_id' => $tenant->id, 'pay_volume' => false, 'special_rate' => false, 'vol_price' => 2.00, 'real_price' => 1.50, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.create');
        $response->assertViewHas(['userBranches', 'branches', 'mailers', ]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'gen_invoice' => 1,
            'tot_packages' => 2,
            'tot_weight' => 21,

            //
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'total' => $branchB->real_price * 23,
            'notes' => 'The notes of the invoice',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 12, 'width' => 12, 'height' => 12, 'real_weight' => 14, ],
                ['qty' => 1, 'type' => 2, 'length' => 10, 'width' => 10, 'height' => 10, 'real_weight' => 9, ],
            ]
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $this->assertDatabaseHas('warehouses', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'status' => 'A',
        ]);

        $this->assertDatabaseHas('invoices', [
            "tenant_id" => $tenant->id,
            "warehouse_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_id' => $branchB->id,
            'client_id' => $client->id,
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'status' => 'A',
            'volumetric_weight' => 21,
            'real_weight' => 23,
            'total' => $branchB->real_price * 23,
            'notes' => 'The notes of the invoice',
        ]);
    }

    /** @test */
    public function it_successfuly_creates_the_warehouse_when_mailer_is_dhl()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        \Gate::define('create-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)
            ->create(['tenant_id' => $tenant->id, 'pay_volume' => false, 'special_rate' => false, 'vol_price' => 2.00, 'real_price' => 1.50, ]);
        $box = factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.create');
        $response->assertViewHas(['userBranches', 'branches', 'mailers', ]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'is_dhl' => true,
            'gen_invoice' => 1,
            'tot_packages' => 2,
            'tot_weight' => 21,

            //
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'total_volumetric_weight' => 21,
            'total_real_weight' => 23,
            'total' => $branchB->dhl_price * 23,
            'notes' => 'The notes of the invoice',
            'invoice_detail' => [
                ['qty' => 1, 'type' => 1, 'length' => 12, 'width' => 12, 'height' => 12, 'vol_weight' => 13, 'real_weight' => 14, ],
                ['qty' => 1, 'type' => 2, 'length' => 10, 'width' => 10, 'height' => 10, 'vol_weight' => 8, 'real_weight' => 9, ],
            ]
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $this->assertDatabaseHas('warehouses', [
            "tenant_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'status' => 'A',
        ]);

        $this->assertDatabaseHas('invoices', [
            "tenant_id" => $tenant->id,
            "warehouse_id" => $tenant->id,
            "created_by_code" => $admin->id,
            'branch_id' => $branchB->id,
            'client_id' => $client->id,
            'client_name' => $client->full_name,
            'client_email' => $client->email,
            'status' => 'A',
            'volumetric_weight' => 21,
            'real_weight' => 23,
            'total' => $branchB->dhl_price * 23,
            'notes' => 'The notes of the invoice',
        ]);
    }

    /** @test */
    public function client_get_an_email_notification_when_warehouse_is_created()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, 'name' => 'Branch to', ]);
        $branchB = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $mailer = factory(Mailer::class)->create(['tenant_id' => $tenant->id, ]);

        $admin = factory(User::class)->states('admin')->create(['tenant_id' => $tenant->id, ]);
        $admin->branches()->sync([$branch->id]);
        $admin->branchesForInvoice()->sync([$branch->id, ]);

        \Gate::define('create-warehouse', function ($admin) {
            return true;
        });

        $client = factory(Client::class)
            ->create(['tenant_id' => $tenant->id, 'pay_volume' => false, 'special_rate' => false, 'vol_price' => 2.00, 'real_price' => 1.50, ]);
        factory(Box::class)->create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'branch_id' => $branchB->id,
            'branch_code' => $branchB->code,
        ]);

        $response = $this->actingAs($admin)->get(route('tenant.warehouse.create', $tenant->domain));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.warehouse.create');
        $response->assertViewHas(['userBranches', 'branches', 'mailers', ]);

        $response = $this->actingAs($admin)->post(route('tenant.warehouse.store', $tenant->domain), [
            'branch_from' => $branch->id,
            'branch_to' => $branchB->id,
            'client_id' => $client->id,
            'reference' => 'The reference',
            'type' => 'A',
            'trackings' => '12345,234434,55645',
            'mailer_id' => $mailer->id,
            'qty' => 2,
            'tot_packages' => 2,
            'tot_weight' => 2,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.warehouse.edit', [$tenant->domain, 1]));

        $warehouse = $tenant->warehouses->fresh()->first();

        Queue::assertPushed(SendWarehouseCreatedEmail::class, function ($job) use ($warehouse) {
            return $job->warehouse->id === $warehouse->id;
        });

    }
}
