<?php

namespace Invertus\Brad\Util;

/**
 * Class RangeParser
 *
 * @package Invertus\Brad\Util
 */
class RangeParser
{
    /**
     * Split value into ranges
     *
     * @param float $minValue
     * @param float $maxValue
     * @param int $n Number of ranges
     *
     * @return array
     */
    public static function splitIntoRanges($minValue, $maxValue, $n)
    {
        $ranges = [];
        $delta = ($maxValue - $minValue) / $n;

        for ($i = 0; $i < $n; $i++) {
            $min = $minValue + $i * $delta;
            $max = $minValue + ($i + 1) * $delta;

            $ranges[] = [
                'min_range' => $min,
                'max_range' => $max,
            ];
        }

        return $ranges;
    }
}
