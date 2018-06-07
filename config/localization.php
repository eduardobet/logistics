<?php

return [

    /* -----------------------------------------------------------------
     |  Settings
     | -----------------------------------------------------------------
 */

    'supported-locales' => ['en', 'es', 'fr'],

    'accept-language-header' => true,

    'hide-default-in-url' => false,

    'facade' => 'Localization',

    /* -----------------------------------------------------------------
     |  Route
     | -----------------------------------------------------------------
     */

    'route' => [
        'middleware' => [
            'localization-session-redirect' => true,
            'localization-cookie-redirect' => false,
            'localization-redirect' => true,
            'localized-routes' => true,
            'translation-redirect' => true,
        ],
    ],

    /* -----------------------------------------------------------------
     |  Ignored URI from localization
     | -----------------------------------------------------------------
     */

    'ignored-uri' => [
        //
    ],

    /* -----------------------------------------------------------------
     |  Locales
     | -----------------------------------------------------------------
     */

    'locales' => [
        'en' => [
            'name' => 'English',
            'script' => 'Latn',
            'dir' => 'ltr',
            'native' => 'English',
            'regional' => 'en_US',
        ],
        'es' => [
            'name' => 'Spanish',
            'script' => 'Latn',
            'dir' => 'ltr',
            'native' => 'Español',
            'regional' => 'es_ES',
        ],
    ],
];
