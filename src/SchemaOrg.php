<?php

declare(strict_types=1);

namespace JonatasFenske\Optimizer;

use DateTimeInterface;
use JsonException;

/**
 * Gerador de Schema.org JSON-LD para Rich Results do Google
 * PHP 8.5: tipagem estrita, readonly, named arguments, match expressions
 */
class SchemaOrg
{
 /** @var array<int, array<string, mixed>> Schemas acumulados */
 private array $schemas = [];

 /**
 * Article / BlogPosting / NewsArticle
 *
 * @param string $headline Título do artigo
 * @param string $description Descrição/resumo
 * @param string $url URL canônica do artigo
 * @param string|null $image URL da imagem principal
 * @param string|null $author Nome do autor
 * @param string|null $authorUrl URL do perfil do autor
 * @param DateTimeInterface|string|null $datePublished Data de publicação
 * @param DateTimeInterface|string|null $dateModified Data de modificação
 * @param array{name: string, logo?: string}|null $publisher Dados do publisher
 * @param string $type Subtipo: Article, BlogPosting, NewsArticle, TechArticle
 * @return static
 */
 public function article(
 string $headline,
 string $description,
 string $url,
 ?string $image = null,
 ?string $author = null,
 ?string $authorUrl = null,
 DateTimeInterface|string|null $datePublished = null,
 DateTimeInterface|string|null $dateModified = null,
 ?array $publisher = null,
 string $type = 'Article',
 ): static {
 $schema = [
 '@type' => $type,
 'headline' => $headline,
 'description' => $description,
 'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
 ];

 if ($image !== null) {
 $schema['image'] = $image;
 }

 if ($author !== null) {
 $authorData = ['@type' => 'Person', 'name' => $author];
 if ($authorUrl !== null) {
 $authorData['url'] = $authorUrl;
 }
 $schema['author'] = $authorData;
 }

 $schema['datePublished'] = $this->formatDate($datePublished);
 $schema['dateModified'] = $this->formatDate($dateModified ?? $datePublished);

 if ($publisher !== null) {
 $pub = ['@type' => 'Organization', 'name' => $publisher['name']];
 if (isset($publisher['logo'])) {
 $pub['logo'] = ['@type' => 'ImageObject', 'url' => $publisher['logo']];
 }
 $schema['publisher'] = $pub;
 }

 $this->schemas[] = $schema;
 return $this;
 }

 /**
 * Product com oferta
 *
 * @param string $name Nome do produto
 * @param string $description Descrição do produto
 * @param string $url URL do produto
 * @param string|null $image URL da imagem
 * @param float|null $price Preço
 * @param string $currency Código da moeda (BRL, USD, EUR)
 * @param string $availability Disponibilidade: InStock, OutOfStock, PreOrder
 * @param string|null $sku SKU do produto
 * @param string|null $brand Marca
 * @param string|null $gtin GTIN/EAN/UPC
 * @param array<int, array{name: string, value: string}>|null $additionalProperties Propriedades adicionais
 * @return static
 */
 public function product(
 string $name,
 string $description,
 string $url,
 ?string $image = null,
 ?float $price = null,
 string $currency = 'BRL',
 string $availability = 'InStock',
 ?string $sku = null,
 ?string $brand = null,
 ?string $gtin = null,
 ?array $additionalProperties = null,
 ): static {
 $schema = [
 '@type' => 'Product',
 'name' => $name,
 'description' => $description,
 'url' => $url,
 ];

 if ($image !== null) {
 $schema['image'] = $image;
 }

 if ($sku !== null) {
 $schema['sku'] = $sku;
 }

 if ($brand !== null) {
 $schema['brand'] = ['@type' => 'Brand', 'name' => $brand];
 }

 if ($gtin !== null) {
 $schema['gtin'] = $gtin;
 }

 if ($price !== null) {
 $schema['offers'] = [
 '@type' => 'Offer',
 'price' => $price,
 'priceCurrency' => $currency,
 'availability' => "https://schema.org/{$availability}",
 'url' => $url,
 ];
 }

 if ($additionalProperties !== null) {
 $props = [];
 foreach ($additionalProperties as $prop) {
 $props[] = [
 '@type' => 'PropertyValue',
 'name' => $prop['name'],
 'value' => $prop['value'],
 ];
 }
 $schema['additionalProperty'] = $props;
 }

 $this->schemas[] = $schema;
 return $this;
 }

 /**
 * BreadcrumbList
 *
 * @param array<int, array{name: string, url: string}> $items Lista de itens [{name, url}, ...]
 * @return static
 */
 public function breadcrumbList(array $items): static
 {
 $listItems = [];
 foreach ($items as $position => $item) {
 $listItems[] = [
 '@type' => 'ListItem',
 'position' => $position + 1,
 'name' => $item['name'],
 'item' => $item['url'],
 ];
 }

 $this->schemas[] = [
 '@type' => 'BreadcrumbList',
 'itemListElement' => $listItems,
 ];

 return $this;
 }

 /**
 * FAQPage
 *
 * @param array<int, array{question: string, answer: string}> $faqs Lista de perguntas e respostas
 * @return static
 */
 public function faqPage(array $faqs): static
 {
 $entities = [];
 foreach ($faqs as $faq) {
 $entities[] = [
 '@type' => 'Question',
 'name' => $faq['question'],
 'acceptedAnswer' => [
 '@type' => 'Answer',
 'text' => $faq['answer'],
 ],
 ];
 }

 $this->schemas[] = [
 '@type' => 'FAQPage',
 'mainEntity' => $entities,
 ];

 return $this;
 }

