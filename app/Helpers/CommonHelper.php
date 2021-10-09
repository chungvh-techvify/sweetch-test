<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class CommonHelper
{
    public static function printMemoryUsage()
    {
        /* Currently, used memory */
        $mem_usage = round(memory_get_usage() / 1024);
        /* Peak memory usage */
        $mem_peak = round(memory_get_peak_usage() / 1024);

        echo "The script is now using: \033[31m".$mem_usage."KB\033[0m of memory.\n";
        echo "Peak usage: \033[31m".$mem_peak."KB\033[0m of memory.\n\n";
    }

    public static function logMemoryUsage()
    {
        /* Currently, used memory */
        $mem_usage = round(memory_get_usage() / 1024);
        /* Peak memory usage */
        $mem_peak = round(memory_get_peak_usage() / 1024);

        Log::info("The script is now using: ".$mem_usage."KB of memory.");
        Log::info("Peak usage: ".$mem_usage."KB of memory.");
    }
}
