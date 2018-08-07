<?php

$type = auth()->check() && auth()->user()->isAdmin() ? 'admin' : 'employee';

Breadcrumbs::for('tenant.employee.dashboard', function ($trail) {
    $trail->add(__('Dashboard'), route('tenant.employee.dashboard', request()->domain));
});

// clients
Breadcrumbs::for('tenant.client.create', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Clients'), route('tenant.client.list', request()->domain));
    $trail->add(__('Creating :what', ['what' => __('Client') ]), route('tenant.client.create', request()->domain));
});
Breadcrumbs::for('tenant.client.list', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Clients'), '');
});
Breadcrumbs::for('tenant.client.edit', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Clients'), route('tenant.client.list', request()->domain));
    $trail->add(__('Editing :what', ['what' => __('Client') ]), route('tenant.client.edit', [request()->domain, request('id')]));
});

// Profile
Breadcrumbs::for('tenant.employee.profile.edit', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Profile'), '');
});

// warehouses
Breadcrumbs::for('tenant.warehouse.list', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Warehouses'), '');
});
Breadcrumbs::for('tenant.warehouse.create', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Warehouses'), route('tenant.warehouse.list', request()->domain));
    $trail->add(__('Creating :what', ['what' => __('Warehouse')]), route('tenant.warehouse.create', request()->domain));
});
Breadcrumbs::for('tenant.warehouse.edit', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Warehouses'), route('tenant.warehouse.list', request()->domain));
    $trail->add(__('Editing :what', ['what' => __('Warehouse')]), route('tenant.warehouse.edit', [request()->domain, request('id'), ]));
});

// invoices
Breadcrumbs::for('tenant.invoice.list', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Invoices'), '');
});
Breadcrumbs::for('tenant.invoice.create', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Invoices'), route('tenant.invoice.list', request()->domain));
    $trail->add(__('Creating :what', ['what' => __('Invoice')]), route('tenant.invoice.create', request()->domain));
});
Breadcrumbs::for('tenant.invoice.edit', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Invoices'), route('tenant.invoice.list', request()->domain));
    $trail->add(__('Editing :what', ['what' => __('Invoice')]), route('tenant.invoice.edit', [request()->domain, request('id'), ]));
});

// payments
Breadcrumbs::for('tenant.payment.list', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Payments'), '');
});

// Mailers
Breadcrumbs::for('tenant.mailer.list', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Mailers'), '');
});
Breadcrumbs::for('tenant.mailer.edit', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Mailers'), route('tenant.mailer.list', request()->domain));
    $trail->add(__('Editing :what', ['what' => __('Mailer')]), route('tenant.mailer.edit', [request()->domain, request('id')]));
});

Breadcrumbs::for('tenant.mailer.create', function ($trail) use ($type) {
    $trail->parent("tenant.{$type}.dashboard");
    $trail->add(__('Mailers'), route('tenant.mailer.list', request()->domain));
    $trail->add(__('Creating :what', ['what' => __('Mailer')]), route('tenant.mailer.create', request()->domain));
});
