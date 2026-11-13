<?php

declare(strict_types=1);

namespace JonatasFenske\Optimizer\Tests;

use JonatasFenske\Optimizer\SchemaOrg;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários do SchemaOrg JSON-LD para PHP 8.5
 */
final class SchemaOrgTest extends TestCase
{
 private SchemaOrg $schema;

 protected function setUp(): void
 {
 $this->schema = new SchemaOrg();
 }

 public function testArticleGeneratesValidJsonLd(): void
 {
 $html = $this->schema
 ->article(
 headline: 'Título do Artigo',
 description: 'Descrição do artigo',
 url: 'https://example.com/artigo',
 author: 'João Silva',
 datePublished: new DateTimeImmutable('2025-06-15T10:00:00-03:00'),
 publisher: ['name' => 'Meu Blog', 'logo' => 'https://example.com/logo.png'],
 )
 ->render();

 self::assertStringContainsString('<script type="application/ld+json">', $html);
 self::assertStringContainsString('"@type":"Article"', $html);
 self::assertStringContainsString('"headline":"Título do Artigo"', $html);
 self::assertStringContainsString('"@context":"https://schema.org"', $html);
 self::assertStringContainsString('"publisher"', $html);
 }

 public function testArticleWithBlogPostingType(): void
 {
 $html = $this->schema
 ->article(
 headline: 'Post',
 description: 'Desc',
 url: 'https://example.com/post',
 type: 'BlogPosting',
 )
 ->render();

 self::assertStringContainsString('"@type":"BlogPosting"', $html);
 }

 public function testProductWithOffer(): void
 {
 $html = $this->schema
 ->product(
 name: 'Camiseta PHP',
 description: 'Camiseta oficial',
 url: 'https://example.com/camiseta',
 price: 79.90,
 currency: 'BRL',
 availability: 'InStock',
 sku: 'CAM-001',
 brand: 'DevStore',
 )
 ->render();

 self::assertStringContainsString('"@type":"Product"', $html);
 self::assertStringContainsString('"price":79.9', $html);
 self::assertStringContainsString('"priceCurrency":"BRL"', $html);
 self::assertStringContainsString('"sku":"CAM-001"', $html);
 self::assertStringContainsString('"brand"', $html);
 self::assertStringContainsString('InStock', $html);
 }

 public function testProductWithoutPrice(): void
 {
 $schemas = $this->schema
 ->product(name: 'Grátis', description: 'Sem preço', url: 'https://example.com/free')
 ->toArray();

 self::assertArrayNotHasKey('offers', $schemas[0]);
 }

 public function testBreadcrumbList(): void
 {
 $html = $this->schema
 ->breadcrumbList([
 ['name' => 'Home', 'url' => 'https://example.com'],
 ['name' => 'Blog', 'url' => 'https://example.com/blog'],
 ['name' => 'Artigo', 'url' => 'https://example.com/blog/artigo'],
 ])
 ->render();

 self::assertStringContainsString('"@type":"BreadcrumbList"', $html);
 self::assertStringContainsString('"position":1', $html);
 self::assertStringContainsString('"position":3', $html);
 self::assertStringContainsString('"name":"Home"', $html);
 }

 public function testFaqPage(): void
 {
 $html = $this->schema
 ->faqPage([
 ['question' => 'O que é PHP?', 'answer' => 'Uma linguagem de programação'],
 ['question' => 'PHP é gratuito?', 'answer' => 'Sim, é open source'],
 ])
 ->render();

 self::assertStringContainsString('"@type":"FAQPage"', $html);
 self::assertStringContainsString('"@type":"Question"', $html);
 self::assertStringContainsString('"@type":"Answer"', $html);
 self::assertStringContainsString('O que é PHP?', $html);
 }

 public function testLocalBusiness(): void
 {
 $html = $this->schema
 ->localBusiness(
 name: 'Café Código',
 address: 'Rua Dev, 123',
 city: 'São Paulo',
 state: 'SP',
 postalCode: '01000-000',
 country: 'BR',
 telephone: '+55 11 9999-9999',
 url: 'https://cafecodigo.com.br',
 latitude: -23.5505,
 longitude: -46.6333,
 type: 'Restaurant',
 )
 ->render();

 self::assertStringContainsString('"@type":"Restaurant"', $html);
 self::assertStringContainsString('"@type":"PostalAddress"', $html);
 self::assertStringContainsString('"addressLocality":"São Paulo"', $html);
 self::assertStringContainsString('"@type":"GeoCoordinates"', $html);
 self::assertStringContainsString('-23.5505', $html);
 }

