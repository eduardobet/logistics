<?php

use Carbon\Carbon;

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
            // $host = request()->getSchemeAndHttpHost();

            return request()->getHttpHost();
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

if (!function_exists('do_forget_cache')) {
    /**
     * Forgets cache. Called on model events.
     *
     * @param Model $model
     * @param array $cacheNames cache keys
     * @param array $xtras      extra keys part form model
     */
    function do_forget_cache($model, array $cacheNames, array $xtras = [])
    {
        $model::saved(function ($mod) use ($cacheNames, $xtras) {
            __do_forget_cache($mod, $cacheNames, $xtras);
        });

        $model::deleted(function ($mod) use ($cacheNames, $xtras) {
            __do_forget_cache($mod, $cacheNames, $xtras);
        });
    }
}

/**
 * Forget cache. Called on model events.
 *
 * @param Model $model
 * @param array $cacheNames cache keys
 * @param array $xtras      extra keys part form model
 */
function __do_forget_cache($mod, $cacheNames, $xtras)
{
    foreach ($cacheNames as $k => $cacheName) {
        $extra_name = '';
        if (isset($xtras[$k])) {
            foreach ($xtras[$k] as $exval) {
                $extra_name .= '.' . $mod->{$exval};
            }
        }
        $key = $cacheName . $extra_name;

        Cache::forget($key);
    }
}

if (!function_exists('do_diff_for_humans')) {
    /**
     * Shows date for humans
     * @param  Carbon $date
     * @param  bool $is_short
     * @return string
     */
    function do_diff_for_humans($date, $is_short = false)
    {
        $txt = (!$is_short) ? 'app.timediff.' : 'app.timediff_s.';

        $other = Carbon::now();

        $delta = abs($other->diffInSeconds($date));

        $divs = [
            'second' => Carbon::SECONDS_PER_MINUTE,
            'minute' => Carbon::MINUTES_PER_HOUR,
            'hour' => Carbon::HOURS_PER_DAY,
            'day' => 30,
            'month' => Carbon::MONTHS_PER_YEAR,
        ];

        $unit = 'year';

        foreach ($divs as $divUnit => $divValue) {
            if ($delta < $divValue) {
                $unit = $divUnit;
                break;
            }
            $delta = floor($delta / $divValue);
        }

        if ($delta == 0) {
            $delta = 1;
        }

        $txt .= $unit;

        $since = trans('app.since_text');

        return \Lang::choice($txt, $delta, compact('delta', 'since'));
    }
}
