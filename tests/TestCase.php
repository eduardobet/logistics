<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Get the rendered email
     *
     * @param  Illuminate\Mail\Mailable $mailable
     * @return string
     */
    public function render($mailable)
    {
        $mailable = $mailable->build();

        return $mailable->render();
    }
}
