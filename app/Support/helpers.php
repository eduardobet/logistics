<?php

if (!function_exists('get_exe_queries')) {
    function get_exe_queries()
    {
        if (!app()->environment('testing')) {
            $path = storage_path().'/logs/queries.log';

            DB::listen(
                function ($params) use ($path) {
                    $sql = str_replace(array('%', '?'), array('%%', '%s'), $params->sql);
                    $sql = vsprintf($sql, $params->bindings);
                    $time_now = (new DateTime())->format('Y-m-d H:i:s');
                    $log = $time_now.' | '.$sql.' | '.$params->time.'ms'.PHP_EOL;
                    File::append($path, $log);
                }
            );
        }
    }
}

if (!function_exists('get_host')) {
    /**
     * Get host from the request
     * @param string|null $host for testing purpose
     *
     * @return string
     */
    function get_host(string $host = null)
    {
        if (is_null($host)) {
            $host = request()->getSchemeAndHttpHost();
        }

        $hostParts = explode('.', $host);
        $schemeParts = explode(':', $hostParts[0]);

        if (strpos($host, 'localhost') !== false) {
            if ($hostParts[0] == $host) {
                return $host;
            }

            return $schemeParts[0] . '://' .$hostParts[1];
        } else {
            if (strpos($host, 'www') !== false) {
                if (count($hostParts) == 4) {
                    return $schemeParts[0] . '://' .$hostParts[2]. '.' .$hostParts[3];
                }

                if (count($hostParts) == 3) {
                    return $schemeParts[0] . '://' .$hostParts[1]. '.' .$hostParts[2];
                }
            }
        }

        if (count($hostParts) > 2) {
            $host = $schemeParts[0] . '://' .$hostParts[1] . '.' . $hostParts[2];
        }

        return $host;
    }
}

if (!function_exists('array_has_dupes')) {
    /**
     * Checks if array has dups.
     *
     * @param array $array
     *
     * @return bool
     */
    function array_has_dupes($array)
    {
        $trimmedArray = array_map('trim', $array);

        return count(array_keys(array_flip($trimmedArray))) !== count($trimmedArray);
    }
}

if (!function_exists('is_valid_phone')) {
    /**
     * Validates a phone number (507/509).
     *
     * @param string $phone
     *
     * @return bool
     */
    function is_valid_phone($phone)
    {
        return preg_match("/^(00\s)?(\(509\)[\s]|\(507\)[\s])?(\d{3,4})[\s.-]\d{4}$/", trim($phone));
    }
}
