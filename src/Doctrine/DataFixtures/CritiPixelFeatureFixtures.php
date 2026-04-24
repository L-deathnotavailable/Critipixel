<?php

declare(strict_types=1);

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CritiPixelFeatureFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $users = [];

        $usersData = [
            ['username' => 'NovaPlayer', 'email' => 'nova.player@critipixel.test'],
            ['username' => 'PixelFox', 'email' => 'pixel.fox@critipixel.test'],
            ['username' => 'ArcadeLuna', 'email' => 'arcade.luna@critipixel.test'],
            ['username' => 'RetroNeko', 'email' => 'retro.neko@critipixel.test'],
            ['username' => 'QuestRider', 'email' => 'quest.rider@critipixel.test'],
        ];

        foreach ($usersData as $userData) {
            $user = new User();
            $user
                ->setUsername($userData['username'])
                ->setEmail($userData['email'])
                ->setPlainPassword('Password123!')
                ->setPassword('$2y$13$uTGYJwHtKnxSyVxFckGMZOBh2VjFPcRW3BaX1XawB3lZBCSDe0j4O');

            $manager->persist($user);
            $users[$userData['username']] = $user;
        }

        $tags = [];

        $tagNames = [
            'Cyberpunk',
            'Exploration',
            'Coopération',
            'Rogue-like',
            'Narratif',
            'Gestion',
            'Plateforme',
            'Fantasy',
        ];

        foreach ($tagNames as $tagName) {
            $tag = new Tag();
            $tag->setName($tagName);

            $manager->persist($tag);
            $tags[$tagName] = $tag;
        }

        $games = [];

        $gamesData = [
            [
                'title' => 'Neon Abyss Runner',
                'description' => 'Un jeu de course futuriste dans une ville cyberpunk où chaque virage peut changer le classement.',
                'releaseDate' => '2023-04-12',
                'tags' => ['Cyberpunk', 'Coopération'],
            ],
            [
                'title' => 'Elder Grove',
                'description' => 'Une aventure fantasy centrée sur l’exploration d’une forêt ancienne remplie de secrets.',
                'releaseDate' => '2022-09-21',
                'tags' => ['Fantasy', 'Exploration', 'Narratif'],
            ],
            [
                'title' => 'Moon Factory',
                'description' => 'Un jeu de gestion où le joueur développe une colonie industrielle sur la lune.',
                'releaseDate' => '2024-01-18',
                'tags' => ['Gestion', 'Exploration'],
            ],
            [
                'title' => 'Tiny Dungeon Loop',
                'description' => 'Un rogue-like rapide avec des donjons générés aléatoirement et des combats nerveux.',
                'releaseDate' => '2021-11-05',
                'tags' => ['Rogue-like', 'Fantasy'],
            ],
            [
                'title' => 'Cloud Jumpers',
                'description' => 'Un jeu de plateforme coloré où les joueurs traversent des îles flottantes.',
                'releaseDate' => '2020-06-30',
                'tags' => ['Plateforme', 'Coopération'],
            ],
        ];

        foreach ($gamesData as $gameData) {
            $videoGame = new VideoGame();
            $videoGame
                ->setTitle($gameData['title'])
                ->setDescription($gameData['description'])
                ->setReleaseDate(new DateTimeImmutable($gameData['releaseDate']))
                ->setTest('Test CritiPixel : ' . $gameData['description']);

            foreach ($gameData['tags'] as $tagName) {
                $videoGame->getTags()->add($tags[$tagName]);
            }

            $manager->persist($videoGame);
            $games[$gameData['title']] = $videoGame;
        }

        $reviewsData = [
            ['game' => 'Neon Abyss Runner', 'user' => 'NovaPlayer', 'rating' => 5, 'comment' => 'Ambiance incroyable, gameplay très nerveux.'],
            ['game' => 'Neon Abyss Runner', 'user' => 'PixelFox', 'rating' => 4, 'comment' => 'Très fun en coopération, même si certains circuits sont difficiles.'],

            ['game' => 'Elder Grove', 'user' => 'ArcadeLuna', 'rating' => 5, 'comment' => 'L’univers est magnifique et l’histoire donne envie d’avancer.'],
            ['game' => 'Elder Grove', 'user' => 'RetroNeko', 'rating' => 3, 'comment' => 'Très joli, mais parfois un peu lent.'],

            ['game' => 'Moon Factory', 'user' => 'QuestRider', 'rating' => 4, 'comment' => 'Bonne profondeur de gestion, progression satisfaisante.'],
            ['game' => 'Moon Factory', 'user' => 'NovaPlayer', 'rating' => 2, 'comment' => 'Concept intéressant mais interface un peu confuse.'],

            ['game' => 'Tiny Dungeon Loop', 'user' => 'PixelFox', 'rating' => 5, 'comment' => 'Excellent rogue-like, très addictif.'],
            ['game' => 'Tiny Dungeon Loop', 'user' => 'RetroNeko', 'rating' => 1, 'comment' => null],

            ['game' => 'Cloud Jumpers', 'user' => 'ArcadeLuna', 'rating' => 4, 'comment' => 'Très agréable à jouer, parfait à deux joueurs.'],
            ['game' => 'Cloud Jumpers', 'user' => 'QuestRider', 'rating' => 3, 'comment' => 'Sympa, mais manque un peu de variété.'],
        ];

        foreach ($reviewsData as $reviewData) {
            $review = new Review();
            $review
                ->setVideoGame($games[$reviewData['game']])
                ->setUser($users[$reviewData['user']])
                ->setRating($reviewData['rating'])
                ->setComment($reviewData['comment']);

            $manager->persist($review);

            $numberOfRatings = $games[$reviewData['game']]->getNumberOfRatingsPerValue();

            match ($reviewData['rating']) {
                1 => $numberOfRatings->increaseOne(),
                2 => $numberOfRatings->increaseTwo(),
                3 => $numberOfRatings->increaseThree(),
                4 => $numberOfRatings->increaseFour(),
                5 => $numberOfRatings->increaseFive(),
            };
        }

        foreach ($games as $game) {
            $ratings = [];

            foreach ($reviewsData as $reviewData) {
                if ($reviewData['game'] === $game->getTitle()) {
                    $ratings[] = $reviewData['rating'];
                }
            }

            if ($ratings !== []) {
                $average = (int) round(array_sum($ratings) / count($ratings));
                $game->setAverageRating($average);
                $game->setRating($average);
            }
        }

        $manager->flush();
    }
}