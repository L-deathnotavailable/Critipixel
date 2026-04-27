<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Tag;
use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    /**
     * @dataProvider provideTagFilters
     */
    public function testShouldFilterVideoGamesByTags(array $tags, int $expectedCount, array $expectedTitles, bool $useRawTagIds = false): void
    {
        $parameters = [];

        if ($tags !== []) {
            $parameters['filter']['tags'] = $useRawTagIds ? $tags : $this->resolveTagIds($tags);
        }

        $this->get('/', $parameters);

        self::assertResponseIsSuccessful();
        self::assertSelectorCount($expectedCount, 'article.game-card');

        foreach ($expectedTitles as $expectedTitle) {
            self::assertSelectorTextContains('.game-card-title', $expectedTitle);
        }
    }

    public static function provideTagFilters(): iterable
    {
        yield 'no tag' => [[], 10, []];
        yield 'single tag' => [['Cyberpunk'], 1, ['Neon Abyss Runner']];
        yield 'multiple tags on a game' => [['Fantasy', 'Exploration'], 1, ['Elder Grove']];
        yield 'unknown tag' => [[999999], 0, [], true];
    }

    /**
     * @param string[] $tagNames
     * @return int[]
     */
    private function resolveTagIds(array $tagNames): array
    {
        $tagRepository = $this->getEntityManager()->getRepository(Tag::class);
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tag = $tagRepository->findOneBy(['name' => $tagName]);

            self::assertNotNull($tag, sprintf('Tag "%s" should exist in fixtures.', $tagName));
            self::assertNotNull($tag->getId(), sprintf('Tag "%s" should have an identifier.', $tagName));

            $tagIds[] = $tag->getId();
        }

        return $tagIds;
    }
}
