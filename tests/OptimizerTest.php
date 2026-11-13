<?php

declare(strict_types=1);

namespace JonatasFenske\Optimizer\Tests;

use JonatasFenske\Optimizer\Optimizer;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários do componente Optimizer para PHP 8.5
 */
final class OptimizerTest extends TestCase
{
    private Optimizer $optimizer;

    protected function setUp(): void
    {
        $this->optimizer = new Optimizer();
    }

    public function testOptimizeGeneratesBasicMetaTags(): void
    {
        $this->optimizer->optimize(
            title: 'Título de Teste',
            description: 'Descrição de teste para SEO',
            url: 'https://example.com/pagina',
            image: 'https://example.com/imagem.jpg'
        );

        $html = $this->optimizer->render();

        self::assertStringContainsString('<title>', $html);
        self::assertStringContainsString('Título de Teste', $html);
        self::assertStringContainsString('name="description"', $html);
        self::assertStringContainsString('rel="canonical"', $html);
        self::assertStringContainsString('https://example.com/pagina', $html);
    }

    public function testOptimizeGeneratesOpenGraphTags(): void
    {
        $this->optimizer->optimize(
            title: 'OG Title',
            description: 'OG Description',
            url: 'https://example.com',
            image: 'https://example.com/img.jpg'
        );

        $html = $this->optimizer->render();

        self::assertStringContainsString('property="og:title"', $html);
        self::assertStringContainsString('property="og:description"', $html);
        self::assertStringContainsString('property="og:url"', $html);
        self::assertStringContainsString('property="og:image"', $html);
    }

    public function testOptimizeGeneratesTwitterCardTags(): void
    {
        $this->optimizer->optimize(
            title: 'Twitter Title',
            description: 'Twitter Desc',
            url: 'https://example.com',
            image: 'https://example.com/img.jpg'
        );

        $html = $this->optimizer->render();

        self::assertStringContainsString('name="twitter:title"', $html);
        self::assertStringContainsString('name="twitter:description"', $html);
    }

    public function testOptimizeWithNoFollowRobots(): void
    {
        $this->optimizer->optimize(
            title: 'Private Page',
            description: 'No index',
            url: 'https://example.com/private',
            image: 'https://example.com/img.jpg',
            follow: false
        );

        $html = $this->optimizer->render();

        self::assertStringContainsString('noindex, nofollow', $html);
    }

    public function testOptimizeWithIndexFollowRobots(): void
    {
        $this->optimizer->optimize(
            title: 'Public Page',
            description: 'Index this',
            url: 'https://example.com/public',
            image: 'https://example.com/img.jpg',
            follow: true
        );

        $html = $this->optimizer->render();

        self::assertStringContainsString('index, follow', $html);
    }

    public function testOpenGraphAddsSiteNameAndLocale(): void
    {
        $this->optimizer
            ->optimize('Title', 'Desc', 'https://example.com', 'https://example.com/img.jpg')
            ->openGraph(siteName: 'Meu Site', locale: 'pt_BR', schema: 'article');

        $html = $this->optimizer->render();

        self::assertStringContainsString('og:site_name', $html);
        self::assertStringContainsString('og:locale', $html);
        self::assertStringContainsString('pt_BR', $html);
        self::assertStringContainsString('og:type', $html);
        self::assertStringContainsString('article', $html);
    }

    public function testTwitterCardAddsCreatorAndSite(): void
    {
        $this->optimizer
            ->optimize('Title', 'Desc', 'https://example.com', 'https://example.com/img.jpg')
            ->twitterCard(
                creator: '@criador',
                site: '@site',
                domain: 'example.com',
                card: 'summary_large_image'
            );

        $html = $this->optimizer->render();

        self::assertStringContainsString('twitter:card', $html);
        self::assertStringContainsString('summary_large_image', $html);
        self::assertStringContainsString('twitter:creator', $html);
        self::assertStringContainsString('@criador', $html);
        self::assertStringContainsString('twitter:site', $html);
        self::assertStringContainsString('@site', $html);
    }

    public function testPublisherAddsFacebookPage(): void
    {
        $this->optimizer
            ->optimize('Title', 'Desc', 'https://example.com', 'https://example.com/img.jpg')
            ->publisher(fbPage: 'minhapagina', fbAuthor: 'autor');

        $html = $this->optimizer->render();

        self::assertStringContainsString('article:publisher', $html);
        self::assertStringContainsString('facebook.com/minhapagina', $html);
        self::assertStringContainsString('article:author', $html);
        self::assertStringContainsString('facebook.com/autor', $html);
    }

    public function testFacebookAppId(): void
    {
        $this->optimizer
            ->optimize('Title', 'Desc', 'https://example.com', 'https://example.com/img.jpg')
            ->facebook(appId: '123456789');

        $html = $this->optimizer->render();

        self::assertStringContainsString('fb:app_id', $html);
        self::assertStringContainsString('123456789', $html);
    }

    public function testFacebookAdmins(): void
    {
        $this->optimizer
            ->optimize('Title', 'Desc', 'https://example.com', 'https://example.com/img.jpg')
            ->facebook(admins: ['admin1', 'admin2']);

        $html = $this->optimizer->render();

        self::assertStringContainsString('fb:admins', $html);
        self::assertStringContainsString('admin1', $html);
        self::assertStringContainsString('admin2', $html);
    }

    public function testFluentInterfaceReturnsSelf(): void
    {
        $result = $this->optimizer
            ->optimize('Title', 'Desc', 'https://example.com', 'https://example.com/img.jpg')
            ->openGraph('Site')
            ->twitterCard('@user', '@site', 'example.com')
            ->publisher('page');

        self::assertInstanceOf(Optimizer::class, $result);
    }

    public function testDebugReturnsArrayOfTags(): void
    {
        $this->optimizer->optimize(
            'Debug Title',
            'Debug Desc',
            'https://example.com',
            'https://example.com/img.jpg'
        );

        $debug = $this->optimizer->debug();

        self::assertNotEmpty($debug);
    }

    public function testSchemaOrgItempropTags(): void
    {
        $this->optimizer->optimize(
            title: 'Schema Title',
            description: 'Schema Desc',
            url: 'https://example.com',
            image: 'https://example.com/img.jpg'
        );

        $html = $this->optimizer->render();

        self::assertStringContainsString('itemprop="name"', $html);
        self::assertStringContainsString('itemprop="description"', $html);
        self::assertStringContainsString('itemprop="url"', $html);
        self::assertStringContainsString('itemprop="image"', $html);
    }

    public function testDataMagicMethods(): void
    {
        $this->optimizer->optimize(
            'Data Title',
            'Data Desc',
            'https://example.com',
            'https://example.com/img.jpg'
        );

        self::assertSame('Data Title', $this->optimizer->title);
        self::assertSame('Data Desc', $this->optimizer->description);
        self::assertSame('https://example.com', $this->optimizer->url);
        self::assertTrue(isset($this->optimizer->title));
        self::assertFalse(isset($this->optimizer->nonexistent));
    }
}