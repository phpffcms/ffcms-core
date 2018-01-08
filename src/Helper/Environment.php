<?php

namespace Ffcms\Core\Helper;

/**
 * Class Environment. Special function to get information about working environment
 * @package Ffcms\Core\Helper
 */
class Environment
{
    /**
     * Get current operation system full name
     * @return string
     */
    public static function osName()
    {
        return php_uname('s');
    }

    /**
     * Get working API type (apache2handler, cgi, fcgi, etc)
     * @return string
     */
    public static function phpSAPI()
    {
        return php_sapi_name();
    }

    /**
     * Get current php version and small build info
     * @return string
     */
    public static function phpVersion()
    {
        return phpversion();
    }

    /**
     * Get load average in percents.
     * @return string
     */
    public static function loadAverage()
    {
        $load = 0;
        if (stristr(PHP_OS, 'win')) {
            // its not a better solution, but no other way to do this
            $cmd = "wmic cpu get loadpercentage /all";
            @exec($cmd, $output);

            // if output is exist
            if ($output) {
                // try to find line with numeric data
                foreach ($output as $line) {
                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
                        $load = $line;
                        break;
                    }
                }
            }
        } else {
            $sys_load = sys_getloadavg(); // get linux load average (1 = 100% of 1 CPU)
            $load = $sys_load[0] * 100; // to percentage
        }

        if ((int)$load <= 0) {
            return 'error';
        }

        return (int)$load . '%';
    }
}
