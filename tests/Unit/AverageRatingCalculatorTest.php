<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use PHPUnit\Framework\TestCase;

class AverageRatingCalculatorTest extends TestCase
{
    /**
     * @dataProvider ratingsProvider
     */
    public function testAverageRatingCalculation(array $ratings, ?int $expectedAverage): void
    {
        $videoGame = new VideoGame();

        foreach ($ratings as $rating) {
            $review = new Review();
            $review->setRating($rating);

            $videoGame->getReviews()->add($review);
        }

        if ($videoGame->getReviews()->count() === 0) {
            $videoGame->setAverageRating(null);
        } else {
            $sum = 0;

            foreach ($videoGame->getReviews() as $review) {
                $sum += $review->getRating();
            }

            $videoGame->setAverageRating((int) round($sum / $videoGame->getReviews()->count()));
        }

        self::assertSame($expectedAverage, $videoGame->getAverageRating());
    }

    public static function ratingsProvider(): array
    {
        return [
            'aucune note' => [[], null],
            'une seule note' => [[5], 5],
            'plusieurs notes identiques' => [[4, 4, 4], 4],
            'moyenne arrondie vers le haut' => [[5, 4, 4], 4],
            'moyenne basse' => [[1, 2, 2], 2],
            'notes très variées' => [[1, 3, 5, 5], 4],
        ];
    }
}