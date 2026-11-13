<?php

declare(strict_types=1);

namespace JonatasFenske\Optimizer;

use SimpleXMLIterator;
use stdClass;

/**
 * Classe base para geração de meta tags modernizada para PHP 8.5
 * Remove dependência de JetBrains\PhpStorm\Pure e adiciona tipagem estrita
 *
 * @property string $title
 * @property string $description
 * @property string $url
 * @property string $image
 */
class MetaTags
{
    protected SimpleXMLIterator $meta;
    protected ?stdClass $data = null;

    /** @var array<string, string> Prefixos de meta tags por atributo */
    protected array $tags = [
        'property' => 'og',
        'name' => 'twitter',
    ];

    public function __construct()
    {
        $this->meta = new SimpleXMLIterator('<meta/>');
    }

    public function __set(string $name, mixed $value): void
    {
        if ($this->data === null) {
            $this->data = new stdClass();
        }

        $this->data->$name = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data->$name);
    }

    public function __get(string $name): mixed
    {
        return $this->data->$name ?? null;
    }

    /**
     * Define dados básicos de SEO/SMO
     */
    public function data(
        ?string $title = null,
        ?string $desc = null,
        ?string $url = null,
        ?string $image = null,
    ): ?object {
        if ($title !== null) {
            $this->title = $title;
        }
        if ($desc !== null) {
            $this->description = $desc;
        }
        if ($url !== null) {
            $this->url = $url;
        }
        if ($image !== null) {
            $this->image = $image;
        }

        return $this->data;
    }

    /**
     * Retorna o objeto SimpleXMLIterator interno
     */
    public function meta(): SimpleXMLIterator
    {
        return $this->meta;
    }

    /**
     * Renderiza todas as meta tags como string HTML
     */
    public function render(): string
    {
        $render = '';

        for ($this->meta->rewind(); $this->meta->valid(); $this->meta->next()) {
            $current = $this->meta->current();
            $render .= $current->asXML();
        }

        return urldecode($render);
    }

    /**
     * Retorna array de debug das meta tags geradas
     *
     * @return array<string>
     */
    public function debug(bool $sort = true): array
    {
        $debug = explode('&', implode('>&<', explode('><', $this->render())));

        if ($sort) {
            rsort($debug);
        }

        return $debug;
    }

    /**
     * Adiciona meta tag com atributos dinâmicos
     *
     * @param string $meta Nome do atributo (property, name, itemprop)
     * @param array<string, string> $attributes Pares nome=>conteúdo
     */
    protected function buildMeta(string $meta, array $attributes): void
    {
        foreach ($attributes as $name => $content) {
            $add = $this->meta->addChild('meta');
            $add->addAttribute($meta, $name);
            $add->addAttribute('content', $content);
        }
    }

    /**
     * Adiciona tag HTML genérica (title, etc.)
     */
    protected function buildTag(string $tagName, string $tagContent): void
    {
        $this->meta->addChild($tagName, htmlspecialchars($tagContent, ENT_XML1, 'UTF-8'));
    }

    /**
     * Adiciona tag link (canonical, etc.)
     */
    protected function buildLink(string $rel, string $href): void
    {
        $link = $this->meta->addChild('link');
        $link->addAttribute('rel', $rel);
        $link->addAttribute('href', $href);
    }

    /**
     * Filtra string para uso seguro em meta tags
     */
    protected function filter(string $string): string
    {
        return urlencode(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS));
    }
}