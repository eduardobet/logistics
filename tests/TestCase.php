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

    /**
     * Set the correct headers for ajax request
     *
     * @return array
     */
    protected function headers()
    {
        $headers = ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'];

        return $headers;
    }
}
