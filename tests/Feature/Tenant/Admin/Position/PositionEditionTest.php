<?php

namespace Tests\Feature\Tenant\Admin\Branch;

use Tests\TestCase;
use Logistics\DB\User;
use Logistics\DB\Tenant\Branch;
use Logistics\DB\Tenant\Position;
use Illuminate\Foundation\Testing\WithFaker;
use Logistics\DB\Tenant\Tenant as TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PositionEditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_login_if_not_logged_in_administrator()
    {
        // $this->withoutExceptionHandling();
        $tenant = factory(TenantModel::class)->create();

        $response = $this->get(route('tenant.admin.position.edit', [$tenant->domain, 1]));
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
    }

    /** @test */
    public function position_can_only_be_edited_by_authenticated_admin()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->get(route('tenant.admin.position.edit', [ $tenant->domain, 1]));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function position_can_only_be_edited_by_admin()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $employee = factory(User::class)->states('employee')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($employee)->get(route('tenant.admin.position.edit', [ $tenant->domain, 1]));
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.auth.get.login', $tenant->domain));
        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function position_cannot_be_edited_with_invalid_inputs()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);

        $response = $this->actingAs($admin)->patch(route('tenant.admin.position.update', [$tenant->domain, 1]), [
            'id' => 1
        ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.position.edit', [$tenant->domain, 1]));

        $response->assertSessionHasErrors([
            'name',
            'status',
        ]);
    }

    /** @test */
    public function position_name_must_be_unique()
    {
        // $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $positionA = factory(Position::class)->create(['tenant_id' => $tenant->id]);
        $positionB = factory(Position::class)->create(['tenant_id' => $tenant->id, 'name' => 'Position Name B']);

        $response = $this->actingAs($admin)->patch(route('tenant.admin.position.update', [$tenant->domain, $positionA->id]), [
            'name' => $positionB->name,
            'status' => 'A',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('tenant.admin.position.edit', [$tenant->domain, $positionA->id]));
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function it_successfully_updates_the_position()
    {
        $this->withoutExceptionHandling();

        $tenant = factory(TenantModel::class)->create();
        $branch = factory(Branch::class)->create(['tenant_id' => $tenant->id]);
        $admin = factory(User::class)->states('admin')->create(['tenant_id' =>$tenant->id, ]);
        $position = factory(Position::class)->create(['tenant_id' => $tenant->id]);

        $admin->branches()->sync([$branch->id]);
        
        $response = $this->actingAs($admin)->get(route('tenant.admin.position.edit', [$tenant->domain, $position->id]));
        $response->assertStatus(200);
        $response->assertViewIs('tenant.position.edit');

        $response = $this->actingAs($admin)->patch(route('tenant.admin.position.update', [$tenant->domain, $position->id]), [
            'name' => 'Position Name Updated',
            'id' => $branch->id,
            'description' => 'Description of the position',
            'status' => 'I',
        ]);

        $this->assertDatabaseHas('positions', [
            "tenant_id" => $tenant->id,
            "created_by_code" => null,
            "updated_by_code" => $admin->id,
            'name' => 'Position Name Updated',
            'status' => 'I',
            'description' => 'Description of the position',
        ]);

        $response->assertRedirect(route('tenant.admin.position.list', $tenant->domain));
        $response->assertSessionHas(['flash_success']);
    }
}
