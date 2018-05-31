<?php

    $host = $_SERVER['HTTP_HOST'];

    $env_dir = __DIR__.'/../envs/';

    $hostParts = explode('.', $host);

    if (count($hostParts) > 2) {
        $host = $hostParts[1] . '.' . $hostParts[2];
    }

    if (file_exists($env_dir.$host));
    {
        $dotenv = new \Dotenv\Dotenv($env_dir, $host);
        $dotenv->load();
    }
