<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();

        $tags = [];

        foreach (['Cyberpunk', 'Exploration', 'Coopération', 'Rogue-like', 'Narratif', 'Gestion', 'Plateforme', 'Fantasy'] as $tagName) {
            $tag = (new Tag())->setName($tagName);
            $manager->persist($tag);
            $tags[] = $tag;
        }

        /** @var VideoGame[] $videoGames */
        $videoGames = array_map(
            fn (int $index): VideoGame => (new VideoGame)
                ->setTitle(sprintf('Jeu vidéo %d', $index))
                ->setDescription($this->faker->paragraphs(10, true))
                ->setReleaseDate(new DateTimeImmutable())
                ->setTest($this->faker->paragraphs(6, true))
                ->setRating(($index % 5) + 1)
                ->setImageName(sprintf('video_game_%d.png', $index))
                ->setImageSize(2_098_872),
            range(0, 50)
        );

        foreach ($videoGames as $index => $videoGame) {
            $videoGame->getTags()->add($tags[$index % count($tags)]);

            if ($index % 3 === 0) {
                $videoGame->getTags()->add($tags[($index + 3) % count($tags)]);
            }

            $manager->persist($videoGame);
        }

        $reviewSeed = 0;

        foreach (array_slice($videoGames, 0, 10) as $videoGame) {
            $ratings = [5, 4, 3, 2, 1];

            foreach ($ratings as $rating) {
                $review = (new Review())
                    ->setVideoGame($videoGame)
                    ->setUser($users[$reviewSeed % count($users)])
                    ->setRating($rating)
                    ->setComment(sprintf('Avis de test #%d', $reviewSeed + 1));

                $manager->persist($review);

                $numberOfRatings = $videoGame->getNumberOfRatingsPerValue();

                match ($rating) {
                    1 => $numberOfRatings->increaseOne(),
                    2 => $numberOfRatings->increaseTwo(),
                    3 => $numberOfRatings->increaseThree(),
                    4 => $numberOfRatings->increaseFour(),
                    5 => $numberOfRatings->increaseFive(),
                };

                $reviewSeed++;
            }

            $videoGame->setAverageRating((int) round(array_sum($ratings) / count($ratings)));
        }

        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
