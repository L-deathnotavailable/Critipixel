<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\NumberOfRatingPerValue;
use PHPUnit\Framework\TestCase;

class NumberOfRatingPerValueTest extends TestCase
{
    /**
     * @dataProvider ratingsProvider
     */
    public function testNumberOfRatingsPerValue(array $ratings, array $expected): void
    {
        $numberOfRatingsPerValue = new NumberOfRatingPerValue();

        foreach ($ratings as $rating) {
            match ($rating) {
                1 => $numberOfRatingsPerValue->increaseOne(),
                2 => $numberOfRatingsPerValue->increaseTwo(),
                3 => $numberOfRatingsPerValue->increaseThree(),
                4 => $numberOfRatingsPerValue->increaseFour(),
                5 => $numberOfRatingsPerValue->increaseFive(),
            };
        }

        self::assertSame($expected[1], $numberOfRatingsPerValue->getNumberOfOne());
        self::assertSame($expected[2], $numberOfRatingsPerValue->getNumberOfTwo());
        self::assertSame($expected[3], $numberOfRatingsPerValue->getNumberOfThree());
        self::assertSame($expected[4], $numberOfRatingsPerValue->getNumberOfFour());
        self::assertSame($expected[5], $numberOfRatingsPerValue->getNumberOfFive());
    }

    public static function ratingsProvider(): array
    {
        return [
            'aucune note' => [
                [],
                [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            ],
            'une note de chaque valeur' => [
                [1, 2, 3, 4, 5],
                [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1],
            ],
            'plusieurs notes similaires' => [
                [5, 5, 5, 4, 3],
                [1 => 0, 2 => 0, 3 => 1, 4 => 1, 5 => 3],
            ],
            'jeu mal noté' => [
                [1, 1, 2, 2, 2],
                [1 => 2, 2 => 3, 3 => 0, 4 => 0, 5 => 0],
            ],
        ];
    }
}