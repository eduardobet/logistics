<?php

Breadcrumbs::for('tenant.admin.dashboard.home', function ($trail) {
    $trail->add(__('Dashboard'), route('tenant.home', request()->domain));
});


Breadcrumbs::for('tenant.admin.dashboard', function ($trail) {
    $trail->add(__('Dashboard'), route('tenant.admin.dashboard', request()->domain));
});

//Company
Breadcrumbs::for('tenant.admin.company.edit', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Editing :what', ['what' => __('Company') ]), '');
});

// branches
Breadcrumbs::for('tenant.admin.branch.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Branches'), route('tenant.admin.branch.list', request()->domain));
});
Breadcrumbs::for('tenant.admin.branch.create', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Branches'), route('tenant.admin.branch.list', request()->domain));
    $trail->add(__('Creating :what', ['what' => __('Branch')]), '');
});

Breadcrumbs::for('tenant.admin.branch.edit', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Branches'), route('tenant.admin.branch.list', request()->domain));
    $trail->add(__('Editing :what', ['what' => __('Branch')]), '');
});

//Positions
Breadcrumbs::for('tenant.admin.position.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Positions'), route('tenant.admin.position.list', request()->domain));
});
Breadcrumbs::for('tenant.admin.position.create', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Positions'), route('tenant.admin.position.list', request()->domain));
    $trail->add(__('Creating :what', ['what' => __('Position')]), '');
});

Breadcrumbs::for('tenant.admin.position.edit', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(__('Positions'), route('tenant.admin.position.list', request()->domain));
    $trail->add(__('Editing :what', ['what' => __('Position')]), '');
});


// Employees
Breadcrumbs::for('tenant.admin.employee.list', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(request('list') == 'U' ? __('Users') : __('Employees'), route('tenant.admin.employee.list', [ request()->domain, 'list' => request('list')]));
});
Breadcrumbs::for('tenant.admin.employee.create', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(request('list') == 'U' ? __('Users') : __('Employees'), route('tenant.admin.employee.list', [ request()->domain, 'list' => request('list')]));
    $trail->add(__('Creating :what', ['what' => request('list') == 'U' ? __('User') : __('Employee') ]), '');
});

Breadcrumbs::for('tenant.admin.employee.edit', function ($trail) {
    $trail->parent('tenant.admin.dashboard');
    $trail->add(request('list') == 'U' ? __('Users') : __('Employees'), route('tenant.admin.employee.list', [ request()->domain, 'list' => request('list')]));
    $trail->add(__('Editing :what', ['what' => request('list') == 'U' ? __('User') : __('Employee') ]), '');
});
