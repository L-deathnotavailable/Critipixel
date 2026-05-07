<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
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

        // TODO : Ajouter les tags aux vidéos

        foreach ($videoGames as $videoGame) {
            $manager->persist($videoGame);
        }

        $manager->flush();

        // TODO : Ajouter des reviews aux vidéos

    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
