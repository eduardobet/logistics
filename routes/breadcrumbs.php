<?php

Breadcrumbs::for('tenant.admin.dashboard', function ($trail) {
    $trail->add(__('Dashboard'), route('tenant.admin.dashboard'));
});

Breadcrumbs::for('tenant.admin.branch.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('System'), '');
    $trail->add(__('Branches'), route('tenant.admin.branch.list'));
});
