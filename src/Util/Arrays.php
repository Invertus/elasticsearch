<?php
/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Invertus\Brad\Util;

/**
 * Class Arrays
 *
 * @package Invertus\Brad\Util
 */
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

    /**
     * Get first n elements of given array
     *
     * @param array $data
     * @param int $numberOfElements
     *
     * @return array
     */
    public static function getFirstElements(array $data, $numberOfElements)
    {
        return array_slice($data, 0, $numberOfElements);
    }
}
