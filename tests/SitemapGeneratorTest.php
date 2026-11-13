<?php

declare(strict_types=1);

namespace JonatasFenske\Optimizer\Tests;

use JonatasFenske\Optimizer\SitemapGenerator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários do SitemapGenerator para PHP 8.5
 */
final class SitemapGeneratorTest extends TestCase
{
 private SitemapGenerator $generator;

 protected function setUp(): void
 {
 $this->generator = new SitemapGenerator('https://example.com');
 }

 public function testAddUrlWithRelativePath(): void
 {
 $this->generator->addUrl('/pagina');

 $urls = $this->generator->toArray();
 self::assertSame('https://example.com/pagina', $urls[0]['loc']);
 }

 public function testAddUrlWithAbsoluteUrl(): void
 {
 $this->generator->addUrl('https://other.com/page');

 $urls = $this->generator->toArray();
 self::assertSame('https://other.com/page', $urls[0]['loc']);
 }

 public function testAddUrlWithAllOptions(): void
 {
 $date = new DateTimeImmutable('2025-06-15T10:00:00-03:00');
 $this->generator->addUrl(
 path: '/artigo',
 lastmod: $date,
 changefreq: 'weekly',
 priority: 0.8,
 );

 $urls = $this->generator->toArray();
 self::assertStringContainsString('2025-06-15', $urls[0]['lastmod']);
 self::assertSame('weekly', $urls[0]['changefreq']);
 self::assertSame('0.8', $urls[0]['priority']);
 }

 public function testAddUrlWithStringLastmod(): void
 {
 $this->generator->addUrl('/page', lastmod: '2025-01-01');

 $urls = $this->generator->toArray();
 self::assertSame('2025-01-01', $urls[0]['lastmod']);
 }

 public function testInvalidChangefreqIsIgnored(): void
 {
 $this->generator->addUrl('/page', changefreq: 'invalid');

 $urls = $this->generator->toArray();
 self::assertArrayNotHasKey('changefreq', $urls[0]);
 }

 public function testPriorityClampedBetweenZeroAndOne(): void
 {
 $this->generator->addUrl('/low', priority: -0.5);
 $this->generator->addUrl('/high', priority: 2.0);

 $urls = $this->generator->toArray();
 self::assertSame('0.0', $urls[0]['priority']);
 self::assertSame('1.0', $urls[1]['priority']);
 }

 public function testAddUrlsBatch(): void
 {
 $this->generator->addUrls([
 ['path' => '/a', 'priority' => 1.0],
 ['path' => '/b', 'changefreq' => 'daily'],
 ['path' => '/c'],
 ]);

 self::assertSame(3, $this->generator->count());
 }

 public function testRenderGeneratesValidXml(): void
 {
 $this->generator
 ->addUrl('/', priority: 1.0, changefreq: 'daily')
 ->addUrl('/sobre', priority: 0.5);

 $xml = $this->generator->render();

 self::assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
 self::assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $xml);
 self::assertStringContainsString('<loc>https://example.com/</loc>', $xml);
 self::assertStringContainsString('<priority>1.0</priority>', $xml);
 self::assertStringContainsString('<changefreq>daily</changefreq>', $xml);
 self::assertStringContainsString('</urlset>', $xml);
 }

 public function testRenderEscapesSpecialCharacters(): void
 {
 $this->generator->addUrl('/page?a=1&b=2');

 $xml = $this->generator->render();
 self::assertStringContainsString('&amp;', $xml);
 }

 public function testRobotsTxtAllowAll(): void
 {
 $txt = $this->generator->robotsTxt();

 self::assertStringContainsString('User-agent: *', $txt);
 self::assertStringContainsString('Allow: /', $txt);
 self::assertStringContainsString('Sitemap: https://example.com/sitemap.xml', $txt);
 }

 public function testRobotsTxtCustomRules(): void
 {
 $txt = $this->generator->robotsTxt(
 allowAll: false,
 rules: [
 '*' => [
 'allow' => ['/public'],
 'disallow' => ['/admin', '/private'],
 ],
 'Googlebot' => [
 'allow' => ['/'],
 ],
 ],
 );

 self::assertStringContainsString('User-agent: *', $txt);
 self::assertStringContainsString('Disallow: /admin', $txt);
 self::assertStringContainsString('Allow: /public', $txt);
 self::assertStringContainsString('User-agent: Googlebot', $txt);
 }

 public function testRobotsTxtCustomSitemapUrl(): void
 {
 $txt = $this->generator->robotsTxt(sitemapUrl: 'https://cdn.example.com/sitemap.xml');

 self::assertStringContainsString('Sitemap: https://cdn.example.com/sitemap.xml', $txt);
 }

 public function testCountReturnsNumberOfUrls(): void
 {
 self::assertSame(0, $this->generator->count());

 $this->generator->addUrl('/a')->addUrl('/b')->addUrl('/c');

 self::assertSame(3, $this->generator->count());
 }

 public function testClearRemovesAllUrls(): void
 {
 $this->generator->addUrl('/a')->addUrl('/b')->clear();

 self::assertSame(0, $this->generator->count());
 self::assertSame([], $this->generator->toArray());
 }

 public function testFluentInterface(): void
 {
 $result = $this->generator
 ->addUrl('/a')
 ->addUrl('/b')
 ->addUrls([['path' => '/c']])
 ->clear()
 ->addUrl('/d');

 self::assertInstanceOf(SitemapGenerator::class, $result);
 self::assertSame(1, $result->count());
 }

 public function testBaseUrlTrailingSlashIsTrimmed(): void
 {
 $gen = new SitemapGenerator('https://example.com/');
 $gen->addUrl('/page');

 $urls = $gen->toArray();
 self::assertSame('https://example.com/page', $urls[0]['loc']);
 }
}