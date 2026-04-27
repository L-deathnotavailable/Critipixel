<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

class ShowTest extends FunctionalTestCase
{
    public function testShouldShowVideoGame(): void
    {
        $this->get('/jeu-video-0');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Jeu vidéo 0');
    }

    public function testAuthenticatedUserCanAddReview(): void
    {
        $entityManager = $this->getEntityManager();
        $user = $entityManager->getRepository(User::class)->findOneByEmail('user+0@email.com');
        $videoGame = $entityManager->getRepository(VideoGame::class)->findOneBy(['slug' => 'jeu-video-0']);

        $existingReview = $entityManager->getRepository(Review::class)->findOneBy([
            'user' => $user,
            'videoGame' => $videoGame,
        ]);

        if ($existingReview !== null) {
            $entityManager->remove($existingReview);
            $entityManager->flush();
        }

        $this->login();

        $this->get('/jeu-video-0');

        $this->client->submitForm('Poster', [
            'review[rating]' => 5,
            'review[comment]' => 'Très bon jeu testé automatiquement.',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $review = $this->getEntityManager()
            ->getRepository(Review::class)
            ->findOneBy([
                'rating' => 5,
                'comment' => 'Très bon jeu testé automatiquement.',
            ]);

        self::assertNotNull($review);

        $this->client->followRedirect();

        self::assertSelectorNotExists('form[name="review"]');
    }

    public function testAnonymousUserCannotSeeReviewForm(): void
    {
        $this->get('/jeu-video-0');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('form[name="review"]');
    }

    public function testAnonymousUserCannotPostReview(): void
    {
        $this->client->request('POST', '/jeu-video-0', [
            'review' => [
                'rating' => 5,
                'comment' => 'Tentative sans connexion.',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testReviewWithMissingRatingReturnsValidationError(): void
    {
        $this->login('user+1@email.com');

        $this->client->request('POST', '/jeu-video-1', [
            'review' => [
                'comment' => 'Commentaire sans note.',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}