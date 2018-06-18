<?php

$type = auth()->check() && auth()->user()->isAdmin() ? 'admin' : 'employee';

// clients
Breadcrumbs::for('tenant.client.create', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Clients'), route('tenant.client.list'));
    $trail->add(__('Creating :what', ['what' => __('Client') ]), route('tenant.client.create'));
});
Breadcrumbs::for('tenant.client.list', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Clients'), '');
});
Breadcrumbs::for('tenant.client.edit', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Clients'), route('tenant.client.list'));
    $trail->add(__('Editing :what', ['what' => __('Client') ]), route('tenant.client.edit', request('id')));
});

// Profile
Breadcrumbs::for('tenant.employee.profile.edit', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Profile'), '');
});