 /**
 * LocalBusiness
 *
 * @param string $name Nome do negócio
 * @param string $address Endereço completo
 * @param string $city Cidade
 * @param string $state Estado/UF
 * @param string $postalCode CEP
 * @param string $country País
 * @param string|null $telephone Telefone
 * @param string|null $url Website
 * @param string|null $image Logo/imagem
 * @param array<string>|null $openingHours Horários de funcionamento
 * @param float|null $latitude Latitude
 * @param float|null $longitude Longitude
 * @param string $type Subtipo: Restaurant, Store, MedicalBusiness, etc.
 * @return static
 */
 public function localBusiness(
 string $name,
 string $address,
 string $city,
 string $state,
 string $postalCode,
 string $country,
 ?string $telephone = null,
 ?string $url = null,
 ?string $image = null,
 ?array $openingHours = null,
 ?float $latitude = null,
 ?float $longitude = null,
 string $type = 'LocalBusiness',
 ): static {
 $schema = [
 '@type' => $type,
 'name' => $name,
 'address' => [
 '@type' => 'PostalAddress',
 'streetAddress' => $address,
 'addressLocality' => $city,
 'addressRegion' => $state,
 'postalCode' => $postalCode,
 'addressCountry' => $country,
 ],
 ];

 if ($telephone !== null) {
 $schema['telephone'] = $telephone;
 }

 if ($url !== null) {
 $schema['url'] = $url;
 }

 if ($image !== null) {
 $schema['image'] = $image;
 }

 if ($openingHours !== null) {
 $schema['openingHoursSpecification'] = array_map(
 fn(string $hours): array => ['@type' => 'OpeningHoursSpecification', 'opens' => $hours],
 $openingHours
 );
 }

 if ($latitude !== null && $longitude !== null) {
 $schema['geo'] = [
 '@type' => 'GeoCoordinates',
 'latitude' => $latitude,
 'longitude' => $longitude,
 ];
 }

 $this->schemas[] = $schema;
 return $this;
 }

 /**
 * Event
 *
 * @param string $name Nome do evento
 * @param string $description Descrição
 * @param DateTimeInterface|string $startDate Data/hora de início
 * @param DateTimeInterface|string|null $endDate Data/hora de término
 * @param string|null $location Nome do local
 * @param string|null $locationAddress Endereço do local
 * @param string|null $image Imagem do evento
 * @param float|null $price Preço do ingresso
 * @param string $currency Moeda
 * @param string|null $url URL do evento
 * @return static
 */
 public function event(
 string $name,
 string $description,
 DateTimeInterface|string $startDate,
 DateTimeInterface|string|null $endDate = null,
 ?string $location = null,
 ?string $locationAddress = null,
 ?string $image = null,
 ?float $price = null,
 string $currency = 'BRL',
 ?string $url = null,
 ): static {
 $schema = [
 '@type' => 'Event',
 'name' => $name,
 'description' => $description,
 'startDate' => $this->formatDate($startDate),
 ];

 if ($endDate !== null) {
 $schema['endDate'] = $this->formatDate($endDate);
 }

 if ($location !== null) {
 $place = ['@type' => 'Place', 'name' => $location];
 if ($locationAddress !== null) {
 $place['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $locationAddress];
 }
 $schema['location'] = $place;
 }

 if ($image !== null) {
 $schema['image'] = $image;
 }

 if ($price !== null) {
 $schema['offers'] = [
 '@type' => 'Offer',
 'price' => $price,
 'priceCurrency' => $currency,
 'url' => $url ?? '',
 ];
 }

 if ($url !== null) {
 $schema['url'] = $url;
 }

 $this->schemas[] = $schema;
 return $this;
 }

 /**
 * Adiciona schema customizado raw
 *
 * @param array<string, mixed> $schema Schema completo com @type
 * @return static
 */
 public function raw(array $schema): static
 {
 $this->schemas[] = $schema;
 return $this;
 }

 /**
 * Renderiza todos os schemas como tags <script type="application/ld+json">
 *
 * @param bool $pretty Se true, formata JSON com indentação
 * @return string HTML com tags script JSON-LD
 */
 public function render(bool $pretty = false): string
 {
 if ($this->schemas === []) {
 return '';
 }

 $output = '';
 $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
 if ($pretty) {
 $flags |= JSON_PRETTY_PRINT;
 }

 // Se há apenas um schema, renderiza diretamente
 if (count($this->schemas) === 1) {
 $json = $this->encodeSchema($this->schemas[0], $flags);
 $output .= "<script type=\"application/ld+json\">{$json}</script>\n";
 return trim($output);
 }

 // Múltiplos schemas: usa @graph
 $graph = [
 '@context' => 'https://schema.org',
 '@graph' => $this->schemas,
 ];

 try {
 $json = json_encode($graph, $flags | JSON_THROW_ON_ERROR);
 } catch (JsonException) {
 return '';
 }

 $output .= "<script type=\"application/ld+json\">{$json}</script>\n";
 return trim($output);
 }

 /**
 * Retorna todos os schemas como array
 *
 * @return array<int, array<string, mixed>>
 */
 public function toArray(): array
 {
 return $this->schemas;
 }

 /**
 * Limpa todos os schemas registrados
 *
 * @return static
 */
 public function clear(): static
 {
 $this->schemas = [];
 return $this;
 }

 /**
 * Formata data para ISO 8601
 */
 private function formatDate(DateTimeInterface|string|null $date): string
 {
 if ($date === null) {
 return date('c');
 }

 if ($date instanceof DateTimeInterface) {
 return $date->format('c');
 }

 return $date;
 }

 /**
 * Codifica schema individual com @context
 */
 private function encodeSchema(array $schema, int $flags): string
 {
 $schema = array_merge(['@context' => 'https://schema.org'], $schema);

 try {
 return json_encode($schema, $flags | JSON_THROW_ON_ERROR);
 } catch (JsonException) {
 return '{}';
 }
 }
}