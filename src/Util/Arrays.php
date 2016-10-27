<?php

namespace Invertus\Brad\Util;

class Arrays
{
    /**
     * Get last element's key of array
     *
     * @param array $data
     *
     * @return mixed
     */
    public static function getLastKey(array $data)
    {
        $keys = array_keys($data);

        return array_pop($keys);
    }

    /**
     * Remove value from array
     *
     * @param array $data
     * @param mixed $value
     */
    public static function removeValue(array &$data, $value)
    {
        if (empty($data)) {
            return;
        }

        $key = array_search($value, $data);

        if (false !== $key) {
            unset($data[$key]);
        }
    }
}