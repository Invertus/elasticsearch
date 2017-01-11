<?php

use Invertus\Brad\Util\Arrays;
use PHPUnit\Framework\TestCase;

class ArraysTest extends TestCase
{
    /**
     * @param array $data
     * @param $expectedResult
     *
     * @dataProvider getLastKeysTestData
     */
    public function testGetLastKeyReturnsCorrectKeys(array $data, $expectedResult)
    {
        $result = Arrays::getLastKey($data);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param array $data
     * @param mixed $valueToRemove
     * @param array $expectedResult
     *
     * @dataProvider getRemoveValueTestData
     */
    public function testRemoveValueReturnsCorrectValues(array $data, $valueToRemove, $expectedResult)
    {
        Arrays::removeValue($data, $valueToRemove);

        $this->assertEquals($data, $expectedResult);
    }

    public function getLastKeysTestData()
    {
        return [
            [
                [4, 8, 9, 5],
                3,
            ],
            [
                ['test1' => 4, 'test2' =>8, 'test3' =>9,],
                'test3',
            ],
            [
                [],
                null,
            ],
        ];
    }

    public function getRemoveValueTestData()
    {
        return [
            [
                [4, 8, 9, 5],
                3,
                [4, 8, 9, 5]
            ],
            [
                ['test1', 'test2', 'test3'],
                'test2',
                [0 => 'test1', 2 => 'test3'],
            ],
        ];
    }
}
