<?php

Breadcrumbs::for('tenant.admin.dashboard', function ($trail) {
    $trail->add(__('Dashboard'), route('tenant.admin.dashboard'));
});

// branches
Breadcrumbs::for('tenant.admin.branch.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Branches'), route('tenant.admin.branch.list'));
});
Breadcrumbs::for('tenant.admin.branch.create', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Branches'), route('tenant.admin.branch.list'));
    $trail->add(__('Creating branch'), '');
});

Breadcrumbs::for('tenant.admin.branch.edit', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Branches'), route('tenant.admin.branch.list'));
    $trail->add(__('Editing branch'), '');
});

//Positions
Breadcrumbs::for('tenant.admin.position.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Positions'), route('tenant.admin.position.list'));
});
Breadcrumbs::for('tenant.admin.position.create', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Positions'), route('tenant.admin.position.list'));
    $trail->add(__('Creating position'), '');
});

Breadcrumbs::for('tenant.admin.position.edit', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Positions'), route('tenant.admin.position.list'));
    $trail->add(__('Editing position'), '');
});


// Employees
Breadcrumbs::for('tenant.admin.employee.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Employees'), route('tenant.admin.employee.list'));
});
Breadcrumbs::for('tenant.admin.employee.create', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Employees'), route('tenant.admin.employee.list'));
    $trail->add(__('Creating employee'), '');
});

Breadcrumbs::for('tenant.admin.employee.edit', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Employees'), route('tenant.admin.employee.list'));
    $trail->add(__('Editing employee'), '');
});



// clients
Breadcrumbs::for('tenant.client.create', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Clients'), route('tenant.client.list'));
    $trail->add(__('Creating client'), route('tenant.client.create'));
});
Breadcrumbs::for('tenant.client.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Clients'), '');
});
Breadcrumbs::for('tenant.client.edit', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Clients'), route('tenant.client.list'));
    $trail->add(__('Editing client'), route('tenant.client.edit', request('id')));
});