 public function testEvent(): void
 {
 $html = $this->schema
 ->event(
 name: 'PHP Conference Brasil',
 description: 'Maior evento de PHP do Brasil',
 startDate: new DateTimeImmutable('2025-11-15T09:00:00-03:00'),
 endDate: new DateTimeImmutable('2025-11-16T18:00:00-03:00'),
 location: 'Centro de Convenções',
 locationAddress: 'Av. Paulista, 1000, São Paulo',
 price: 299.00,
 url: 'https://phpconf.com.br',
 )
 ->render();

 self::assertStringContainsString('"@type":"Event"', $html);
 self::assertStringContainsString('"@type":"Place"', $html);
 self::assertStringContainsString('"price":299', $html);
 self::assertStringContainsString('startDate', $html);
 self::assertStringContainsString('endDate', $html);
 }

 public function testMultipleSchemasUseGraph(): void
 {
 $html = $this->schema
 ->article(headline: 'Artigo', description: 'Desc', url: 'https://example.com/a')
 ->breadcrumbList([['name' => 'Home', 'url' => 'https://example.com']])
 ->render();

 self::assertStringContainsString('"@graph"', $html);
 self::assertStringContainsString('"@type":"Article"', $html);
 self::assertStringContainsString('"@type":"BreadcrumbList"', $html);
 }

 public function testSingleSchemaDoesNotUseGraph(): void
 {
 $html = $this->schema
 ->article(headline: 'Único', description: 'Desc', url: 'https://example.com/u')
 ->render();

 self::assertStringNotContainsString('"@graph"', $html);
 self::assertStringContainsString('"@type":"Article"', $html);
 }

 public function testRenderReturnsEmptyWhenNoSchemas(): void
 {
 self::assertSame('', $this->schema->render());
 }

 public function testClearRemovesAllSchemas(): void
 {
 $this->schema
 ->article(headline: 'Test', description: 'Desc', url: 'https://example.com')
 ->clear();

 self::assertSame([], $this->schema->toArray());
 self::assertSame('', $this->schema->render());
 }

 public function testRawSchema(): void
 {
 $html = $this->schema
 ->raw(['@type' => 'WebSite', 'name' => 'Meu Site', 'url' => 'https://example.com'])
 ->render();

 self::assertStringContainsString('"@type":"WebSite"', $html);
 self::assertStringContainsString('"name":"Meu Site"', $html);
 }

 public function testFluentInterface(): void
 {
 $result = $this->schema
 ->article(headline: 'A', description: 'D', url: 'https://a.com')
 ->product(name: 'P', description: 'D', url: 'https://p.com')
 ->breadcrumbList([['name' => 'H', 'url' => 'https://h.com']])
 ->faqPage([['question' => 'Q?', 'answer' => 'A']]);

 self::assertInstanceOf(SchemaOrg::class, $result);
 self::assertCount(4, $result->toArray());
 }

 public function testPrettyPrintOutput(): void
 {
 $html = $this->schema
 ->article(headline: 'Pretty', description: 'Desc', url: 'https://example.com')
 ->render(pretty: true);

 // Pretty print contém quebras de linha e indentação
 self::assertStringContainsString("\n", $html);
 self::assertStringContainsString('    ', $html);
 }

 public function testDateAsStringIsPassedThrough(): void
 {
 $schemas = $this->schema
 ->article(
 headline: 'Test',
 description: 'Desc',
 url: 'https://example.com',
 datePublished: '2025-01-01T00:00:00+00:00',
 )
 ->toArray();

 self::assertSame('2025-01-01T00:00:00+00:00', $schemas[0]['datePublished']);
 }

 public function testNullDatesDefaultToCurrentTime(): void
 {
 $schemas = $this->schema
 ->article(headline: 'Test', description: 'Desc', url: 'https://example.com')
 ->toArray();

 // datePublished e dateModified devem estar preenchidos com data atual
 self::assertNotEmpty($schemas[0]['datePublished']);
 self::assertNotEmpty($schemas[0]['dateModified']);
 }

 public function testProductAdditionalProperties(): void
 {
 $schemas = $this->schema
 ->product(
 name: 'Notebook',
 description: 'Gamer',
 url: 'https://example.com/nb',
 additionalProperties: [
 ['name' => 'RAM', 'value' => '16GB'],
 ['name' => 'SSD', 'value' => '512GB'],
 ],
 )
 ->toArray();

 self::assertCount(2, $schemas[0]['additionalProperty']);
 self::assertSame('RAM', $schemas[0]['additionalProperty'][0]['name']);
 }
}