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
        return array_pop(array_keys($data));
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

        $key = array_search($data, $value);

        if (false !== $key) {
            unset($data[$key]);
        }
    }
}
