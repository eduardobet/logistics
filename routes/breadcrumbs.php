<?php

Breadcrumbs::for('tenant.admin.dashboard', function ($trail) {
    $trail->add(__('Dashboard'), route('tenant.admin.dashboard'));
});

Breadcrumbs::for('tenant.admin.branch.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('System'), '');
    $trail->add(__('Branches'), route('tenant.admin.branch.list'));
});

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
