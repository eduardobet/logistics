<?php

Breadcrumbs::for('tenant.admin.dashboard', function ($trail) {
    $trail->add('Dashboard', route('tenant.admin.dashboard'));
});
