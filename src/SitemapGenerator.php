<?php

declare(strict_types=1);

namespace JonatasFenske\Optimizer;

use DateTimeInterface;

/**
 * Gerador de Sitemap.xml e robots.txt para SEO técnico
 * PHP 8.5: tipagem estrita, readonly, named arguments
 */
class SitemapGenerator
{
 /** @var array<int, array{loc: string, lastmod?: string, changefreq?: string, priority?: float}> */
 private array $urls = [];

 private string $baseUrl;

 /**
 * @param string $baseUrl URL base do site (sem barra final)
 */
 public function __construct(string $baseUrl)
 {
 $this->baseUrl = rtrim($baseUrl, '/');
 }

 /**
 * Adiciona URL ao sitemap
 *
 * @param string $path Caminho relativo ou URL absoluta
 * @param DateTimeInterface|string|null $lastmod Última modificação
 * @param string|null $changefreq Frequência: always, hourly, daily, weekly, monthly, yearly, never
 * @param float|null $priority Prioridade: 0.0 a 1.0
 * @return static
 */
 public function addUrl(
 string $path,
 DateTimeInterface|string|null $lastmod = null,
 ?string $changefreq = null,
 ?float $priority = null,
 ): static {
 $url = str_starts_with($path, 'http')
            ? $path
            : rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');

 $entry = ['loc' => $url];

 if ($lastmod !== null) {
 $entry['lastmod'] = $lastmod instanceof DateTimeInterface
 ? $lastmod->format('Y-m-d\TH:i:sP')
 : $lastmod;
 }

 if ($changefreq !== null) {
 $validFreqs = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
 if (in_array($changefreq, $validFreqs, true)) {
 $entry['changefreq'] = $changefreq;
 }
 }

 if ($priority !== null) {
 $entry['priority'] = number_format(max(0.0, min(1.0, $priority)), 1, '.', '');
 }

 $this->urls[] = $entry;
 return $this;
 }

 /**
 * Adiciona múltiplas URLs de uma vez
 *
 * @param array<int, array{path: string, lastmod?: DateTimeInterface|string, changefreq?: string, priority?: float}> $urls
 * @return static
 */
 public function addUrls(array $urls): static
 {
 foreach ($urls as $url) {
 $this->addUrl(
 path: $url['path'],
 lastmod: $url['lastmod'] ?? null,
 changefreq: $url['changefreq'] ?? null,
 priority: $url['priority'] ?? null,
 );
 }

 return $this;
 }

 /**
 * Gera XML do sitemap completo
 *
 * @return string XML válido do sitemap
 */
 public function render(): string
 {
 $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
 $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

 foreach ($this->urls as $url) {
 $xml .= "  <url>\n";
 $xml .= "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";

 if (isset($url['lastmod'])) {
 $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
 }

 if (isset($url['changefreq'])) {
 $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
 }

 if (isset($url['priority'])) {
 $xml .= "    <priority>{$url['priority']}</priority>\n";
 }

 $xml .= "  </url>\n";
 }

 $xml .= '</urlset>';

 return $xml;
 }

 /**
 * Gera conteúdo do arquivo robots.txt
 *
 * @param bool $allowAll Se true, permite todos os crawlers; se false, usa regras customizadas
 * @param string|null $sitemapUrl URL do sitemap (null = auto-genera baseada no baseUrl)
 * @param array<string, array{allow?: array<string>, disallow?: array<string>}> $rules Regras por user-agent
 * @return string Conteúdo do robots.txt
 */
 public function robotsTxt(
 bool $allowAll = true,
 ?string $sitemapUrl = null,
 array $rules = [],
 ): string {
 $txt = '';

 if ($rules === [] && $allowAll) {
 $txt .= "User-agent: *\n";
 $txt .= "Allow: /\n";
 } else {
 foreach ($rules as $agent => $agentRules) {
 $txt .= "User-agent: {$agent}\n";

 foreach ($agentRules['allow'] ?? [] as $path) {
 $txt .= "Allow: {$path}\n";
 }

 foreach ($agentRules['disallow'] ?? [] as $path) {
 $txt .= "Disallow: {$path}\n";
 }

 $txt .= "\n";
 }
 }

 $sitemap = $sitemapUrl ?? "{$this->baseUrl}/sitemap.xml";
 $txt .= "Sitemap: {$sitemap}\n";

 return trim($txt);
 }

 /**
 * Retorna contagem de URLs no sitemap
 */
 public function count(): int
 {
 return count($this->urls);
 }

 /**
 * Limpa todas as URLs registradas
 *
 * @return static
 */
 public function clear(): static
 {
 $this->urls = [];
 return $this;
 }

 /**
 * Retorna todas as URLs como array
 *
 * @return array<int, array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>
 */
 public function toArray(): array
 {
 return $this->urls;
 }
}