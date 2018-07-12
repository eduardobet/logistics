<?php

namespace Tests\Feature\Tenant\Invoice;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\User;

class InvoiceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_employee()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.invoice.list', $tenant->domain));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function employee_can_see_invoice_list()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id, ]);
        $employee = factory(User::class)->states('employee')->create(['tenant_id' => $tenant->id, 'status' => 'A', ]);
        $employee->branches()->sync([$branch->id, ]);

        $response = $this->actingAs($employee)->get(route('tenant.invoice.list', $tenant->domain));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.invoice.index');
        $response->assertViewHas(['invoices']);
    }
}
