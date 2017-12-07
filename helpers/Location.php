<?php namespace Octommerce\Courier\Helpers;

class Location 
{
    /**
     * Get location code by given level
     * 1. Province
     * 2. Regency/City
     * 3. District 
     * 4. Village
     *
     * @param string $locationCode
     * @param int $level
     * @return string 
     */
    public static function codeByLevel($locationCode, $level)
    {
        $pattern = implode(array_fill(0, $level, '[0-9]+'), '.');

        preg_match("/^$pattern/", $locationCode, $matches);

        return ($arg = array_shift($matches)) != null ? $arg : '';
    }
}
