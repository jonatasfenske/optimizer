<?php

declare(strict_types=1);

/**
 * Exemplo de SEO, Schema.org JSON-LD e Sitemap com Optimizer
 * Execute: php examples/seo_and_schema.php
 */

require __DIR__ . '/../vendor/autoload.php';

use JonatasFenske\Optimizer\Optimizer;
use JonatasFenske\Optimizer\SchemaOrg;
use JonatasFenske\Optimizer\SitemapGenerator;

// 1. Meta Tags + Open Graph + Twitter Cards
echo "=== META TAGS ===\n";
$opt = new Optimizer();
echo $opt
    ->optimize(
        title: 'Como Modernizar PHP para 8.5',
        description: 'Guia completo de modernização com enums, atributos e tipagem estrita',
        url: 'https://meusite.com/blog/php-85',
        image: 'https://meusite.com/img/php85.jpg',
    )
    ->openGraph(siteName: 'Meu Blog', locale: 'pt_BR', schema: 'BlogPosting')
    ->twitterCard(creator: '@jonatasfenske', site: '@meusite', domain: 'meusite.com')
    ->publisher(fbPage: 'meublog')
    ->render();

echo "\n\n";

// 2. Schema.org JSON-LD — Article
echo "=== SCHEMA.ORG ARTICLE ===\n";
echo (new SchemaOrg())
    ->article(
        headline: 'Como Modernizar PHP para 8.5',
        description: 'Guia completo de modernização',
        url: 'https://meusite.com/blog/php-85',
        author: 'Jonatas Fenske',
        datePublished: new DateTimeImmutable('2025-06-15T10:00:00-03:00'),
        publisher: ['name' => 'Meu Blog', 'logo' => 'https://meusite.com/logo.png'],
        type: 'BlogPosting',
    )
    ->render(pretty: true);

echo "\n\n";

// 3. Schema.org JSON-LD — Product
echo "=== SCHEMA.ORG PRODUCT ===\n";
echo (new SchemaOrg())
    ->product(
        name: 'Camiseta PHP 8.5',
        description: 'Edição limitada algodão orgânico',
        url: 'https://loja.meusite.com/camiseta',
        price: 79.90,
        currency: 'BRL',
        availability: 'InStock',
        brand: 'DevStore',
    )
    ->render(pretty: true);

echo "\n\n";

// 4. FAQ Page
echo "=== FAQ PAGE ===\n";
echo (new SchemaOrg())
    ->faqPage([
        ['question' => 'O que é CoffeeCode?', 'answer' => 'Biblioteca de componentes PHP modernizada'],
        ['question' => 'Suporta PHP 8.5?', 'answer' => 'Sim, totalmente reescrito com recursos modernos'],
    ])
    ->render(pretty: true);

echo "\n\n";

// 5. Sitemap.xml
echo "=== SITEMAP.XML ===\n";
$sitemap = new SitemapGenerator('https://meusite.com');
$sitemap
    ->addUrl('/', priority: 1.0, changefreq: 'daily')
    ->addUrl('/blog', priority: 0.8, changefreq: 'weekly')
    ->addUrl('/sobre', priority: 0.5, changefreq: 'monthly');

echo $sitemap->render();

echo "\n\n";

// 6. robots.txt
echo "=== ROBOTS.TXT ===\n";
echo $sitemap->robotsTxt(
    allowAll: false,
    rules: [
        '*' => ['allow' => ['/'], 'disallow' => ['/admin', '/api/internal']],
        'Googlebot' => ['allow' => ['/']],
    ],
);

echo "\n\n✅ Exemplo concluído!\n";