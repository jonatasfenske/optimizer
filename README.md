# JonatasFenske\Optimizer

<p align="center">
 <strong>SEO e meta tags PHP 8.5 com Schema.org JSON-LD, Sitemap e robots.txt</strong>
</p>

<p align="center">
 <img src="https://img.shields.io/badge/PHP-8.5%2B-8892BF?logo=php" alt="PHP 8.5+">
 <img src="https://img.shields.io/badge/license-MIT-blue" alt="MIT License">
</p>

## Instalação

```bash
composer require jonatasfenske/optimizer
```

## Recursos

- **Meta tags completas** — title, description, robots, canonical
- **Open Graph** — og:title, og:description, og:image, og:type, og:locale
- **Twitter Cards** — twitter:card, twitter:site, twitter:creator
- **Schema.org JSON-LD** — Article, Product, BreadcrumbList, FAQPage, LocalBusiness, Event
- **@graph automático** para múltiplos schemas
- **Sitemap.xml generator** com XML válido e escape de caracteres
- **robots.txt generator** com regras por user-agent
- **Interface fluente** — todos os métodos retornam `$this`

## Uso Rápido

### Meta Tags + Open Graph + Twitter Cards

```php
use JonatasFenske\Optimizer\Optimizer;

echo (new Optimizer())
 ->optimize(
 title: 'Título da Página',
 description: 'Descrição para SEO',
 url: 'https://meusite.com/pagina',
 image: 'https://meusite.com/img.jpg',
 )
 ->openGraph(siteName: 'Meu Site', locale: 'pt_BR')
 ->twitterCard(creator: '@user', site: '@site', domain: 'meusite.com')
 ->render();
```

### Schema.org JSON-LD

```php
use JonatasFenske\Optimizer\SchemaOrg;

echo (new SchemaOrg())
 ->article(
 headline: 'Como Modernizar PHP',
 description: 'Guia completo',
 url: 'https://meusite.com/blog/php',
 author: 'João Silva',
 datePublished: new DateTimeImmutable(),
 publisher: ['name' => 'Meu Blog', 'logo' => 'https://meusite.com/logo.png'],
 type: 'BlogPosting',
 )
 ->breadcrumbList([
 ['name' => 'Home', 'url' => 'https://meusite.com'],
 ['name' => 'Blog', 'url' => 'https://meusite.com/blog'],
 ])
 ->render();
```

### Sitemap.xml e robots.txt

```php
use JonatasFenske\Optimizer\SitemapGenerator;

$sitemap = new SitemapGenerator('https://meusite.com');
$sitemap
 ->addUrl('/', priority: 1.0, changefreq: 'daily')
 ->addUrl('/blog', priority: 0.8, changefreq: 'weekly');

file_put_contents('public/sitemap.xml', $sitemap->render());
file_put_contents('public/robots.txt', $sitemap->robotsTxt());
```

## Testes

```bash
composer test
```

## Licença

MIT