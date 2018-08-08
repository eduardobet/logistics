<?php

if (!app()->runningInConsole()) {
    get_exe_queries();
}

if (app()->environment('testing')) {
    require __DIR__ . '/all-web-routes.php';
} else {
    Route::localizedGroup(function () {
        require __DIR__ . '/all-web-routes.php';
    });
}
